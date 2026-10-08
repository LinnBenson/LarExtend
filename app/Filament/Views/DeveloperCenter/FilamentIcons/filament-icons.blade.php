<x-filament-panels::page>
    @php
        $t = fn ( string $key, array $replace = [] ): string => __( "admin::DeveloperCenter.FilamentIcons.{$key}", $replace );
        $iconData = $this->getIconData();
    @endphp

    {{-- 图标搜索和分类 --}}
    <x-filament::section
        :heading="$t( 'heading' )"
        :description="$t( 'description' )"
        icon="heroicon-o-squares-2x2"
    >
        <div class="filament-icons-toolbar">
            <div class="filament-icons-toolbar-search">
                <x-filament::icon icon="heroicon-o-magnifying-glass" />
                <input
                    type="search"
                    placeholder="{{ $t( 'search' ) }}"
                    wire:model.live.debounce.300ms="search"
                >
            </div>
            <div class="filament-icons-toolbar-filters">
                @foreach ( ['all' => $t( 'styles.all' ), 'outline' => $t( 'styles.outline' ), 'solid' => $t( 'styles.solid' )] as $style => $label )
                    <button
                        type="button"
                        class="filament-icons-toolbar-filter{{ $iconStyle === $style ? ' is-active' : '' }}"
                        wire:click="setIconStyle('{{ $style }}')"
                    >
                        {{ $label }}
                    </button>
                @endforeach
            </div>
            <div class="filament-icons-toolbar-count">
                {{ $t( 'count', ['count' => $iconData['total']] ) }}
            </div>
        </div>
    </x-filament::section>

    {{-- 图标展示网格 --}}
    @if ( $iconData['total'] === 0 )
        <x-filament::section>
            <div class="filament-icons-empty">{{ $t( 'empty' ) }}</div>
        </x-filament::section>
    @else
        <div
            class="filament-icons-grid"
            x-data="{
                async copyIcon( value ) {
                    try {
                        if ( window.navigator.clipboard ) {
                            await window.navigator.clipboard.writeText( value );
                        }else {
                            this.copyFallback( value );
                        }
                    }catch ( error ) {
                        try {
                            this.copyFallback( value );
                        }catch ( fallbackError ) {
                            return false;
                        }
                    }
                    return true;
                },
                copyFallback( value ) {
                    const input = document.createElement( 'textarea' );
                    input.value = value;
                    input.setAttribute( 'readonly', '' );
                    input.style.position = 'fixed';
                    input.style.opacity = '0';
                    document.body.appendChild( input );
                    try {
                        input.select();
                        if ( !document.execCommand( 'copy' ) ) { throw new Error( 'Copy failed' ); }
                    }finally {
                        input.remove();
                    }
                },
            }"
        >
            @foreach ( $iconData['icons'] as $icon )
                <div wire:key="icon-{{ $icon['name'] }}" class="filament-icons-grid-item">
                    <div class="filament-icons-grid-item-preview">
                        <x-filament::icon :icon="$icon['preview']" />
                    </div>
                    <div class="filament-icons-grid-item-name" title="{{ $icon['name'] }}">
                        {{ $icon['name'] }}
                    </div>
                    <button
                        type="button"
                        class="filament-icons-grid-item-code fi-copyable"
                        title="{{ $t( 'copy', ['value' => $icon['usage']] ) }}"
                        x-on:click="
                            const copied = await copyIcon( {{ Illuminate\Support\Js::from( $icon['usage'] ) }} );
                            $tooltip( copied ? {{ Illuminate\Support\Js::from( $t( 'copied' ) ) }} : {{ Illuminate\Support\Js::from( $t( 'copy_failed' ) ) }}, {
                                theme: $store.theme,
                                timeout: 2000,
                            })
                        "
                    >
                        <code>{{ $icon['usage'] }}</code>
                    </button>
                    <button
                        type="button"
                        class="filament-icons-grid-item-value fi-copyable"
                        title="{{ $t( 'copy', ['value' => $icon['preview']] ) }}"
                        x-on:click="
                            const copied = await copyIcon( {{ Illuminate\Support\Js::from( $icon['preview'] ) }} );
                            $tooltip( copied ? {{ Illuminate\Support\Js::from( $t( 'copied' ) ) }} : {{ Illuminate\Support\Js::from( $t( 'copy_failed' ) ) }}, {
                                theme: $store.theme,
                                timeout: 2000,
                            })
                        "
                    >
                        <code>{{ $icon['preview'] }}</code>
                    </button>
                    <span class="filament-icons-grid-item-style">{{ $icon['style'] }}</span>
                </div>
            @endforeach
        </div>

    @endif

    <style>
        /* 图标页面搜索工具栏 */
        .filament-icons-toolbar {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .filament-icons-toolbar-search {
            display: flex;
            min-width: min(16rem, 100%);
            padding: 0.625rem 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            align-items: center;
            gap: 0.5rem;
            flex: 1;
        }
        .filament-icons-toolbar-search:focus-within {
            border-color: var(--primary-500);
            box-shadow: 0 0 0 1px var(--primary-500);
        }
        .filament-icons-toolbar-search svg {
            width: 1.25rem;
            height: 1.25rem;
            color: #6b7280;
        }
        .filament-icons-toolbar-search input {
            width: 100%;
            border: 0;
            outline: 0;
            background: transparent;
        }
        .filament-icons-toolbar-filters {
            display: flex;
            padding: 0.25rem;
            border-radius: 0.5rem;
            background: rgba(107, 114, 128, 0.1);
            gap: 0.25rem;
        }
        .filament-icons-toolbar-filter {
            padding: 0.5rem 0.75rem;
            border-radius: 0.375rem;
            color: #6b7280;
            cursor: pointer;
        }
        .filament-icons-toolbar-filter.is-active {
            background: var(--primary-500);
            color: #fff;
            font-weight: 600;
        }
        .filament-icons-toolbar-count {
            color: #6b7280;
            white-space: nowrap;
        }
        /* 图标预览网格 */
        .filament-icons-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(13rem, 1fr));
            gap: 0.75rem;
        }
        .filament-icons-grid-item {
            display: flex;
            min-width: 0;
            padding: 1rem;
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            background: var(--color-white);
            align-items: center;
            flex-direction: column;
            gap: 0.5rem;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }
        .filament-icons-grid-item:hover {
            border-color: var(--primary-500);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }
        .filament-icons-grid-item-preview {
            display: flex;
            width: 3.5rem;
            height: 3.5rem;
            border-radius: 0.75rem;
            background: color-mix(in srgb, var(--primary-500) 12%, transparent);
            color: var(--primary-600);
            align-items: center;
            justify-content: center;
        }
        .filament-icons-grid-item-preview svg {
            width: 2rem;
            height: 2rem;
        }
        .filament-icons-grid-item-name {
            width: 100%;
            overflow: hidden;
            font-weight: 600;
            text-align: center;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .filament-icons-grid-item-code,
        .filament-icons-grid-item-value {
            display: block;
            width: 100%;
            overflow: hidden;
            color: #6b7280;
            font-size: 0.7rem;
            text-align: center;
            text-overflow: ellipsis;
            white-space: nowrap;
            cursor: pointer;
            transition: color 0.15s ease;
        }
        .filament-icons-grid-item-code:hover,
        .filament-icons-grid-item-value:hover {
            color: var(--primary-600);
        }
        .filament-icons-grid-item-code code,
        .filament-icons-grid-item-value code {
            font-size: inherit;
        }
        .filament-icons-grid-item-style {
            padding: 0.125rem 0.5rem;
            border-radius: 9999px;
            background: rgba(107, 114, 128, 0.1);
            color: #6b7280;
            font-size: 0.7rem;
        }
        .filament-icons-empty {
            padding: 4rem 1rem;
            color: #6b7280;
            text-align: center;
        }
        @media (max-width: 640px) {
            .filament-icons-toolbar-search {
                min-width: 100%;
            }
            .filament-icons-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        /* 深色主题图标卡片 */
        .dark .filament-icons-grid .filament-icons-grid-item {
            border-color: var(--gray-700);
            background: var(--gray-900);
        }
        .dark .filament-icons-toolbar .filament-icons-toolbar-search {
            border-color: var(--gray-700);
        }
    </style>
</x-filament-panels::page>
