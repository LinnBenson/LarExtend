{{-- 资源预览与内联上传操作 --}}
<div class="lar-asset-upload" wire:key="asset-{{ $field->getId() }}"
    x-data="{
        files: $wire.$entangle(@js( $field->getStatePath() )), busy: false, removing: false, progress: 0, failed: false, local: '', selectedName: '', selectedSize: '', message: '',
        get address() {
            const file = Object.values(this.files || {})[0];
            return typeof file === 'string' &amp;&amp; /^(\/(?!\/)|https?:\/\/)/i.test(file) ? file : '';
        },
        get filename() { return this.selectedName || this.address.split('/').pop().split('?')[0]; },
        get preview() { return this.local || this.address; },
        init() { this.$watch('files', () => { this.failed = false; }); },
        destroy() { if (this.local) URL.revokeObjectURL(this.local); },
        select(event) {
            const file = event.target.files[0];
            if (!file) return;
            const previous = this.files;
            this.busy = true; this.progress = 0; this.message = ''; this.$dispatch('asset-upload-start');
            this.files = {}; $wire.set(@js( $field->getStatePath() ), {}, false);
            $wire.upload(@js( $field->getStatePath() ) + '.' + Date.now(), file, () => {
                if (this.local) URL.revokeObjectURL(this.local);
                this.local = URL.createObjectURL(file); this.selectedName = file.name; this.selectedSize = file.size >= 1048576 ? (file.size / 1048576).toFixed(1) + ' MB' : Math.max(1, Math.round(file.size / 1024)) + ' KB'; this.failed = false; this.busy = false;
                this.$dispatch('asset-upload-finish'); event.target.value = '';
            }, () => {
                this.files = previous; this.busy = false; this.message = @js( __( 'lar::frame.upload.uploadFailed' ) );
                this.$dispatch('asset-upload-finish'); event.target.value = '';
            }, event => { this.progress = event.detail.progress; });
        },
        async remove() {
            if (this.removing || this.busy) return;
            this.removing = true; this.message = ''; this.$dispatch('asset-upload-start');
            const previous = this.files;
            try {
                await $wire.set(@js( $field->getStatePath() ), {});
                if (this.local) URL.revokeObjectURL(this.local);
                this.local = ''; this.selectedName = ''; this.selectedSize = ''; this.failed = false;
            } catch {
                this.files = previous; this.message = @js( __( 'lar::frame.upload.removeFailed' ) );
            } finally {
                this.removing = false; this.$dispatch('asset-upload-finish');
            }
        },
        async copy(showTooltip) {
            try {
                if (navigator.clipboard &amp;&amp; window.isSecureContext) { await navigator.clipboard.writeText(this.address); }
                else {
                    const input = document.createElement('textarea'); input.value = this.address;
                    document.body.appendChild(input); input.select();
                    const copied = document.execCommand('copy'); input.remove();
                    if (!copied) throw new Error('copy');
                }
                showTooltip(@js( __( 'lar::frame.upload.copied' ) ));
            } catch { showTooltip(@js( __( 'lar::frame.upload.copyFailed' ) )); }
        }
    }"
