#!/bin/bash
# =============================================================================
#  SÀMM - Script d'installation et de mise à jour pour hébergement mutualisé
#
#  Usage   : bash deploy.sh [branche]
#  Exemple : bash deploy.sh main
#
#  Première installation (accès SSH requis) :
#    git clone https://github.com/samm-by-pointel/samm.git ~/samm
#    cd ~/samm && bash deploy.sh
#  Le script crée le .env (questions interactives), installe les dépendances,
#  construit les assets, migre la base et insère les données système.
#  Faites ensuite pointer la racine web du domaine sur ~/samm/public.
#
#  Mises à jour : relancez simplement `bash deploy.sh` depuis le dossier.
#
#  Variables d'environnement facultatives :
#    PHP_BIN                 binaire PHP CLI (ex. /opt/cpanel/ea-php84/root/usr/bin/php)
#    COMPOSER_BIN            binaire composer ou chemin vers composer.phar
#    SAMM_REMOTE             dépôt git distant (défaut : origin)
#    SAMM_BACKUP_DIR         dossier des sauvegardes (défaut : ~/.samm-backups/<dossier>)
#    SAMM_BACKUP_KEEP        nombre de sauvegardes conservées (défaut : 10)
#    SAMM_SKIP_ASSETS=1      ne pas construire les assets (public/build envoyé à la main)
#    SAMM_KEEP_NODE_MODULES=1  conserver node_modules après la construction
#
#  Le script ne pousse jamais rien vers le dépôt : il ne fait que récupérer
#  (git fetch) la branche demandée.
# =============================================================================

set -Eeuo pipefail
IFS=$'\n\t'

# ─────────────────────────────────────────────────────────────────────────────
# COULEURS & HELPERS
# ─────────────────────────────────────────────────────────────────────────────
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
BOLD='\033[1m'
NC='\033[0m'

ok()   { echo -e "  ${GREEN}✓${NC} $1"; }
warn() { echo -e "  ${YELLOW}⚠${NC}  $1"; }
err()  { echo -e "  ${RED}✗${NC} $1"; }
step() { echo -e "\n${BLUE}${BOLD}▶️ $1${NC}"; }

# ─────────────────────────────────────────────────────────────────────────────
# CONFIGURATION
# ─────────────────────────────────────────────────────────────────────────────
DEPLOY_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "${DEPLOY_DIR}"

LOG_DIR="${DEPLOY_DIR}/storage/logs"
LOG_FILE="${LOG_DIR}/deploy-$(date +%Y%m%d-%H%M%S).log"
BRANCH="${1:-main}"
REMOTE="${SAMM_REMOTE:-origin}"

# Sauvegardes hors du projet : sur un mutualisé, le projet est parfois placé
# dans la racine web, et un dump de signalements ne doit jamais y atterrir.
BACKUP_DIR="${SAMM_BACKUP_DIR:-${HOME}/.samm-backups/$(basename "${DEPLOY_DIR}")}"
BACKUP_KEEP="${SAMM_BACKUP_KEEP:-10}"

# Logging compatible avec tous les environnements mutualisés (/dev/fd non disponible)
# Auto-relance le script en pipant vers tee pour écrire dans le log ET le terminal
mkdir -p "${LOG_DIR}"
if [ -z "${SAMM_DEPLOY_LOGGED:-}" ]; then
    export SAMM_DEPLOY_LOGGED=1
    set +o pipefail
    # tee -i : un Ctrl+C ne doit pas couper la sortie pendant le rollback
    bash "${DEPLOY_DIR}/$(basename "${BASH_SOURCE[0]}")" "$@" 2>&1 | tee -i -a "${LOG_FILE}"
    exit "${PIPESTATUS[0]}"
fi

# ─────────────────────────────────────────────────────────────────────────────
# BANNIÈRE
# ─────────────────────────────────────────────────────────────────────────────
echo ""
echo -e "${BLUE}${BOLD}╔══════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}${BOLD}║       SÀMM - Installation / mise à jour      ║${NC}"
echo -e "${BLUE}${BOLD}╚══════════════════════════════════════════════╝${NC}"
echo -e "  Dossier     : ${DEPLOY_DIR}"
echo -e "  Branche     : ${REMOTE}/${BRANCH}"
echo -e "  Sauvegardes : ${BACKUP_DIR}"
echo -e "  Log         : ${LOG_FILE}"
echo -e "  Date        : $(date '+%d/%m/%Y %H:%M:%S')"
echo ""

# ─────────────────────────────────────────────────────────────────────────────
# OUTILS
# ─────────────────────────────────────────────────────────────────────────────
PHP="${PHP_BIN:-$(command -v php 2>/dev/null || true)}"
COMPOSER_CMD="${COMPOSER_BIN:-$(command -v composer 2>/dev/null || true)}"
if [ -z "${COMPOSER_CMD}" ] && [ -f "${DEPLOY_DIR}/composer.phar" ]; then
    COMPOSER_CMD="${DEPLOY_DIR}/composer.phar"
fi

