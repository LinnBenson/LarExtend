<x-filament-panels::page>
    @php
        $t = fn ( string $key, array $replace = [] ): string => __( "admin::DeveloperCenter.Readme.{$key}", $replace );
    @endphp
    {{-- 项目 README 内容 --}}
    <x-filament::section
        :heading="$t( 'heading' )"
        :description="$t( 'description' )"
        icon="heroicon-o-book-open"
    >
        <div class="readme-document-meta">
            <span>{{ $t( 'file', ['file' => 'README.md'] ) }}</span>
            <span>{{ $t( 'modified', ['time' => $this->getReadmeModifiedAt()] ) }}</span>
        </div>
        <article class="readme-document-content">
            {!! $this->getReadmeHtml() !!}
        </article>
    </x-filament::section>

    <style>
        /* README 文件信息 */
        .readme-document-meta {
            display: flex;
            margin-bottom: 1.5rem;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            background: rgba(107, 114, 128, 0.08);
            color: var(--gray-500);
            font-size: 0.8125rem;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }
        /* README Markdown 格式化内容 */
        .readme-document-content {
            max-width: 72rem;
            color: inherit;
            font-size: 1rem;
            line-height: 1.8;
        }
        .readme-document-content h1,
        .readme-document-content h2,
        .readme-document-content h3,
        .readme-document-content h4,
        .readme-document-content h5,
        .readme-document-content h6 {
            margin-top: 1.75rem;
            margin-bottom: 0.75rem;
            font-weight: 700;
            line-height: 1.3;
        }
        .readme-document-content h1 {
            padding-bottom: 0.75rem;
            border-bottom: 1px solid var(--gray-200);
            font-size: 2rem;
        }
        .readme-document-content h2 {
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--gray-200);
            font-size: 1.5rem;
        }
        .readme-document-content h3 {
            font-size: 1.25rem;
        }
        .readme-document-content p {
            margin: 0.75rem 0;
        }
        .readme-document-content ul,
        .readme-document-content ol {
            margin: 0.75rem 0;
            padding-left: 1.75rem;
        }
        .readme-document-content ul {
            list-style: disc;
        }
        .readme-document-content ol {
            list-style: decimal;
        }
        .readme-document-content li {
            margin: 0.375rem 0;
        }
        .readme-document-content li > ul,
        .readme-document-content li > ol {
            margin: 0.25rem 0;
        }
        .readme-document-content a {
            color: var(--primary-600);
            text-decoration: underline;
            text-underline-offset: 0.2rem;
        }
        .readme-document-content code {
            padding: 0.125rem 0.375rem;
            border-radius: 0.25rem;
            background: rgba(107, 114, 128, 0.12);
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 0.875em;
        }
        .readme-document-content pre {
            margin: 1rem 0;
            padding: 1rem;
            overflow-x: auto;
            border-radius: 0.5rem;
            background: var(--gray-100);
            color: inherit;
        }
        .readme-document-content pre code {
            padding: 0;
            background: transparent;
            color: inherit;
        }
        .readme-document-content blockquote {
            margin: 1rem 0;
            padding: 0.5rem 1rem;
            border-left: 0.25rem solid var(--primary-500);
            background: color-mix(in srgb, var(--primary-500) 8%, transparent);
            color: var(--gray-500);
        }
        .readme-document-content table {
            width: 100%;
            margin: 1rem 0;
            border-collapse: collapse;
        }
        .readme-document-content th,
        .readme-document-content td {
            padding: 0.625rem 0.75rem;
            border: 1px solid var(--gray-200);
            text-align: left;
        }
        .readme-document-content th {
            background: rgba(107, 114, 128, 0.08);
            font-weight: 600;
        }
        .readme-document-content hr {
            margin: 1.5rem 0;
            border: 0;
            border-top: 1px solid var(--gray-200);
        }
        /* 深色主题与窄屏文档适配 */
        .dark .readme-document-content {
            --readme-border: var(--gray-700);
        }
        .dark .readme-document-content h1,
        .dark .readme-document-content h2,
        .dark .readme-document-content th,
        .dark .readme-document-content td,
        .dark .readme-document-content hr {
            border-color: var(--readme-border);
        }
        .dark .readme-document-content pre {
            background: var(--gray-950);
        }
        .dark .readme-document-content a {
            color: var(--primary-400);
        }
        .readme-document-content {
            min-width: 0;
            overflow-wrap: anywhere;
        }
        .readme-document-content > :first-child {
            margin-top: 0;
        }
        .readme-document-content img {
            max-width: 100%;
            height: auto;
        }
        .readme-document-content table {
            display: block;
            overflow-x: auto;
        }
        @media (max-width: 640px) {
            .readme-document-meta {
                flex-direction: column;
                gap: 0.5rem;
            }
            .readme-document-content h1 {
                font-size: 1.5rem;
            }
            .readme-document-content h2 {
                font-size: 1.25rem;
            }
        }
    </style>
</x-filament-panels::page>
