# 开始使用
1. 克隆项目到本地
   - `git clone https://github.com/LinnBenson/ToLaravel.git`
2. 安装依赖
   - `composer install`
3. 复制 .env.example 为 .env 并修改配置
   - `cp .env.example .env`
4. 生成应用密钥
   - `php artisan key:generate`
5. 运行数据库迁移和数据填充
   - `php artisan migrate --seed`
6. 创建公开存储目录链接
   - `php artisan storage:link`
   - 用于访问管理员头像、用户头像及其它存储在 `public` 磁盘中的文件

# 伪静态部署
```
location ^~ /internal- {
    try_files $uri $uri/ /index.php?$query_string;
}
location ^~ /livewire- {
    try_files $uri $uri/ /index.php?$query_string;
}
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

## 公共函数 [app/Helpers/Common.php]
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

## 系统函数 [app/Helpers/System.php]
- 输出标准 JSON 响应
  - `echoJson( [int]状态, [array|string|null]响应消息, [mixed]响应数据 = null, [int|null]HTTP 状态码 = null, [array]响应头 = [] )`
  - 布尔状态或整数状态会转换为 `success`、`info`、`error`、`warning` 或 `unknown`
  - 响应消息可传入 `[翻译键, 替换参数数组]`，函数会通过 Laravel 多语言机制转换为对应文案
  - 响应消息为数组且不传入响应数据时，会将其作为响应数据，原有的响应消息设为 null
  - return [JsonResponse]JSON 响应对象
- 获取访问设备类型
  - `getDeviceType()`
  - return [string]Linux|Windows|Mac|iPhone|iPad|Android|Other
- 获取插件实例
  - `plugin( [string]插件 ID )`
  - return [PluginServiceProvider|null]返回插件实例或者 null

## 用户模型 [app/Models/User.php]
- 获取用户可公开信息
  - `$user->getUserinfo()`
  - return [array]包含 uid、username、nickname、avatar、level 的用户信息

## 管理员用户模型 [app/Models/AdminUser.php]
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
- 判断管理员用户是否达到后台管理等级
  - `$adminUser->canManage()`
  - return [bool]是否达到管理等级

## 用户服务类 [app/Services/UserService.php]
- 生成随机邀请码
  - `UserService::generateInvite()`
  - return [string]8 位大写十六进制邀请码
- 生成随机密码盐
  - `UserService::generateHash()`
  - return [string]32 位随机字符串

## 后台工具类 [app/Filament/Concerns/AdminTool.php]
- 获取管理员等级键名
  - `AdminTool::levelName( [int]管理员等级 )`
  - return [string]等级名称，低于所有门槛或无有效配置时返回空字符串

# 插件系统设计

## 插件基本目录结构
- PluginId // 插件的唯一标识符目录名
  - index.php // [必需] 插件的入口文件
  - config.php // [可选] 插件的配置文件
  - package.json // [必需] 插件的包信息文件

## 插件设计说明
- `index.php` 为插件必需的主入口文件，此文件需要返回一个继承 `app\Providers\PluginServiceProvider` 的匿名类实例
- `config.php` 为插件可选的配置文件，用于定义插件的配置信息，返回一个数组。
- `package.json` 为插件必需的包信息文件，包含插件的基本信息，示例如下:
  ```json
  {
    "name": "插件名称", // [必需] 插件名称
    "version": "插件版本", // [必需] 插件版本信息
    "author": "插件作者", // [必需] 插件作者信息
    "description": "插件描述", // [必需] 插件描述信息
    "source": "插件来源地址", // [可选] market|null|下载直链
    "rely_plugins": {}, // [可选] 依赖的其他插件
    "rely_composers": { "laravel/framework": ">=12.0" } // [可选] 依赖的 Composer 包
  }
  ```
- 插件返回类实例可创建 `boot` 公开方法，用于在插件加载后执行初始化逻辑，如果该方法返回 `false`，插件将被视为加载失败。

## 插件服务提供者 [app/Providers/PluginServiceProvider.php]
- 插件 ID
  - `$this->id` [string]
- 插件基本路径
  - `$this->base` [string]
- 插件包信息
  - `$this->package` [array]
- 获取插件配置
  - `$this->config( [string]配置项键名 = '' , [mixed]默认值 = null )`
  - return [mixed]配置值，如果未传入键名则返回整个配置数组

## 插件服务 [app/Services/PluginService.php]
- 获取插件实例
  - `PluginService::getPluginInstance( [string]插件基础路径 )`
  - return [PluginServiceProvider|null]返回插件实例，如果插件已经加载过则从缓存中返回，否则进行预加载
- 预加载插件
  - `PluginService::preload( [string]插件基础路径 )`
  - return [PluginServiceProvider|null]返回插件实例，如果加载失败返回 null
- 插件基础错误检查
  - `PluginService::checkBaseErrors( [string]插件基础路径 )`
  - return [array]返回检测到的错误列表
