<?php

namespace LarExtend\Filament\Concerns;

use Closure;
use Filament\Facades\Filament;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * 后台会话访问检查
 * 面板访问资格失效时清理登录状态，使用户可以重新选择账号登录。
 */
class EnsureAdminSession {
    /**
     * 校验当前后台会话
     * @param Request $request 请求对象
     * @param Closure $next 后续处理
     * @return Response HTTP 响应
     */
    public function handle( Request $request, Closure $next ): Response {
        $guard = Filament::auth();
        $user = $guard->user();
        if ( $user instanceof FilamentUser && !$user->canAccessPanel( Filament::getCurrentOrDefaultPanel() ) ) {
            $guard->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->to( Filament::getLoginUrl() );
        }
        return $next( $request );
    }
}
