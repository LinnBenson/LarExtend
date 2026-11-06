{{-- 插件元数据详情 --}}
@php( $package = $plugin['package'] )
<div class="plugin-details" x-data="{ detailTab: 'plugins' }" wire:key="plugin-details-{{ $plugin['id'] }}">
    <header class="plugin-details-heading">
        <span class="plugin-details-avatar" aria-hidden="true">{{ mb_substr( $plugin['id'], 0, 2 ) }}</span>
        <div class="plugin-details-identity">
            <h2>{{ $package['name'] }}</h2>
            <p class="plugin-details-id">{{ $plugin['id'] }}</p>
        </div>
            <div class="plugin-details-badges">
                <x-filament::badge>{{ __( 'lar::AdminControl.Plugin.' . $package['type'] ) }}</x-filament::badge>
                <x-filament::badge color="gray">v{{ $package['version'] }}</x-filament::badge>
            </div>
    </header>
    <p class="plugin-details-description">{{ $package['description'] ?: __( 'lar::AdminControl.Plugin.noDescription' ) }}</p>
    <dl class="plugin-details-meta">
        <div><dt>{{ __( 'lar::AdminControl.Plugin.author' ) }}</dt><dd>{{ $package['author'] }}</dd></div>
        <div><dt>{{ __( 'lar::AdminControl.Plugin.source' ) }}</dt><dd>{{ $package['source'] ?: __( 'lar::AdminControl.Plugin.notProvided' ) }}</dd></div>
    </dl>
    <div class="plugin-details-inspector">
        <x-filament::tabs :label="__( 'lar::AdminControl.Plugin.details' )">
            @foreach ( ['plugins' => 'pluginDependencies', 'composer' => 'composerDependencies', 'permissions' => 'declaredPermissions'] as $tab => $label )
                <x-filament::tabs.item :alpine-active="'detailTab === ' . \Illuminate\Support\Js::from( $tab )"
                    x-on:click="detailTab = '{{ $tab }}'"
                    :wire:click="$tab === 'permissions' ? 'loadPluginPermissions(' . \Illuminate\Support\Js::from( $plugin['id'] ) . ')' : null"
                    :badge="count( $tab === 'permissions' ? $package['permissions'] : $package['require'][$tab] )">
                    {{ __( 'lar::AdminControl.Plugin.' . $label ) }}
                </x-filament::tabs.item>
            @endforeach
        </x-filament::tabs>
        @foreach ( ['plugins' => 'pluginDependencies', 'composer' => 'composerDependencies'] as $type => $label )
            <section class="plugin-details-section" x-show="detailTab === '{{ $type }}'" x-cloak aria-label="{{ __( 'lar::AdminControl.Plugin.' . $label ) }}">
                <dl class="plugin-details-requirements">
                    @forelse ( $package['require'][$type] as $name => $version )
                        <div class="plugin-details-row">
                            <dt><span class="plugin-details-row-icon"><x-filament::icon :icon="$type === 'plugins' ? 'heroicon-o-puzzle-piece' : 'heroicon-o-cube'" /></span><span>{{ $name }}</span></dt>
                            <dd><x-filament::badge color="gray">{{ $version }}</x-filament::badge></dd>
                        </div>
                    @empty
                        <div class="plugin-details-empty"><x-filament::icon icon="heroicon-o-cube" /><dd>{{ __( 'lar::AdminControl.Plugin.noDependencies' ) }}</dd></div>
                    @endforelse
                </dl>
            </section>
        @endforeach
    <section class="plugin-details-section" x-show="detailTab === 'permissions'" x-cloak>
        <ul class="plugin-details-permissions">
            @forelse ( $package['permissions'] as $permission )
                <li class="plugin-details-row">
                    <span class="plugin-details-row-icon"><x-filament::icon icon="heroicon-o-key" /></span>
                    <div class="plugin-details-permission-name">
                        <code>{{ $permission }}</code>
                        @if ( $permissionsLoaded && array_key_exists( $permission, $permissionStates ) )
                            <x-filament::badge :color="$permissionStates[$permission] ? 'success' : 'gray'">{{ __( 'lar::AdminControl.Plugin.' . ( $permissionStates[$permission] ? 'permissionEnabled' : 'permissionDisabled' ) ) }}</x-filament::badge>
                        @endif
                    </div>
                    <div class="plugin-details-permission-actions">
                        @if ( $permissionsLoaded && array_key_exists( $permission, $permissionStates ) )
                            @php( $enabled = $permissionStates[$permission] )
                            <x-filament::button size="sm" :color="$enabled ? 'danger' : 'primary'" outlined
                                wire:click="setPluginPermission({{ \Illuminate\Support\Js::from( $plugin['id'] ) }}, {{ \Illuminate\Support\Js::from( $permission ) }}, {{ $enabled ? 'false' : 'true' }})"
                                wire:loading.attr="disabled" wire:target="setAllPluginPermissions,setPluginPermission,loadPluginPermissions">
                                {{ __( 'lar::AdminControl.Plugin.' . ( $enabled ? 'disablePermission' : 'enablePermission' ) ) }}
                            </x-filament::button>
                        @else
                            <span>{{ __( 'lar::AdminControl.Plugin.permissionsNotLoaded' ) }}</span>
                        @endif
                    </div>
                </li>
            @empty
                <li class="plugin-details-empty"><x-filament::icon icon="heroicon-o-shield-check" />{{ __( 'lar::AdminControl.Plugin.noPermissions' ) }}</li>
            @endforelse
        </ul>
    </section>
        @if ( $package['permissions'] && \LarExtend\Filament\Resources\AdminControl\PluginList\PluginResource::canMaintain() )
            <div class="plugin-details-permission-toolbar" x-show="detailTab === 'permissions'" x-cloak>
                <x-filament::button size="sm" icon="heroicon-o-shield-check" :disabled="!$permissionsLoaded"
                    wire:click="setAllPluginPermissions({{ \Illuminate\Support\Js::from( $plugin['id'] ) }}, true)"
                    wire:loading.attr="disabled" wire:target="setAllPluginPermissions,setPluginPermission,loadPluginPermissions">
                    {{ __( 'lar::AdminControl.Plugin.enableAllPermissions' ) }}
                </x-filament::button>
                <x-filament::button size="sm" color="danger" icon="heroicon-o-shield-exclamation" outlined :disabled="!$permissionsLoaded"
                    wire:click="setAllPluginPermissions({{ \Illuminate\Support\Js::from( $plugin['id'] ) }}, false)"
                    wire:loading.attr="disabled" wire:target="setAllPluginPermissions,setPluginPermission,loadPluginPermissions">
                    {{ __( 'lar::AdminControl.Plugin.disableAllPermissions' ) }}
                </x-filament::button>
            </div>
        @endif
    </div>
    @if ( $readme !== null )
        <x-filament::section heading="README.md">
            @if ( $readme === false )
                <p role="alert">{{ __( 'lar::DeveloperCenter.Readme.readFailed' ) }}</p>
            @elseif ( trim( $readme ) === '' )
                <p>{{ __( 'lar::DeveloperCenter.Readme.empty' ) }}</p>
            @else
                <x-Filament::markdown :content="$readme" />
            @endif
        </x-filament::section>
    @endif
