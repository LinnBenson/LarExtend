<?php
return [
    // 允许使用插件
    'enable' => true,
    // 插件相关路径配置
    'path' => [
        // 工作目录
        'work' => app_path( 'Plugins' ),
        // 安装检查目录
        'install' => storage_path( 'framework/Plugins/install' ),
        // 卸载检查目录
        'uninstall' => storage_path( 'framework/Plugins/uninstall' ),
        // 插件打包目录
        'package' => storage_path( 'framework/Plugins/package' ),
        // 插件配置目录
        'config' => config_path( 'Plugins' ),
    ],
    // 保留的插件 ID 列表
    'reserve' => [
        'plugin', 'plugins', 'package', 'packages',
        'permissions', // 用于设定插件权限
    ],
    // 允许的权限声明
    'permissions' => [
        // 应用服务注册钩子
        'APP_SERVICE_REGISTER_HOOK',
        // 应用服务启动钩子
        'APP_SERVICE_BOOT_HOOK',
        // 管理员面板钩子
        'ADMINISTRATOR_PANEL_HOOK',
        // 管理员面板注册钩子
        'ADMINISTRATOR_PANEL_REGISTER_HOOK',
        // API 路由注册
        'API_ROUTE_REGISTRATION',
        // WEB 路由注册
        'WEB_ROUTE_REGISTRATION',
        // Console 路由注册
        'CONSOLE_ROUTE_REGISTRATION',
        // 数据库填充钩子
        'DATABASE_SEEDER_HOOK',
    ]
];