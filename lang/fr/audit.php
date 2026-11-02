<?php

return [

    'actions' => [
        'auth' => [
            'login_succeeded' => 'Connexion réussie',
            'login_failed' => 'Échec de connexion',
            'locked_out' => 'Compte verrouillé après trop de tentatives',
            'logout' => 'Déconnexion',
        ],
        'reports' => [
            'submitted' => 'Signalement déposé',
        ],
        'identities' => [
            'viewed' => 'Identité consultée',
        ],
        'cases' => [
            'viewed' => 'Dossier consulté',
            'assigned' => 'Dossier affecté',
            'reassigned' => 'Dossier réaffecté',
            'unassigned' => 'Affectation retirée',
            'priority_changed' => 'Priorité du dossier modifiée',
            'status_changed' => 'Statut du dossier modifié',
        ],
        'tracking' => [
            'access_succeeded' => 'Accès au suivi réussi',
            'access_failed' => 'Accès au suivi refusé',
        ],
        'settings' => [
            'updated' => 'Paramètres de l\'organisation modifiés',
            'emergency_contact_changed' => 'Numéro d\'urgence modifié',
            'category_changed' => 'Catégorie de signalement modifiée',
        ],
        'roles' => [
            'assigned' => 'Rôle attribué',
            'removed' => 'Rôle retiré',
            'permissions_changed' => 'Permissions d\'un rôle modifiées',
        ],
    ],

    'reasons' => [
        'invalid_credentials' => 'identifiants invalides',
        'inactive' => 'compte désactivé',
        'unknown_account' => 'compte inconnu',
    ],

    'actor_kinds' => [
        'user' => 'Compte interne',
        'reporter' => 'Personne qui signale',
        'system' => 'Système',
    ],

];
