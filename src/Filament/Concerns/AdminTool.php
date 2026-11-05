<?php

namespace LarExtend\Filament\Concerns;

use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use LarExtend\Models\AdminUser;

/**
 * 后台工具类
 * 提供管理员等级等后台公共功能。
 */
class AdminTool {
    /**
     * 获取等级键名
     * @param int $level 管理员等级
     * @return string 等级键名，未匹配或配置无效时返回 Unknown
     */
    public static function levelName( int $level ): string {
        $levels = AdminUser::getLevel();
        if ( !is_array( $levels ) ) { return 'Unknown'; }
        $matchedName = '';
        $matchedLevel = -1;
        foreach ( $levels as $name => $value ) {
            if ( !is_string( $name ) || !is_int( $value ) || $value < 0 ) { continue; }
            if ( $value <= $level && $value > $matchedLevel ) {
                $matchedName = $name;
                $matchedLevel = $value;
            }
        }
        return $matchedName !== '' ? __( "lar::frame.levels.{$matchedName}" ) : 'Unknown';
    }

    /**
     * 检查管理员等级
     * @param int|string $level 最低等级边界，默认为 'service'
     * @return bool 是否允许访问
     */
    public static function check( int|string $level = 'service' ): bool {
        if ( !is_numeric( $level ) ) {
            $levels = AdminUser::LEVELS;
            $level = $levels[$level] ?? $levels['administrator'];
        }
        $adminUser = auth( config( 'lar.admin.guard', 'filament' ) )->user();
        return $adminUser instanceof AdminUser && $adminUser->status === true && $adminUser->level >= (int) $level;
    }

    /**
     * 渲染后台 Markdown
     * 转换 GitHub 风格 Markdown，过滤原始 HTML 和不安全链接。
     * @param string $content Markdown 原文
     * @return HtmlString 可在 Blade 中输出的 HTML
     */
    public static function markdown( string $content ): HtmlString {
        return new HtmlString( Str::markdown( $content, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ] ) );
    }
}