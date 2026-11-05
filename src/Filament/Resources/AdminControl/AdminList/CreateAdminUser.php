<?php

namespace LarExtend\Filament\Resources\AdminControl\AdminList;

use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Filament\Support\Enums\Width;

/**
 * 新增管理员页面
 */
class CreateAdminUser extends CreateRecord {
    protected static string $resource = AdminUserResource::class;
    protected Width|string|null $maxContentWidth = Width::SevenExtraLarge;

    protected function handleRecordCreation( array $data ): Model {
        return AdminUserWriter::save( $data );
    }
}
