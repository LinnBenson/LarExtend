<x-filament-panels::page.simple>
    @php
        $brand = config( 'app.name' ) ?: filament()->getBrandName();
    @endphp

    <link rel="stylesheet" href="{{ asset( 'assets/filament/css/login-v0.css' ) }}?v={{ filemtime( public_path( 'assets/filament/css/login-v0.css' ) ) }}">

    {{-- V4 登录页：斜切湖畔照片与双栏认证面板 --}}
    <div class="login-v0">
        <div class="login-v0-backdrop" aria-hidden="true"></div>
        <div class="login-v0-panel">
            <aside class="login-v0-visual" aria-label="{{ __( 'admin::login.v0.brand_intro' ) }}">
                <div class="login-v0-photo" aria-hidden="true"></div>
                <div class="login-v0-brand">
                    <x-filament::icon icon="heroicon-o-squares-2x2" />
                    <span>{{ $brand }}</span>
                </div>
                <div class="login-v0-caption">
                    <span class="login-v0-caption-line" aria-hidden="true"></span>
                    <p>{{ __( 'admin::login.v0.caption_first' ) }}<br>{{ __( 'admin::login.v0.caption_second' ) }}</p>
                    <span>{{ __( 'admin::login.v0.workspace' ) }}</span>
                </div>
            </aside>

            <section class="login-v0-entry" aria-labelledby="login-v0-title">
                <div class="login-v0-toolbar">
                    <span class="login-v0-mobile-brand">{{ $brand }}</span>
                    @if ( filament()->hasDarkMode() && !filament()->hasDarkModeForced() )
                        <div class="login-v0-theme" x-data="{ close() {} }">
                            <x-filament-panels::theme-switcher />
                        </div>
                    @endif
                </div>
                <div class="login-v0-form">
                    <header class="login-v0-heading">
                        <h1 id="login-v0-title">{{ __( 'admin::login.v0.title' ) }}</h1>
                        <p>{{ __( 'admin::login.v0.subtitle', ['brand' => $brand] ) }}</p>
                    </header>

                    {{-- 沿用 Filament 的认证、表单校验与多因素认证 --}}
                    {{ $this->content }}

                    <p class="login-v0-note">
                        <x-filament::icon icon="heroicon-o-lock-closed" />
                        <span>{{ __( 'admin::login.v0.access_note' ) }}</span>
                    </p>
                </div>
                <footer class="login-v0-footer">© {{ date( 'Y' ) }} {{ $brand }}</footer>
            </section>
        </div>
    </div>
</x-filament-panels::page.simple>
