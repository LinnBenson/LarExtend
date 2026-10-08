<?php
/**
 * 后台配置文件
 * @package config
 */
return [
    // 后台面板路径
    'path' => env( 'APP_ADMIN_PREFIX', 'admin' ),
    // 后台资源路径
    'assets_path' => 'assets/filament',
    // 登录页面模版
    'login' => 'Filament::Dashboard.Login.login_v0',
    // 后台菜单分组
    'navigation_groups' => [
        'admin::frame.groups.admin',
        'admin::frame.groups.developer'
    ]
];