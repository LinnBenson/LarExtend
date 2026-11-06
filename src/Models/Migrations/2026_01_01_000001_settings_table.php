<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * 运行迁移
     */
    public function up(): void {
        Schema::create( 'settings', function( Blueprint $table ) {
            $table->bigIncrements( 'id' )->comment( '配置 ID' );
            $table->string( 'category', 32)->index()->comment( '类别' );
            $table->string( 'type', 32 )->comment( '类型' );
            $table->string( 'name', 191 )->nullable()->comment( '名称' );
            $table->string( 'key', 191 )->unique()->comment( '键名' );
            $table->longText( 'value' )->nullable()->comment( '值' );
            $table->text( 'description' )->nullable()->comment( '描述' );
            $table->unsignedInteger( 'index' )->default( 0 )->index()->comment( '排序' );
            $table->timestamp( 'created_at' )->nullable()->comment( '创建时间' );
            $table->timestamp( 'updated_at' )->nullable()->comment( '更新时间' );
        });

        $this->insert();
    }

    /**
     * 插入数据
     */
    public function insert(): void {
        $insertData = [
            'admin' => [
                [
                    'type' => 'text',
                    'name' => 'lar::frame.settings.path',
                    'key' => 'path',
                    'value' => 'admin',
                    'description' => 'lar::frame.settings.pathDes'
                ],[
                    'type' => 'text',
                    'name' => 'lar::frame.settings.login',
                    'key' => 'login',
                    'value' => 'Filament::Pages.Login.login_v0',
                    'description' => 'lar::frame.settings.loginDes'
                ],[
                    'type' => 'boolean',
                    'name' => 'lar::frame.settings.send',
                    'key' => 'send',
                    'value' => true,
                    'description' => 'lar::frame.settings.sendDes'
                ],[
                    'type' => 'textarea',
                    'name' => 'lar::frame.settings.ascription',
                    'key' => 'ascription',
                    'value' => '© '.date( 'Y' ).' '.config( 'app.name' ).' Administrator All Rights Reserved.',
                    'description' => 'lar::frame.settings.ascriptionDes'
                ]
            ],
            'app' => [
                [
                    'type' => 'text',
                    'name' => 'lar::frame.settings.name',
                    'key' => 'name',
                    'value' => config( 'app.name' ),
                    'description' => 'lar::frame.settings.nameDes'
                ],[
                    'type' => 'text',
                    'name' => 'lar::frame.settings.host',
                    'key' => 'host',
                    'value' => explode( '//', config( 'app.url' ) )[1] ?? '',
                    'description' => 'lar::frame.settings.hostDes'
                ],[
                    'type' => 'image',
                    'name' => 'lar::frame.settings.icon',
                    'key' => 'icon',
                    'value' => '/favicon.ico',
                    'description' => 'lar::frame.settings.iconDes'
                ],[
                    'type' => 'boolean',
                    'name' => 'lar::frame.settings.debug',
                    'key' => 'debug',
                    'value' => config( 'app.debug' ),
                    'description' => 'lar::frame.settings.debugDes'
                ],[
                    'type' => 'textarea',
                    'name' => 'lar::frame.settings.copyright',
                    'key' => 'copyright',
                    'value' => '© '.date( 'Y' ).' '.config( 'app.name' ).' All Rights Reserved.',
                    'description' => 'lar::frame.settings.copyrightDes'
                ],[
                    'type' => 'json',
                    'name' => 'lar::frame.settings.theme',
                    'key' => 'theme',
                    'value' => json_encode([
                        'Default' => [
                            'icon' => '/favicon.ico',
                            'logo' => '/assets/icons/logo_dark.png',
                            'img' => '/assets/images/Default.jpg',
                            'style' => '',
                            '--r0' => '237, 236, 231',
                            '--r1' => '70, 70, 70',
                            '--r2' => '65, 115, 179',
                            '--r2c' => 'var( --r0 )',
                            '--r3' => '53, 75, 111',
                            '--r3c' => 'var( --r0 )',
                            '--r4' => '52, 168, 83',
                            '--r4c' => 'var( --r0 )',
                            '--r5' => '207, 99, 89',
                            '--r5c' => 'var( --r0 )',
                            '--r6' => '249, 247, 244',
                            '--font' => '15px',
                            '--radius' => '4px',
                        ],
                    ], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR ),
                    'description' => 'lar::frame.settings.themeDes'
                ]
            ]
        ]; $insert = []; $index = 10;
        foreach ( $insertData as $category => $items ) {
            foreach ( $items as $item ) {
                $insert[] = array_merge( $item, [
                    'category' => $category,
                    'index' => $index,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                $index = $index + 10;
            }
        }
        DB::table( 'settings' )->insert( $insert );
    }

    /**
     * 回滚迁移
     */
    public function down(): void {
        Schema::dropIfExists( 'settings' );
    }
};
