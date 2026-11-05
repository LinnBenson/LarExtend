{{-- 插件日志预览 --}}
<div class="plugin-log-preview">
    @if ( $error )
        <p role="alert" class="plugin-log-description">{{ __( 'lar::DeveloperCenter.Logs.readFailed' ) }}</p>
    @elseif ( $content === '' )
        <p class="plugin-log-description">{{ __( 'lar::DeveloperCenter.Logs.emptyContent' ) }}</p>
    @else
        <pre tabindex="0" x-data x-init="$nextTick(() => { $el.scrollTop = $el.scrollHeight })">{{ $content }}</pre>
    @endif
</div>
<style>
    .plugin-log-preview { min-width: 0; max-width: 100%; overflow: hidden; }
    .plugin-log-preview .plugin-log-description { margin: .5rem 0 1rem; color: var(--gray-500); font-size: .875rem; }
    .plugin-log-preview pre { max-width: 100%; max-height: 60vh; margin: 0; padding: 1rem; border-radius: .5rem; background: var(--gray-950); color: var(--gray-100); font-family: monospace; font-size: .8125rem; line-height: 1.65; white-space: pre; overflow: auto; }
</style>
