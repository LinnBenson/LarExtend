<?php

namespace App\Filament\Concerns;

/**
 * 后台工具类
 * 提供管理员等级等后台公共功能。
 */
class AdminTool {

    /**
     * 获取等级键名
     * 匹配不超过当前等级的最高门槛，不依赖配置排列顺序。
     * @param int $level 管理员等级
     * @return string 等级键名，未匹配或配置无效时返回空字符串
     */
    public static function levelName( int $level ): string {
        $levels = config( 'admin_level.levels', [] );
        if ( !is_array( $levels ) ) { return ''; }
        $matchedName = '';
        $matchedLevel = -1;
        foreach ( $levels as $name => $value ) {
            if ( !is_string( $name ) || !is_int( $value ) || $value < 0 ) { continue; }
            if ( $value <= $level && $value > $matchedLevel ) {
                $matchedName = $name;
                $matchedLevel = $value;
            }
        }
        return $matchedName !== '' ? __( "admin::frame.levels.{$matchedName}" ) : 'Unknown';
    }

}
