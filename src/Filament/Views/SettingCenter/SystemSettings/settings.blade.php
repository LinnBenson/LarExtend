<x-filament-panels::page>
<x-Filament::asset-upload-form class="lar-settings-layout">
    <style>
        /* 设置分类与表单卡片布局 */
        .lar-settings-layout { display: grid; gap: 2rem; }
        /* 当前设置分类 */
        .lar-settings-tabs.fi-tabs { width: 100%; gap: 0.5rem; padding: 0.4375rem; border: 1px solid var(--gray-200); border-radius: 1rem; background: white; }
        .lar-settings-tabs .fi-tabs-item { flex: 1 0 0; min-width: 0; padding: 0.8125rem 0.9375rem; border: 1px solid transparent; border-radius: 0.75rem; text-align: left; }
        .lar-settings-tabs .fi-tabs-item-label { display: flex; align-items: center; width: 100%; gap: 0.75rem; }
        .lar-settings-tabs .lar-category-icon { display: flex; align-items: center; justify-content: center; width: 2.75rem; height: 2.75rem; flex-shrink: 0; border-radius: 0.875rem; background: var(--gray-100); color: var(--gray-500); }
        .lar-settings-tabs .lar-category-icon .fi-icon { width: 1.25rem; height: 1.25rem; }
        .lar-settings-tabs .lar-category-info { display: flex; flex-direction: column; gap: 0.25rem; min-width: 0; }
        .lar-settings-tabs .lar-category-title { font-size: 0.95rem; font-weight: 650; }
        .lar-settings-tabs .lar-category-count { color: var(--gray-500); font-size: 0.75rem; font-weight: 400; }
        .lar-settings-tabs .fi-tabs-item.fi-active { border-color: var(--primary-200); background: var(--primary-50); }
        .lar-settings-tabs .fi-tabs-item.fi-active .lar-category-icon { background: white; color: var(--primary-600); }
        .lar-settings-tabs .fi-tabs-item.fi-active .lar-category-title { color: var(--primary-600); }
        .dark .lar-settings-tabs.fi-tabs { border-color: var(--gray-700); background: var(--gray-900); }
        .dark .lar-settings-tabs .lar-category-icon { background: var(--gray-800); color: var(--gray-400); }
        .dark .lar-settings-tabs .lar-category-count { color: var(--gray-400); }
        .dark .lar-settings-tabs .fi-tabs-item.fi-active { border-color: var(--primary-700); background: color-mix(in srgb, var(--primary-400) 12%, transparent); }
        .dark .lar-settings-tabs .fi-tabs-item.fi-active .lar-category-icon { background: var(--gray-900); color: var(--primary-400); }
        .dark .lar-settings-tabs .fi-tabs-item.fi-active .lar-category-title { color: var(--primary-400); }
        @media (max-width: 640px) {
            .lar-settings-tabs.fi-tabs { gap: 0.25rem; }
            .lar-settings-tabs .fi-tabs-item { padding: 0.625rem 0.25rem; text-align: center; }
            .lar-settings-tabs .fi-tabs-item-label { flex-direction: column; gap: 0.5rem; }
            .lar-settings-tabs .lar-category-title { font-size: 0.85rem; }
            .lar-settings-tabs .lar-category-count { display: none; font-size: 0.65rem; }
        }
        /* 设置表单统一标题列 */
        .lar-settings-form .lar-settings-actions { display: flex; flex-wrap: wrap; gap: 0.75rem; margin-top: 2rem; }
        .lar-settings-form .fi-fo-field-has-inline-label { grid-template-columns: 8rem minmax(0, 1fr); align-items: start; column-gap: 1rem; }
        .lar-settings-form .fi-fo-field-has-inline-label > .fi-fo-field-content-col { grid-column: auto; min-width: 0; }
        .lar-settings-form .fi-fo-field-has-inline-label > .fi-fo-field-label-col { align-self: start; align-items: start; height: auto; padding-top: 0.5rem; }
        @media (min-width: 641px) {
            .lar-settings-form .lar-settings-actions { margin-left: 9rem; }
            .lar-settings-form .fi-fo-field-has-inline-label > .fi-fo-field-label-col { justify-items: end; text-align: right; }
            .lar-settings-form .fi-fo-field-has-inline-label:has(.fi-fo-toggle) > .fi-fo-field-label-col { padding-top: 0.125rem; }
        }
        @media (max-width: 640px) {
            .lar-settings-form .fi-fo-field-has-inline-label { grid-template-columns: minmax(0, 1fr); row-gap: 0.5rem; }
            .lar-settings-form .fi-fo-field-has-inline-label > .fi-fo-field-label-col { padding-top: 0; }
        }
    </style>
    {{-- 分类选择与设置表单 --}}
    @if ( count( $this->getCategoryOptions() ) > 1 )
        @php( $categoryCounts = $this->getCategoryCounts() )
        <x-filament::tabs contained class="lar-settings-tabs" :label="__( 'lar::SettingCenter.SystemSettings.category' )">
            @foreach ( $this->getCategoryOptions() as $key => $label )
                <x-filament::tabs.item :active="$category === $key" wire:click="$set('category', '{{ $key }}')" wire:key="category-{{ $key }}" x-bind:disabled="uploads > 0">
                    <span class="lar-category-icon">
                        <x-filament::icon :icon="match ( $key ) { 'app' => 'heroicon-o-squares-2x2', 'system' => 'heroicon-o-cpu-chip', default => 'heroicon-o-adjustments-horizontal' }" />
                    </span>
                    <span class="lar-category-info">
                        <span class="lar-category-title">{{ $label }}</span>
                        <span class="lar-category-count">{{ __( 'lar::SettingCenter.SystemSettings.itemCount', ['count' => $categoryCounts[$key] ?? 0] ) }}</span>
                    </span>
                </x-filament::tabs.item>
            @endforeach
        </x-filament::tabs>
    @endif
    <form wire:submit="save" class="lar-settings-form space-y-6">
        <x-filament::section :heading="$this->getCategoryOptions()[$category] ?? ''">
            @if ( count( $loadedIds ) === 0 )
                <x-filament::empty-state
                    :contained="false"
                    icon="heroicon-o-adjustments-horizontal"
                    icon-color="gray"
                    :heading="__( 'lar::SettingCenter.SystemSettings.emptyHeading' )"
                    :description="__( 'lar::SettingCenter.SystemSettings.emptyDescription' )"
                />
            @else
                <div wire:key="settings-form-{{ $formRevision }}">
                    {{ $this->form }}
                </div>
                <fieldset class="lar-settings-actions" wire:loading.attr="disabled" wire:target="save,resetSettings">
                    <x-filament::button type="submit" icon="heroicon-o-check" wire:loading.attr="disabled" wire:target="save" x-bind:disabled="uploads > 0">
                        {{ __( 'lar::SettingCenter.SystemSettings.save' ) }}
                    </x-filament::button>
                    <x-filament::button type="button" color="gray" icon="heroicon-o-arrow-uturn-left" wire:click="resetSettings" wire:loading.attr="disabled" wire:target="resetSettings" x-bind:disabled="uploads > 0">
                        {{ __( 'lar::base.reset' ) }}
                    </x-filament::button>
                </fieldset>
            @endif
        </x-filament::section>
    </form>
</x-Filament::asset-upload-form>
</x-filament-panels::page>
