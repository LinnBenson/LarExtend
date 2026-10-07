<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * 运行迁移
     */
    public function up(): void {
        Schema::create( 'jobs', function( Blueprint $table ) {
            $table->id()->comment( '任务 ID' );
            $table->string( 'queue' )->index()->comment( '队列名称' );
            $table->longText( 'payload' )->comment( '任务数据' );
            $table->unsignedTinyInteger( 'attempts' )->comment( '尝试次数' );
            $table->unsignedInteger( 'reserved_at' )->nullable()->comment( '领取时间' );
            $table->unsignedInteger( 'available_at' )->comment( '可执行时间' );
            $table->unsignedInteger( 'created_at' )->comment( '创建时间' );
        });
        Schema::create( 'job_batches', function( Blueprint $table ) {
            $table->string( 'id' )->primary()->comment( '批次 ID' );
            $table->string( 'name' )->comment( '批次名称' );
            $table->integer( 'total_jobs' )->comment( '任务总数' );
            $table->integer( 'pending_jobs' )->comment( '未完成任务数' );
            $table->integer( 'failed_jobs' )->comment( '失败任务数' );
            $table->longText( 'failed_job_ids' )->comment( '失败任务 ID 列表' );
            $table->mediumText( 'options' )->nullable()->comment( '批次选项' );
            $table->integer( 'cancelled_at' )->nullable()->comment( '取消时间' );
            $table->integer( 'created_at' )->comment( '创建时间' );
            $table->integer( 'finished_at' )->nullable()->comment( '完成时间' );
        });
        Schema::create( 'failed_jobs', function( Blueprint $table ) {
            $table->id()->comment( '记录 ID' );
            $table->string( 'uuid' )->unique()->comment( '任务 UUID' );
            $table->text( 'connection' )->comment( '队列连接' );
            $table->text( 'queue' )->comment( '队列名称' );
            $table->longText( 'payload' )->comment( '任务数据' );
            $table->longText( 'exception' )->comment( '异常详情' );
            $table->timestamp( 'failed_at' )->useCurrent()->comment( '失败时间' );
        });
    }

    /**
     * 回滚迁移
     */
    public function down(): void {
        Schema::dropIfExists( 'jobs' );
        Schema::dropIfExists( 'job_batches' );
        Schema::dropIfExists( 'failed_jobs' );
    }

};