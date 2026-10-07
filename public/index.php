<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define( 'LARAVEL_START', microtime( true ) );

// 判断应用是否处于维护模式。
if ( file_exists( $maintenance = __DIR__.'/../storage/framework/maintenance.php' ) ) {
    require $maintenance;
}

// 注册 Composer 自动加载器。
require __DIR__.'/../vendor/autoload.php';

// 启动 Laravel 并处理请求。
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest( Request::capture() );