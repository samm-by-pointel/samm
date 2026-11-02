<?php

return [

    'actions' => [
        'auth' => [
            'login_succeeded' => 'Successful sign-in',
            'login_failed' => 'Failed sign-in',
            'locked_out' => 'Account locked after too many attempts',
            'logout' => 'Sign-out',
        ],
        'reports' => [
            'submitted' => 'Report submitted',
        ],
        'identities' => [
            'viewed' => 'Identity viewed',
        ],
        'cases' => [
            'viewed' => 'Case viewed',
            'assigned' => 'Case assigned',
            'reassigned' => 'Case reassigned',
            'unassigned' => 'Assignment removed',
            'priority_changed' => 'Case priority changed',
            'status_changed' => 'Case status changed',
        ],
        'tracking' => [
            'access_succeeded' => 'Tracking access granted',
            'access_failed' => 'Tracking access refused',
        ],
        'settings' => [
            'updated' => 'Organization settings changed',
            'emergency_contact_changed' => 'Emergency contact changed',
            'category_changed' => 'Report category changed',
        ],
        'roles' => [
            'assigned' => 'Role assigned',
            'removed' => 'Role removed',
            'permissions_changed' => 'Role permissions changed',
        ],
    ],

    'reasons' => [
        'invalid_credentials' => 'invalid credentials',
        'inactive' => 'disabled account',
        'unknown_account' => 'unknown account',
    ],

    'actor_kinds' => [
        'user' => 'Internal account',
        'reporter' => 'Reporting person',
        'system' => 'System',
    ],

];
