<?php

namespace LarExtend\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;
use ZipArchive;

/**
 * 插件打包服务
 * 负责处理插件的打包、解包及相关操作
 */
class PluginPackService {
    /**
     * 从链接安装插件
     * 下载并确认 ZIP 格式后调用安装流程，结束时清理本次下载的临时文件。
     * @param string $link 插件压缩包下载链接
     * @param string $method 请求方法，默认 GET
     * @param array $headers 请求头
     * @param array $data 请求数据，GET 使用查询参数，其它方法使用 JSON 请求体
     * @return bool 安装是否成功
     */
    public static function installToLink( string $link, string $method = 'GET', array $headers = [], array $data = [] ): bool {
        $source = null;
        $archive = null;
        $archiveOpened = false;
        $repository = null;
        $host = null;
        try {
            $url = parse_url( $link );
            if ( !filter_var( $link, FILTER_VALIDATE_URL ) || !in_array( strtolower( $url['scheme'] ?? '' ), ['http', 'https'], true ) || isset( $url['user'] ) || isset( $url['pass'] ) ) {
                // 下载链接无效、协议不受支持或包含明文身份凭据。
                Log::error( '[PluginPackService::installToLink] Invalid plugin download URL.' );
                return false;
            }
            $host = $url['host'];
            if ( strtolower( $host ) === 'github.com' && !isset( $url['port'] ) && preg_match( '~^/([a-zA-Z0-9-]+)/([a-zA-Z0-9_.-]+)/?$~D', $url['path'] ?? '', $matches ) ) {
                $repository = $matches[2];
                if ( !PluginLoadService::verificationPluginId( $repository ) ) {
                    // 仓库名不能作为合法插件 ID。
                    Log::error( '[PluginPackService::installToLink] Invalid GitHub repository plugin ID.', ['repository' => $repository] );
                    return false;
                }
                $link = "https://codeload.github.com/{$matches[1]}/{$repository}/zip/refs/heads/main";
                $url = parse_url( $link );
                $host = $url['host'];
            }
            $installPath = PluginLoadService::normalizePath( config( 'lar.plugin.path.install' ) );
            if ( !is_dir( $installPath ) && !File::makeDirectory( $installPath, 0755, true ) ) {
                // 创建插件下载文件存放目录失败。
                Log::error( '[PluginPackService::installToLink] Unable to create plugin download directory.', ['path' => $installPath] );
                return false;
            }
            $source = tempnam( $installPath, '.download-' );
            if ( $source === false ) {
                // 创建插件下载临时文件失败。
                Log::error( '[PluginPackService::installToLink] Unable to create plugin download file.', ['path' => $installPath] );
                return false;
            }
            $method = strtoupper( trim( $method ) );
            $options = $method === 'GET' ? [] : ['json' => $data];
            if ( $method === 'GET' && $data !== [] ) {
                parse_str( $url['query'] ?? '', $query );
                $options['query'] = array_replace( $query, $data );
            }
            $response = Http::withHeaders( $headers )->connectTimeout( 10 )->timeout( 120 )->withOptions( [
                'sink' => $source,
                'allow_redirects' => ['max' => 5, 'protocols' => ['http', 'https']],
            ] )->send( $method, $link, $options );
            clearstatcache( true, $source );
            if ( $response->status() !== 200 || !is_file( $source ) || filesize( $source ) === 0 ) {
                // 下载响应异常或文件为空，不能交给安装流程。
                Log::error( '[PluginPackService::installToLink] Failed to download plugin archive.', ['host' => $host, 'status' => $response->status()] );
                return false;
            }
            $archive = new ZipArchive();
            $flags = $repository === null ? ZipArchive::RDONLY | ZipArchive::CHECKCONS : ZipArchive::CHECKCONS;
            if ( $archive->open( $source, $flags ) !== true ) {
                // 下载内容不是有效 ZIP，清理文件且不进入安装检查。
                Log::error( '[PluginPackService::installToLink] Downloaded plugin file is not a valid ZIP archive.', ['host' => $host] );
                return false;
            }
            $archiveOpened = true;
            if ( $repository !== null ) {
                $root = "{$repository}-main/";
                $entries = [];
                for ( $index = 0; $index < $archive->numFiles; $index++ ) {
                    $name = $archive->getNameIndex( $index );
                    if ( $name === false || !str_starts_with( $name, $root ) ) {
                        // GitHub 源码包必须只包含对应仓库的 main 分支顶层目录。
                        Log::error( '[PluginPackService::installToLink] Unexpected GitHub archive root.', ['repository' => $repository] );
                        return false;
                    }
                    $entries[$index] = $repository.'/'.substr( $name, strlen( $root ) );
                }
                // 只替换已确认的顶层目录，子目录名称和仓库自身的 -main 后缀保持原样。
                foreach ( $entries as $index => $name ) {
                    if ( !$archive->renameIndex( $index, $name ) ) {
                        // 将 GitHub 源码包顶层目录改为插件 ID 失败。
                        Log::error( '[PluginPackService::installToLink] Unable to normalize GitHub archive root.', ['repository' => $repository] );
                        return false;
                    }
                }
            }
            $archiveOpened = false;
            if ( !$archive->close() ) {
                // 关闭用于格式检查的 ZIP 文件失败。
                Log::error( '[PluginPackService::installToLink] Unable to close downloaded plugin archive.', ['host' => $host] );
                return false;
            }
            $installed = self::install( $source );
            if ( !$installed ) {
                Log::error( '[PluginPackService::installToLink] Downloaded plugin installation failed.', ['host' => $host, 'source' => $source] );
            }
            return $installed;
        }catch ( Throwable $exception ) {
            // 仅记录异常类型，避免网络异常消息泄露下载链接中的敏感参数。
            Log::error( '[PluginPackService::installToLink] Plugin download installation failed.', ['host' => $host, 'source' => $source, 'exception' => $exception::class] );
            return false;
        }finally {
            try {
                if ( $archiveOpened && !$archive->close() ) {
                    // 关闭尚未完成检查的下载 ZIP 失败。
                    Log::error( '[PluginPackService::installToLink] Unable to close unfinished download archive.', ['source' => $source] );
                }
            }catch ( Throwable $exception ) {
                // 关闭下载 ZIP 时发生异常，继续清理临时文件。
                Log::error( '[PluginPackService::installToLink] Failed to close unfinished download archive.', ['source' => $source, 'exception' => $exception::class] );
            }
            try {
                if ( is_string( $source ) && is_file( $source ) && !File::delete( $source ) ) {
                    // 删除插件下载临时文件失败。
                    Log::error( '[PluginPackService::installToLink] Unable to remove plugin download.', ['source' => $source] );
                }
            }catch ( Throwable $exception ) {
                // 清理插件下载临时文件时发生异常。
                Log::error( '[PluginPackService::installToLink] Failed to clean plugin download.', ['source' => $source, 'exception' => $exception::class] );
            }
        }
    }