# Un composer.phar doit tourner avec le même PHP que l'application : sur un
# mutualisé, le `php` du PATH n'est pas toujours la bonne version.
run_composer() {
    case "${COMPOSER_CMD}" in
        *.phar) "${PHP}" "${COMPOSER_CMD}" "$@" ;;
        *)      "${COMPOSER_CMD}" "$@" ;;
    esac
}

artisan() {
    "${PHP}" artisan "$@"
}

# Supprime les plus anciens fichiers correspondant au motif, en gardant les N plus récents.
purger_anciens() {
    local dossier="$1" motif="$2" garder="$3" fichier
    for fichier in $(ls -1t "${dossier}"/${motif} 2>/dev/null | tail -n +"$((garder + 1))" || true); do
        rm -f "${fichier}"
    done
}

# Node : Vite exige ^20.19.0 ou >= 22.12.0.
node_compatible() {
    command -v node >/dev/null 2>&1 && command -v npm >/dev/null 2>&1 || return 1
    node -e 'const [a, b] = process.versions.node.split(".").map(Number);
             process.exit((a === 20 && b >= 19) || (a === 22 && b >= 12) || a > 22 ? 0 : 1);'
}

# ─────────────────────────────────────────────────────────────────────────────
# .ENV - helpers
# ─────────────────────────────────────────────────────────────────────────────
ENV_FILE="${DEPLOY_DIR}/.env"
ENV_EXAMPLE="${DEPLOY_DIR}/.env.example"

# Valeur d'une clé sur une ligne non commentée, sans ses guillemets englobants ;
# chaîne vide si absente.
lire_valeur_env() {
    grep -m1 "^${1}=" "${ENV_FILE}" 2>/dev/null | cut -d'=' -f2- \
        | sed -E -e 's/^"(.*)"$/\1/' -e "s/^'(.*)'\$/\\1/" || true
}

# Vrai si la clé est déclarée (même avec une valeur vide).
cle_presente_env() {
    grep -qE "^${1}=" "${ENV_FILE}" 2>/dev/null
}

# Met une valeur entre guillemets si nécessaire. Les guillemets simples sont
# littéraux pour phpdotenv (pas d'interpolation de ${...}) : on les préfère,
# sauf si la valeur en contient elle-même.
formater_valeur_env() {
    local valeur="$1" sure='^[A-Za-z0-9_./:@+=-]*$'
    if [[ "${valeur}" =~ ${sure} ]]; then
        printf '%s' "${valeur}"
    elif [[ "${valeur}" != *"'"* ]]; then
        printf "'%s'" "${valeur}"
    else
        valeur="${valeur//\\/\\\\}"
        valeur="${valeur//\"/\\\"}"
        valeur="${valeur//\$/\\\$}"
        printf '"%s"' "${valeur}"
    fi
}

# Écrit ou remplace une clé dans le .env.
# Réécriture via un fichier temporaire puis recopie du contenu : `cat >`
# conserve l'inode, donc le propriétaire et les permissions du .env.
# La ligne passe par l'environnement plutôt que par `awk -v`, qui
# interpréterait les séquences d'échappement de la valeur.
ecrire_env() {
    local ligne tmp
    ligne="${1}=$(formater_valeur_env "${2}")"
    tmp="$(mktemp "${LOG_DIR}/.env.tmp.XXXXXX")"
    if cle_presente_env "${1}"; then
        LIGNE_ENV="${ligne}" awk -v cle="${1}" '
            BEGIN { ligne = ENVIRON["LIGNE_ENV"] }
            $0 ~ "^" cle "=" { if (!fait) print ligne; fait = 1; next }
            { print }
        ' "${ENV_FILE}" > "${tmp}"
    else
        cat "${ENV_FILE}" > "${tmp}"
        printf '%s\n' "${ligne}" >> "${tmp}"
    fi
    cat "${tmp}" > "${ENV_FILE}"
    rm -f "${tmp}"
}

demander_env() {
    local cle="$1" libelle="$2" defaut="$3" reponse=""
    read -r -p "    ${libelle}${defaut:+ [${defaut}]} : " reponse || true
    ecrire_env "${cle}" "${reponse:-${defaut}}"
}

# Saisie masquée : la valeur n'apparaît ni à l'écran ni dans le log.
demander_secret_env() {
    local cle="$1" libelle="$2" reponse=""
    read -r -s -p "    ${libelle} : " reponse || true
    echo ""
    ecrire_env "${cle}" "${reponse}"
}

# ─────────────────────────────────────────────────────────────────────────────
# BASE MYSQL - test de connexion et création via cPanel (uapi)
# ─────────────────────────────────────────────────────────────────────────────
# Connexion avec les valeurs du .env, en PHP pur : vendor/ n'existe pas encore
# à la première installation. Les identifiants passent par l'environnement,
# jamais en argument de commande.
tester_connexion_bdd() {
    SAMM_DB_HOST="$(lire_valeur_env DB_HOST)" SAMM_DB_PORT="$(lire_valeur_env DB_PORT)" \
    SAMM_DB_NAME="$(lire_valeur_env DB_DATABASE)" SAMM_DB_USER="$(lire_valeur_env DB_USERNAME)" \
    SAMM_DB_PASS="$(lire_valeur_env DB_PASSWORD)" \
    "${PHP}" -r '
        try {
            new PDO(
                sprintf("mysql:host=%s;port=%s;dbname=%s", getenv("SAMM_DB_HOST") ?: "127.0.0.1", getenv("SAMM_DB_PORT") ?: "3306", getenv("SAMM_DB_NAME")),
                getenv("SAMM_DB_USER"),
                getenv("SAMM_DB_PASS"),
                [PDO::ATTR_TIMEOUT => 5]
            );
            exit(0);
        } catch (Throwable $e) {
            exit(1);
        }' 2>/dev/null
}

