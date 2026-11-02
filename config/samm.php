<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Sortie rapide
    |--------------------------------------------------------------------------
    |
    | Page neutre vers laquelle le bouton « Quitter rapidement » redirige
    | (charte §12.2). Modifiable par l'organisation. Ce bouton n'efface pas
    | l'historique de navigation et ne doit jamais être présenté ainsi.
    |
    */

    'quick_exit_url' => env('SAMM_QUICK_EXIT_URL', 'https://www.google.fr'),

    /*
    |--------------------------------------------------------------------------
    | Titre neutre
    |--------------------------------------------------------------------------
    |
    | Lorsqu'il est défini, remplace le nom de l'application dans l'onglet du
    | navigateur sur l'espace public (charte §8), par exemple « Espace
    | information ».
    |
    */

    'neutral_title' => env('SAMM_NEUTRAL_TITLE'),

    /*
    |--------------------------------------------------------------------------
    | Langues disponibles
    |--------------------------------------------------------------------------
    |
    | Langues proposées sur l'espace public (code => nom dans la langue
    | elle-même). Une langue n'est ajoutée ici qu'avec ses fichiers lang/.
    |
    */

    'locales' => [
        'fr' => 'Français',
        'en' => 'English',
    ],

    /*
    |--------------------------------------------------------------------------
    | Drapeaux du sélecteur de langue
    |--------------------------------------------------------------------------
    |
    | Code de drapeau par langue, indépendant du code de langue : un drapeau
    | désigne un pays, pas une langue, d'où le libellé toujours affiché à côté.
    | Codes dessinés dans le composant <x-samm.flag> : fr, gb, sn.
    |
    */

    'locale_flags' => [
        'fr' => 'fr',
        'en' => 'gb',
    ],

    /*
    |--------------------------------------------------------------------------
    | Sécurité des comptes internes
    |--------------------------------------------------------------------------
    |
    | Les sessions des visiteurs de l'espace public ne contiennent jamais
    | d'adresse IP ni de user-agent. Pour les comptes internes, l'adresse IP
    | est conservée (journal des connexions) pendant la durée ci-dessous.
    |
    */

    'security' => [
        'ip_retention_days' => (int) env('SAMM_IP_RETENTION_DAYS', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Dépôt et suivi des signalements
    |--------------------------------------------------------------------------
    |
    | consent_wording_version : version des textes de consentement affichés,
    | enregistrée avec chaque décision. À incrémenter quand les textes changent.
    | tracking.secret_lock_minutes : durée du verrouillage du suivi après
    | cinq secrets erronés.
    |
    */

    'consent_wording_version' => env('SAMM_CONSENT_WORDING_VERSION', '2026-11'),

    'tracking' => [
        'secret_lock_minutes' => (int) env('SAMM_TRACKING_LOCK_MINUTES', 15),
        'session_minutes' => (int) env('SAMM_TRACKING_SESSION_MINUTES', 30),
    ],

];
