### 项目介绍
你现在是在 VScode Remote SSH 中开发我的项目，你可以通过 VScode 直接访问我的 ubuntu 虚拟机 SHH。如果如果访问这个项目的网站，可以访问 http://127.0.0.1

### 开发环境说明
PHP 8.2.28 + Nginx 1.28.0 + MySQL 5.7.44 + Redis 7.4.2 + Node.js 24.4.1
框架版本: Laravel 12.0

### 开发规范
- 前台 Blade 模板文件在 `resources/views` 下，前端静态文件需要放在 `public/assets` 的对应目录下；管理员后台 Blade 模板遵循下面的后台约定。
- 管理员后台 Blade 模板必须放在 `app/Filament/Views` 下，按现有模块和页面目录组织，例如 `app/Filament/Views/DeveloperCenter/Readme/readme.blade.php`，不要放到 `resources/views` 下。
- 管理员后台视图使用已注册的 `Filament::` 命名空间引用，例如 `Filament::DeveloperCenter.Readme.readme`。
- 管理员后台页面专用 CSS 必须写在对应 Blade 模板的 `<style>` 标签内，不要为单个页面在 `public/assets/filament/css` 下新增独立 CSS 文件；已有公共样式继续复用。
- 后台页面结构和写法可以参考 `app/Filament/Resources/AdminControl/AdminUsers` 这个页面。
- 如果是开发 resources 目录下的 Nodejs/Vite 项目，你同样需要阅读和遵守对应 Nodejs/Vite 项目根目录中可能存在的 AGENTS.md 文件。
- 除非我明确你可以修改我的代码，否则将不要直接修改代码，将修改建议以评论或说明的形式提供。
- 如果你修改了我的文件，请在回复时说明你修改了哪一些文件。

### 运行说明
- 每轮对话前都要先说 `AGENTS.md 读取加载完成！`，让我知道你已经阅读了本文件并还没有遗忘。
- 开始修改前，先查看相关目录、已有命名方式、路由、组件和服务结构，优先保持项目现有风格。
- 不要随意新增 Composer 或 npm 依赖。确实需要新增依赖时，先说明原因、替代方案和影响。
- 你可以生成文件或日志以用于测试，但你测试完成后记得把生成的测试文件和日志删除，避免占用空间。
- 修改代码时只处理和当前任务直接相关的文件，不要进行无关重构、格式化或目录调整。
- 涉及数据库结构变更时，优先使用 migration，不要直接修改线上数据库。不要删除已有 migration，除非我明确要求。
- 修改字段、索引、默认值时，需要考虑已有数据兼容。
- 不要把账号、密码、Token、API Key 写死在代码中，应使用 .env 或配置文件。
- 外部请求、文件操作、数据库操作需要考虑失败情况，并给出合理错误处理。
- 日志内容应有助于定位问题，但不能记录密码、Token、身份证、手机号等敏感信息。
- 用户可见文案应优先放入项目现有的语言包或翻译机制中，不要散落硬编码，除非项目本身没有多语言结构。
- 新增接口、控制器、后台功能时，需要检查权限、登录状态、CSRF、表单校验和越权访问问题。
- 完成任务后，需要简要说明修改内容、影响范围、已执行的验证命令，以及未验证的原因。
- 接口控制器不应该包含过多业务逻辑，复杂业务逻辑应封装为服务类或函数。
- 操作数据库时需要有完整的事务处理，避免出现数据不一致的情况。也需要包含回滚处理，避免出现部分成功、部分失败的情况。

### 代码基本要求
- 修改文件时，不要直接修改 composer 的 vendor 目录下的文件，如果实在要修改才能实现，需要经由我同意。Node.js 项目的 node_modules 也是如此。
- 写代码时，请注意使用正确的缩进和排版，代码需要有可读性。并且不要使用无意义的空行。
- 当重复逻辑具有明确业务含义、会被多处长期复用，或能明显提升可读性时，应封装为函数、类或服务项，然后将这些函数或类或服务项放在合适的目录下，并补充到根目录的 `README.md` 指定位置中 (注意书写格式要跟文件内的内容格式一样)，以便下次使用。
- 项目函数如果支持限制参数类型和返回值类型，请尽量使用类型约束，避免使用 mixed 或者不写类型。
- PHP / JavaScript 字符串连接简单变量可优先使用:
  ```
  // php
  $str = "string1{$value}string2";
  // js
  const str = `string1${value}str${t( 'text' )}ing2`;
  const code = `
    <h1>hello world</h1>
  `;
  ```
