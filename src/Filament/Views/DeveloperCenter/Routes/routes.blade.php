<x-filament-panels::page class="routes-page">
    {{-- 路由统计 --}}
    <div class="routes-stats">
        @foreach ( [
            ['value' => $total, 'label' => 'total', 'icon' => 'heroicon-o-queue-list'],
            ['value' => $named, 'label' => 'named', 'icon' => 'heroicon-o-tag'],
            ['value' => $matched, 'label' => 'matched', 'icon' => 'heroicon-o-funnel'],
        ] as $stat )
            <x-filament::section>
                <div class="routes-stat">
                    <x-filament::icon :icon="$stat['icon']" class="routes-stat-icon" />
                    <div>
                        <strong>{{ $stat['value'] }}</strong>
                        <span>{{ __( 'lar::DeveloperCenter.Routes.'.$stat['label'] ) }}</span>
                    </div>
                </div>
            </x-filament::section>
        @endforeach
    </div>
    {{-- 系统路由表格，详情按需展开 --}}
    {{ $this->table }}
    <style>
        /* 路由统计和可展开详情，跟随面板主题 */
        .routes-page .routes-stats { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1rem; }
        .routes-page .routes-stat { display: flex; align-items: center; gap: 1rem; }
        .routes-page .routes-stat-icon { width: 1.5rem; height: 1.5rem; color: var(--primary-500); }
        .routes-page .routes-stat strong { display: block; font-size: 1.75rem; line-height: 1.2; }
        .routes-page .routes-stat span { display: block; margin-top: .25rem; color: var(--gray-500); font-size: .875rem; }
        .routes-page .routes-detail { min-width: 7rem; max-width: 24rem; padding: .75rem 1rem; font-size: .8125rem; }
        .routes-page .routes-detail summary { color: var(--primary-500); cursor: pointer; overflow-wrap: anywhere; }
        .routes-page .routes-detail code { display: block; margin-top: .5rem; font-size: .75rem; overflow-wrap: anywhere; white-space: normal; }
        @media (max-width: 640px) {
            .routes-page .routes-stats { grid-template-columns: minmax(0, 1fr); }
        }
    </style>
</x-filament-panels::page>
