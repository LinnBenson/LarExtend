# LarExtend

> Laravel 插件式开发支持器，专注您的开发，其它流程交给 LarExtend 为您完成！

---

### 开始使用
1. 安装依赖
   - `composer install`
2. 复制 .env.example 为 .env 并修改配置
   - `cp .env.example .env`
3. 生成应用密钥
   - `php artisan key:generate`
4. 运行数据库迁移
   - `php artisan migrate`
5. 创建公开存储目录链接
   - `php artisan storage:link`
6. 安装 LarExtend
   - `composer require todu-io/lar-extend`

### 伪静态部署
```
# 安全保护 防止可执行文件被执行
location ^~ /storage/ {
    location ~* \.(php[0-9]*|phtml|phar)(/|$) {
        return 404;
    }

    add_header X-Content-Type-Options nosniff always;
    autoindex off;
    try_files $uri =404;
}
# 内部预留的资源请求接口
location ^~ /internal- {
    try_files $uri $uri/ /index.php?$query_string;
}
# Livewire 资源请求接口
location ^~ /livewire- {
    try_files $uri $uri/ /index.php?$query_string;
}
# Laravel 应用入口
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

### 命令集合
- 迁移 LarExtend 数据文件
  - `php artisan lar-extend:migrate`
- 发布静态资源
  - `php artisan vendor:publish --tag=lar-extend-assets`

### 默认信息
- 默认超级管理员 Administrator
  - 用户名: `admin@admin.com`
  - 密码: `admin`
- 默认后台入口
  - `https://your-app-domain/admin`
  - 可通过 env 配置 `APP_ADMIN_PREFIX` 修改后台入口路径

#### 通用助手函数 [src/Helpers/Common.php]
- 判断字符串是否为 JSON
  - `is_json( [mixed]待判断的内容 )`
  - return [bool]是否为合法 JSON 对象或 JSON 数组字符串
- 判断对象方法是否公开
  - `is_public( [object]待判断的对象, [string]待判断的方法名称 )`
  - return [bool]是否为对象上存在的公开方法
- 判断字符串是否为 UUID
  - `is_uuid( [mixed]待判断的内容 )`
  - return [bool]是否为标准 UUID 字符串
- 生成 UUID
  - `uuid()`
  - return [string]标准 UUID 字符串
- 生成随机字符串
  - `randomString( [int]字符串长度, [0|1|2]字符串类型 = 2 )`
  - 字符串类型: 0 仅数字，1 仅大小写字母，2 大小写字母加数字
  - return [string]随机字符串
- 格式化时间戳为日期字符串
  - `toDate( [int|null]时间戳 = null )`
  - 时间戳为 null 时使用当前时间
  - return [string]`Y-m-d H:i:s` 格式的日期时间字符串
- 将任意值转换为字符串
  - `toString( [mixed]待转换的值 )`
  - 字符串原样返回；纯字符串数组使用换行符连接，其它数组使用 `var_export()` 转换
  - 布尔值、null 和对象转换为对应的类型标记，数值直接转换为字符串，其它类型使用 `var_export()` 转换
  - return [string]转换后的字符串

#### 系统助手函数 [src/Helpers/System.php]
- 读取系统配置
  - `setting( [string]:配置键, [mixed]:默认值 = null )`
  - return [mixed]配置值或默认值
- 添加配置信息
  - `addConfig( [string]:配置键, [array]:配置值 )`
  - return void
- 输出 JSON 响应
  - `echoJson( [int]状态码, [mixed]消息, [array|null]响应数据 = null, [int]HTTP 状态码 = 200 )`
  - return [string]JSON 字符串
- 输出请求响应
  - `echoRequest( [int]状态码, [mixed]消息, [array|null]响应数据 = null, [int]HTTP 状态码 = 200, [array]HTTP 头 = [] )`
  - return [\Illuminate\Http\JsonResponse] JSON 响应对象
- 获取插件
  - `plugin( [string]:插件 ID, [bool]:是否刷新 = false )`
  - return [PluginProvider|null]插件对象或 null
- 执行 Shell 命令
  - `runShell( [string]Shell 命令 )`
  - 执行 Shell 命令并合并返回标准输出和错误输出
  - return [string|null]命令输出，无法获取输出时返回 null

