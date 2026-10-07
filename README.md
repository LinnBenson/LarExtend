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

## 用户模型 [app/Models/User.php]
- 获取用户可公开信息
  - `$user->getUserinfo()`
  - return [array]包含 uid、username、nickname、avatar、level 的用户信息
- 获取用户表所有字段对应的备注
  - `User::fields()`
  - return [array]字段备注列表
- 根据字段名获取用户表字段对应的备注
  - `User::field( [string]字段名 )`
  - return [string]字段备注

## 管理员用户模型 [app/Models/AdminUser.php]
- 获取管理员用户表所有字段对应的备注
  - `AdminUser::fields()`
  - return [array]字段备注列表
- 根据字段名获取管理员用户表字段对应的备注
  - `AdminUser::field( [string]字段名 )`
  - return [string]字段备注
- 获取管理员用户头像地址
  - `$adminUser->getFilamentAvatarUrl()`
  - return [string|null]头像地址
- 判断管理员用户是否可以访问 Filament 面板
  - `$adminUser->canAccessPanel( [Panel]Filament 面板 )`
  - return [bool]是否允许访问

## 用户服务类 [app/Services/UserService.php]
- 生成随机邀请码
  - `UserService::generateInvite()`
  - return [string]8 位大写十六进制邀请码
- 生成随机密码盐
  - `UserService::generateHash()`
  - return [string]32 位随机字符串