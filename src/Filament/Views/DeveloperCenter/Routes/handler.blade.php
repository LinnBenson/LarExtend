{{-- 路由处理器完整名称 --}}
<details class="routes-detail" wire:key="handler-{{ $getRecord()['id'] }}">
    <summary>{{ $getRecord()['handlerLabel'] }}</summary>
    <code>{{ $getRecord()['handler'] }}</code>
</details>
