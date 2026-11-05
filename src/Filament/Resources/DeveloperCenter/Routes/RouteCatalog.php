<?php

namespace LarExtend\Filament\Resources\DeveloperCenter\Routes;

use Closure;
use Illuminate\Routing\Route;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route as RouteFacade;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionException;

/**
 * 应用路由目录
 * 仅检查已注册路由及处理器元信息，不执行路由处理器。
 */
class RouteCatalog {
    /**
     * 获取当前应用全部路由
     * @return Collection<int, array> 路由元信息
     */
    public function all(): Collection {
        return collect( RouteFacade::getRoutes()->getRoutes() )->map( function ( Route $route, int $index ): array {
            $action = $route->getActionName();
            $handler = $route->getAction( 'uses' );
            $source = '';
            $line = null;
            $kind = 'controller';
            try {
                if ( $handler instanceof Closure ) {
                    $reflection = new ReflectionFunction( $handler );
                    $kind = 'closure';
                }else {
                    [$class, $method] = str_contains( $action, '@' ) ? explode( '@', $action, 2 ) : [$action, '__invoke'];
                    $reflection = new ReflectionMethod( $class, $method );
                    if ( is_subclass_of( $class, \Filament\Pages\BasePage::class ) ) { $kind = 'page'; }
                }
                $file = $reflection->getFileName();
                $source = $file ? str_replace( base_path().DIRECTORY_SEPARATOR, '', $file ) : '';
                $line = $reflection->getStartLine();
            }catch ( ReflectionException ) {
                // 框架内部或无法反射的处理器仍保留路由声明信息。
            }
            return [
                'id' => (string) $index,
                'methods' => $route->methods(),
                'domain' => $route->getDomain() ?? '',
                'uri' => '/'.ltrim( $route->uri(), '/' ),
                'name' => $route->getName() ?? '',
                'handler' => $action,
                'handlerLabel' => $action === 'Closure' ? 'Closure' : class_basename( $action ),
                'middleware' => $route->gatherMiddleware(),
                'source' => $source,
                'line' => $line,
                'kind' => $kind,
            ];
        } );
    }
}
