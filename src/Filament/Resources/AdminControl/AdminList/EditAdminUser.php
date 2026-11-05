<?php

namespace LarExtend\Filament\Resources\AdminControl\AdminList;

use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Filament\Support\Enums\Width;

/**
 * 编辑管理员页面
 */
class EditAdminUser extends EditRecord {
    protected static string $resource = AdminUserResource::class;
    protected Width|string|null $maxContentWidth = Width::SevenExtraLarge;

    protected function handleRecordUpdate( Model $record, array $data ): Model {
        return AdminUserWriter::save( $data, $record );
    }
}
