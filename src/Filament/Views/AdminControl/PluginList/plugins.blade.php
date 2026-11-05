<x-filament-panels::page>
    @php( $canViewPlugin = \LarExtend\Filament\Resources\AdminControl\PluginList\PluginResource::canMaintain() )
    {{-- 搜索与软件、辅助插件双栏布局 --}}
    <div class="plugin-catalog" x-data="{ category: 'expand' }">
        <x-filament::input.wrapper prefix-icon="heroicon-o-magnifying-glass">
            <x-filament::input type="search" wire:model.live.debounce.300ms="search" :placeholder="__( 'lar::AdminControl.Plugin.search' )" :aria-label="__( 'lar::AdminControl.Plugin.search' )" />
        </x-filament::input.wrapper>
        @if ( $scanFailed )
            <p role="alert" class="plugin-catalog-error">{{ __( 'lar::AdminControl.Plugin.scanFailed' ) }}</p>
        @endif
        <div class="plugin-catalog-layout">
            <section aria-label="{{ __( 'lar::AdminControl.Plugin.software' ) }}">
                @if ( !$groups['software'] )
                    <p class="plugin-catalog-empty">{{ __( trim( $search ) === '' ? 'lar::AdminControl.Plugin.emptyGroup' : 'lar::AdminControl.Plugin.noMatches' ) }}</p>
                @else
                    <div class="plugin-catalog-grid">
                        @foreach ( $groups['software'] as $plugin )
                            <article class="plugin-catalog-card" wire:key="plugin-{{ $plugin['id'] }}">
                                <div class="plugin-catalog-card-heading">
                                    @if ( $canViewPlugin )
                                        <button type="button" class="plugin-catalog-view plugin-catalog-view-card"
                                            aria-label="{{ __( 'lar::AdminControl.Plugin.view' ) }}：{{ $plugin['package']['name'] }}"
                                            wire:click="mountAction('viewPlugin', {{ \Illuminate\Support\Js::from( ['id' => $plugin['id']] ) }})">
                                    @else
                                        <div class="plugin-catalog-view plugin-catalog-view-card">
                                    @endif
                                        <span class="plugin-catalog-icon" aria-hidden="true">{{ mb_substr( $plugin['id'], 0, 2 ) }}</span>
                                        <span class="plugin-catalog-heading-text">
                                            <span class="plugin-catalog-name">{{ $plugin['package']['name'] }}</span>
                                            <span class="plugin-catalog-id">{{ $plugin['id'] }}</span>
                                        </span>
                                    @if ( $canViewPlugin )
                                        </button>
                                    @else
                                        </div>
                                    @endif
                                    @include( 'Filament::AdminControl.PluginList.plugin-menu' )
                                </div>
                                <p class="plugin-catalog-description">{{ $plugin['package']['description'] }}</p>
                                <footer><span class="plugin-catalog-author">@if ( $plugin['permissionCount'] > 0 )<span @class(['plugin-catalog-permissions-granted' => $plugin['grantedCount'] >= $plugin['permissionCount']])>[{{ $plugin['grantedCount'] }}/{{ $plugin['permissionCount'] }}]</span>@endif<span>{{ $plugin['package']['author'] }}</span></span><x-filament::badge color="gray">v{{ $plugin['package']['version'] }}</x-filament::badge></footer>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>
            <aside class="plugin-catalog-sidebar">
                <div class="plugin-catalog-tabs" role="tablist" aria-label="{{ __( 'lar::AdminControl.Plugin.title' ) }}">
                    @foreach ( ['expand', 'depend', 'invalid'] as $type )
                        <button type="button" id="plugin-tab-{{ $type }}" role="tab" class="plugin-catalog-tab"
                            aria-controls="plugin-panel-{{ $type }}" x-bind:aria-selected="category === '{{ $type }}'"
                            x-on:click="category = '{{ $type }}'">
                            {{ __( "lar::AdminControl.Plugin.{$type}" ) }} · {{ count( $groups[$type] ) }}
                        </button>
                    @endforeach
                </div>
                @foreach ( ['expand', 'depend', 'invalid'] as $type )
                    <div id="plugin-panel-{{ $type }}" role="tabpanel" aria-labelledby="plugin-tab-{{ $type }}" x-show="category === '{{ $type }}'" x-cloak>
                        @if ( !$groups[$type] )
                            <p class="plugin-catalog-empty">{{ __( trim( $search ) === '' ? 'lar::AdminControl.Plugin.emptyGroup' : 'lar::AdminControl.Plugin.noMatches' ) }}</p>
                        @else
                            @foreach ( $groups[$type] as $plugin )
                                <article class="plugin-catalog-item" wire:key="plugin-{{ $plugin['id'] }}">
                                    @if ( $type === 'invalid' )
                                        <div class="plugin-catalog-item-heading">
                                            <h3 class="plugin-catalog-heading-text">{{ $plugin['id'] }}</h3>
                                            @include( 'Filament::AdminControl.PluginList.plugin-menu' )
                                        </div>
                                    @else
                                        <div class="plugin-catalog-item-heading">
                                            @if ( $canViewPlugin )
                                                <button type="button" class="plugin-catalog-view"
                                                    aria-label="{{ __( 'lar::AdminControl.Plugin.view' ) }}：{{ $plugin['package']['name'] }}"
                                                    wire:click="mountAction('viewPlugin', {{ \Illuminate\Support\Js::from( ['id' => $plugin['id']] ) }})">
                                            @else
                                                <div class="plugin-catalog-view">
                                            @endif
                                                <span class="plugin-catalog-name">{{ $plugin['package']['name'] }}</span>
                                                <span class="plugin-catalog-id">{{ $plugin['id'] }}</span>
                                            @if ( $canViewPlugin )
                                                </button>
                                            @else
                                                </div>
                                            @endif
                                            @include( 'Filament::AdminControl.PluginList.plugin-menu' )
                                        </div>
                                        <div class="plugin-catalog-item-meta"><span class="plugin-catalog-author">@if ( $plugin['permissionCount'] > 0 )<span @class(['plugin-catalog-permissions-granted' => $plugin['grantedCount'] >= $plugin['permissionCount']])>[{{ $plugin['grantedCount'] }}/{{ $plugin['permissionCount'] }}]</span>@endif<span>{{ $plugin['package']['author'] }}</span></span><x-filament::badge color="gray">v{{ $plugin['package']['version'] }}</x-filament::badge></div>
                                    @endif
                                </article>
                            @endforeach
                        @endif
                    </div>
                @endforeach
            </aside>
        </div>
    </div>
    <style>
        /* 插件分类与元数据展示 */
        .plugin-catalog .plugin-catalog-item-heading { display: grid; grid-template-columns: minmax(0, 1fr) auto; align-items: center; column-gap: .75rem; }
        .plugin-catalog .plugin-catalog-view { display: block; flex: 1; min-width: 0; border-radius: .375rem; text-align: start; }
        .plugin-catalog button.plugin-catalog-view { cursor: pointer; }
        .plugin-catalog .plugin-catalog-view-card { display: flex; align-items: center; gap: .75rem; }
        .plugin-catalog button.plugin-catalog-view:focus-visible { outline: 2px solid var(--primary-500); outline-offset: 4px; }
        .plugin-catalog button.plugin-catalog-view:hover .plugin-catalog-name { color: var(--primary-500); }
        .plugin-catalog .plugin-catalog-item .plugin-catalog-view > .plugin-catalog-id { margin-top: .5rem; }
        .plugin-catalog .plugin-catalog-item-heading > .plugin-catalog-menu { align-self: start; }
        .plugin-catalog .plugin-catalog-heading-text { flex: 1; min-width: 0; }
        .plugin-catalog .plugin-catalog-menu { display: flex; align-items: center; flex-shrink: 0; gap: .75rem; }
        .plugin-catalog .plugin-catalog-layout { display: grid; grid-template-columns: minmax(0, 7fr) minmax(18rem, 3fr); align-items: start; gap: 1.5rem; }
        .plugin-catalog .plugin-catalog-sidebar { min-width: 0; border: 1px solid var(--gray-200); border-radius: .75rem; background: white; overflow: hidden; }
        .plugin-catalog .plugin-catalog-sidebar > [role="tabpanel"] { max-height: 600px; overflow-y: auto; }
        .plugin-catalog .plugin-catalog-tabs { display: flex; gap: .25rem; padding: .75rem; border-bottom: 1px solid var(--gray-200); overflow-x: auto; }
        .plugin-catalog .plugin-catalog-tab { flex: 1; padding: .625rem .25rem; border-radius: .5rem; color: var(--gray-500); font-size: .8125rem; white-space: nowrap; cursor: pointer; }
        .plugin-catalog .plugin-catalog-tab[aria-selected="true"] { background: var(--primary-50); color: var(--primary-600); font-weight: 600; }
        .plugin-catalog .plugin-catalog-item { display: grid; gap: .5rem; padding: 1rem; border-bottom: 1px solid var(--gray-100); }
        .plugin-catalog .plugin-catalog-item:last-child { border-bottom: 0; }
        .plugin-catalog .plugin-catalog-item-meta { display: flex; align-items: center; justify-content: space-between; gap: .5rem; color: var(--gray-500); font-size: .75rem; }
        .plugin-catalog .plugin-catalog-sidebar .plugin-catalog-empty { margin: 1rem; }
        .dark .plugin-catalog .plugin-catalog-card, .dark .plugin-catalog .plugin-catalog-sidebar { border-color: var(--gray-700); background: var(--gray-900); }
        .dark .plugin-catalog .plugin-catalog-tabs, .dark .plugin-catalog .plugin-catalog-item, .dark .plugin-catalog .plugin-catalog-card footer { border-color: var(--gray-800); }
        .dark .plugin-catalog .plugin-catalog-tab[aria-selected="true"] { background: var(--gray-800); color: var(--primary-400); }
        .dark .plugin-catalog .plugin-catalog-description, .dark .plugin-catalog .plugin-catalog-id, .dark .plugin-catalog .plugin-catalog-item-meta { color: var(--gray-400); }
        @media (max-width: 1024px) { .plugin-catalog .plugin-catalog-layout { grid-template-columns: minmax(0, 1fr); } }
        .plugin-catalog { display: grid; gap: 1.5rem; }
        .plugin-catalog h3, .plugin-catalog .plugin-catalog-name { display: block; min-width: 0; overflow: hidden; font-size: .875rem; font-weight: 600; text-overflow: ellipsis; white-space: nowrap; }
        .plugin-catalog .plugin-catalog-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem; }
        .plugin-catalog .plugin-catalog-card { display: flex; flex-direction: column; gap: 1rem; padding: 1.25rem; border: 1px solid var(--gray-200); border-radius: .75rem; background: white; }
        .plugin-catalog .plugin-catalog-card-heading { display: flex; align-items: center; gap: .75rem; }
        .plugin-catalog .plugin-catalog-card-heading > .plugin-catalog-menu { align-self: start; margin-top: .25rem; }
        .plugin-catalog .plugin-catalog-icon { display: flex; align-items: center; justify-content: center; flex-shrink: 0; width: 2.75rem; height: 2.75rem; border-radius: .625rem; background: var(--primary-50); color: var(--primary-600); font-size: 1.125rem; font-weight: 600; line-height: 1; }
        .plugin-catalog .plugin-catalog-id { display: block; min-width: 0; overflow: hidden; color: var(--gray-500); font-size: .75rem; font-weight: 400; text-overflow: ellipsis; white-space: nowrap; }
        .plugin-catalog .plugin-catalog-card footer > span:first-child, .plugin-catalog .plugin-catalog-item-meta > span:first-child { flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .plugin-catalog .plugin-catalog-card footer > .fi-badge, .plugin-catalog .plugin-catalog-item-meta > .fi-badge { flex-shrink: 0; }
        .plugin-catalog .plugin-catalog-author { display: flex; align-items: center; gap: .375rem; }
        .plugin-catalog .plugin-catalog-permissions-granted { color: var(--success-600); }
        .dark .plugin-catalog .plugin-catalog-permissions-granted { color: var(--success-400); }
        .plugin-catalog .plugin-catalog-author > span:first-child:not(:last-child) { flex-shrink: 0; }
        .plugin-catalog .plugin-catalog-author > span:last-child { min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .plugin-catalog .plugin-catalog-layout > section, .plugin-catalog .plugin-catalog-card, .plugin-catalog .plugin-catalog-item-heading, .plugin-catalog .plugin-catalog-item-meta { min-width: 0; }
        .plugin-catalog .plugin-catalog-description { display: -webkit-box; flex-shrink: 0; height: 3.2em; overflow: hidden; color: var(--gray-500); font-size: .875rem; line-height: 1.6; overflow-wrap: anywhere; -webkit-box-orient: vertical; -webkit-line-clamp: 2; }
        .plugin-catalog .plugin-catalog-card footer { display: flex; align-items: center; justify-content: space-between; gap: 1rem; margin-top: auto; padding-top: .75rem; border-top: 1px solid var(--gray-100); color: var(--gray-500); font-size: .75rem; }
        .plugin-catalog .plugin-catalog-empty { padding: 1.5rem; border: 1px dashed var(--gray-200); border-radius: .75rem; color: var(--gray-500); font-size: .875rem; }
        html.fi.dark .plugin-catalog .plugin-catalog-empty {
            border-color: color-mix( in oklab, var( --color-white ) 10%, transparent );
        }
        .plugin-catalog .plugin-catalog-error { color: var(--danger-600); }
        .dark .plugin-catalog .plugin-catalog-icon { background: var(--gray-800); color: var(--primary-400); }
        @media (max-width: 640px) { .plugin-catalog .plugin-catalog-grid { grid-template-columns: minmax(0, 1fr); } }
        /* 插件安装弹窗的安装方式导航 */
        .plugin-install-modal .plugin-install-method { gap: 1.5rem; border-bottom: 1px solid var(--gray-200); }
        .plugin-install-modal .plugin-install-method .fi-btn { padding: .75rem .25rem; border: 0; border-bottom: 2px solid transparent; border-radius: 0; background: transparent; box-shadow: none; color: var(--gray-500); transition: color .15s, border-color .15s; }
        .plugin-install-modal .plugin-install-method input:checked + .fi-btn { border-bottom-color: var(--primary-600); background: transparent; color: var(--primary-600); }
        .plugin-install-modal .plugin-install-method .fi-btn .fi-icon { color: inherit; }
        .plugin-install-modal .plugin-install-method .fi-btn:hover { background: transparent; color: var(--primary-600); }
        .plugin-install-modal .plugin-install-method input:focus-visible + .fi-btn { outline: 2px solid var(--primary-600); outline-offset: 3px; }
        .dark .plugin-install-modal .plugin-install-method { border-bottom-color: var(--gray-700); }
        .dark .plugin-install-modal .plugin-install-method .fi-btn { color: var(--gray-400); }
        .dark .plugin-install-modal .plugin-install-method input:checked + .fi-btn, .dark .plugin-install-modal .plugin-install-method .fi-btn:hover { color: var(--primary-400); border-bottom-color: var(--primary-400); }
        @media (max-width: 480px) { .plugin-install-modal .plugin-install-method { gap: .75rem; } }
    </style>
</x-filament-panels::page>
