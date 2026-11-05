<x-filament-panels::page class="logs-page">
    {{-- 日志文件列表与内容预览 --}}
    <div class="logs-workspace">
        <x-filament::section icon="heroicon-o-document-text">
            <x-slot name="heading">{{ __( 'lar::DeveloperCenter.Logs.list' ) }}</x-slot>
            <x-slot name="description">{{ __( 'lar::DeveloperCenter.Logs.listDescription' ) }}</x-slot>
            <div class="logs-files">
                @forelse ( $files as $file )
                    <div @class(['logs-file', 'is-selected' => $selectedPath === $file['path']]) wire:key="log-{{ md5( $file['path'] ) }}">
                        <button type="button" class="logs-file-select" wire:click="selectLog(@js( $file['path'] ))" @if ( $selectedPath === $file['path'] ) aria-current="true" @endif>
                            <span class="logs-file-name">{{ $file['name'] }}</span>
                            <span class="logs-file-meta">{{ $file['size'] }} · {{ $file['modified'] }}</span>
                        </button>
                        {{ ($this->deleteLogAction)( ['path' => $file['path']] ) }}
                    </div>
                @empty
                    <p class="logs-empty">{{ __( 'lar::DeveloperCenter.Logs.noFiles' ) }}</p>
                @endforelse
            </div>
        </x-filament::section>
        <x-filament::section icon="heroicon-o-code-bracket">
            <x-slot name="heading">{{ $selectedPath ?? __( 'lar::DeveloperCenter.Logs.title' ) }}</x-slot>
            <x-slot name="description">{{ __( 'lar::DeveloperCenter.Logs.previewDescription' ) }}</x-slot>
            @if ( $error )
                <p role="alert" class="logs-empty">{{ __( 'lar::DeveloperCenter.Logs.readFailed' ) }}</p>
            @elseif ( $content === '' )
                <p class="logs-empty">{{ __( 'lar::DeveloperCenter.Logs.emptyContent' ) }}</p>
            @else
                <pre class="logs-content" tabindex="0" wire:key="log-content-{{ md5( $selectedPath ) }}-{{ $previewVersion }}" x-data x-init="$nextTick(() => { $el.scrollTop = $el.scrollHeight })">{{ $content }}</pre>
            @endif
        </x-filament::section>
    </div>
    <style>
        /* 日志工作区与文件选择，颜色跟随面板主题 */
        .logs-page .logs-workspace { display: grid; align-items: start; grid-template-columns: minmax(0, 3fr) minmax(0, 7fr); gap: 1.25rem; }
        .logs-page .logs-workspace > * { min-width: 0; }
        .logs-page .logs-files { display: flex; max-height: 65vh; flex-direction: column; gap: .5rem; overflow-y: auto; }
        .logs-page .logs-file { display: flex; align-items: center; min-width: 0; padding: .5rem; border: 1px solid transparent; border-radius: .5rem; gap: .5rem; }
        .logs-page .logs-file.is-selected { border-color: var(--primary-500); background: color-mix(in srgb, var(--primary-500) 10%, transparent); }
        .logs-page .logs-file:hover { background: color-mix(in srgb, var(--primary-500) 6%, transparent); }
        .logs-page .logs-file-select { flex: 1; min-width: 0; padding: .25rem; text-align: start; cursor: pointer; }
        .logs-page .logs-file-select:focus-visible { outline: 2px solid var(--primary-500); outline-offset: 2px; }
        .logs-page .logs-file-name { display: block; font-weight: 600; overflow-wrap: anywhere; }
        .logs-page .logs-file-meta { display: block; margin-top: .25rem; color: var(--gray-500); font-size: .75rem; }
        .logs-page .logs-empty { color: var(--gray-500); font-size: .875rem; }
        /* 保留日志缩进，内容区域支持横向和纵向滚动 */
        .logs-page .logs-content { max-height: 65vh; margin: 0; padding: 1rem; border-radius: .5rem; background: var(--gray-950); color: var(--gray-100); font-family: monospace; font-size: .8125rem; line-height: 1.65; white-space: pre; overflow: auto; }
        @media (max-width: 900px) {
            .logs-page .logs-workspace { grid-template-columns: minmax(0, 1fr); }
            .logs-page .logs-files { max-height: 16rem; }
        }
    </style>
</x-filament-panels::page>
