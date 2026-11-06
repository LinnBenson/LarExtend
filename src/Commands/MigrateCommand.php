<?php

namespace LarExtend\Commands;

use Illuminate\Console\Command;

/**
 *
 */
class MigrateCommand extends Command {
    protected $signature = 'lar-extend:migrate';

    protected $description = 'Migrate LarExtend data files';

    /**
     * 执行安装
     * @return int 命令退出状态
     */
    public function handle(): int {
        return $this->call( 'migrate', [
            '--path' => [ __DIR__.'/../Models/Migrations' ],
            '--realpath' => true,
        ]);
    }
}