<x-filament-panels::page.simple>
    @php
        $brand = setting( 'app.name' ) ?: filament()->getBrandName();
        $assetPath = trim( config( 'lar.admin.asset', 'filament' ), '/' );
        $cssPath = "{$assetPath}/css/login-v4.css";
        $jsPath = "{$assetPath}/js/login-v4.js";
        $cssVersion = is_file( public_path( $cssPath ) ) ? filemtime( public_path( $cssPath ) ) : 0;
        $jsVersion = is_file( public_path( $jsPath ) ) ? filemtime( public_path( $jsPath ) ) : 0;
    @endphp

    <link rel="stylesheet" href="{{ asset( $cssPath ) }}?v={{ $cssVersion }}">

    {{-- 能量反应堆：独立背景场景与原生认证表单 --}}
    <div
        class="login-v4 dark"
        data-scene-module="{{ asset( $jsPath ) }}?v={{ $jsVersion }}"
        x-data="{
            destroyScene: null,
            destroy() { this.destroyScene?.(); }
        }"
        x-init="import( $el.dataset.sceneModule ).then( module => {
            if ( $el.isConnected ) destroyScene = module.mountScene( $el );
        } ).catch( () => { $el.dataset.scene = 'fallback'; } )"
    >
        <div class="login-v4-scene" aria-hidden="true" wire:ignore>
            <div class="login-v4-aurora login-v4-aurora-first"></div>
            <div class="login-v4-aurora login-v4-aurora-second"></div>
            <canvas class="login-v4-energy"></canvas>
            <canvas class="login-v4-particles"></canvas>
            <div class="login-v4-shockwaves"><span></span><span></span><span></span></div>
            <div class="login-v4-meteors"><span></span><span></span><span></span><span></span><span></span></div>
            <div class="login-v4-reactor">
                <div class="login-v4-orbit login-v4-orbit-outer"><div></div></div>
                <div class="login-v4-orbit login-v4-orbit-middle"><div></div></div>
                <div class="login-v4-orbit login-v4-orbit-inner"><div></div></div>
                <div class="login-v4-corona"></div>
            </div>
            <div class="login-v4-floor"><div></div></div>
            <div class="login-v4-vignette"></div>
        </div>

        <header class="login-v4-topbar">
            <div class="login-v4-brand">
                <x-filament::icon icon="heroicon-o-cube-transparent" />
                <span>{{ $brand }}</span>
            </div>
        </header>

        <div class="login-v4-center">
            <section class="login-v4-entry" aria-labelledby="login-v4-title">
                <div class="login-v4-panel">
                    <header class="login-v4-heading">
                        <div class="login-v4-emblem" aria-hidden="true">
                            <span></span>
                            <x-filament::icon icon="heroicon-o-cube-transparent" />
                        </div>
                        <span class="login-v4-eyebrow">{{ __( 'lar::frame.login-v4.workspace' ) }}</span>
                        <h1 id="login-v4-title">{{ __( 'lar::frame.login-v4.title' ) }}</h1>
                        <p>{{ __( 'lar::frame.login-v4.subtitle', ['brand' => $brand] ) }}</p>
                    </header>

                    {{-- 复用 Filament 登录、校验、限流与多因素认证 --}}
                    {{ $this->content }}

                    <p class="login-v4-note">
                        <x-filament::icon icon="heroicon-o-lock-closed" />
                        <span>{{ __( 'lar::frame.login-v4.access_note' ) }}</span>
                    </p>
                </div>
            </section>
        </div>

        <footer class="login-v4-footer">{{setting( 'admin.ascription' )}}</footer>
    </div>
</x-filament-panels::page.simple>
