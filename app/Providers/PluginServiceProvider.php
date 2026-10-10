<?php

namespace App\Providers;

class PluginServiceProvider {

    // 插件 ID
    public ?string $id = null;

    // 插件基础路径
    public ?string $base = null;

    // 插件包信息
    public ?array $package = null;

}