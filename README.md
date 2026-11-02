# Sàmm - La parole protégée.

Sàmm est une plateforme **open source** de signalement des violences, du harcèlement et des discriminations, destinée aux organisations (entreprises, administrations, universités, établissements de santé, associations). Elle permet à une personne de signaler une situation sans créer de compte, de suivre son dossier avec un code, et à l'organisation de traiter ce dossier de façon structurée, traçable et respectueuse.

Sàmm est édité par Pointel SAS et publié sous licence **GNU AGPL‑3.0** (voir `LICENSE`).

## Principes

- **Confidentialité et minimisation des données.** Trois niveaux au choix de la personne : anonyme, pseudonyme, confidentiel. L'identité, lorsqu'elle est donnée, est stockée à part du contenu et chiffrée ; sa consultation exige un motif et est journalisée.
- **Aucune trace technique sur l'espace public.** Ni adresse IP ni user‑agent ne sont associés aux signalements, aux sessions des visiteurs ou aux accès au suivi. Les protections anti‑abus utilisent une clé anonymisée en cache.
- **Neutralité.** Le vocabulaire parle de « personne qui signale » et de « personne mise en cause ». Rien dans l'interface ne présume de la culpabilité de quiconque.
- **L'humain décide.** Aucune décision automatique. Les fonctions d'assistance par IA, lorsqu'elles existeront, resteront des suggestions désactivables.
- **Traçabilité.** Chaque action sensible est inscrite dans un journal d'audit en ajout seul, sans jamais recopier le contenu des signalements.
- **Une installation = une organisation = une base de données dédiée.** Pas de base partagée entre organisations.

## Ce que fait Sàmm aujourd'hui

- **Espace public** : formulaire de signalement en six étapes utilisable sans JavaScript, bouton « Quitter rapidement » sur chaque page, bloc « En cas de danger immédiat » avec les numéros configurés par l'organisation, interface en français et en anglais.
- **Suivi par codes** : un identifiant de dossier et un secret de récupération, affichés une seule fois, sans caractères ambigus ; consultation du statut, échanges avec la personne en charge.
- **Espace de gestion** : connexion sécurisée (verrouillage après échecs, authentification à deux facteurs), rôles et permissions granulaires vérifiés côté serveur, liste et fiche des dossiers selon l'affectation, affectation, statuts et historique, priorité, notes internes, réponses à la personne, journal d'audit.
- **Administration** : identité et nom du dispositif, logo, couleurs avec contrôle de contraste (WCAG AA), titre d'onglet neutre, page de sortie rapide, langue par défaut, numéros d'urgence et catégories de signalement, sans toucher au code.

## Installation locale

Prérequis : PHP 8.4, Composer 2, Node.js 22, MySQL 8 (SQLite suffit pour un essai).

```bash
git clone <adresse-du-dépôt> samm && cd samm
composer install
cp .env.example .env
php artisan key:generate
```

Renseignez la connexion MySQL dans `.env` (ou mettez `DB_CONNECTION=sqlite` et créez `database/database.sqlite`), puis :

```bash
php artisan migrate --seed
php artisan serve
```

`migrate --seed` crée les données système : rôles, catégories de signalement du cahier des charges et le numéro d'urgence « Police 17 ». L'espace public est à la racine du site, l'espace de gestion sur `/gestion`.

Les assets compilés (`public/build`) sont fournis dans le dépôt : Node n'est pas nécessaire pour installer Sàmm, ce qui permet un hébergement mutualisé sans Node. Si vous modifiez du CSS ou du JavaScript, lancez `npm install && npm run build` puis committez `public/build` ; l'intégration continue vérifie que le build commité correspond au code.

### Comptes de démonstration

Pour essayer l'espace de gestion sur une installation **locale uniquement** :

```bash
php artisan db:seed --class=DemoUsersSeeder
```

Ce seeder crée quatre comptes fictifs (administratrice d'organisation, gestionnaire, superviseure, administrateur technique) avec un mot de passe commun indiqué dans `database/seeders/DemoUsersSeeder.php`. **Ne l'exécutez jamais sur une installation réelle.**

Conservez précieusement `APP_KEY` : les champs sensibles sont chiffrés avec cette clé ; sans elle, ils deviennent illisibles.

## Tests

```bash
php artisan test
vendor/bin/pint --test
```

La suite couvre notamment les contrôles d'accès (rôles, affectation, identité), l'absence d'adresse IP dans les données publiques, le chiffrement au repos et le journal d'audit. L'intégration continue l'exécute sur MySQL 8.

## Architecture

Monolithe Laravel modulaire : chaque module vit dans `app/Modules/<Module>/` (Core, Organizations, Users, AccessControl, Audit, Workflow, Reporting, IdentityVault, Privacy, Tracking, Messaging, Cases). Les vues sont groupées par espace (`resources/views/public`, `manage`), les textes passent tous par les fichiers de langue (`lang/fr`, `lang/en`) pour préparer d'autres langues.

## Contribuer

Les contributions sont bienvenues. Avant de proposer une modification, gardez à l'esprit que ce logiciel s'adresse à des personnes vulnérables : la confidentialité, la sécurité et la neutralité passent avant tout le reste. Chaque fonctionnalité est livrée avec ses tests, en particulier pour les autorisations.

Pour signaler une faille de sécurité, suivez la procédure décrite dans `SECURITY.md`. Ne la publiez pas dans une issue.

## Licence

GNU Affero General Public License v3.0. Voir `LICENSE`.
