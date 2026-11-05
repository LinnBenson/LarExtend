<?php

namespace LarExtend\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 配置模型
 */
class Settings extends Model {

    /**
     * 表信息
     */
    protected $table = 'settings';
    protected $primaryKey = 'id';

    /**
     * 公开类别
     * @var list<string>
     */
    public const CATEGORIES = [ 'app', 'system', 'other' ];

    /**
     * 允许的类型
     * @var list<string>
     */
    public const TYPES = [ 'text', 'textarea', 'boolean', 'url', 'image', 'images', 'file', 'files', 'number', 'decimal', 'json' ];

    /**
     * 获取需要类型转换的属性
     * @return array<string, string> 属性类型映射
     */
    protected function casts(): array {
        return [
            'index' => 'integer',
        ];
    }

    /**
     * 获取类别名称。
     * 根据类别值返回对应中文名称。
     * @return string 类别名称
     */
    public function getCategoryLabelAttribute(): string {
        $key = "lar::frame.settings.categories.{$this->category}";
        $label = __( $key );
        return $label === $key ? $this->category : $label;
    }

    /**
     * 获取类型名称。
     * 根据类型值返回对应中文名称。
     * @return string 类型名称
     */
    public function getTypeLabelAttribute(): string {
        $key = "lar::frame.settings.types.{$this->type}";
        $label = __( $key );
        return $label === $key ? $this->type : $label;
    }
}