    /**
     * 安装插件
     * @param string $source 插件源文件路径
     * @return bool 安装是否成功
     */
    public static function install( string $source ): bool {
        $zip = new ZipArchive();
        $opened = false;
        $temporaryPath = null;
        $cachePath = null;
        $pluginId = null;
        $success = false;
        try {
            if ( !is_file( $source ) || !is_readable( $source ) || $zip->open( $source ) !== true ) {
                // 源压缩包不存在、不可读或无法打开。
                Log::error( '[PluginPackService::install] Unable to open plugin archive.', ['source' => $source] );
                return false;
            }
            $opened = true;
            // 解压前拒绝越界路径、符号链接和其它特殊文件。
            for ( $index = 0; $index < $zip->numFiles; $index++ ) {
                $name = $zip->getNameIndex( $index );
                $system = 0;
                $attributes = 0;
                if ( $name === false || $name === '' || str_contains( $name, '\\' ) || str_contains( $name, ':' ) || str_contains( $name, "\0" ) || str_starts_with( $name, '/' ) || preg_match( '~(?:^|/)\.{1,2}(?:/|$)|//~', $name ) ) {
                    // 压缩包条目路径无效或存在越界风险。
                    Log::error( '[PluginPackService::install] Invalid plugin archive path.', ['source' => $source, 'entry' => $name] );
                    return false;
                }
                if ( !$zip->getExternalAttributesIndex( $index, $system, $attributes ) ) {
                    // 无法读取压缩包条目的文件属性。
                    Log::error( '[PluginPackService::install] Unable to inspect plugin archive entry.', ['entry' => $name] );
                    return false;
                }
                $type = ( $attributes >> 16 ) & 0170000;
                if ( $system === ZipArchive::OPSYS_UNIX && !in_array( $type, [0, 0100000, 0040000], true ) ) {
                    // 压缩包包含符号链接或不支持的特殊文件。
                    Log::error( '[PluginPackService::install] Unsupported plugin archive entry.', ['entry' => $name] );
                    return false;
                }
            }
            $installPath = PluginLoadService::normalizePath( config( 'lar.plugin.path.install' ) );
            if ( !is_dir( $installPath ) && !File::makeDirectory( $installPath, 0755, true ) ) {
                // 创建插件安装临时根目录失败。
                Log::error( '[PluginPackService::install] Unable to create plugin install directory.', ['path' => $installPath] );
                return false;
            }
            $directory = $installPath.bin2hex( random_bytes( 16 ) );
            if ( !mkdir( $directory, 0700 ) ) {
                // 创建本次安装的独立临时目录失败。
                Log::error( '[PluginPackService::install] Unable to create temporary install directory.', ['path' => $directory] );
                return false;
            }
            $temporaryPath = $directory;
            if ( !$zip->extractTo( $temporaryPath ) ) {
                // 解压插件压缩包失败。
                Log::error( '[PluginPackService::install] Unable to extract plugin archive.', ['source' => $source] );
                return false;
            }
            $opened = false;
            if ( !$zip->close() ) {
                // 关闭插件源压缩包失败。
                Log::error( '[PluginPackService::install] Unable to close plugin archive.', ['source' => $source] );
                return false;
            }
            $entries = array_values( array_diff( scandir( $temporaryPath ), ['.', '..'] ) );
            if ( count( $entries ) !== 1 || !is_dir( "{$temporaryPath}/{$entries[0]}" ) || !PluginLoadService::verificationPluginId( $entries[0] ) ) {
                // 压缩包顶层结构不符合要求或插件 ID 无效。
                Log::error( '[PluginPackService::install] Plugin archive must contain one valid plugin directory.', ['source' => $source] );
                return false;
            }
            $pluginId = $entries[0];
            $pluginPath = "{$temporaryPath}/{$pluginId}";
            $workPath = PluginLoadService::normalizePath( config( 'lar.plugin.path.work' ) );
            $targetPath = $workPath.$pluginId;
            // 替换目标目录时，源压缩包和本次临时目录必须位于目标之外。
            $targetRoot = PluginLoadService::normalizePath( $targetPath );
            if ( str_starts_with( realpath( $source ), $targetRoot ) || str_starts_with( PluginLoadService::normalizePath( $temporaryPath ), $targetRoot ) ) {
                // 源压缩包或临时目录位于待替换的插件目录内。
                Log::error( '[PluginPackService::install] Plugin installation source overlaps its target.', ['id' => $pluginId] );
                return false;
            }
            $oldPlugin = is_dir( $targetPath ) ? plugin( $pluginId, true ) : null;
            $plugin = PluginLoadService::init( $pluginPath, true, false, false );
            if ( $plugin === null ) {
                // 待安装插件检查或加载失败。
                Log::error( '[PluginPackService::install] Unable to load plugin for installation.', ['id' => $pluginId] );
                return false;
            }
            if ( $oldPlugin !== null && !version_compare( $plugin->package['version'], $oldPlugin->package['version'], '>' ) ) {
                // 待安装版本未高于已安装版本。
                Log::error( '[PluginPackService::install] Plugin version must be newer than the installed version.', ['id' => $pluginId] );
                return false;
            }
            if ( is_public( $plugin, 'install' ) && $plugin->install() === false ) {
                // 插件安装方法返回失败。
                Log::error( '[PluginPackService::install] Plugin installation returned false.', ['id' => $pluginId] );
                return false;
            }
            if ( !is_dir( $workPath ) && !File::makeDirectory( $workPath, 0755, true ) ) {
                // 创建插件工作目录失败。
                Log::error( '[PluginPackService::install] Unable to create plugin work directory.', ['path' => $workPath] );
                return false;
            }
            $cachePath = $targetRoot;
            if ( file_exists( $targetPath ) || is_link( $targetPath ) ) {
                $deleted = is_dir( $targetPath ) && !is_link( $targetPath ) ? File::deleteDirectory( $targetPath ) : File::delete( $targetPath );
                clearstatcache( true, $targetPath );
                if ( !$deleted || file_exists( $targetPath ) || is_link( $targetPath ) ) {
                    // 旧插件目录删除失败或仍有残留。
                    Log::error( '[PluginPackService::install] Unable to remove previous plugin directory.', ['id' => $pluginId] );
                    return false;
                }
            }
            if ( !File::moveDirectory( $pluginPath, $targetPath ) ) {
                // 将插件移动到工作目录失败。
                Log::error( '[PluginPackService::install] Unable to move installed plugin directory.', ['id' => $pluginId] );
                return false;
            }
            $success = true;
        }catch ( Throwable $exception ) {
            // 插件安装过程中发生异常。
            Log::error( '[PluginPackService::install] Failed to install plugin.', ['id' => $pluginId, 'source' => $source, 'message' => $exception->getMessage()] );
        }finally {
            try {
                if ( $opened && !$zip->close() ) {
                    $success = false;
                    // 关闭插件源压缩包失败。
                    Log::error( '[PluginPackService::install] Unable to close plugin archive.', ['source' => $source] );
                }
            }catch ( Throwable $exception ) {
                $success = false;
                // 关闭插件源压缩包时发生异常。
                Log::error( '[PluginPackService::install] Failed to close plugin archive.', ['message' => $exception->getMessage()] );
            }
            try {
                if ( $temporaryPath !== null && is_dir( $temporaryPath ) ) {
                    $deleted = File::deleteDirectory( $temporaryPath );
                    clearstatcache( true, $temporaryPath );
                    if ( !$deleted || file_exists( $temporaryPath ) || is_link( $temporaryPath ) ) {
                        $success = false;
                        // 安装临时目录删除失败或仍有残留。
                        Log::error( '[PluginPackService::install] Unable to remove temporary install directory.', ['path' => $temporaryPath] );
                    }
                }
            }catch ( Throwable $exception ) {
                $success = false;
                // 清理安装临时文件时发生异常。
                Log::error( '[PluginPackService::install] Failed to clean plugin installation files.', ['path' => $temporaryPath, 'message' => $exception->getMessage()] );
            }
            if ( $cachePath !== null ) {
                PluginLoadService::clearCache( $cachePath );
                PluginLoadService::clearCache( $targetPath );
            }
        }
        if ( !$success ) { return false; }
        try {
            if ( File::delete( $source ) ) { return true; }
            // 安装完成后删除源压缩包失败。
            Log::error( '[PluginPackService::install] Unable to delete installed plugin archive.', ['source' => $source] );
        }catch ( Throwable $exception ) {
            // 安装完成后删除源压缩包时发生异常。
            Log::error( '[PluginPackService::install] Failed to delete installed plugin archive.', ['source' => $source, 'message' => $exception->getMessage()] );
        }
        return false;
    }

