<?php

namespace LarExtend\Filament\Resources\AdminControl\NotificationList;

use Filament\Resources\Pages\ListRecords;

/**
 * 通知信息列表
 */
class ListNotifications extends ListRecords {
    protected static string $resource = NotificationResource::class;
    /**
     * 获取页面面包屑
     * @return array 面包屑列表
     */
    public function getBreadcrumbs(): array {
        return [
            NotificationResource::getNavigationGroup(),
            NotificationResource::getNavigationLabel(),
        ];
    }
}
