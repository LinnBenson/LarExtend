{{-- 路由声明的中间件 --}}
<details class="routes-detail" wire:key="middleware-{{ $getRecord()['id'] }}">
    <summary>{{ __( 'lar::DeveloperCenter.Routes.middlewareCount', ['count' => count( $getRecord()['middleware'] )] ) }}</summary>
    @foreach ( $getRecord()['middleware'] as $middleware )
        <code>{{ $middleware }}</code>
    @endforeach
</details>
