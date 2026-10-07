<?php

return [
    'AdminUsers' => [
        'title' => 'Administrators',
        'model' => 'Administrator',
        'enabled' => 'Enabled',
        'disabled' => 'Disabled',
        'all_statuses' => 'All statuses',
        'empty' => 'No administrators found',
        'fields' => [
            'id' => 'Administrator ID',
            'name' => 'Username',
            'email' => 'Email',
            'status' => 'Status',
            'level' => 'Level',
            'created_at' => 'Created at',
            'updated_at' => 'Updated at',
        ],
    ],
];
