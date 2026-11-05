<?php

namespace LarExtend\Filament\Resources\AdminControl\PluginList;

use FilesystemIterator;
use LarExtend\Filament\Resources\DeveloperCenter\Logs\LogFiles;
use LarExtend\Services\PluginLoadService;
use LarExtend\Services\PluginPermissionService;
use Throwable;

/**
 * 插件展示目录
 * 从工作目录读取包元数据，按校验结果和类型分组。
 */
class PluginCatalog {
    /**
     * 扫描插件工作目录
     * @return array 分组后的插件与目录读取状态
     */
    public function all(): array {
        $groups = ['software' => [], 'expand' => [], 'depend' => [], 'invalid' => []];
        $path = config( 'lar.plugin.path.work' );
        if ( !is_dir( $path ) ) { return ['groups' => $groups, 'scanFailed' => false]; }
        try {
            $permissions = PluginPermissionService::getCurrentPermissions( true );
            $logs = new LogFiles( PluginLoadService::normalizePath( config( 'lar.plugin.path.log' ) ) );
            foreach ( new FilesystemIterator( $path ) as $directory ) {
                if ( !$directory->isDir() ) { continue; }
                $id = $directory->getFilename();
                try {
                    $package = $directory->isLink() ? false : PluginLoadService::verificationPackage( $directory->getPathname() );
                }catch ( Throwable ) {
                    $package = false;
                }
                if ( $package === false ) {
                    $groups['invalid'][$id] = ['id' => $id];
                    continue;
                }
                $adminPath = $directory->getPathname() . '/admin.blade.php';
                $requestedPermissions = array_unique( $package['permissions'] );
                $grantedCount = count( array_filter( $requestedPermissions, fn ( string $permission ): bool => in_array( $id, $permissions[$permission] ?? [], true ) ) );
                $groups[$package['type']][$id] = ['id' => $id, 'package' => $package, 'permissionCount' => count( $requestedPermissions ), 'grantedCount' => $grantedCount, 'hasAdmin' => is_file( $adminPath ) && !is_link( $adminPath ) && is_readable( $adminPath )];
                $groups[$package['type']][$id]['hasLog'] = $logs->exists( "{$id}.log" );
            }
        }catch ( Throwable ) {
            return ['groups' => $groups, 'scanFailed' => true];
        }
        foreach ( $groups as &$plugins ) { ksort( $plugins, SORT_NATURAL | SORT_FLAG_CASE ); }
        return ['groups' => $groups, 'scanFailed' => false];
    }
}
