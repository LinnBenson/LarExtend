<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * 运行迁移
     */
    public function up(): void {
        Schema::create( 'admin_users', function( Blueprint $table ) {
            $table->bigIncrements( 'id' )->comment( '后台用户ID' );
            $table->string( 'name' )->unique()->comment( '用户名' );
            $table->string( 'email' )->unique()->comment( '邮箱' );
            $table->boolean( 'status' )->default( true )->comment( '状态：1启用，0禁用' );
            $table->unsignedInteger( 'level' )->default( 1 )->comment( '级别' );
            $table->string( 'password' )->comment( '密码哈希' );
            $table->string( 'avatar' )->nullable()->comment( '头像' );
            $table->rememberToken()->comment( '登录记忆令牌' );
            $table->timestamp( 'created_at' )->nullable()->comment( '创建时间' );
            $table->timestamp( 'updated_at' )->nullable()->comment( '更新时间' );
        });
        $this->insert();
    }

    /**
     * 插入数据
     */
    public function insert(): void {
        DB::statement( 'ALTER TABLE `'.DB::getTablePrefix().'admin_users` AUTO_INCREMENT = 3015;' );
        DB::table('admin_users')->insert([
            'name' => 'Administrator',
            'email' => 'admin@admin.com',
            'status' => true,
            'level' => 99999,
            'password' => Hash::make('admin'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * 回滚迁移
     */
    public function down(): void {
        Schema::dropIfExists('admin_users');
    }
};