# Appel à l'API cPanel du compte. Vrai si cPanel répond status=1 ; sinon les
# messages d'erreur de cPanel sont affichés (ils ne contiennent pas le mot de passe).
uapi_mysql() {
    local reponse
    reponse="$(uapi --output=json Mysql "$@" 2>/dev/null)" || true
    printf '%s' "${reponse}" | "${PHP}" -r '
        $r = json_decode(stream_get_contents(STDIN), true);
        if ((int) ($r["result"]["status"] ?? 0) === 1) {
            exit(0);
        }
        foreach ((array) ($r["result"]["errors"] ?? ["réponse cPanel illisible"]) as $erreur) {
            echo "        ", $erreur, PHP_EOL;
        }
        exit(1);'
}

# Crée la base, l'utilisateur et ses privilèges avec les droits du compte
# cPanel. Une base ou un utilisateur déjà existant n'est pas bloquant : c'est
# le test de connexion final qui décide. Le mot de passe est passé en argument
# de uapi : visible un instant dans les processus du compte, jamais à l'écran
# ni dans le log de ce script.
creer_bdd_cpanel() {
    local base utilisateur
    base="$(lire_valeur_env DB_DATABASE)"
    utilisateur="$(lire_valeur_env DB_USERNAME)"

    if uapi_mysql create_database name="${base}"; then
        ok "Base ${base} créée"
    else
        warn "Base ${base} non créée (déjà existante ?) - on continue"
    fi
    if uapi_mysql create_user name="${utilisateur}" password="$(lire_valeur_env DB_PASSWORD)"; then
        ok "Utilisateur ${utilisateur} créé"
    else
        warn "Utilisateur ${utilisateur} non créé (déjà existant ?) - on continue"
    fi
    if uapi_mysql set_privileges_on_database user="${utilisateur}" database="${base}" privileges="ALL PRIVILEGES"; then
        ok "Privilèges accordés à ${utilisateur} sur ${base}"
    else
        err "Attribution des privilèges refusée par cPanel (voir ci-dessus)."
        err "Terminez dans cPanel > Bases de données MySQL, puis relancez deploy.sh."
        exit 1
    fi
    if tester_connexion_bdd; then
        ok "Connexion à la base réussie"
    else
        err "Connexion à la base toujours impossible : vérifiez DB_* dans ${ENV_FILE}, puis relancez deploy.sh."
        exit 1
    fi
}

# ─────────────────────────────────────────────────────────────────────────────
# ÉTAPE 0 - Vérifications pré-déploiement
# Aucune modification n'est faite ici : un refus ne coûte aucune coupure.
# ─────────────────────────────────────────────────────────────────────────────
step "0/8 - Vérifications"

if [ ! -f "${DEPLOY_DIR}/artisan" ]; then
    err "Fichier artisan introuvable. Êtes-vous dans le bon dossier ?"
    exit 1
fi
ok "Dossier Laravel détecté"

# Verrou : deux déploiements simultanés se marcheraient dessus.
LOCK_DIR="${DEPLOY_DIR}/storage/framework/deploy.lock"
if ! mkdir "${LOCK_DIR}" 2>/dev/null; then
    err "Un déploiement semble déjà en cours (${LOCK_DIR})."
    err "S'il a été interrompu, supprimez ce dossier puis relancez deploy.sh."
    exit 1
fi
trap 'rmdir "${LOCK_DIR}" 2>/dev/null || true' EXIT

case "$(basename "${DEPLOY_DIR}")" in
    public_html|www|htdocs|httpdocs)
        warn "Le projet semble placé dans la racine web : .env et storage/ seraient exposés."
        warn "Placez-le hors de la racine web et faites pointer le domaine sur public/."
        ;;
esac

if [ -z "${PHP}" ]; then
    err "PHP introuvable. Ajoutez PHP au PATH ou précisez PHP_BIN=/chemin/vers/php."
    exit 1
fi
# Sur certains mutualisés, `php` est le binaire CGI : il affiche des en-têtes
# HTTP et ignore les arguments de la ligne de commande.
if [ "$("${PHP}" -r 'echo PHP_SAPI;' 2>/dev/null)" != "cli" ]; then
    err "${PHP} n'est pas le PHP en ligne de commande (CLI)."
    err "Précisez le binaire CLI : PHP_BIN=/chemin/vers/php-cli bash deploy.sh"
    exit 1
