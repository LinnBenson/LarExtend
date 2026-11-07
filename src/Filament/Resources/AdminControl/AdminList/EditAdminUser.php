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
    /**
     * 获取页面面包屑
     * @return array 面包屑列表
     */
    public function getBreadcrumbs(): array {
        return [
            AdminUserResource::getNavigationGroup(),
            AdminUserResource::getUrl( 'index' ) => AdminUserResource::getNavigationLabel(),
            $this->getRecord()->name,
            $this->getBreadcrumb(),
        ];
    }

    protected function handleRecordUpdate( Model $record, array $data ): Model {
        return AdminUserWriter::save( $data, $record );
    }
}
