<x-filament-panels::page class="dashboard-page">
    @php
        $brand = config( 'app.name' ) ?: filament()->getBrandName();
    @endphp

    {{-- 静态起始页，不加载业务数据或提供跳转入口 --}}
    <section class="dashboard-welcome" aria-labelledby="dashboard-welcome-title">
        <div class="dashboard-brand">
            <x-filament::icon icon="heroicon-o-squares-2x2" />
            <span>{{ $brand }}</span>
        </div>
        <div class="dashboard-greeting">
            <h2 id="dashboard-welcome-title">{{ __( 'lar::frame.dashboard.welcome' ) }}<span>{{ __( 'lar::frame.dashboard.punctuation' ) }}</span></h2>
            <p>{{ __( 'lar::frame.dashboard.description' ) }}</p>
        </div>
        <footer class="dashboard-footer">
            <span>{{ $brand }}</span>
            <span>{{ __( 'lar::frame.dashboard.workspace' ) }}</span>
        </footer>
    </section>

    <style>
        /* 仅调整仪表盘内容区，保留原生导航和顶栏 */
        .fi-main-ctn:has(.dashboard-page) {
            background: var(--color-white);
        }
        .dark .fi-main-ctn:has(.dashboard-page) {
            background: var(--gray-900);
        }
        .dashboard-page {
            --dashboard-line: color-mix(in srgb, var(--gray-300) 45%, transparent);
            --dashboard-heading: var(--gray-800);
            --dashboard-text: var(--gray-500);
            --dashboard-muted: var(--gray-400);
            --dashboard-accent: var(--primary-500);
        }
        .dark .dashboard-page {
            --dashboard-line: color-mix(in srgb, var(--gray-600) 45%, transparent);
            --dashboard-heading: var(--gray-100);
            --dashboard-text: var(--gray-400);
            --dashboard-muted: var(--gray-500);
            --dashboard-accent: var(--primary-400);
        }
        /* 品牌和欢迎文案，强调色跟随面板主题 */
        .dashboard-page .dashboard-welcome {
            min-width: 0;
            padding-top: 14px;
        }
        .dashboard-page .dashboard-brand {
            display: flex;
            min-width: 0;
            color: var(--gray-600);
            font-size: 14px;
            font-weight: 600;
            letter-spacing: -0.2px;
            overflow-wrap: anywhere;
            align-items: center;
            gap: 9px;
        }
        .dark .dashboard-page .dashboard-brand {
            color: var(--gray-300);
        }
        .dashboard-page .dashboard-brand svg {
            width: 19px;
            height: 19px;
            color: var(--dashboard-accent);
            flex-shrink: 0;
        }
        .dashboard-page .dashboard-greeting {
            padding: 64px 0 84px;
        }
        .dashboard-page .dashboard-greeting h2 {
            margin: 0;
            color: var(--dashboard-heading);
            font-size: 46px;
            font-weight: 500;
            line-height: 1.5;
            letter-spacing: 2px;
            overflow-wrap: anywhere;
        }
        .dashboard-page .dashboard-greeting h2 span {
            color: var(--dashboard-accent);
        }
        .dashboard-page .dashboard-greeting p {
            margin: 18px 0 0;
            color: var(--dashboard-text);
            font-size: 15px;
            line-height: 1.9;
            letter-spacing: 0.5px;
        }
        /* 页尾仅展示静态品牌和工作台名称 */
        .dashboard-page .dashboard-footer {
            display: flex;
            padding: 22px 0;
            border-top: 1px solid var(--dashboard-line);
            color: var(--dashboard-muted);
            font-size: 11px;
            overflow-wrap: anywhere;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
        }
        @media (max-width: 600px) {
            .dashboard-page .dashboard-welcome { padding-top: 6px; }
            .dashboard-page .dashboard-brand { font-size: 13px; }
            .dashboard-page .dashboard-greeting { padding: 64px 0 75px; }
            .dashboard-page .dashboard-greeting h2 { font-size: 35px; letter-spacing: 1px; }
            .dashboard-page .dashboard-greeting p { font-size: 13px; }
            .dashboard-page .dashboard-footer { font-size: 10px; }
        }
    </style>
</x-filament-panels::page>