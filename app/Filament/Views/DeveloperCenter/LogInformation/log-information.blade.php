<x-filament-panels::page>
    @php
        $t = fn ( string $key, array $replace = [] ): string => __( "admin::DeveloperCenter.LogInformation.{$key}", $replace );
        $logFiles = $this->getLogFiles();
    @endphp

    {{-- 日志两栏布局 --}}
    <div class="log-information-layout">
        {{-- 左侧日志列表 --}}
        <x-filament::section
            :heading="$t( 'list_heading' )"
            :description="$t( 'list_description' )"
            icon="heroicon-o-document-text"
        >
            @if ( count( $logFiles ) === 0 )
                <div class="log-information-layout-list-empty">
                    {{ $t( 'empty_list' ) }}
                </div>
            @else
                <div class="log-information-layout-list">
                    @foreach ( $logFiles as $file )
                        <div
                            wire:key="log-{{ md5( $file['path'] ) }}"
                            class="log-information-layout-list-item{{ $selectedLog === $file['path'] ? ' is-active' : '' }}"
                        >
                            <button
                                type="button"
                                class="log-information-layout-list-item-select"
                                wire:click="viewLog(@js($file['path']))"
                            >
                                <span class="log-information-layout-list-item-name">{{ $file['name'] }}</span>
                                <span class="log-information-layout-list-item-meta">
                                    {{ $file['size'] }} · {{ $file['modified_at'] }}
                                </span>
                            </button>
                            <button
                                type="button"
                                class="log-information-layout-list-item-delete"
                                aria-label="{{ $t( 'delete_file', ['file' => $file['name']] ) }}"
                                wire:click="mountAction('deleteLog', @js(['fileName' => $file['path']]))"
                            >
                                <x-filament::icon icon="heroicon-o-trash" />
                            </button>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-filament::section>

        {{-- 右侧日志内容 --}}
        <x-filament::section
            :heading="$selectedLog ?? $t( 'content_heading' )"
            :description="$t( 'content_description' )"
            icon="heroicon-o-code-bracket"
        >
            @if ( $selectedLog === null )
                <div class="log-information-layout-content-empty">
                    {{ $t( 'select_file' ) }}
                </div>
            @else
                <pre
                    class="log-information-layout-content"
                    x-data
                    x-init="$nextTick(() => { $el.scrollTop = $el.scrollHeight })"
                    x-on:log-content-updated.window="$nextTick(() => { $el.scrollTop = $el.scrollHeight })"
                >{{ $logContent }}</pre>
            @endif
        </x-filament::section>
    </div>

    <style>
        /* 日志信息页面两栏布局 */
        .log-information-layout {
            display: grid;
            grid-template-columns: minmax(18rem, 30%) minmax(0, 1fr);
            gap: 1rem;
            align-items: stretch;
        }
        .log-information-layout > section {
            min-width: 0;
        }
        /* 左侧日志文件列表 */
        .log-information-layout-list {
            display: flex;
            max-height: calc(100vh - 18rem);
            overflow-y: auto;
            flex-direction: column;
            gap: 0.375rem;
        }
        .log-information-layout-list-empty,
        .log-information-layout-content-empty {
            display: flex;
            min-height: 20rem;
            color: var(--gray-500);
            align-items: center;
            justify-content: center;
            text-align: center;
        }
        .log-information-layout-list-item {
            display: flex;
            padding: 0.375rem;
            border: 1px solid transparent;
            border-radius: 0.5rem;
            align-items: center;
            gap: 0.25rem;
        }
        .log-information-layout-list-item:hover {
            background: rgba(107, 114, 128, 0.08);
        }
        .log-information-layout-list-item.is-active {
            border-color: var(--primary-500);
            background: color-mix(in srgb, var(--primary-500) 10%, transparent);
        }
        .log-information-layout-list-item-select {
            display: flex;
            min-width: 0;
            padding: 0.375rem;
            flex: 1;
            flex-direction: column;
            cursor: pointer;
            text-align: left;
        }
        .log-information-layout-list-item-name {
            overflow: hidden;
            font-weight: 600;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .log-information-layout-list-item-meta {
            overflow: hidden;
            color: var(--gray-500);
            font-size: 0.75rem;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .log-information-layout-list-item-delete {
            display: inline-flex;
            width: 2rem;
            height: 2rem;
            border-radius: 0.5rem;
            color: var(--danger-500);
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }
        .log-information-layout-list-item-delete:hover {
            background: rgba(239, 68, 68, 0.1);
        }
        .log-information-layout-list-item-delete svg {
            width: 1rem;
            height: 1rem;
        }
        /* 右侧日志内容 */
        .log-information-layout-content {
            min-height: 30rem;
            max-height: calc(100vh - 18rem);
            margin: 0;
            padding: 1rem;
            overflow: auto;
            border-radius: 0.5rem;
            background: var(--gray-100);
            color: var(--gray-900);
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 0.8125rem;
            line-height: 1.6;
            white-space: pre;
            overflow-wrap: normal;
            word-break: normal;
        }
        @media (max-width: 1024px) {
            .log-information-layout {
                grid-template-columns: minmax(0, 1fr);
            }
            .log-information-layout-list {
                max-height: 20rem;
            }
        }
        /* 深色主题日志内容 */
        .dark .log-information-layout .log-information-layout-content {
            background: var(--gray-950);
            color: var(--gray-100);
        }
        .log-information-layout-list-item-select:focus-visible,
        .log-information-layout-list-item-delete:focus-visible {
            outline: 2px solid var(--primary-500);
            outline-offset: 2px;
        }
    </style>
</x-filament-panels::page>
