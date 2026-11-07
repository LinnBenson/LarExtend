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
    /**
     * 获取页面面包屑
     * @return array 面包屑列表
     */
    public function getBreadcrumbs(): array {
        return [
            AdminUserResource::getNavigationGroup(),
            AdminUserResource::getUrl( 'index' ) => AdminUserResource::getNavigationLabel(),
            $this->getBreadcrumb(),
        ];
    }

    protected function handleRecordCreation( array $data ): Model {
        return AdminUserWriter::save( $data );
    }
}
