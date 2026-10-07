<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * 运行迁移
     */
    public function up(): void {
        Schema::create( 'cache', function( Blueprint $table ) {
            $table->string( 'key' )->primary()->comment( '缓存键' );
            $table->mediumText( 'value' )->comment( '缓存数据' );
            $table->integer( 'expiration' )->index()->comment( '缓存过期时间' );
        });
        Schema::create( 'cache_locks', function( Blueprint $table ) {
            $table->string( 'key' )->primary()->comment( '缓存锁键' );
            $table->string( 'owner' )->comment( '锁持有者' );
            $table->integer( 'expiration' )->index()->comment( '锁过期时间' );
        });
    }

    /**
     * 回滚迁移
     */
    public function down(): void {
        Schema::dropIfExists( 'cache' );
        Schema::dropIfExists( 'cache_locks' );
    }

};