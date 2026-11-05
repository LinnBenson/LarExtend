<x-filament-panels::page.simple>
    @php
        $brand = setting( 'app.name' ) ?: filament()->getBrandName();
        $assetPath = trim( config( 'lar.admin.asset', 'filament' ), '/' );
        $cssPath = "{$assetPath}/css/login-v2.css";
        $cssVersion = is_file( public_path( $cssPath ) ) ? filemtime( public_path( $cssPath ) ) : 0;
    @endphp

    <link rel="stylesheet" href="{{ asset( $cssPath ) }}?v={{ $cssVersion }}">

    {{-- 活力登录页：笑脸贴纸与圆润认证区域 --}}
    <div class="login-v2">
        <header class="login-v2-topbar">
            <div class="login-v2-brand">
                <x-filament::icon icon="heroicon-o-sparkles" />
                <span>{{ $brand }}</span>
            </div>
            @if ( filament()->hasDarkMode() && !filament()->hasDarkModeForced() )
                <div class="login-v2-theme" x-data="{ close() {} }">
                    <x-filament-panels::theme-switcher />
                </div>
            @endif
        </header>

        <div class="login-v2-workspace">
            <aside class="login-v2-visual" aria-label="{{ __( 'lar::frame.login-v2.brand_intro' ) }}">
                <div class="login-v2-art" aria-hidden="true">
                    <span class="login-v2-sticker">{{ __( 'lar::frame.login-v2.greeting' ) }}</span>
                    <span class="login-v2-spark login-v2-spark-top">✳</span>
                    <div class="login-v2-mascot">
                        <div class="login-v2-eyes"><span></span><span></span></div>
                        <div class="login-v2-smile"></div>
                    </div>
                    <span class="login-v2-spark login-v2-spark-bottom">✦</span>
                    <div class="login-v2-arrow"><x-filament::icon icon="heroicon-o-arrow-up-right" /></div>
                </div>
                <div class="login-v2-caption">
                    <h2>{{ __( 'lar::frame.login-v2.caption_first' ) }}<br><span>{{ __( 'lar::frame.login-v2.caption_second' ) }}</span></h2>
                    <p>{{ __( 'lar::frame.login-v2.description' ) }}</p>
                </div>
            </aside>

            <section class="login-v2-entry" aria-labelledby="login-v2-title">
                <header class="login-v2-heading">
                    <span class="login-v2-wave" aria-hidden="true">✦</span>
                    <h1 id="login-v2-title">{{ __( 'lar::frame.login-v2.title' ) }}</h1>
                    <p>{{ __( 'lar::frame.login-v2.subtitle', ['brand' => $brand] ) }}</p>
                </header>

                {{-- 沿用 Filament 认证、校验与多因素认证 --}}
                {{ $this->content }}

                <p class="login-v2-note">
                    <x-filament::icon icon="heroicon-o-lock-closed" />
                    <span>{{ __( 'lar::frame.login-v2.access_note' ) }}</span>
                </p>
            </section>
        </div>

        <footer class="login-v2-footer">{{setting( 'admin.ascription' )}}</footer>
    </div>
</x-filament-panels::page.simple>
