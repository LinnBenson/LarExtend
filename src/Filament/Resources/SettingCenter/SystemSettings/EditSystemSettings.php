<?php

namespace LarExtend\Filament\Resources\SettingCenter\SystemSettings;

use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Collection;
use LarExtend\Models\Settings;
use Livewire\Attributes\Locked;

/**
 * 分类设置编辑页面
 */
class EditSystemSettings extends Page {
    protected static string $resource = SystemSettingsResource::class;
    protected string $view = 'Filament::SettingCenter.SystemSettings.settings';
    public ?array $data = [];
    public ?string $category = null;
    #[Locked]
    public array $loadedIds = [];
    #[Locked]
    public int $formRevision = 0;

    protected function categories(): array { return SettingsForm::categories(); }

    public function mount(): void {
        abort_unless( static::getResource()::canAccess(), 403 );
        $this->category = array_key_first( $this->categories() );
        $this->loadSettings();
    }

    public function getTitle(): string { return static::getResource()::getNavigationLabel(); }

    /**
     * 查询当前允许访问的分类
     * @return Collection 设置记录
     */
    protected function records(): Collection {
        abort_unless( static::getResource()::canAccess(), 403 );
        abort_unless( array_key_exists( $this->category ?? '', $this->categories() ), 403 );
        return Settings::query()->where( 'category', $this->category )->orderBy( 'index' )->orderBy( 'id' )->get();
    }

    protected function loadSettings(): void {
        $records = $this->records();
        $this->formRevision++;
        $this->loadedIds = $records->modelKeys();
        $this->cacheSchema( 'form', null );
        $this->form->fill( $records->mapWithKeys( fn ( Settings $record ): array => [
            $record->id => match ( $record->type ) {
                'boolean' => filter_var( $record->value, FILTER_VALIDATE_BOOLEAN ),
                'images', 'files' => SettingsFiles::addresses( $record->type, $record->value ),
                default => $record->value,
            },
        ] )->all() );
    }

    public function updatedCategory(): void {
        $this->resetValidation();
        $this->loadSettings();
    }

    public function getCategoryOptions(): array { return $this->categories(); }

    /**
     * 统计可访问分类的配置项数量
     * @return array 分类与数量映射
     */
    public function getCategoryCounts(): array {
        abort_unless( static::getResource()::canAccess(), 403 );
        return Settings::query()->whereIn( 'category', array_keys( $this->categories() ) )
            ->selectRaw( 'category, COUNT(*) as total' )->groupBy( 'category' )->pluck( 'total', 'category' )->all();
    }

    public function form( Schema $schema ): Schema {
        return $schema->statePath( 'data' )->components(
            $this->records()->map( fn ( Settings $record ) => SettingsForm::valueField( $record, (string) $record->id ) )->all()
        );
    }

    /**
     * 重置当前分类
     * 重新读取已保存的值，并清除未保存内容的验证提示。
     * @return void
     */
    public function resetSettings(): void {
        $this->resetValidation();
        $this->loadSettings();
    }

    /**
     * 保存当前分类
     * 只提交本次加载的字段，避免并发新增项被空值覆盖。
     * @return void
     */
    public function save(): void {
        $this->records();
        $values = $this->form->getState();
        SettingsWriter::saveValues( $this->category, array_intersect_key( $values, array_flip( $this->loadedIds ) ) );
        $this->loadSettings();
        Notification::make()->success()->title( __( 'lar::SettingCenter.SystemSettings.saved' ) )->send();
    }
}