#### API 异常 [src/Exceptions/ApiException.php]
- 创建 API 异常
  - `new ApiException( [string]错误消息, [int]HTTP 状态码 = 400, [array|null]附加数据 = null )`
  - `LarExtend\Exceptions\ApiException`
  - return [ApiException]异常对象

#### 插件提供者 [src/Providers/PluginProvider.php]
- 插件 ID: string `$plugin->id`
- 插件路径: string `$plugin->path`
- 插件包信息: array `$plugin->package`
- 获取插件声明权限的授权状态
  - `$plugin->getPermissions()`
  - return [array]返回插件的授权状态
- 获取插件配置
  `$plugin->config( [string]配置键 = '', [mixed]默认值 = null )`
  - `$key` 为配置项键名，默认为空字符串，表示获取所有配置
  - return [mixed]配置值或默认值
- 写入插件日志
  - `$plugin->log( [string]日志内容, [array]日志上下文 = [], [string]日志等级 = 'info' )`
  - return void
- 保存插件用户配置
  - `$plugin->saveConfig( [array]本次修改的配置 )`
  - return [bool]保存结果
- 设置插件的视图路径
  - `$plugin->setViews( [string]视图文件夹相对路径 )`
  - 命名空间: `p.{插件 ID}`
  - return void
- 设置插件的语言包路径
  - `$plugin->setLang( [string]语言包文件夹相对路径 )`
  - 命名空间: `p.{插件 ID}`
  - return void
- 设置插件的普通路由
  - `$plugin->setRoute( [callable]路由定义回调, [array]路由中间件数组 = [] )`
  - URL 前缀: `/plugin/{插件 ID}`
  - 路由名称前缀: `plugin.{插件 ID}.`
  - return void
- 设置插件的管理路由
  - `$plugin->setAdminRoute( [callable]路由定义回调, [int|string]最低管理权限等级 = 'manage' )`
  - URL 前缀: `/{admin.path}/{插件 ID}`
  - 路由名称前缀: `admin.{插件 ID}.`
  - return void
- 设置插件的 SQLite 数据库连接
  - `$plugin->setSQLite( [string]连接名称, [string]数据库文件相对路径, [array]连接选项 = [] )`
  - 数据库名称: `database.connections.{连接名称}`
  - return bool
- 设置插件的静态资源路径
  - `$plugin->setAssets( [string]资源文件夹相对路径 )`
  - 路由名称: `"plugin.{插件 ID}.assets`
  - return void
- 获取插件静态资源的 URL
  - `$plugin->asset( [string]资源文件相对路径 )`
  - return [string]静态资源的完整 URL

#### 插件加载服务 [src/Services/PluginLoadService.php]
- 初始化插件
  - `PluginLoadService::init( [string]:插件路径, [bool]:是否刷新 = false, [bool]:是否保存 = true )`
  - return [PluginProvider|null]插件对象或 null
- 清理插件缓存
  - `PluginLoadService::clearCache( [string]:插件路径 )`
  - return void
- 加载插件
  - `PluginLoadService::load( [string]:插件路径 )`
  - return [PluginProvider|null]插件对象或 null
- 验证插件包信息
  - `PluginLoadService::verificationPackage( [string]:插件路径 )`
  - return [array|false]验证通过返回插件数据，失败返回 false
- 验证插件依赖是否存在及格式是否正确
  - `PluginLoadService::verificationRequire( [mixed]:插件依赖信息, [string]:插件路径 )`
  - return [array|false]验证通过返回依赖信息，失败返回 false
- 验证插件 ID 是否有效
  - `PluginLoadService::verificationPluginId( [string]:插件 ID )`
  - return [bool]是否有效
- 路径整理
  - `PluginLoadService::normalizePath( [string]:插件路径 )`
  - return [string]规范化后的插件路径

#### 插件权限服务 [src/Services/PluginPermissionService.php]
- 获取当前权限配置
  - `PluginPermissionService::getCurrentPermissions( [bool]是否刷新 = false )`
  - return [array]当前权限配置
- 添加插件权限
  - `PluginPermissionService::add( [string]插件 ID, [array]权限名称列表 )`
  - 权限必须属于 `lar.plugin.permissions` 白名单；重复授权不重复添加插件 ID
  - return [bool]保存结果