fi
PHP_VERSION=$("${PHP}" -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')
if ! "${PHP}" -r 'exit(version_compare(PHP_VERSION, "8.3.0", ">=") ? 0 : 1);'; then
    err "PHP ${PHP_VERSION} détecté : Sàmm exige PHP 8.3 ou plus récent."
    err "Précisez une autre version : PHP_BIN=/chemin/vers/php8.4 bash deploy.sh"
    exit 1
fi
ok "PHP ${PHP_VERSION} détecté (${PHP})"

if [ -z "${COMPOSER_CMD}" ]; then
    err "Composer introuvable. Installez-le ou déposez composer.phar à la racine du projet."
    exit 1
fi
ok "Composer détecté (${COMPOSER_CMD})"

if ! command -v git >/dev/null 2>&1 || ! git status > /dev/null 2>&1; then
    err "Ce dossier n'est pas un dépôt git (ou git est absent)."
    exit 1
fi
if ! git check-ref-format --branch "${BRANCH}" >/dev/null 2>&1; then
    err "Nom de branche invalide : ${BRANCH}"
    exit 1
fi
ok "Dépôt git détecté"

if ! git diff --quiet || ! git diff --cached --quiet; then
    warn "Des modifications locales non commitées existent - elles seront écrasées."
fi

# Assets front : public/build est versionné (hébergement sans Node) ; il n'est
# reconstruit ici que si un Node compatible est présent.
CONSTRUIRE_ASSETS=true
if [ "${SAMM_SKIP_ASSETS:-}" = "1" ]; then
    CONSTRUIRE_ASSETS=false
    warn "SAMM_SKIP_ASSETS=1 - les assets ne seront pas reconstruits"
elif node_compatible; then
    ok "Node $(node -v) détecté"
else
    CONSTRUIRE_ASSETS=false
    if [ -f "${DEPLOY_DIR}/public/build/manifest.json" ]; then
        warn "Node absent ou trop ancien (^20.19 ou >= 22.12 requis) - les assets existants sont conservés"
        warn "    s'ils ont changé, construisez-les en local et envoyez public/build sur le serveur"
    else
        err "Node absent ou trop ancien (^20.19 ou >= 22.12 requis) et aucun asset construit."
        err "Construisez-les en local (npm ci && npm run build), envoyez le dossier"
        err "public/build sur le serveur, puis relancez : SAMM_SKIP_ASSETS=1 bash deploy.sh"
        exit 1
    fi
fi

IS_FIRST_DEPLOY=false
if [ ! -f "${ENV_FILE}" ] || [ ! -f "${DEPLOY_DIR}/vendor/autoload.php" ]; then
    IS_FIRST_DEPLOY=true
fi

# ─────────────────────────────────────────────────────────────────────────────
# ÉTAPE 1 - Configuration .env
# ─────────────────────────────────────────────────────────────────────────────
step "1/8 - Configuration"

if [ ! -f "${ENV_FILE}" ]; then
    warn "Pas de .env détecté - première installation"
    cp "${ENV_EXAMPLE}" "${ENV_FILE}"
    chmod 600 "${ENV_FILE}"

    ecrire_env APP_ENV production
    ecrire_env APP_DEBUG false
    ecrire_env LOG_LEVEL warning
    # Même format que `php artisan key:generate` (AES-256-CBC), sans dépendre
    # de vendor/ qui n'est pas encore installé.
    ecrire_env APP_KEY "base64:$("${PHP}" -r 'echo base64_encode(random_bytes(32));')"
    ok ".env créé à partir de .env.example (production, APP_KEY générée)"
    warn "Conservez une copie de APP_KEY hors du serveur : sans elle, les données chiffrées sont perdues."

    if [ -t 0 ]; then
        echo ""
        echo "  Répondez aux questions (Entrée = valeur entre crochets) :"
        demander_env APP_URL "URL publique du site (https://...)" ""
        demander_env DB_CONNECTION "Base de données (mysql, mariadb ou sqlite)" "mysql"
        if [ "$(lire_valeur_env DB_CONNECTION)" = "sqlite" ]; then
            ecrire_env DB_DATABASE "${DEPLOY_DIR}/database/database.sqlite"
            touch "${DEPLOY_DIR}/database/database.sqlite"
        else
            # Sur cPanel, base et utilisateur portent obligatoirement le
            # préfixe du compte : on le propose par défaut.
            UAPI_DISPONIBLE=false
            DEFAUT_BDD=""
            if command -v uapi >/dev/null 2>&1; then
                UAPI_DISPONIBLE=true
                DEFAUT_BDD="${USER:-$(id -un)}_samm"
            fi
            demander_env DB_HOST "Hôte de la base" "localhost"
            demander_env DB_PORT "Port de la base" "3306"
            demander_env DB_DATABASE "Nom de la base" "${DEFAUT_BDD}"
            demander_env DB_USERNAME "Utilisateur de la base" "${DEFAUT_BDD}"
            demander_secret_env DB_PASSWORD "Mot de passe de la base (masqué)"

            if "${PHP}" -m 2>/dev/null | grep -ix pdo_mysql >/dev/null; then
                if tester_connexion_bdd; then
                    ok "Connexion à la base réussie"
                elif [ "${UAPI_DISPONIBLE}" = true ]; then
                    warn "Connexion à la base impossible (base ou utilisateur inexistant ?)"
                    read -r -p "    Créer la base et l'utilisateur via cPanel (uapi) ? [O/n] : " reponse_uapi || true
                    case "${reponse_uapi:-O}" in
                        n|N) warn "Création ignorée : la base doit exister avant les migrations" ;;
                        *)   creer_bdd_cpanel ;;
                    esac
                else
                    warn "Connexion à la base impossible : la base doit exister (vérifiez DB_* dans ${ENV_FILE})"
                fi
            fi
        fi
        echo ""
        ok "Configuration enregistrée dans .env"
    else
        warn "Session non interactive : complétez APP_URL et DB_* dans ${ENV_FILE}"
    fi
