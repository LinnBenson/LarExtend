<x-filament-panels::page class="readme-page">
    {{-- 项目说明文件及正文 --}}
    <x-filament::section icon="heroicon-o-book-open">
        <x-slot name="heading">{{ __( 'lar::DeveloperCenter.Readme.heading' ) }}</x-slot>
        <x-slot name="description">{{ __( 'lar::DeveloperCenter.Readme.description' ) }}</x-slot>
        <div class="readme-meta">
            <span>{{ __( 'lar::DeveloperCenter.Readme.file' ) }}：{{ $path }}</span>
            @if ( $modified )
                <span>{{ __( 'lar::DeveloperCenter.Readme.modified' ) }}：{{ $modified }}</span>
            @endif
        </div>
        @if ( $error )
            <p role="alert" class="readme-message">{{ __( 'lar::DeveloperCenter.Readme.readFailed' ) }}</p>
        @elseif ( trim( $content ) === '' )
            <p class="readme-message">{{ __( 'lar::DeveloperCenter.Readme.empty' ) }}</p>
        @else
            <x-Filament::markdown :content="$content" />
        @endif
    </x-filament::section>
    <style>
        /* 文件信息及 Markdown 阅读区域，正文使用通用 Markdown 组件 */
        .readme-page .readme-meta { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; margin-bottom: 1.5rem; padding: .875rem 1rem; border-radius: .5rem; background: var(--gray-100); color: var(--gray-500); font-size: .875rem; gap: .5rem 1.5rem; overflow-wrap: anywhere; }
        .dark .readme-page .readme-meta { background: var(--gray-800); color: var(--gray-400); }
        .readme-page .readme-message { color: var(--gray-500); font-size: .875rem; }
    </style>
</x-filament-panels::page>