- 移除插件权限
  - `PluginPermissionService::remove( [string]插件 ID, [array]权限名称列表 )`
  - 仅移除指定权限中的插件 ID，重复移除仍返回成功
  - return [bool]保存结果
- 清理失效插件和权限
  - `PluginPermissionService::clean()`
  - 清理失效授权、白名单外权限、重复项和空项
  - return [bool]保存结果
- 执行指定权限的操作
  - `PluginPermissionService::execute( [string]权限名称, [array]传递给权限操作的数据 = [], [bool]是否收集操作结果 = false )`
  - return [mixed]操作结果，若收集结果则为数组，否则为最后一次操作的数据，若未执行则返回传入的数据

#### 插件打包服务 [src/Services/PluginPackService.php]
- 从链接安装插件
  - `PluginPackService::installToLink( [string]压缩包链接, [string]请求方法 = 'GET', [array]请求头 = [], [array]请求数据 = [] )`
  - 支持 `https://github.com/作者/仓库`，直接下载 main 分支，以仓库名作为插件 ID，将源码包顶层的 `{仓库名}-main` 改为仓库名；版本仍按 plugin.json 检查
  - return [bool]安装是否成功
- 安装插件
  - `PluginPackService::install( [string]插件 ZIP 文件路径 )`
  - return [bool]安装是否成功
- 卸载插件
  - `PluginPackService::uninstall( [string]插件 ID )`
  - return [bool]卸载是否成功
- 打包插件
  - `PluginPackService::packPlugin( [string]插件 ID )`
  - return [bool]打包是否成功

#### 管理员用户模型 [src/Models/AdminUser.php]
- 根据等级获取等级名称映射
  - `AdminUser::getLevel( [int|string]管理员等级 )`
  - return [string]等级名称，无效输入返回 `Unknown`；
- 获取管理员等级名称属性
  - `$adminUser->grade` 或 `$adminUser->getGradeAttribute()`
  - return [string]等级名称
- 获取管理员用户头像地址
  - `$adminUser->getFilamentAvatarUrl()`
  - return [string|null]头像地址
- 判断管理员用户是否可以访问 Filament 面板
  - `$adminUser->canAccessPanel( [Panel]Filament 面板 )`
  - return [bool]是否允许访问
- 判断是否超过服务级别
  - `$adminUser->exceedService()`
  - return [bool]是否超过服务级别

#### 配置模型 [src/Models/Settings.php]
- 获取配置类别翻译名称
  - `$setting->category_label` 或 `$setting->getCategoryLabelAttribute()`
  - return [string]类别名称
- 获取配置类型翻译名称
  - `$setting->type_label` 或 `$setting->getTypeLabelAttribute()`
  - return [string]类型名称

#### 管理员检查中间件 [src/Filament/Concerns/AdminMiddleware.php]
- 默认最低等级边界为 `service`
- 支持字符串类型: ordinary|agent|service|manage|administrator
- 支持整数类型，表示具体的管理员等级值
- 无效的输入将被视为最高等级 (administrator)
- 此中间件只用于管理面板外部的路由使用，内部路由由面板服务接管。
- 中间件处理管理员等级检查
  - `AdminMiddleware::class.:{[int|string|null]最低等级边界}`
  - return [Response]HTTP 响应

#### 后台工具类 [src/Filament/Concerns/AdminTool.php]
- 获取管理员等级键名
  - `AdminTool::levelName( [int]管理员等级 )`
  - return [string]等级名称，低于所有门槛或无有效配置时返回空字符串
- 检查管理员等级
  - `AdminTool::check( [int|string]最低等级边界 = 'service' )`
  - return [bool]是否允许访问
- 渲染 GitHub 风格 Markdown，过滤原始 HTML 和不安全链接
  - `AdminTool::markdown( [string]Markdown 原文 )`
  - return HtmlString 可在 Blade 中安全输出的 HTML
- 后台阅读组件，包含标题、列表、引用、代码块和表格样式，支持深浅主题
  - `<x-Filament::markdown :content="$markdown" />`
  - `content` 传入 Markdown 原文，支持通过组件属性追加 class 和其它 HTML 属性

