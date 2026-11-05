<x-filament-panels::page class="icons-page">
    <div x-data="{
        async copyIcon(value, showTooltip) {
            try {
                if (window.isSecureContext &amp;&amp; navigator.clipboard) {
                    await navigator.clipboard.writeText(value);
                } else {
                    const input = document.createElement('textarea');
                    input.value = value;
                    input.style.position = 'fixed';
                    input.style.opacity = '0';
                    document.body.appendChild(input);
                    try {
                        input.select();
                        if (!document.execCommand('copy')) { throw new Error('Copy failed'); }
                    } finally { input.remove(); }
                }
                showTooltip(@js( __( 'lar::DeveloperCenter.Icons.copied' ) ));
            } catch (error) {
                showTooltip(@js( __( 'lar::DeveloperCenter.Icons.copyFailed' ) ));
            }
        }
    }">
        {{-- 图标搜索与样式切换 --}}
        <x-filament::section icon="heroicon-o-squares-2x2">
            <x-slot name="heading">{{ __( 'lar::DeveloperCenter.Icons.heading' ) }}</x-slot>
            <x-slot name="description">{{ __( 'lar::DeveloperCenter.Icons.description' ) }}</x-slot>
            <div class="icons-toolbar">
                <div class="icons-search">
                    <x-filament::input.wrapper prefix-icon="heroicon-o-magnifying-glass">
                        <x-filament::input type="search" wire:model.live.debounce.300ms="search" :placeholder="__( 'lar::DeveloperCenter.Icons.search' )" :aria-label="__( 'lar::DeveloperCenter.Icons.search' )" />
                    </x-filament::input.wrapper>
                </div>
                <div class="icons-styles" role="group" aria-label="{{ __( 'lar::DeveloperCenter.Icons.style' ) }}">
                    @foreach ( ['all', 'outline', 'solid'] as $style )
                        <x-filament::button :color="$this->style === $style ? 'primary' : 'gray'" size="sm" wire:click="$set('style', '{{ $style }}')" :aria-pressed="$this->style === $style ? 'true' : 'false'">
                            {{ __( 'lar::DeveloperCenter.Icons.'.$style ) }}
                        </x-filament::button>
                    @endforeach
                </div>
                <span class="icons-count">{{ __( 'lar::DeveloperCenter.Icons.count', ['count' => $count] ) }}</span>
            </div>
        </x-filament::section>
        {{-- 点击图标卡片复制枚举引用 --}}
        <div class="icons-grid">
            @forelse ( $icons as $icon )
                <button type="button" class="icons-card" wire:key="icon-{{ $icon['name'] }}" x-on:click="copyIcon(@js( $icon['code'] ), (message) => $tooltip(message, { theme: $store.theme, timeout: 1500 }))" title="{{ __( 'lar::DeveloperCenter.Icons.copy', ['code' => $icon['code']] ) }}">
                    <span class="icons-preview"><x-filament::icon :icon="$icon['svg']" /></span>
                    <strong class="icons-name">{{ $icon['name'] }}</strong>
                    <code>{{ $icon['code'] }}</code>
                    <code>{{ $icon['svg'] }}</code>
                    <x-filament::badge color="gray">{{ __( 'lar::DeveloperCenter.Icons.'.$icon['style'] ) }}</x-filament::badge>
                </button>
            @empty
                <p class="icons-empty">{{ __( 'lar::DeveloperCenter.Icons.empty' ) }}</p>
            @endforelse
        </div>
        <div class="icons-pagination">
            <x-filament::pagination :paginator="$icons" />
        </div>
    </div>
    <style>
        /* 图标搜索工具栏及响应式网格，强调色跟随面板主题 */
        .icons-page .icons-toolbar { display: flex; align-items: center; flex-wrap: wrap; gap: 1rem; }
        .icons-page .icons-search { flex: 1; min-width: 12rem; }
        .icons-page .icons-styles { display: flex; align-items: center; gap: .25rem; }
        .icons-page .icons-count { color: var(--gray-500); font-size: .875rem; white-space: nowrap; }
        .icons-page .icons-grid { display: grid; margin-top: 1.5rem; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 1rem; }
        .icons-page .icons-card { display: flex; align-items: center; flex-direction: column; min-width: 0; padding: 1.25rem 1rem; border: 1px solid var(--gray-200); border-radius: .75rem; gap: .75rem; cursor: pointer; transition: border-color .15s, background-color .15s; }
        .dark .icons-page .icons-card { border-color: var(--gray-700); }
        .icons-page .icons-card:hover, .icons-page .icons-card:focus-visible { border-color: var(--primary-500); background: color-mix(in srgb, var(--primary-500) 4%, transparent); outline: 2px solid transparent; }
        .icons-page .icons-preview { display: flex; align-items: center; justify-content: center; width: 3.5rem; height: 3.5rem; border-radius: .75rem; background: color-mix(in srgb, var(--primary-500) 12%, transparent); color: var(--primary-500); }
        .icons-page .icons-preview svg { width: 2rem; height: 2rem; }
        .icons-page .icons-name { max-width: 100%; font-size: .9375rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .icons-page .icons-card code { display: block; max-width: 100%; color: var(--gray-500); font-size: .75rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .icons-page .icons-empty { grid-column: 1 / -1; padding: 2rem; color: var(--gray-500); text-align: center; }
        .icons-page .icons-pagination { margin-top: 1.5rem; }
        @media (max-width: 1280px) { .icons-page .icons-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        @media (max-width: 640px) { .icons-page .icons-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (prefers-reduced-motion: reduce) { .icons-page .icons-card { transition: none; } }
    </style>
</x-filament-panels::page>
