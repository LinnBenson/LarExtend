<x-filament-panels::page.simple>
    @php
        $brand = setting( 'app.name' ) ?: filament()->getBrandName();
        $assetPath = trim( config( 'lar.admin.asset', 'filament' ), '/' );
        $cssPath = "{$assetPath}/css/login-v1.css";
        $cssVersion = is_file( public_path( $cssPath ) ) ? filemtime( public_path( $cssPath ) ) : 0;
    @endphp

    <link rel="stylesheet" href="{{ asset( $cssPath ) }}?v={{ $cssVersion }}">

    {{-- 极客工作台：品牌轨道与认证终端 --}}
    <div class="login-v1">
        <header class="login-v1-topbar">
            <div class="login-v1-brand">
                <x-filament::icon icon="heroicon-o-command-line" />
                <span>{{ $brand }}</span>
            </div>
            @if ( filament()->hasDarkMode() && !filament()->hasDarkModeForced() )
                <div class="login-v1-theme" x-data="{ close() {} }">
                    <x-filament-panels::theme-switcher />
                </div>
            @endif
        </header>

        <main class="login-v1-workspace">
            <aside class="login-v1-visual" aria-label="{{ __( 'lar::frame.login-v1.brand_intro' ) }}">
                <span class="login-v1-eyebrow">{{ __( 'lar::frame.login-v1.workspace' ) }}</span>
                <div class="login-v1-orbit" aria-hidden="true">
                    <div class="login-v1-axis"></div>
                    <div class="login-v1-ring login-v1-ring-outer"></div>
                    <div class="login-v1-ring login-v1-ring-inner"></div>
                    <div class="login-v1-core"><x-filament::icon icon="heroicon-o-command-line" /></div>
                    <span class="login-v1-coordinate login-v1-coordinate-top">[ AUTH ]</span>
                    <span class="login-v1-coordinate login-v1-coordinate-bottom">&lt; / &gt;</span>
                </div>
                <div class="login-v1-caption">
                    <h2>{{ __( 'lar::frame.login-v1.caption' ) }}</h2>
                    <p>{{ __( 'lar::frame.login-v1.description' ) }}</p>
                </div>
            </aside>

            <section class="login-v1-entry" aria-labelledby="login-v1-title">
                <div class="login-v1-terminal-bar" aria-hidden="true">
                    <span class="login-v1-terminal-mark">&gt;_</span>
                    <span>admin / sign-in</span>
                </div>
                <div class="login-v1-form">
                    <header class="login-v1-heading">
                        <span class="login-v1-eyebrow">{{ __( 'lar::frame.login-v1.authorization' ) }}</span>
                        <h1 id="login-v1-title">{{ __( 'lar::frame.login-v1.title' ) }}<span aria-hidden="true">_</span></h1>
                        <p>{{ __( 'lar::frame.login-v1.subtitle', ['brand' => $brand] ) }}</p>
                    </header>

                    {{-- 保留 Filament 认证、校验及多因素认证流程 --}}
                    {{ $this->content }}

                    <p class="login-v1-note">
                        <x-filament::icon icon="heroicon-o-lock-closed" />
                        <span>{{ __( 'lar::frame.login-v1.access_note' ) }}</span>
                    </p>
                </div>
            </section>
        </main>

        <footer class="login-v1-footer">
            <span>{!!setting( 'app.copyright' )!!}</span>
            <span>{{setting( 'admin.ascription' )}}</span>
        </footer>
    </div>
</x-filament-panels::page.simple>
