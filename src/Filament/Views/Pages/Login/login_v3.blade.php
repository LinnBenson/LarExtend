<x-filament-panels::page.simple>
    @php
        $brand = setting( 'app.name' ) ?: filament()->getBrandName();
        $assetPath = trim( config( 'lar.admin.asset', 'filament' ), '/' );
        $cssPath = "{$assetPath}/css/login-v3.css";
        $cssVersion = is_file( public_path( $cssPath ) ) ? filemtime( public_path( $cssPath ) ) : 0;
    @endphp

    <link rel="stylesheet" href="{{ asset( $cssPath ) }}?v={{ $cssVersion }}">

    {{-- 光幕空间：背景光环与中央认证面板 --}}
    <div class="login-v3">
        <div class="login-v3-scene" aria-hidden="true">
            <div class="login-v3-halo login-v3-halo-back"></div>
            <div class="login-v3-halo login-v3-halo-front"></div>
            <div class="login-v3-horizon"></div>
        </div>

        <header class="login-v3-topbar">
            <div class="login-v3-brand">
                <x-filament::icon icon="heroicon-o-square-3-stack-3d" />
                <span>{{ $brand }}</span>
            </div>
            @if ( filament()->hasDarkMode() && !filament()->hasDarkModeForced() )
                <div class="login-v3-theme" x-data="{ close() {} }">
                    <x-filament-panels::theme-switcher />
                </div>
            @endif
        </header>

        <div class="login-v3-center">
            <section class="login-v3-entry" aria-labelledby="login-v3-title">
                <header class="login-v3-heading">
                    <div class="login-v3-emblem" aria-hidden="true">
                        <x-filament::icon icon="heroicon-o-square-3-stack-3d" />
                    </div>
                    <h1 id="login-v3-title">{{ __( 'lar::frame.login-v3.title' ) }}</h1>
                    <p>{{ __( 'lar::frame.login-v3.subtitle', ['brand' => $brand] ) }}</p>
                </header>

                {{-- 沿用 Filament 的认证、校验与多因素认证 --}}
                {{ $this->content }}

                <p class="login-v3-note">
                    <x-filament::icon icon="heroicon-o-lock-closed" />
                    <span>{{ __( 'lar::frame.login-v3.access_note' ) }}</span>
                </p>
            </section>
        </div>

        <footer class="login-v3-footer">{{setting( 'admin.ascription' )}}</footer>
    </div>
</x-filament-panels::page.simple>
