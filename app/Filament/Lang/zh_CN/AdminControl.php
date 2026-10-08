<?php

return [
    'AdminUsers' => [
        'title' => '管理员列表',
        'model' => '管理员',
        'enabled' => '启用',
        'disabled' => '禁用',
        'all_statuses' => '全部状态',
        'empty' => '暂无管理员',
        'avatar_help' => '支持 JPG、PNG、WebP，最大 2 MB。',
        'avatar_invalid' => '头像文件无效，请重新上传。',
        'password_help' => '新增时必须填写至少12位密码；编辑时留空保持原密码。',
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