    /**
     * 卸载插件
     * @param string $pluginId 插件 ID
     * @return bool 卸载是否成功
     */
    public static function uninstall( string $pluginId ): bool {
        $cachePath = null;
        $path = null;
        try {
            if ( !PluginLoadService::verificationPluginId( $pluginId ) ) {
                // 插件 ID 无效，停止卸载。
                Log::error( '[PluginPackService::uninstall] Invalid plugin ID.', ['id' => $pluginId] );
                return false;
            }
            // 准备删除的内容
            $delete = [
                // 插件工作目录
                PluginLoadService::normalizePath( config( 'lar.plugin.path.work' ) ).$pluginId,
                // 用户配置文件
                PluginLoadService::normalizePath( config( 'lar.plugin.path.config' ) )."{$pluginId}.php",
                // 用户配置文件的锁
                PluginLoadService::normalizePath( config( 'lar.plugin.path.config' ) )."{$pluginId}.php.lock",
                // 插件日志文件
                PluginLoadService::normalizePath( config( 'lar.plugin.path.log' ) )."{$pluginId}.log",
            ];
            // 尝试调用内部的卸载方法
            $plugin = plugin( $pluginId );
            if ( $plugin && is_public( $plugin, 'uninstall' ) && !$plugin->uninstall() ) {
                // 插件卸载方法返回假值，停止后续清理。
                Log::error( '[PluginPackService::uninstall] Plugin uninstall returned a false value.', ['id' => $pluginId] );
                return false;
            }
            // 清理插件权限
            if ( !PluginPermissionService::remove( $pluginId, config( 'lar.plugin.permissions', [] ) ) ) {
                // 移除插件授权失败。
                Log::error( '[PluginPackService::uninstall] Unable to remove plugin permissions.', ['id' => $pluginId] );
                return false;
            }
            // 删除前保存缓存路径，部分删除失败时也清理旧插件对象。
            $cachePath = PluginLoadService::normalizePath( $delete[0] );
            foreach ( $delete as $path ) {
                if ( !file_exists( $path ) && !is_link( $path ) ) { continue; }
                $deleted = is_dir( $path ) && !is_link( $path ) ? File::deleteDirectory( $path ) : File::delete( $path );
                clearstatcache( true, $path );
                if ( !$deleted || file_exists( $path ) || is_link( $path ) ) {
                    // 插件相关文件删除失败或仍有残留。
                    Log::error( '[PluginPackService::uninstall] Unable to delete plugin files.', ['id' => $pluginId, 'path' => $path] );
                    return false;
                }
            }
            return true;
        }catch ( Throwable $exception ) {
            // 插件卸载过程中发生异常。
            Log::error( '[PluginPackService::uninstall] Failed to uninstall plugin.', [
                'id' => $pluginId,
                'path' => $path,
                'message' => $exception->getMessage(),
            ] );
            return false;
        }finally {
            if ( $cachePath !== null ) { PluginLoadService::clearCache( $cachePath ); }
        }
    }

