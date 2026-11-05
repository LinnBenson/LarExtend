{{-- 上传表单状态：上传或移除期间阻止提交，按钮可通过 uploads 绑定禁用状态 --}}
<div {{ $attributes }}
    x-data="{ uploads: 0 }"
    x-on:form-processing-started.capture="uploads++"
    x-on:form-processing-finished.capture="uploads = Math.max(0, uploads - 1)"
    x-on:asset-upload-start="uploads++"
    x-on:asset-upload-finish="uploads = Math.max(0, uploads - 1)"
    x-on:submit.capture="if (uploads > 0) { $event.preventDefault(); $event.stopImmediatePropagation(); }"
>
    {{ $slot }}
</div>
