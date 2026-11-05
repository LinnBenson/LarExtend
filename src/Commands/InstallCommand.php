<?php

namespace LarExtend\Commands;

use Illuminate\Console\Command;

/**
 *
 */
class InstallCommand extends Command {
    protected $signature = 'lar-extend:install';

    protected $description = 'Install the LarExtend admin panel';

    /**
     * 执行安装
     * @return int 命令退出状态
     */
    public function handle(): int {
        return $this->call( 'migrate', [
            '--path' => [
                __DIR__.'/../Models/Migrations/admin_users_table.php',
                __DIR__.'/../Models/Migrations/settings_table.php',
            ],
            '--realpath' => true,
        ]);
    }
}