    /**
     * 打包插件
     * @param string $pluginId 插件 ID
     * @return bool 打包是否成功
     */
    public static function packPlugin( string $pluginId ): bool {
        $pluginPath = PluginLoadService::normalizePath( config( 'lar.plugin.path.work' ) ).$pluginId.DIRECTORY_SEPARATOR;
        if ( !is_dir( $pluginPath ) ) { return false; }
        $zip = new ZipArchive();
        $opened = false;
        $temporaryPath = null;
        try {
            $rules = [];
            $ignorePath = $pluginPath.'.gitignore';
            $lines = is_file( $ignorePath ) ? file( $ignorePath, FILE_IGNORE_NEW_LINES ) : [];
            if ( $lines === false ) { throw new RuntimeException( 'Unable to read plugin ignore rules.' ); }
            foreach ( $lines as $line ) {
                $pattern = trim( $line );
                if ( $pattern === '' || str_starts_with( $pattern, '#' ) ) { continue; }
                $include = str_starts_with( $pattern, '!' );
                if ( $include ) { $pattern = substr( $pattern, 1 ); }
                $directoryOnly = str_ends_with( $pattern, '/' );
                $anyDirectory = str_starts_with( $pattern, '**/' );
                if ( $anyDirectory ) { $pattern = substr( $pattern, 3 ); }
                $rootOnly = str_starts_with( $pattern, '/' );
                $pattern = trim( $pattern, '/' );
                // 仅支持普通路径、单星号和 **/目录/，其它语法整行跳过。
                if ( $pattern === '' || preg_match( '~[?\\\\\[\]{}!#]|\*\*|//|(?:^|/)\.{1,2}(?:/|$)~', $pattern ) ) { continue; }
                if ( $anyDirectory && ( !$directoryOnly || str_contains( $pattern, '/' ) || str_contains( $pattern, '*' ) ) ) { continue; }
                $prefix = $rootOnly || str_contains( $pattern, '/' ) ? '^' : '(?:^|/)';
                $expression = str_replace( '\\*', '[^/]*', preg_quote( $pattern, '~' ) );
                $rules[] = [
                    'expression' => "~{$prefix}{$expression}$~D",
                    'directoryOnly' => $directoryOnly,
                    'fileOnly' => !$directoryOnly && str_contains( $pattern, '*' ),
                    'include' => $include,
                ];
            }
            $packPath = PluginLoadService::normalizePath( config( 'lar.plugin.path.pack' ) );
            if ( !is_dir( $packPath ) && !mkdir( $packPath, 0755, true ) && !is_dir( $packPath ) ) {
                throw new RuntimeException( 'Unable to create plugin package directory.' );
            }
            $temporaryPath = tempnam( $packPath, '.plugin-' );
            if ( $temporaryPath === false ) { throw new RuntimeException( 'Unable to create temporary package.' ); }
            if ( $zip->open( $temporaryPath, ZipArchive::OVERWRITE ) !== true ) {
                throw new RuntimeException( 'Unable to open plugin package.' );
            }
            $opened = true;
            if ( !$zip->addEmptyDir( $pluginId ) ) { throw new RuntimeException( 'Unable to add package root.' ); }
            $directories = [''];
            while ( $directories !== [] ) {
                $relativeDirectory = array_pop( $directories );
                $entries = scandir( $pluginPath.$relativeDirectory );
                if ( $entries === false ) { throw new RuntimeException( 'Unable to read plugin directory.' ); }
                foreach ( $entries as $entry ) {
                    if ( $entry === '.' || $entry === '..' ) { continue; }
                    $relativePath = $relativeDirectory.$entry;
                    $path = $pluginPath.$relativePath;
                    // 不跟随符号链接，避免把插件目录之外的文件打入包中。
                    if ( is_link( $path ) ) { continue; }
                    $isDirectory = is_dir( $path );
                    if ( $isDirectory && $entry === '.git' ) { continue; }
                    $ignored = false;
                    foreach ( $rules as $rule ) {
                        if ( ( $rule['directoryOnly'] && !$isDirectory ) || ( $rule['fileOnly'] && $isDirectory ) ) { continue; }
                        if ( preg_match( $rule['expression'], $relativePath ) === 1 ) {
                            $ignored = !$rule['include'];
                        }
                    }
                    if ( $ignored ) { continue; }
                    $archivePath = "{$pluginId}/{$relativePath}";
                    if ( $isDirectory ) {
                        if ( !$zip->addEmptyDir( $archivePath ) ) { throw new RuntimeException( 'Unable to add plugin directory.' ); }
                        $directories[] = $relativePath.'/';
                    }elseif ( !is_file( $path ) || !$zip->addFile( $path, $archivePath ) ) {
                        throw new RuntimeException( 'Unable to add plugin file.' );
                    }
                }
            }
            $opened = false;
            if ( !$zip->close() ) { throw new RuntimeException( 'Unable to finish plugin package.' ); }
            if ( !rename( $temporaryPath, "{$packPath}{$pluginId}.zip" ) ) {
                throw new RuntimeException( 'Unable to save plugin package.' );
            }
            return true;
        }catch ( Throwable $exception ) {
            // 记录插件打包失败的错误信息。
            Log::error( '[PluginPackService::packPlugin] Failed to pack plugin.', ['id' => $pluginId, 'message' => $exception->getMessage()] );
            return false;
        }finally {
            try {
                if ( $opened ) { $zip->close(); }
            }catch ( Throwable $exception ) {
                // 记录插件关闭失败的错误信息。
                Log::error( '[PluginPackService::packPlugin] Failed to close plugin package.', ['id' => $pluginId, 'message' => $exception->getMessage()] );
            }finally {
                try {
                    if ( is_string( $temporaryPath ) && is_file( $temporaryPath ) && !unlink( $temporaryPath ) ) {
                        // 删除打包临时文件失败。
                        Log::error( '[PluginPackService::packPlugin] Unable to remove temporary plugin package.', ['id' => $pluginId, 'path' => $temporaryPath] );
                    }
                }catch ( Throwable $exception ) {
                    // 清理打包临时文件时发生异常。
                    Log::error( '[PluginPackService::packPlugin] Failed to clean temporary plugin package.', ['id' => $pluginId, 'path' => $temporaryPath, 'message' => $exception->getMessage()] );
                }
            }
        }
    }
}
