<?php

namespace LarExtend\Filament\Resources\DeveloperCenter\Routes;

use Filament\Resources\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * 系统路由浏览页面
 */
class ListRoutes extends Page implements HasTable {
    use InteractsWithTable;

    protected static string $resource = RouteResource::class;

    /**
     * 获取页面面包屑
     * @return array 面包屑列表
     */
    public function getBreadcrumbs(): array {
        return [
            RouteResource::getNavigationGroup(),
            RouteResource::getNavigationLabel(),
        ];
    }

    protected string $view = 'Filament::DeveloperCenter.Routes.routes';
    protected ?Collection $routeCatalog = null;

    /**
     * 路由展示排除规则
     * name 匹配路由名称，url 匹配域名 / URI 列中的路径；支持 * 通配符。
     * @var array{name: list<string>, url: list<string>}
     */
    protected array $excludedRoutes = [
        'name' => [
            'filament.exports.*',
            'filament.imports.*',
            'filament.administrator.*',
            'storage.local',
            'storage.local.upload'
        ],
        'url' => [
            '/livewire-*',
            '/up',
            '/broadcasting/auth'
        ],
    ];

    public function mount(): void {
        abort_unless( RouteResource::canAccess(), 403 );
    }

    public function getTitle(): string {
        return __( 'lar::DeveloperCenter.Routes.title' );
    }

    /**
     * 配置只读路由表格
     * @param Table $table 表格
     * @return Table 路由表格
     */
    public function table( Table $table ): Table {
        return $table->records( function ( ?string $search, ?array $filters, int $page, int|string $recordsPerPage ): LengthAwarePaginator {
            $records = $this->filteredRoutes( $search, $filters );
            $perPage = $recordsPerPage === 'all' ? max( 1, $records->count() ) : (int) $recordsPerPage;
            return new LengthAwarePaginator( $records->forPage( $page, $perPage )->values(), $records->count(), $perPage, $page );
        } )->columns([
            TextColumn::make( 'name' )->label( __( 'lar::DeveloperCenter.Routes.name' ) )->placeholder( '—' )->wrap(),
            TextColumn::make( 'methods' )->label( __( 'lar::DeveloperCenter.Routes.methods' ) )->badge()
                ->color( fn ( string $state ): string => match ( $state ) {
                    'GET', 'HEAD' => 'info',
                    'POST' => 'success',
                    'PUT', 'PATCH' => 'warning',
                    'DELETE' => 'danger',
                    default => 'gray',
                } ),
            TextColumn::make( 'uri' )->label( __( 'lar::DeveloperCenter.Routes.uri' ) )->wrap()
                ->description( fn ( array $record ): ?string => $record['domain'] ?: null ),
            ViewColumn::make( 'handler' )->label( __( 'lar::DeveloperCenter.Routes.handler' ) )->view( 'Filament::DeveloperCenter.Routes.handler' ),
            ViewColumn::make( 'middleware' )->label( __( 'lar::DeveloperCenter.Routes.middleware' ) )->view( 'Filament::DeveloperCenter.Routes.middleware' ),
            ViewColumn::make( 'source' )->label( __( 'lar::DeveloperCenter.Routes.source' ) )->view( 'Filament::DeveloperCenter.Routes.source' ),
        ])->searchable()->searchPlaceholder( __( 'lar::DeveloperCenter.Routes.search' ) )->filters([
            SelectFilter::make( 'method' )->label( __( 'lar::DeveloperCenter.Routes.methods' ) )
                ->placeholder( __( 'lar::DeveloperCenter.Routes.allMethods' ) )
                ->options( fn (): array => $this->registeredRoutes()->pluck( 'methods' )->flatten()->unique()->sort()->mapWithKeys( fn ( string $method ): array => [$method => $method] )->all() ),
        ] )->paginated( [25, 50, 100] )->defaultPaginationPageOption( 25 )->emptyStateHeading( __( 'lar::DeveloperCenter.Routes.empty' ) );
    }

    /**
     * 获取路由统计
     * @return array 页面数据
     */
    protected function getViewData(): array {
        $routes = $this->registeredRoutes();
        return [
            'total' => $routes->count(),
            'named' => $routes->filter( fn ( array $route ): bool => $route['name'] !== '' )->count(),
            'matched' => $this->filteredRoutes( $this->getTableSearch(), $this->tableFilters )->count(),
        ];
    }

    /**
     * 获取当前请求的路由快照
     * @return Collection 路由集合
     */
    protected function registeredRoutes(): Collection {
        abort_unless( RouteResource::canAccess(), 403 );
        return $this->routeCatalog ??= ( new RouteCatalog() )->all()->reject(
            fn ( array $route ): bool => Str::is( $this->excludedRoutes['name'], $route['name'] )
                || Str::is( $this->excludedRoutes['url'], $route['uri'] )
        )->values();
    }

    /**
     * 筛选路由集合
     * @param string|null $search 搜索文本
     * @param array|null $filters 筛选条件
     * @return Collection 筛选结果
     */
    protected function filteredRoutes( ?string $search, ?array $filters ): Collection {
        $method = $filters['method']['value'] ?? null;
        $search = trim( $search ?? '' );
        return $this->registeredRoutes()->filter( function ( array $route ) use ( $search, $method ): bool {
            if ( filled( $method ) && !in_array( $method, $route['methods'], true ) ) { return false; }
            if ( $search === '' ) { return true; }
            return Str::contains( implode( ' ', [$route['domain'], $route['uri'], $route['name'], $route['handler'], implode( ' ', $route['middleware'] )] ), $search, ignoreCase: true );
        } )->values();
    }
}