#### 头像上传组件 [src/Filament/Components/AvatarUpload.php]
- `AvatarUpload::make( 'avatar' )->disk( 'public' )->directory( 'admin-avatars' )`
- 依赖 Imagick 及对应格式解码器；允许 jpg、jpeg、png、webp，最大 2 MB、宽高各不超过 4096 像素
- 服务端校验扩展名和 MIME，解码首帧、移除元数据并重新编码为最长边不超过 500px 的静态 WebP；保存值为磁盘相对路径

#### 后台资源上传组件 [src/Filament/Components/AssetUpload.php]
- 单文件使用内联上传，支持预览、移除和复制地址；多文件使用 Filament 原生上传列表，保存值为公开 URL 数组
  - `AssetUpload::make( 'icon' )->assetType( 'image' )->directory( 'brands' )`
  - `AssetUpload::make( 'attachment' )->uploadTypes( ['document', 'archive'] )`
  - `AssetUpload::make( 'gallery' )->assetType( 'images' )->reorderable()`
  - `assetType()` 支持 image、images、file、files；images/files 自动启用多文件，默认最多 5 个，也可显式调用 `multiple()`；通过 `maxFiles( 数量 )` 覆盖默认上限
  - 设置类型 images/files 使用 JSON 数组存储（如 `["/storage/a.png","/storage/b.png"]`），清空保存 `[]`，`setting()` 返回数组；当前设置移除的附件在事务提交后删除
  - `lar.files.upload_mime_types` 可按扩展名覆盖上传 MIME 白名单；文本格式兼容实际识别到的 text/plain，Markdown 同时接受 HTML 内容，APNG 兼容 image/png；响应类型仍由 mime_types 决定
  - `assetType()` 设置展示类型及默认白名单；显式调用 `uploadTypes()` 后，白名单以指定分组为准
  - 默认目录、大小上限（KB）和文件分组读取 `lar.admin.upload.directory / max_size / file_groups`，默认 uploads、10240 KB
  - 可用 `directory() / maxSize() / disabled() / deletable() / previewable() / openable()` 调整行为；多文件可用 `maxFiles() / minFiles() / reorderable()` 调整数量和排序
  - 编辑表单应绑定对应模型记录；自定义状态路径需通过 `preventFilePathTampering( allowFilePathUsing: ... )` 授权数据库原始地址
- 上传表单状态容器
  - 用 `<x-Filament::asset-upload-form>` 包裹表单和操作按钮，上传或移除期间会阻止表单提交
  - 保存、重置及切换按钮使用 `x-bind:disabled="uploads > 0"`；通过点击直接调用 Livewire 方法的按钮也应绑定此状态
- 后台文件清理服务 [LarExtend/src/Filament/Concerns/UploadedFiles.php]
  - `UploadedFiles::releaseAfterCommit( $oldUrl, $disk, $directory, $connection, $isReferenced, $onFailure )`
  - 在业务事务中，确定更换或删除旧值后调用；传入数据库原始 URL、业务连接以及提交后检查引用的闭包
  - 仅删除指定磁盘目录中的直接子文件；事务回滚保留文件，仍被引用的文件保留；失败记录日志并调用可选失败回调
  - 设置模块通过 SettingsFiles 接入该服务；其他业务需要在自身保存或删除事务中接入

#### 上传图片与文件生命周期 [LarExtend/src/Filament/Concerns/UploadImages.php / UploadedFiles.php]
- `UploadImages::encode( $temporaryFile, $decoder, $targetSize = null, $preserveGif = false )`：受限解码、检查宽高不超过 4096px、清除元数据，默认输出首帧静态 WebP；通用 GIF 保留动画并重新编码为 GIF，最多 100 帧、累计画布像素不超过 4000 万；需要 Imagick 和相应解码器
- JSON 与 Web Manifest 必须通过 JSON 解析校验；空 TXT 允许上传
- 通用资源上传逐扩展名验证 MIME；图片（包括以文件类型上传的图片）统一重新编码，非图片保留原格式；无法安全解码的图片拒绝保存
- 头像与资源组件自动登记本次请求新文件；业务写入后必须调用 `UploadedFiles::retainAfterCommit( $values, $connection )`，在最外层事务提交后确认保留
- `$values` 为最终写入数据库的文件值数组：头像是磁盘相对路径，资源上传是公开 URL；管理员写入服务和设置写入服务已接入
- 请求结束时删除未确认的新文件，清理失败记录日志；事务回滚不删除旧文件，头像替换、移除和管理员删除在提交后清理旧头像
- 普通 PHP 请求正常结束会执行回收；进程被强制终止等情况仍需运维定期检查孤立文件

