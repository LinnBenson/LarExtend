<?php

namespace LarExtend\Filament\Resources\DeveloperCenter\Icons;

use Filament\Resources\Pages\Page;
use Filament\Support\Enums\IconSize;
use Filament\Support\Icons\Heroicon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Livewire\WithPagination;

/**
 * Filament 图标浏览页面
 * 图标目录直接来自当前安装版本的 Heroicon 枚举。
 */
class ListIcons extends Page {
    use WithPagination;

    protected static string $resource = IconResource::class;

    /**
     * 获取页面面包屑
     * @return array 面包屑列表
     */
    public function getBreadcrumbs(): array {
        return [
            IconResource::getNavigationGroup(),
            IconResource::getNavigationLabel(),
        ];
    }

    protected string $view = 'Filament::DeveloperCenter.Icons.icons';

    public string $search = '';
    public string $style = 'all';

    public function mount(): void {
        abort_unless( IconResource::canAccess(), 403 );
    }

    public function getTitle(): string {
        return __( 'lar::DeveloperCenter.Icons.title' );
    }

    public function updatedSearch(): void {
        $this->resetPage();
    }

    public function updatedStyle(): void {
        $this->resetPage();
    }

    /**
     * 获取搜索及样式筛选后的图标
     * @return array 页面数据
     */
    protected function getViewData(): array {
        abort_unless( IconResource::canAccess(), 403 );
        $search = trim( $this->search );
        $filtered = collect( Heroicon::cases() )->map( fn ( Heroicon $icon ): array => [
            'name' => $icon->name,
            'code' => "Heroicon::{$icon->name}",
            'svg' => $icon->getIconForSize( IconSize::Large ),
            'style' => str_starts_with( $icon->value, 'o-' ) ? 'outline' : 'solid',
        ] )->filter( function ( array $icon ) use ( $search ): bool {
            if ( $this->style !== 'all' && $icon['style'] !== $this->style ) { return false; }
            return $search === '' || Str::contains( implode( ' ', [$icon['name'], $icon['code'], $icon['svg']] ), $search, ignoreCase: true );
        } )->values();
        $page = $this->getPage();
        $icons = new LengthAwarePaginator( $filtered->forPage( $page, 60 )->values(), $filtered->count(), 60, $page );
        return ['icons' => $icons, 'count' => $filtered->count()];
    }
}
