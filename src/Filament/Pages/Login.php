<?php

namespace LarExtend\Filament\Pages;

use Illuminate\Contracts\Support\Htmlable;

/**
 * 后台登录页面
 */
class Login extends \Filament\Auth\Pages\Login {

    /**
     * 获取登录视图。
     * @return string 登录视图名称
     */
    public function getView(): string {
        return setting( 'admin.login', 'Filament::Pages.Login.login_v0' );
    }

    /**
     * 隐藏默认标题。
     * 标题由自定义登录视图统一呈现。
     * @return string|Htmlable|null 页面标题
     */
    public function getHeading(): string | Htmlable | null { return null; }

    /**
     * 隐藏默认品牌标识。
     * 品牌区域由自定义登录视图统一呈现。
     * @return bool 是否显示默认品牌标识
     */
    public function hasLogo(): bool { return false; }

}