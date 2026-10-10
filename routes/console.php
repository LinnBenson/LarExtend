<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command( 'inspire', function() { $this->comment( Inspiring::quote() ); })->purpose( '显示一句激励语' );

// Console 路由注册
\App\Services\PluginService::HookPlugin( 'CONSOLE_ROUTE_REGISTRATION' );