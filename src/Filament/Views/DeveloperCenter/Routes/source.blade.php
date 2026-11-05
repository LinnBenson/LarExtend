{{-- 处理器定义位置，不代表路由注册语句的位置 --}}
<details class="routes-detail" wire:key="source-{{ $getRecord()['id'] }}">
    <summary>{{ __( 'lar::DeveloperCenter.Routes.kinds.'.$getRecord()['kind'] ) }}</summary>
    <code>{{ $getRecord()['source'] ?: __( 'lar::DeveloperCenter.Routes.unknownSource' ) }}@if ( $getRecord()['line'] ):{{ $getRecord()['line'] }}@endif</code>
</details>