- 请注意 if 嵌套层级，不要使用超过 4 层的嵌套，优先使用 early return、拆分函数、合并条件来降低嵌套。
- CSS 的样式编辑和命名需要遵循以下顺序:
  ```
  /* css 选择链的关系完整 */
  页面 盒子 元素 {
    /* 按布局、盒模型、视觉、文字、交互、定位的顺序组织属性 */
  }
  ```
- JS / PHP 编辑在允许的情况下大括号不要换行，并且 if / else / for 和小括号的空格写法要统一，使用以下方式：
  ```
  function exampleFunction( $param1, $param2 ) {
    $a = 1;
    $b = [];
    // 小括号两边要有空格
    if ( condition ) {
        // 运算符两边要有空格
        $val = $a + 2;
        // do something
        // 数组的中括号两边不要有空格，元素之间要有空格
        $b[] = [$val, $param1];
    }else {
        // do something else
    }
    for ( let i = 0; i < length; i++ ) {
        // do something
    }
    // 简单三元表达式可以保持在一行；复杂条件应改用 if 或拆分变量。
    $result = condition ? true : false;
    return $result;
  }
  ```
- 在写 React 组件时，在允许的情况下，要优先使用函数组件而非类组件。
- 代码要符合我的写作风格，如下示例( namespace / use / import  上下要有空行):
  ```
    if ( !function_exists( 'is_json' ) ) {
        /**
        * 判断字符串是否为 JSON
        * 判断传入字符串是否为 JSON 对象或 JSON 数组字符串。
        * @param string $value 待判断的字符串
        * @return bool 是否为合法 JSON 字符串
        */
        function is_json( string $value ): bool {
            $value = trim( $value );
            if ( $value === '' ) { return false; }
            if ( ! in_array( $value[0], ['{', '['], true ) ) { return false; }
            json_decode( $value );
            return json_last_error() === JSON_ERROR_NONE;
        }
    }
    <?php

    namespace App\Http\Controllers;

    use Illuminate\Http\Request;
    use Illuminate\Http\Response;

    /**
     * IndexController
     * 网站首页控制器
     * @package App\Http\Controllers
     */
    class IndexController extends Controller {
        /**
        * 首页
        * 显示网站首页。
        * @returns \Illuminate\View\View
        */
        public function index(): \Illuminate\View\View {
            return view('welcome');
        }
        /**
        * 调试
        * 用于调试的测试方法。
        * @returns any
        */
        public function debug() {
            dd( 'ok' );
            return '';
        }
    }
  ```

### 注释要求
- 公开函数、服务类、复杂业务逻辑、跨模块复用代码需要有中文注释。简单函数如果命名已经清楚，可以不写注释。
- HTML/CSS 也需要有基本的大模块注释，说明以下代码的作用范围或者是名称
- 函数注释需要使用以下格式：
  ```
  /**
   * 函数名称
   * 函数功能说明
   * @param {string} param1 参数1说明
   * @param {number} param2 参数2说明
   * @returns {boolean} 返回值说明
   */
  function exampleFunction( param1, param2 ) {
      // 函数内部逻辑说明
      return true;
  }
  ```
- JS 和 PHP 代码单行注释只能使用 //，多行注释只能使用 /* */
- 请注意但不要滥用注释，注释应当简明扼要，避免过多冗余信息。不能出现一行代码一个注释，或者满屏全是注释的情况，注释主要作用是提升代码的可读性和可维护性。

### Laravel 约定
- 请求验证规则优先使用以 `|` 分隔的字符串，每个字段独占一行，例如 `'username' => 'string|max:50'`、`'remember' => 'boolean|sometimes'`。需要 Rule 对象、闭包或正则表达式包含 `|` 时，使用数组规则。
- Laravel 的路由、控制器、服务类、模型、迁移文件等都需要遵循 Laravel 的约定和最佳实践，尽量使用 Laravel 提供的功能和特性来实现功能，避免重复造轮子。并使用驼峰命名法和 Pascal 命名法，避免使用下划线命名法和其他不规范的命名方式。
- Laravel 接口都需要使用内置的 `echoJson()` 方法返回 JSON 数据（ status: 0 => success, 1 => info, 2 => error, 3 => warning ），以便统一处理 JSON 输出格式和错误码。