#### 数据库通知服务 [src/Services/SendNotification.php]
- 在 HTTP 控制器、路由或服务中向指定用户同步发送数据库通知
  ```php
  use LarExtend\Services\SendNotification;
  use Filament\Actions\Action;
  use Illuminate\Support\Facades\DB;

  DB::transaction( function () use ( $user ): void {
      SendNotification::make()
          ->title( '系统通知' )
          ->body( '你的申请已经通过。' )
          ->status( 'success' )
          ->color( 'success' )
          ->icon( 'heroicon-o-check-circle' )
          ->iconColor( 'success' )
          ->seconds( 10 )
          ->actions( [
              Action::make( 'viewDetails' )
                  ->label( '查看详情' )
                  ->icon( 'heroicon-o-eye' )
                  ->color( 'primary' )
                  ->url( '/admin/example', true )
                  ->markAsRead()
                  ->close(),
          ] )
          ->sendToDatabase( $user );
  } );
  ```
- `make()` 创建通知并自动生成 ID；`title( string|null )`、`body( string|null )` 设置标题和正文
- `status()` 支持 success、info、warning、danger，默认 null；也可调用同名快捷方法
- `color()` 和 `iconColor()` 支持 primary、success、info、warning、danger、gray；颜色默认 null，图标颜色默认跟随状态
- `icon()` 设置 Heroicon 图标，未指定时按状态选择
- `seconds( float )` 或 `duration( int )` 分别按秒或毫秒设置关闭时间，默认 6 秒；`persistent()` 禁用自动关闭
- 关闭时间由浏览器中 Filament 前端处理，不依赖定时任务或队列；关闭显示不等于删除数据库记录
- `actions( Action[] )` 默认空数组；按钮名称使用字母、数字、短横线或下划线，支持 label、icon、color、url、markAsRead、close
- `url( $url, true )` 在新窗口打开，默认不启用；markAsRead 和 close 仅在调用后启用
- `sendToDatabase( Model|Collection|array $users, bool $isEventDispatched = false )` 同步写入 notifications 表，返回当前通知对象
- 接收者模型必须使用 Laravel Notifiable Trait；User 和 AdminUser 均可使用，批量接收者需使用同一数据库连接
- 批量写入使用事务；嵌入业务事务时应使用接收者的同一连接，回滚时通知一起回滚
- 默认不广播，后台每 30 秒轮询；第二参数为 true 时在最外层事务提交后广播 DatabaseNotificationsSent，回滚不广播
- 实时刷新需配置 Broadcasting、Laravel Echo、WebSocket 服务；非 sync 广播队列需要运行 Queue Worker，同步数据库写入不需要
- 普通用户的消息展示由其前端实现；此服务只负责通知构建与入库
- 只给当前 HTTP 请求操作人显示临时提示，可用 `Filament\Notifications\Notification::make()->title( '操作成功' )->success()->send()`，该方式通过 Session 显示，不写入数据库

### 插件系统设计

#### 插件基本目录结构
- PluginId // 插件的唯一标识符目录名
  - index.php // [必需] 插件的入口文件，此文件需要返回一个继承 `LarExtend\Providers\PluginProvider` 的匿名类实例
  - config.php // [可选] 插件的配置文件，用于定义插件的配置信息，返回一个数组。
  - plugin.json // [必需] 插件的包信息文件
  - admin.blade.php // [可选] 插件的后台管理 Blade 文件，无需权限定义插件的后台管理界面。
  - README.md // [可选] 插件的说明文件，支持 Markdown 格式。
  - .gitignore // [可选] Git 忽略文件，同时也是插件打包时的忽略规则
