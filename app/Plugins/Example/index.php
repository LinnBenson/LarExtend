<?php

namespace App\Plugins\Example;

return new class extends \App\Providers\PluginServiceProvider {

    /**
     * 初始化插件
     * @return mixed
     */
    public function boot(): mixed {
        return null;
    }

    /**
     * 注册一个示例功能
     * @return string
     */
    public function text(): string {
        return "This is an example text.";
    }

};