>
    <div class="lar-asset-upload-panel" x-bind:class="{ 'is-uploading': busy }">
        <div class="lar-asset-upload-preview">
            @if ( $field->isImageAsset() && $field->isPreviewable() )
                <template x-if="preview &amp;&amp; !failed">
                    <img x-bind:src="preview" x-on:error="failed = true" alt="{{ $field->getLabel() }}" />
                </template>
            @endif
            <div x-show="{{ $field->isImageAsset() ? '!preview || failed' : 'true' }}" class="lar-asset-upload-placeholder">
                @if ( $field->isImageAsset() )
                    <x-filament::icon icon="heroicon-o-photo" />
                @else
                    <template x-if="!preview">
                        <x-filament::icon icon="heroicon-o-arrow-up-tray" />
                    </template>
                    <template x-if="preview">
                        <x-filament::icon icon="heroicon-o-document-text" />
                    </template>
                @endif
            </div>
        </div>
        <div class="lar-asset-upload-content">
            <div class="lar-asset-upload-heading">
                <span class="lar-asset-upload-name" x-bind:title="filename" x-text="filename || @js( __( $field->isImageAsset() ? 'lar::frame.upload.emptyImage' : 'lar::frame.upload.emptyFile' ) )"></span>

            </div>
            <div class="lar-asset-upload-detail">
                <span x-show="failed" x-cloak>{{ __( 'lar::frame.upload.missingImage' ) }}</span>
                <span x-show="!failed" x-text="selectedSize || @js( __( 'lar::frame.upload.uploadLimit', ['size' => $field->getMaxSize() / 1024] ) )"></span>
            </div>
            <div class="lar-asset-upload-actions">
                <input x-ref="input" type="file" hidden accept="{{ implode( ',', $field->getAcceptedFileTypes() ?? [] ) }}" x-on:change="select($event)" @disabled( $field->isDisabled() ) />
                <x-filament::button type="button" size="sm" color="primary" outlined icon="heroicon-o-arrow-up-tray" x-on:click="$refs.input.click()" :x-bind:disabled="'busy || removing || '.( $field->isDisabled() ? 'true' : 'false' )" :disabled="$field->isDisabled()">
                    <span x-text="busy ? @js( __( 'lar::frame.upload.uploading' ) ) : (preview ? @js( __( $field->isImageAsset() ? 'lar::frame.upload.replaceImage' : 'lar::frame.upload.replaceFile' ) ) : @js( __( $field->isImageAsset() ? 'lar::frame.upload.selectImage' : 'lar::frame.upload.selectFile' ) ))"></span>
                </x-filament::button>
                @if ( $field->isDeletable() )
                <x-filament::button type="button" color="danger" size="sm" outlined x-cloak x-show="!busy &amp;&amp; (removing || Object.keys(files || {}).length)" x-on:click="remove()" :x-bind:disabled="'removing || '.( $field->isDisabled() ? 'true' : 'false' )" x-bind:aria-busy="removing" :aria-label="__( $field->isImageAsset() ? 'lar::frame.upload.removeImage' : 'lar::frame.upload.removeFile' )" :disabled="$field->isDisabled()">
                    <x-filament::icon icon="heroicon-o-trash" x-show="!removing" />
                    <x-filament::loading-indicator x-show="removing" x-cloak />
                </x-filament::button>
                @endif
                <div class="lar-asset-upload-tools" x-cloak x-show="!busy &amp;&amp; (removing || Object.keys(files || {}).length)">
                    @if ( $field->isOpenable() )
                    <x-filament::icon-button tag="a" color="gray" icon="heroicon-o-arrow-top-right-on-square" :label="__( 'lar::frame.upload.preview' )" x-show="preview &amp;&amp; !failed" x-bind:href="preview" x-bind:target="'_blank'" rel="noopener noreferrer" />
                    @endif
                    <x-filament::icon-button type="button" color="gray" icon="heroicon-o-clipboard" :label="__( 'lar::frame.upload.copy' )" x-show="address" x-on:click="copy((message) => $tooltip(message, { theme: $store.theme, timeout: 1500 }))" />
                </div>
                <span class="lar-asset-upload-feedback" x-show="message || busy" x-cloak x-text="busy ? progress + '%' : message" role="status"></span>
            </div>
        </div>
        <div class="lar-asset-upload-progress" x-show="busy" x-cloak role="progressbar" x-bind:aria-valuenow="progress" aria-valuemin="0" aria-valuemax="100" aria-label="{{ __( 'lar::frame.upload.uploading' ) }}">
            <span x-bind:style="{ width: progress + '%' }"></span>
        </div>
    </div>
