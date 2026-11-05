{{-- 插件条目操作菜单 --}}
<div class="plugin-catalog-menu">
    @if ( $plugin['hasAdmin'] ?? false )
        <x-filament::icon-button
            :icon="\Filament\Support\Icons\Heroicon::OutlinedWindow"
            color="gray"
            :label="__( 'lar::AdminControl.Plugin.admin' )"
            wire:click="mountAction('pluginAdmin', {{ \Illuminate\Support\Js::from( ['id' => $plugin['id']] ) }})"
        />
    @endif
    @if ( \LarExtend\Filament\Resources\AdminControl\PluginList\PluginResource::canMaintain() )
        <x-filament::dropdown placement="bottom-end" teleport>
            <x-slot name="trigger">
                <x-filament::icon-button :icon="\Filament\Support\Icons\Heroicon::OutlinedEllipsisHorizontalCircle" color="gray" :label="__( 'lar::AdminControl.Plugin.actions' )" />
            </x-slot>
            <x-filament::dropdown.list>
                @if ( isset( $plugin['package'] ) )
                    <x-filament::dropdown.list.item icon="heroicon-o-eye"
                        wire:click="mountAction('viewPlugin', {{ \Illuminate\Support\Js::from( ['id' => $plugin['id']] ) }})">
                        {{ __( 'lar::AdminControl.Plugin.view' ) }}
                    </x-filament::dropdown.list.item>
                    <x-filament::dropdown.list.item icon="heroicon-o-adjustments-horizontal"
                        wire:click="mountAction('editPluginConfig', {{ \Illuminate\Support\Js::from( ['id' => $plugin['id']] ) }})">
                        {{ __( 'lar::AdminControl.Plugin.editConfig' ) }}
                    </x-filament::dropdown.list.item>
                    @if ( trim( $plugin['package']['source'] ) !== '' )
                        <x-filament::dropdown.list.item icon="heroicon-o-arrow-down-tray"
                            wire:click="mountAction('updatePlugin', {{ \Illuminate\Support\Js::from( ['id' => $plugin['id']] ) }})">
                            {{ __( 'lar::AdminControl.Plugin.update' ) }}
                        </x-filament::dropdown.list.item>
                    @endif
                    @if ( $plugin['hasLog'] ?? false )
                        <x-filament::dropdown.list.item icon="heroicon-o-document-text"
                            wire:click="mountAction('pluginLogs', {{ \Illuminate\Support\Js::from( ['id' => $plugin['id']] ) }})">
                            {{ __( 'lar::AdminControl.Plugin.logs' ) }}
                        </x-filament::dropdown.list.item>
                    @endif
                    <x-filament::dropdown.list.item icon="heroicon-o-archive-box"
                        wire:click="mountAction('backupPlugin', {{ \Illuminate\Support\Js::from( ['id' => $plugin['id']] ) }})">
                        {{ __( 'lar::AdminControl.Plugin.backup' ) }}
                    </x-filament::dropdown.list.item>
                    <x-filament::dropdown.list.item icon="heroicon-o-arrow-path" color="warning"
                        wire:click="mountAction('resetPlugin', {{ \Illuminate\Support\Js::from( ['id' => $plugin['id']] ) }})">
                        {{ __( 'lar::AdminControl.Plugin.reset' ) }}
                    </x-filament::dropdown.list.item>
                @endif
                <x-filament::dropdown.list.item icon="heroicon-o-trash" color="danger"
                    wire:click="mountAction('uninstallPlugin', {{ \Illuminate\Support\Js::from( ['id' => $plugin['id']] ) }})">
                    {{ __( 'lar::AdminControl.Plugin.uninstall' ) }}
                </x-filament::dropdown.list.item>
            </x-filament::dropdown.list>
        </x-filament::dropdown>
    @endif
</div>
