<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * 数据库填充器
 */
class DatabaseSeeder extends Seeder {
    use WithoutModelEvents;

    /**
     * 填充应用数据库。
     */
    public function run(): void {
        // 数据库填充钩子
        \App\Services\PluginService::HookPlugin( 'DATABASE_SEEDER_HOOK' );
    }

}