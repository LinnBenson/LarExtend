<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\IndexController;

// 主页面路由
Route::get( '/', function() { return 'Hello World!'; })->name( 'home' );
// 前端调试工具
if ( config( 'app.debug' ) ) {
    Route::any( '/debug', [IndexController::class, 'debug'] )->name( 'api.index.debug' );
}