- `plugin.json` 为插件必需的包信息文件，包含插件的基本信息，示例如下:
  ```json
  {
    "name": "插件名称", // [必需] 插件名称
    "version": "插件版本", // [必需] 插件版本信息
    "author": "插件作者", // [必需] 插件作者信息
    "description": "插件描述", // [可选] 插件描述信息
    "source": "插件来源地址", // [可选] market|null|下载直链
    "type": "插件类型", // [必需] 插件类型 [expand,software,depend]
    "require": {
        "plugins": {}, // [可选] 依赖的其他插件
        "composer": {}, // [可选] 依赖的 Composer 包
    }, // "laravel/framework": ">=12.0"
    "permissions": [] // [可选] 插件权限调用信息
  }
  ```

#### 插件设计说明
- 插件返回类实例可创建 `boot` 公开方法，用于在插件加载后执行初始化逻辑，如果该方法返回 `false`，插件将被视为加载失败。
- 插件返回类实例可创建 `reset` 公开方法，用于执行插件的重置逻辑。
- 插件返回类实例可创建 `install` 公开方法，用于执行插件的安装逻辑，如果该方法返回 `false`，插件将被视为安装失败。
- 插件返回类实例可创建 `uninstall` 公开方法，用于执行插件的卸载逻辑，如果该方法返回 `false`，插件将被视为卸载失败。
- 插件权限声明
  ```
  APP_SERVICE_REGISTER_HOOK // 应用服务注册钩子：忽略返回值，仅用于服务容器绑定；不应在此阶段注册视图、语言包命名空间或路由。
  APP_SERVICE_BOOT_HOOK // 应用服务启动钩子，忽略返回值
  ADMINISTRATOR_PANEL_HOOK( Panel $panel ) // 管理员面板钩子，忽略返回值
  ADMINISTRATOR_PANEL_REGISTER_HOOK // 管理员面板注册钩子，忽略返回值
  API_ROUTE_REGISTRATION // API 路由注册，忽略返回值
  WEB_ROUTE_REGISTRATION // WEB 路由注册，忽略返回值
  CONSOLE_ROUTE_REGISTRATION // Console 路由注册，忽略返回值
  CHANNELS_ROUTE_REGISTRATION // 频道路由注册，忽略返回值
  CONSOLE_COMMAND_REGISTRATION // 控制台命令注册，忽略返回值
  SCHEDULE_REGISTRATION // 插件定时任务注册，忽略返回值
  // 请求接管插件更新，返回 [ 'link' => string, 'method' => string, 'headers' => array, 'data' => array ]；返回字符串则显示为错误信息并中止更新
  REQUEST_TO_TAKE_OVER_UPDATES( string $id, array $package, string $link, string $method, array $headers, array $data )
  API_MIDDLEWARE_HOOK( \Illuminate\Http\Request $request ) // API 中间件钩子，忽略返回值
  WEB_MIDDLEWARE_HOOK( \Illuminate\Http\Request $request ) // WEB 中间件钩子，忽略返回值
  ```

#### 后台前端工具 [LarExtend/resources/filament/js/global.js]
- 创建链式请求对象
  - `sendRequest( url ).data( data ).success( callback ).error( callback ).post()`
  - `header( headers )` 合并请求头；`timeout( milliseconds )` 默认 15000 毫秒，0 表示不限时
  - `data( data )` 合并对象参数或传入 FormData；GET / HEAD 使用查询参数，其它方法发送 JSON 或表单
  - `get()`、`post()`、`put()`、`delete()`、`patch()`、`options()`、`head()` 或 `send( method = 'GET' )` 发送请求；HEAD 无 JSON 响应时进入 error
  - `loading( callback )` 在请求开始和结束时分别传入 true、false；同源写请求自动携带 CSRF Token
  - `join( options )` 合并 fetch 附加配置；请求地址、方法、请求头、数据、凭据和超时由构建器管理
  - `middleware( callback )` 接收 `{ ref, response, resource }`，在默认业务处理前返回新响应对象，返回 false 则停止处理
  - echoRequest 的 success 将完整响应交给 success 回调；info、error、warning 消息逐条显示对应 Filament 通知
  - HTTP 错误中的有效 echoRequest 响应同样按业务状态处理；超时、网络异常、无效响应显示英文 Request failed，再调用 error
  - return Promise<Object|undefined> 成功响应对象；业务回调异常向调用者抛出，loading 仍会结束
- 显示通知
  - `unitToast( message, status = 'info' )`
  - 支持 error、info、success、warning；消息按纯文本显示，error 对应 Filament danger
  - return void
