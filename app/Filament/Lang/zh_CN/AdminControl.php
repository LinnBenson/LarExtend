<?php

return [
    'AdminUsers' => [
        'filters' => ['level_range' => '权限区间', 'all_levels' => '全部权限'],
        'sections' => ['basic' => '基本信息', 'avatar' => '头像设置'],
        'actions' => ['title' => '操作', 'create' => '新增管理员', 'edit' => '编辑', 'delete' => '删除'],
        'level_max' => '级别必须低于当前管理员级别。',
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
            'id' => 'UID',
            'name' => '用户名',
            'email' => '邮箱',
            'status' => '状态',
            'level' => '级别',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
        ],
    ],
];