else
    ok ".env existant conservé"
fi

# Clés bloquantes : absentes ou vides => refus AVANT toute modification.
CLES_REQUISES=(APP_KEY APP_URL DB_CONNECTION)
if [ "$(lire_valeur_env DB_CONNECTION)" != "sqlite" ]; then
    CLES_REQUISES+=(DB_DATABASE DB_USERNAME)
fi
CLES_MANQUANTES=""
for cle in "${CLES_REQUISES[@]}"; do
    if [ -z "$(lire_valeur_env "${cle}")" ]; then
        CLES_MANQUANTES="${CLES_MANQUANTES}${cle}"$'\n'
    fi
done
if [ -n "${CLES_MANQUANTES}" ]; then
    err "Clés requises absentes ou vides dans .env :"
    for cle in ${CLES_MANQUANTES}; do
        err "    - ${cle}"
    done
    # Jamais de génération automatique sur un .env existant : une nouvelle clé
    # rendrait illisibles les identités et contenus déjà chiffrés.
    if [ -z "$(lire_valeur_env APP_KEY)" ]; then
        err "APP_KEY : si l'application contient déjà des données, restaurez l'ancienne clé."
        err "          S'il s'agit d'une nouvelle installation : php artisan key:generate"
    fi
    err "Renseignez-les dans ${ENV_FILE} puis relancez deploy.sh."
    err "Aucune modification n'a été faite : le site est resté en ligne."
    exit 1
fi
ok "Clés .env requises présentes (${#CLES_REQUISES[@]} vérifiées)"

# Réglages risqués pour une plateforme de signalement : signalés, pas bloquants.
if [ "$(lire_valeur_env APP_ENV)" != "production" ]; then
    warn "APP_ENV=$(lire_valeur_env APP_ENV) - attendu : production"
fi
if [ "$(lire_valeur_env APP_DEBUG | tr '[:upper:]' '[:lower:]')" = "true" ]; then
    warn "APP_DEBUG=true - les pages d'erreur exposeraient la configuration. Passez-le à false."
fi
case "$(lire_valeur_env APP_URL)" in
    https://*localhost*|http://*) warn "APP_URL=$(lire_valeur_env APP_URL) - Sàmm doit être servi en HTTPS sur son vrai domaine" ;;
esac
if [ "$(lire_valeur_env MAIL_MAILER)" = "log" ]; then
    warn "MAIL_MAILER=log - aucun mail ne partira (réinitialisation de mot de passe comprise)"
fi

DB_CONNECTION="$(lire_valeur_env DB_CONNECTION)"
case "${DB_CONNECTION}" in
    mysql|mariadb) PDO_EXT="pdo_mysql" ;;
    *)             PDO_EXT="pdo_${DB_CONNECTION}" ;;
esac
# Pas de `grep -q` derrière un pipe : il s'arrête au premier résultat, le
# programme en amont reçoit SIGPIPE et pipefail rend le test faux au hasard.
if ! "${PHP}" -m 2>/dev/null | grep -ix "${PDO_EXT}" >/dev/null; then
    err "Extension PHP ${PDO_EXT} absente : requise pour DB_CONNECTION=${DB_CONNECTION}."
    exit 1
fi
ok "Extension ${PDO_EXT} présente"

# Diff avec .env.example : clés ajoutées par une nouvelle version.
CLES_NOUVELLES=""
for ligne in $(grep -E '^[A-Z_][A-Z0-9_]*=' "${ENV_EXAMPLE}" || true); do
    cle="${ligne%%=*}"
    if ! cle_presente_env "${cle}"; then
        CLES_NOUVELLES="${CLES_NOUVELLES}${cle}"$'\n'
    fi
done
if [ -n "${CLES_NOUVELLES}" ]; then
    warn "Clés présentes dans .env.example mais absentes du .env (valeur par défaut utilisée) :"
    for cle in ${CLES_NOUVELLES}; do
        warn "    - ${cle}"
    done
else
    ok ".env aligné sur .env.example"
fi

# ─────────────────────────────────────────────────────────────────────────────
# ÉTAPE 2 - Mode maintenance + Sauvegardes
# ─────────────────────────────────────────────────────────────────────────────
step "2/8 - Mode maintenance & Sauvegardes"

mkdir -p "${BACKUP_DIR}"
chmod 700 "${BACKUP_DIR}"
HORODATAGE="$(date +%Y%m%d-%H%M%S)"
BACKUP_FILE=""