</div>
<style>
    /* 插件详情的身份信息与分区布局 */
    .plugin-details { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1.25rem; min-width: 0; width: 100%; color: var(--gray-950); font-size: .875rem; line-height: 1.6; overflow-wrap: anywhere; }
    .plugin-details > *, .plugin-details > .fi-section > .fi-section-content-ctn, .plugin-details > .fi-section > .fi-section-content-ctn > .fi-section-content { min-width: 0; max-width: 100%; }
    .plugin-details .plugin-details-heading { display: grid; grid-template-columns: auto minmax(0, 1fr) auto; align-items: center; gap: 1rem; }
    .plugin-details .plugin-details-avatar { display: flex; align-items: center; justify-content: center; flex-shrink: 0; width: 3.5rem; height: 3.5rem; border-radius: .75rem; background: var(--primary-50); color: var(--primary-600); font-size: 1.5rem; font-weight: 600; }
    .plugin-details .plugin-details-identity { flex: 1; min-width: 0; }
    .plugin-details .plugin-details-identity h2 { font-size: 1.25rem; font-weight: 600; }
    .plugin-details .plugin-details-id { margin-top: .25rem; color: var(--gray-500); font-size: .75rem; }
    .plugin-details .plugin-details-badges { display: flex; flex-wrap: wrap; gap: .5rem; min-width: 0; }
    .plugin-details .fi-badge { max-width: 100%; white-space: normal; overflow-wrap: anywhere; }
    .plugin-details .plugin-details-meta { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1.5rem; padding: 1rem 1.25rem; border-radius: .75rem; background: var(--gray-50); }
    .plugin-details .plugin-details-meta > div { display: grid; align-content: start; gap: .375rem; min-width: 0; }
    .plugin-details .plugin-details-meta dt { font-size: .75rem; }
    .plugin-details .plugin-details-meta dt, .plugin-details .plugin-details-description, .plugin-details .plugin-details-requirements, .plugin-details .plugin-details-permissions { color: var(--gray-500); }
    .plugin-details .plugin-details-permission-toolbar { display: flex; flex-wrap: wrap; align-items: center; justify-content: flex-end; gap: .75rem; padding: .875rem 1rem; border-top: 1px solid var(--gray-200); }
    .plugin-details .plugin-details-inspector { min-width: 0; overflow: hidden; border: 1px solid var(--gray-200); border-radius: .75rem; }
    .plugin-details .plugin-details-inspector > .fi-tabs { justify-content: flex-start; overflow-x: auto; padding: .5rem; border-bottom: 1px solid var(--gray-200); border-radius: 0; background: var(--gray-50); box-shadow: none; }
    .plugin-details .plugin-details-section { min-width: 0; max-height: 18rem; overflow-y: auto; }
    .plugin-details .plugin-details-description { white-space: pre-wrap; }
    .plugin-details .plugin-details-row { display: flex; align-items: center; gap: .75rem; min-width: 0; min-height: 3.75rem; padding: .875rem 1rem; color: var(--gray-700); }
    .plugin-details .plugin-details-row + .plugin-details-row { border-top: 1px solid var(--gray-100); }
    .plugin-details .plugin-details-requirements dt { display: flex; align-items: center; flex: 1; gap: .75rem; font-weight: 500; }
    .plugin-details .plugin-details-requirements dd { flex-shrink: 0; max-width: 40%; }
    .plugin-details .plugin-details-requirements dt > span:last-child { min-width: 0; overflow-wrap: anywhere; }
    .plugin-details .plugin-details-requirements dt, .plugin-details .plugin-details-requirements dd { min-width: 0; }
    .plugin-details .plugin-details-row-icon { display: flex; align-items: center; justify-content: center; flex-shrink: 0; width: 2rem; height: 2rem; border-radius: .5rem; background: var(--gray-100); color: var(--gray-500); }
    .plugin-details .plugin-details-row-icon svg { width: 1.125rem; height: 1.125rem; }
    .plugin-details .plugin-details-permission-name { display: flex; align-items: center; flex: 1; flex-wrap: wrap; gap: .75rem; min-width: 0; }
    .plugin-details .plugin-details-row code { min-width: 0; font-size: .8125rem; overflow-wrap: anywhere; }
    .plugin-details .plugin-details-permission-actions { display: flex; align-items: center; flex-shrink: 0; flex-wrap: wrap; gap: .75rem; margin-left: auto; }
    .plugin-details .plugin-details-empty { display: flex; align-items: center; justify-content: center; gap: .625rem; min-height: 5rem; color: var(--gray-400); }
    .plugin-details .plugin-details-requirements > .plugin-details-empty { justify-content: center; }
    .plugin-details .plugin-details-empty svg { width: 1.25rem; height: 1.25rem; }
    .dark .plugin-details { color: var(--gray-100); }
    .dark .plugin-details .plugin-details-avatar { background: var(--gray-800); color: var(--primary-400); }
    .dark .plugin-details .plugin-details-meta { background: var(--gray-800); }
    .dark .plugin-details .plugin-details-id { color: var(--gray-400); }
    .dark .plugin-details .plugin-details-permission-toolbar, .dark .plugin-details .plugin-details-inspector, .dark .plugin-details .plugin-details-inspector > .fi-tabs, .dark .plugin-details .plugin-details-row + .plugin-details-row { border-color: var(--gray-700); }
    .dark .plugin-details .plugin-details-inspector > .fi-tabs, .dark .plugin-details .plugin-details-row-icon { background: var(--gray-800); }
    .dark .plugin-details .plugin-details-row { color: var(--gray-200); }
    .dark .plugin-details .plugin-details-row-icon { color: var(--gray-400); }
    .dark .plugin-details .plugin-details-meta dt, .dark .plugin-details .plugin-details-description, .dark .plugin-details .plugin-details-requirements, .dark .plugin-details .plugin-details-permissions { color: var(--gray-400); }
    @media (max-width: 640px) {
        .plugin-details { gap: 1rem; }
        .plugin-details .plugin-details-heading { grid-template-columns: auto minmax(0, 1fr); align-items: start; gap: .75rem; } .plugin-details .plugin-details-badges { grid-column: 1 / -1; } .plugin-details .plugin-details-identity h2 { font-size: 1.125rem; }
        .plugin-details .plugin-details-meta { grid-template-columns: minmax(0, 1fr); gap: .75rem; padding: 1rem; }
        .plugin-details .plugin-details-inspector > .fi-tabs { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); width: 100%; gap: .25rem; overflow: visible; padding: .375rem; }
        .plugin-details .fi-tabs-item { flex-direction: column; gap: .375rem; min-width: 0; padding: .5rem .25rem; font-size: .75rem; white-space: normal; text-align: center; } .plugin-details .fi-tabs-item-label { overflow-wrap: anywhere; }
        .plugin-details .plugin-details-inspector > .fi-tabs .fi-badge, .plugin-details .plugin-details-row-icon { display: none; }
        .plugin-details .plugin-details-section { max-height: none; overflow: visible; }
        .plugin-details .plugin-details-row { align-items: center; gap: .625rem; padding: .875rem .75rem; } .plugin-details .plugin-details-requirements .plugin-details-row { flex-direction: row; } .plugin-details .plugin-details-requirements dt { flex: 1; gap: .625rem; } .plugin-details .plugin-details-requirements dd { flex-shrink: 0; max-width: none; margin-left: 0; }
        .plugin-details .plugin-details-requirements dt > span:last-child, .plugin-details .plugin-details-permission-name code { flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .plugin-details .plugin-details-requirements dd > .fi-badge, .plugin-details .plugin-details-permission-name > .fi-badge { flex-shrink: 0; max-width: none; white-space: nowrap; }
        .plugin-details .plugin-details-permissions .plugin-details-row { display: grid; grid-template-columns: minmax(0, 1fr); } .plugin-details .plugin-details-permission-name { flex-wrap: nowrap; gap: .5rem; } .plugin-details .plugin-details-permission-actions { grid-column: 1; justify-content: flex-end; min-width: 0; max-width: 100%; margin-left: 0; }
    }
</style>
