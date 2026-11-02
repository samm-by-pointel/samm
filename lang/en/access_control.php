<?php

return [

    /*
     * Libellés des permissions (enum Permission). Les clés sont imbriquées
     * parce que les valeurs de l'enum contiennent un point (cases.view_list).
     */
    'permissions' => [
        'cases' => [
            'view_list' => 'View the list of cases',
            'view_assigned' => 'View an assigned case',
            'view_unassigned' => 'View an unassigned case',
            'update_status' => 'Change the status of a case',
            'assign' => 'Assign a case',
            'reassign' => 'Reassign a case',
            'close' => 'Close a case',
        ],
        'identities' => [
            'view' => 'View the identity of the reporting person',
        ],
        'evidence' => [
            'view' => 'View attachments',
            'download' => 'Download an attachment',
        ],
        'messages' => [
            'reply' => 'Reply to the reporting person',
        ],
        'notes' => [
            'create' => 'Add an internal note',
        ],
        'audit' => [
            'view' => 'View the audit log',
        ],
        'analytics' => [
            'view' => 'View aggregated statistics',
        ],
        'users' => [
            'manage' => 'Manage users and their roles',
        ],
        'settings' => [
            'manage' => 'Manage the organization settings',
        ],
    ],

];
