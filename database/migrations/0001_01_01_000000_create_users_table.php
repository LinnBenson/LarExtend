<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * 运行迁移
     */
    public function up(): void {
        Schema::create( 'users', function( Blueprint $table ) {
            $table->id( 'uid' )->comment( '用户 UID' );
            $table->unsignedBigInteger( 'agent' )->default( 0 )->index()->comment( '上级代理' );
            $table->string( 'username', 50 )->unique()->comment( '用户名' );
            $table->string( 'email' )->nullable()->unique()->comment( '邮箱' );
            $table->string( 'phone', 20 )->nullable()->unique()->comment( '手机号' );
            $table->string( 'nickname', 50 )->nullable()->comment( '昵称' );
            $table->string( 'password' )->comment( '登录密码' );
            $table->string( 'avatar', 255 )->nullable()->comment( '头像' );
            $table->unsignedInteger( 'level' )->default( 0 )->comment( '级别' );
            $table->boolean( 'status' )->default( true )->comment( '状态：1启用，0禁用' );
            $table->string( 'invite', 8 )->unique()->comment( '邀请码' );
            $table->string( 'hash', 32 )->comment( '密码盐' );
            $table->timestamp( 'created_at' )->nullable()->comment( '创建时间' );
            $table->timestamp( 'updated_at' )->nullable()->comment( '更新时间' );
        });
        // Schema::create( 'password_reset_tokens', function( Blueprint $table ) {
        //     $table->string( 'email' )->primary();
        //     $table->string( 'token' );
        //     $table->timestamp( 'created_at' )->nullable();
        // });
        // Schema::create( 'sessions', function( Blueprint $table ) {
        //     $table->string( 'id' )->primary();
        //     $table->foreignId( 'user_id' )->nullable()->index();
        //     $table->string( 'ip_address', 45 )->nullable();
        //     $table->text( 'user_agent' )->nullable();
        //     $table->longText( 'payload' );
        //     $table->integer( 'last_activity' )->index();
        // });
    }

    /**
     * 插入数据
     */
    public function down(): void {
        Schema::dropIfExists( 'users' );
        // Schema::dropIfExists( 'password_reset_tokens' );
        // Schema::dropIfExists( 'sessions' );
    }

};
