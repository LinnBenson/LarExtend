<?php

namespace LarExtend\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

/**
 * 仪表盘起始页
 * 沿用原生首页路由和权限，仅展示静态欢迎内容。
 * @package App\Filament\Resources\Dashboard
 */
class Dashboard extends BaseDashboard {
    protected string $view = 'Filament::Pages.dashboard';

    /** 隐藏内容区标题，保留导航和浏览器标题。 */
    public function getHeading(): ?string {
        return null;
    }
}