</div>
@once
    <style>
        /* 资源缩略图、文件信息与快捷操作 */
        .lar-asset-upload { width: 100%; min-width: 0; }
        .lar-asset-upload .lar-asset-upload-panel { display: flex; align-items: center; gap: 1rem; min-width: 0; padding: 1rem; overflow: hidden; border: 1px solid var(--gray-200); border-radius: 0.75rem; background: transparent; transition: border-color 160ms ease; position: relative; }
        .lar-asset-upload .lar-asset-upload-panel:hover { border-color: var(--gray-300); }
        .lar-asset-upload .lar-asset-upload-panel.is-uploading { border-color: var(--primary-400); }
        .lar-asset-upload .lar-asset-upload-preview { display: flex; align-self: flex-end; align-items: center; justify-content: center; width: 5.5rem; height: 5.5rem; flex-shrink: 0; overflow: hidden; border-radius: 0.5rem; background: var(--gray-100); }
        .lar-asset-upload .lar-asset-upload-preview img { width: 100%; height: 100%; padding: 0.375rem; object-fit: contain; }
        .lar-asset-upload .lar-asset-upload-placeholder { display: flex; align-items: center; justify-content: center; color: var(--gray-400); }
        .lar-asset-upload .lar-asset-upload-placeholder svg { width: 2rem; height: 2rem; stroke-width: 1.25; }
        .lar-asset-upload .lar-asset-upload-content { flex: 1; min-width: 0; }
        .lar-asset-upload .lar-asset-upload-heading { display: flex; align-items: center; justify-content: space-between; gap: 0.5rem; min-height: 1.75rem; }
        .lar-asset-upload .lar-asset-upload-name { overflow: hidden; color: var(--gray-900); font-size: 0.875rem; font-weight: 500; text-overflow: ellipsis; white-space: nowrap; }
        .lar-asset-upload .lar-asset-upload-tools { display: flex; align-items: center; gap: 0.375rem; flex-shrink: 0; padding-left: 0.75rem; border-left: 1px solid var(--gray-200); }
        .lar-asset-upload .lar-asset-upload-tools .fi-icon-btn { width: 2rem; height: 2rem; }
        .lar-asset-upload .lar-asset-upload-tools .fi-icon-btn svg { width: 1rem; height: 1rem; }
        .lar-asset-upload .lar-asset-upload-detail { margin-top: 0.125rem; color: var(--gray-500); font-size: 0.75rem; line-height: 1.5; }
        .lar-asset-upload .lar-asset-upload-actions { display: flex; flex-wrap: wrap; align-items: center; gap: 0.75rem; margin-top: 0.75rem; }
        .lar-asset-upload .lar-asset-upload-feedback { color: var(--gray-500); font-size: 0.75rem; }
        .lar-asset-upload .lar-asset-upload-progress { height: 2px; background: var(--gray-100); position: absolute; right: 0; bottom: 0; left: 0; }
        .lar-asset-upload .lar-asset-upload-progress span { display: block; height: 100%; background: var(--primary-500); transition: width 180ms ease; }
        .dark .lar-asset-upload .lar-asset-upload-panel { border-color: var(--gray-700); background: var(--gray-900); }
        .dark .lar-asset-upload .lar-asset-upload-panel:hover { border-color: var(--gray-600); }
        .dark .lar-asset-upload .lar-asset-upload-preview { background: var(--gray-800); }
        .dark .lar-asset-upload .lar-asset-upload-tools { border-color: var(--gray-700); }
        .dark .lar-asset-upload .lar-asset-upload-name { color: var(--gray-100); }
        .dark .lar-asset-upload .lar-asset-upload-detail, .dark .lar-asset-upload .lar-asset-upload-feedback { color: var(--gray-400); }
        @media (max-width: 640px) {
            .lar-asset-upload .lar-asset-upload-panel { flex-direction: column; align-items: stretch; gap: 0.75rem; padding: 0.75rem; }
            .lar-asset-upload .lar-asset-upload-preview { width: 100%; height: 5rem; }
            .lar-asset-upload .lar-asset-upload-tools { gap: 0.25rem; padding-left: 0; border-left: 0; }
        }
        @media (prefers-reduced-motion: reduce) { .lar-asset-upload .lar-asset-upload-panel, .lar-asset-upload .lar-asset-upload-progress span { transition: none; } }
    </style>
@endonce
