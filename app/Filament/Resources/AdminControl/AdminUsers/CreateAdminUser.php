<?php

namespace App\Filament\Resources\AdminControl\AdminUsers;

use Filament\Resources\Pages\CreateRecord;

/**
 * CreateAdminUser
 * 新增管理员用户页面。
 * @package App\Filament\Resources\AdminControl\AdminUsers
 */
class CreateAdminUser extends CreateRecord {
    protected static string $resource = AdminUserResource::class;

    protected ?bool $hasDatabaseTransactions = true;
}
