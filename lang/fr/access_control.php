<?php

return [

    /*
     * Libellés des permissions (enum Permission). Les clés sont imbriquées
     * parce que les valeurs de l'enum contiennent un point (cases.view_list).
     */
    'permissions' => [
        'cases' => [
            'view_list' => 'Consulter la liste des dossiers',
            'view_assigned' => 'Consulter un dossier affecté',
            'view_unassigned' => 'Consulter un dossier non affecté',
            'update_status' => 'Modifier le statut d\'un dossier',
            'assign' => 'Affecter un dossier',
            'reassign' => 'Réaffecter un dossier',
            'close' => 'Clôturer un dossier',
        ],
        'identities' => [
            'view' => 'Consulter l\'identité de la personne qui signale',
        ],
        'evidence' => [
            'view' => 'Consulter les pièces jointes',
            'download' => 'Télécharger une pièce jointe',
        ],
        'messages' => [
            'reply' => 'Répondre à la personne qui signale',
        ],
        'notes' => [
            'create' => 'Ajouter une note interne',
        ],
        'audit' => [
            'view' => 'Consulter le journal d\'audit',
        ],
        'analytics' => [
            'view' => 'Consulter les statistiques agrégées',
        ],
        'users' => [
            'manage' => 'Gérer les utilisateurs et leurs rôles',
        ],
        'settings' => [
            'manage' => 'Gérer les paramètres de l\'organisation',
        ],
    ],

];
