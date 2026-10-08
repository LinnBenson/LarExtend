<?php

return [
    'AdminUsers' => [
        'filters' => ['level_range' => 'Permission range', 'all_levels' => 'All permissions'],
        'sections' => ['basic' => 'Basic information', 'avatar' => 'Avatar settings'],
        'actions' => ['title' => 'Actions', 'create' => 'Create administrator', 'edit' => 'Edit', 'delete' => 'Delete'],
        'level_max' => 'The level must be lower than your administrator level.',
        'editStatus' => ['success' => 'Administrator status updated', 'body' => 'Administrator :name is now :status.'],
        'title' => 'Administrators',
        'model' => 'Administrator',
        'enabled' => 'Enabled',
        'disabled' => 'Disabled',
        'all_statuses' => 'All statuses',
        'empty' => 'No administrators found',
        'avatar_help' => 'JPG, PNG or WebP, up to 2 MB.',
        'avatar_invalid' => 'Invalid avatar file. Please upload it again.',
        'password_help' => 'Use at least 12 characters. Leave blank when editing to keep the current password.',
        'fields' => [
            'avatar' => 'Avatar',
            'password' => 'Password',
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