# Dump MySQL/MariaDB. Les identifiants passent par un fichier d'options
# temporaire (600) : ils n'apparaissent ni dans `ps` ni dans le log.
# --no-tablespaces : le privilège PROCESS est rarement accordé en mutualisé.
dump_mysql() {
    local fichier="$1" cnf statut=0
    shift
    echappe() { local v="${1//\\/\\\\}"; printf '%s' "${v//\"/\\\"}"; }
    cnf="$(mktemp "${BACKUP_DIR}/.my.cnf.XXXXXX")"
    printf '[client]\nuser="%s"\npassword="%s"\nhost="%s"\nport="%s"\n' \
        "$(echappe "$(lire_valeur_env DB_USERNAME)")" \
        "$(echappe "$(lire_valeur_env DB_PASSWORD)")" \
        "$(echappe "$(lire_valeur_env DB_HOST || true)")" \
        "$(echappe "$(lire_valeur_env DB_PORT || true)")" > "${cnf}"
    # Valeurs par défaut de Laravel si DB_HOST / DB_PORT sont absents
    sed -i.bak -e 's/^host=""$/host="127.0.0.1"/' -e 's/^port=""$/port="3306"/' "${cnf}" && rm -f "${cnf}.bak"
    mysqldump --defaults-extra-file="${cnf}" --single-transaction --quick --no-tablespaces "$@" \
        "$(lire_valeur_env DB_DATABASE)" | gzip > "${fichier}" || statut=$?
    rm -f "${cnf}"
    return "${statut}"
}

if [ "${IS_FIRST_DEPLOY}" = true ]; then
    warn "Première installation - pas de maintenance ni de sauvegarde"
else
    artisan down --retry=60 2>/dev/null || warn "Impossible d'activer le mode maintenance (ignoré)"
    ok "Mode maintenance activé"

    # Copie du .env : c'est aussi la sauvegarde de APP_KEY.
    cp "${ENV_FILE}" "${BACKUP_DIR}/env-${HORODATAGE}"
    chmod 600 "${BACKUP_DIR}/env-${HORODATAGE}"
    ok "Copie du .env créée : env-${HORODATAGE}"

    case "${DB_CONNECTION}" in
        mysql|mariadb)
            if command -v mysqldump >/dev/null 2>&1; then
                BACKUP_FILE="${BACKUP_DIR}/db-${HORODATAGE}.sql.gz"
                # Un mysqldump 8 face à un serveur MariaDB échoue sur
                # COLUMN_STATISTICS : seconde tentative sans cette table.
                if dump_mysql "${BACKUP_FILE}" 2>/dev/null \
                    || dump_mysql "${BACKUP_FILE}" --column-statistics=0; then
                    chmod 600 "${BACKUP_FILE}"
                    ok "Sauvegarde BDD créée : $(basename "${BACKUP_FILE}")"
                else
                    rm -f "${BACKUP_FILE}"
                    BACKUP_FILE=""
                    warn "Sauvegarde BDD échouée - vérifiez les droits mysqldump dans le log"
                fi
            else
                warn "mysqldump absent - sauvegarde BDD ignorée"
            fi
            ;;
        sqlite)
            SQLITE_FILE="$(lire_valeur_env DB_DATABASE)"
            SQLITE_FILE="${SQLITE_FILE:-database/database.sqlite}"
            if [ -f "${SQLITE_FILE}" ]; then
                BACKUP_FILE="${BACKUP_DIR}/db-${HORODATAGE}.sqlite.gz"
                gzip -c "${SQLITE_FILE}" > "${BACKUP_FILE}"
                chmod 600 "${BACKUP_FILE}"
                ok "Sauvegarde BDD créée : $(basename "${BACKUP_FILE}")"
            fi
            ;;
        *)
            warn "Sauvegarde automatique non prise en charge pour ${DB_CONNECTION}"
            ;;
    esac

    purger_anciens "${BACKUP_DIR}" "db-*" "${BACKUP_KEEP}"
    purger_anciens "${BACKUP_DIR}" "env-*" "${BACKUP_KEEP}"
fi

