@props(['content' => ''])

{{-- 可复用 Markdown 阅读组件 --}}
<article {{ $attributes->class(['lar-markdown']) }}>
    {{ \LarExtend\Filament\Concerns\AdminTool::markdown( $content ) }}
</article>

@once
    <style>
        /* 阅读正文使用面板灰阶与主题色，避免与后台组件争抢视觉层级 */
        .lar-markdown { --md-text: var(--gray-700); --md-heading: var(--gray-950); --md-muted: var(--gray-500); --md-line: var(--gray-200); --md-surface: var(--gray-50); --md-accent: var(--primary-600); min-width: 0; color: var(--md-text); font-size: .9375rem; line-height: 1.9; overflow-wrap: anywhere; }
        .dark .lar-markdown { --md-text: var(--gray-300); --md-heading: var(--gray-100); --md-muted: var(--gray-400); --md-line: var(--gray-700); --md-surface: color-mix(in srgb, var(--gray-950) 45%, transparent); --md-accent: var(--primary-400); }
        .lar-markdown :is(h1, h2, h3, h4, h5, h6) { margin: 2em 0 .75em; color: var(--md-heading); font-weight: 600; line-height: 1.45; letter-spacing: -.02em; text-wrap: balance; scroll-margin-top: 5rem; }
        .lar-markdown h1 { margin-bottom: 1em; padding-bottom: .65em; border-bottom: 1px solid var(--md-line); font-size: clamp(1.65rem, 2.5vw, 2.125rem); font-weight: 700; }
        .lar-markdown h2 { margin-top: 2.25em; padding-bottom: .6em; border-bottom: 1px solid var(--md-line); font-size: 1.5rem; }
        .lar-markdown h3 { font-size: 1.25rem; }
        .lar-markdown h4 { font-size: 1.0625rem; }
        .lar-markdown :is(h5, h6) { font-size: .9375rem; }
        .lar-markdown h6 { color: var(--md-muted); }
        .lar-markdown :is(h1, h2, h3, h4, h5, h6) + :is(p, ul, ol, pre, blockquote, table) { margin-top: .75em; }
        .lar-markdown p { margin: 1em 0; }
        .lar-markdown :is(ul, ol) { margin: 1.1em 0; padding-inline-start: 1.75em; }
        .lar-markdown ul { list-style-type: disc; }
        .lar-markdown ol { list-style-type: decimal; }
        .lar-markdown ul ul { list-style-type: circle; }
        .lar-markdown li { margin: .45em 0; padding-inline-start: .25em; }
        .lar-markdown li::marker { color: var(--md-muted); font-size: .9em; font-weight: 500; }
        .lar-markdown li :is(ul, ol) { margin: .35em 0; }
        .lar-markdown li > p { margin: .5em 0; }
        .lar-markdown a { border-radius: .125rem; color: var(--md-accent); font-weight: 500; text-decoration: underline; text-decoration-color: color-mix(in srgb, var(--md-accent) 30%, transparent); text-underline-offset: .22em; }
        .lar-markdown a:hover { text-decoration-color: currentColor; }
        .lar-markdown a:focus-visible { outline: 2px solid var(--md-accent); outline-offset: 3px; }
        .lar-markdown :is(strong, b) { color: var(--md-heading); font-weight: 600; }
        /* 引用、代码和表格沿用后台浅底色、细边框与圆角 */
        .lar-markdown blockquote { margin: 1.5em 0; padding: .875rem 1.125rem; border: 1px solid var(--md-line); border-inline-start: 3px solid var(--md-accent); border-radius: .5rem; background: var(--md-surface); color: var(--md-muted); }
        .lar-markdown blockquote > :first-child { margin-top: 0; }
        .lar-markdown blockquote > :last-child { margin-bottom: 0; }
        .lar-markdown code { padding: .15em .4em; border: 1px solid var(--md-line); border-radius: .375rem; background: var(--md-surface); color: var(--md-heading); font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; font-size: .85em; font-weight: 400; box-decoration-break: clone; }
        .lar-markdown pre { max-width: 100%; margin: 1.5em 0; padding: 1.125rem 1.25rem; border: 1px solid var(--md-line); border-radius: .75rem; background: var(--md-surface); font-size: .8125rem; line-height: 1.8; tab-size: 4; overflow-x: auto; overscroll-behavior-x: contain; }
        .lar-markdown pre code { display: block; padding: 0; border: 0; border-radius: 0; background: transparent; color: var(--md-text); font-size: inherit; line-height: inherit; white-space: pre; overflow-wrap: normal; }
        .lar-markdown table { display: block; width: fit-content; max-width: 100%; margin: 1.5em 0; border: 1px solid var(--md-line); border-radius: .75rem; border-collapse: separate; border-spacing: 0; font-size: .875rem; line-height: 1.65; overflow-x: auto; }
        .lar-markdown :is(th, td) { padding: .75rem 1rem; border-bottom: 1px solid var(--md-line); text-align: start; vertical-align: top; }
        .lar-markdown th { background: var(--md-surface); color: var(--md-heading); font-size: .8125rem; font-weight: 600; white-space: nowrap; }
        .lar-markdown tbody tr:last-child td { border-bottom: 0; }
        .lar-markdown tbody tr:hover { background: var(--md-surface); }
        .lar-markdown hr { margin: 2.25rem 0; border: 0; border-top: 1px solid var(--md-line); }
        .lar-markdown img { display: block; max-width: 100%; height: auto; margin: 1.5em 0; border: 1px solid var(--md-line); border-radius: .75rem; }
        .lar-markdown ul:has(> .task-list-item) { padding-inline-start: .25em; list-style: none; }
        .lar-markdown input[type="checkbox"] { width: 1rem; height: 1rem; margin-inline-end: .5em; border-radius: .25rem; accent-color: var(--md-accent); vertical-align: middle; }
        .lar-markdown > :first-child { margin-top: 0; }
        .lar-markdown > :last-child { margin-bottom: 0; }
        @media (max-width: 640px) {
            .lar-markdown { font-size: .875rem; line-height: 1.85; }
            .lar-markdown h2 { font-size: 1.25rem; }
            .lar-markdown h3 { font-size: 1.125rem; }
            .lar-markdown pre, .lar-markdown :is(th, td) { padding: .75rem .875rem; }
        }
    </style>
@endonce
