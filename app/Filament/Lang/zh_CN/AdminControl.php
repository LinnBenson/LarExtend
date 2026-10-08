<?php

return [
    'AdminUsers' => [
        'title' => '管理员列表',
        'model' => '管理员',
        'enabled' => '启用',
        'disabled' => '禁用',
        'all_statuses' => '全部状态',
        'empty' => '暂无管理员',
        'editStatus' => [
            'success' => '管理员状态修改成功',
            'body' => '管理员 :name 已:status。',
        ],
        'fields' => [
            'avatar' => '头像',
            'password' => '密码',
            'id' => '管理员ID',
            'name' => '用户名',
            'email' => '邮箱',
            'status' => '状态',
            'level' => '级别',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
        ],
    ],
];
