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
];