# Rollback automatique si une étape échoue
BUILD_BACKUP=""
rollback() {
    # Désarmer le trap : sans cela, une commande de rollback qui échoue
    # relancerait rollback en boucle.
    trap - ERR INT TERM

    err "ERREUR - Rollback en cours..."

    # Retour au commit d'avant le déploiement. CURRENT_COMMIT n'existe qu'à
    # partir de l'étape 3 : un échec plus tôt n'a de toute façon pas touché au code.
    # Chaque commande est neutralisée par `|| true` - on est déjà en erreur,
    # un rollback partiel ne doit jamais empêcher le `artisan up` final.
    if [ "${IS_FIRST_DEPLOY}" = false ] && [ -n "${CURRENT_COMMIT:-}" ]; then
        if [ "$(git rev-parse HEAD 2>/dev/null || echo '')" != "${CURRENT_COMMIT}" ]; then
            if { [ -n "${CURRENT_BRANCH:-}" ] && git checkout --force -B "${CURRENT_BRANCH}" "${CURRENT_COMMIT}" >/dev/null 2>&1; } \
                || git checkout --force --detach "${CURRENT_COMMIT}" >/dev/null 2>&1; then
                ok "Code restauré au commit ${CURRENT_COMMIT:0:7}"

                # vendor/ n'est pas versionné : après le retour en arrière il
                # correspond encore au composer.lock de la version en échec.
                run_composer install --no-dev --optimize-autoloader --no-interaction \
                    --prefer-dist --no-scripts --quiet 2>/dev/null \
                    && ok "Dépendances réalignées sur l'ancien composer.lock" \
                    || warn "Réalignement composer échoué - lancez composer install manuellement"

                rm -f bootstrap/cache/*.php 2>/dev/null || true
                artisan package:discover --ansi >/dev/null 2>&1 || true
            else
                warn "Retour au commit ${CURRENT_COMMIT:0:7} échoué - le code reste sur la nouvelle version"
            fi
        fi
    fi

    # Assets de la version précédente (vite vide public/build avant de construire)
    if [ -n "${BUILD_BACKUP}" ] && [ -d "${BUILD_BACKUP}" ]; then
        rm -rf public/build && mkdir -p public/build \
            && cp -a "${BUILD_BACKUP}/." public/build/ \
            && ok "Assets précédents restaurés" \
            || warn "Restauration des assets échouée"
        rm -rf "${BUILD_BACKUP}"
    fi

    if [ "${IS_FIRST_DEPLOY}" = false ]; then
        artisan up 2>/dev/null || true
    fi
    if [ -n "${BACKUP_FILE}" ]; then
        warn "Les migrations déjà passées ne sont pas annulées. Sauvegarde : ${BACKUP_FILE}"
    fi
    err "Déploiement annulé. Consultez : ${LOG_FILE}"
    exit 1
}
trap rollback ERR INT TERM

# ─────────────────────────────────────────────────────────────────────────────
# ÉTAPE 3 - Mise à jour du code (git fetch + checkout forcé)
# ─────────────────────────────────────────────────────────────────────────────
step "3/8 - Mise à jour du code"

CURRENT_COMMIT=$(git rev-parse HEAD)
CURRENT_BRANCH=$(git symbolic-ref -q --short HEAD || true)
git fetch --prune "${REMOTE}" "${BRANCH}"
git checkout --force -B "${BRANCH}" FETCH_HEAD   # Plus sûr que git pull : pas de conflit possible
NEW_COMMIT=$(git rev-parse HEAD)

if [ "${CURRENT_COMMIT}" = "${NEW_COMMIT}" ]; then
    warn "Aucun nouveau commit. Le code est déjà à jour."
else
    ok "Code mis à jour : ${CURRENT_COMMIT:0:7} → ${NEW_COMMIT:0:7}"
fi

# ─────────────────────────────────────────────────────────────────────────────
# ÉTAPE 4 - Composer
# ─────────────────────────────────────────────────────────────────────────────
step "4/8 - Installation des dépendances PHP (composer)"

# Les mutualisés limitent souvent memory_limit en CLI.
export COMPOSER_MEMORY_LIMIT=-1

run_composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --prefer-dist \
    --no-progress \
    --no-scripts

ok "Dépendances installées (scripts différés)"

# Audit de sécurité - détecte les paquets avec des vulnérabilités connues
if run_composer audit --no-dev --no-interaction >/dev/null 2>&1; then
    ok "Audit sécurité - aucune vulnérabilité connue"
else
    warn "Des vulnérabilités ont été détectées dans les dépendances - vérifiez ci-dessous"
    run_composer audit --no-dev --no-interaction 2>/dev/null || true
fi

# Caches bootstrap de l'ancienne version vidés avant package:discover
rm -f bootstrap/cache/*.php 2>/dev/null || true
mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views bootstrap/cache

artisan package:discover --ansi
ok "Packages découverts"

# ─────────────────────────────────────────────────────────────────────────────
# ÉTAPE 5 - Assets front (Vite)
# ─────────────────────────────────────────────────────────────────────────────
step "5/8 - Assets front"

if [ "${CONSTRUIRE_ASSETS}" = true ]; then
    if [ -d public/build ]; then
        BUILD_BACKUP="$(mktemp -d "${LOG_DIR}/.build-precedent.XXXXXX")"
        cp -a public/build/. "${BUILD_BACKUP}/"
    fi

    npm ci --no-fund
    npm run build

    if [ ! -f public/build/manifest.json ]; then
        err "public/build/manifest.json absent après la construction"
        rollback
    fi
    ok "Assets construits"

    # Les mutualisés limitent le nombre de fichiers (inodes) : node_modules
    # n'est utile qu'à la construction, `npm ci` le recrée de toute façon.
    if [ "${SAMM_KEEP_NODE_MODULES:-}" != "1" ]; then
        rm -rf node_modules
        ok "node_modules supprimé"
    fi
else
    warn "Construction ignorée - public/build conservé tel quel"
fi

# ─────────────────────────────────────────────────────────────────────────────
# ÉTAPE 6 - Migrations & données système
# ─────────────────────────────────────────────────────────────────────────────
step "6/8 - Migrations & données système"

artisan migrate --force --no-interaction
ok "Migrations exécutées"

# DatabaseSeeder ne crée que ce qui manque (rôles, organisation, catégories,
# numéros d'urgence) et ne touche jamais aux réglages de l'administrateur :
# il est donc rejoué à chaque mise à jour pour ajouter les nouveaux rôles.
# DemoUsersSeeder n'en fait pas partie et ne doit jamais être lancé ici.
artisan db:seed --force --no-interaction
ok "Données système à jour"

# ─────────────────────────────────────────────────────────────────────────────
# ÉTAPE 7 - Stockage, caches & permissions
# ─────────────────────────────────────────────────────────────────────────────
step "7/8 - Stockage et optimisation"

if [ -L "${DEPLOY_DIR}/public/storage" ]; then
    ok "Lien storage déjà présent"
elif artisan storage:link >/dev/null 2>&1; then
    ok "Lien storage créé"
else
    warn "Lien storage impossible (liens symboliques désactivés par l'hébergeur ?)"
fi

# Pas de cache:clear (ni optimize:clear, qui l'inclut) : le cache porte les
# verrouillages anti-abus du suivi des dossiers, un déploiement ne doit pas
# les remettre à zéro.
artisan config:clear
artisan route:clear
artisan view:clear
artisan event:clear
artisan optimize
ok "Caches optimisés"

chmod -R u+rwX storage bootstrap/cache
chmod 600 "${ENV_FILE}"
ok "Permissions vérifiées (storage, bootstrap/cache, .env en 600)"

# ─────────────────────────────────────────────────────────────────────────────
# ÉTAPE 8 - Remise en ligne & contrôles
# ─────────────────────────────────────────────────────────────────────────────
step "8/8 - Remise en ligne"

trap - ERR INT TERM

if [ "${IS_FIRST_DEPLOY}" = false ]; then
    artisan up
    ok "Application remise en ligne"
fi

if [ -n "${BUILD_BACKUP}" ]; then
    rm -rf "${BUILD_BACKUP}"
fi
purger_anciens "${LOG_DIR}" "deploy-*.log" 30

APP_URL="$(lire_valeur_env APP_URL)"
APP_URL="${APP_URL%/}"
ALERTE_CRITIQUE=false
if command -v curl >/dev/null 2>&1; then
    CODE_UP="$(curl -s -o /dev/null -w '%{http_code}' --max-time 15 "${APP_URL}/up" || true)"
    if [ "${CODE_UP}" = "200" ]; then
        ok "Contrôle de santé ${APP_URL}/up : 200"
    else
        warn "Contrôle de santé ${APP_URL}/up : ${CODE_UP:-sans réponse} - vérifiez le site et le log Laravel"
    fi

    # Le contenu n'est jamais affiché : on vérifie seulement qu'il n'est pas servi.
    if curl -fs --max-time 15 "${APP_URL}/.env" 2>/dev/null | grep '^APP_KEY=' >/dev/null; then
        ALERTE_CRITIQUE=true
        err "CRITIQUE : ${APP_URL}/.env est accessible publiquement !"
        err "    La racine web doit pointer sur ${DEPLOY_DIR}/public, pas sur le projet."
        err "    Changez APP_KEY et les mots de passe une fois corrigé si le fichier a pu être lu."
    else
        ok ".env non exposé publiquement"
    fi
else
    warn "curl absent - contrôles HTTP ignorés"
fi

# Le planificateur purge chaque nuit les adresses IP des journaux de connexion.
if crontab -l 2>/dev/null | grep "schedule:run" >/dev/null; then
    ok "Tâche cron du planificateur présente"
else
    warn "Aucune tâche cron schedule:run détectée. Ajoutez-la (cPanel > Tâches cron) :"
    warn "    * * * * * cd \"${DEPLOY_DIR}\" && \"${PHP}\" artisan schedule:run >> /dev/null 2>&1"
fi

# ─────────────────────────────────────────────────────────────────────────────
# RÉSUMÉ
# ─────────────────────────────────────────────────────────────────────────────
echo ""
echo -e "${GREEN}${BOLD}╔══════════════════════════════════════════════╗${NC}"
if [ "${IS_FIRST_DEPLOY}" = true ]; then
echo -e "${GREEN}${BOLD}║        ✓  Installation réussie !             ║${NC}"
else
echo -e "${GREEN}${BOLD}║        ✓  Mise à jour réussie !              ║${NC}"
fi
echo -e "${GREEN}${BOLD}╚══════════════════════════════════════════════╝${NC}"
echo ""
echo -e "  Branche    : ${BRANCH}"
echo -e "  Commit     : ${NEW_COMMIT:0:7}"
if [ -n "${BACKUP_FILE}" ]; then
    echo -e "  Sauvegarde : ${BACKUP_FILE}"
fi
echo -e "  Log        : ${LOG_FILE}"
echo -e "  Durée      : $((SECONDS))s"
echo ""

if [ "${IS_FIRST_DEPLOY}" = true ]; then
    echo -e "  ${BOLD}À faire maintenant :${NC}"
    echo -e "    - faire pointer la racine web du domaine sur ${DEPLOY_DIR}/public"
    echo -e "    - activer HTTPS sur le domaine"
    echo -e "    - sauvegarder APP_KEY (dans .env) hors du serveur"
    echo -e "    - configurer l'envoi de mails (MAIL_*) dans .env"
    echo ""
fi

if [ "${ALERTE_CRITIQUE}" = true ]; then
    exit 1
fi
