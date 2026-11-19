<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModbusTag extends Model
{
    use HasFactory;

    protected $table = 'modbus_tags';

    /**
     * 一括代入を許可する属性
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tag_code',
        'name',             // データベースのマイグレーションに合わせた名称
        'unit',
        'register_address', // address ではなくマイグレーションに合わせる
        'data_type',
        'high_limit',
        'is_active',
    ];

    /**
     * キャスト設定（データ型の自動変換）
     *
     * @var array<string, string>
     */
    protected $casts = [
        'register_address' => 'integer',
        'high_limit'       => 'decimal:2',
        'is_active'        => 'boolean',
    ];

    /**
     * このタグに紐づくすべてのログを取得（1対多）
     */
    public function modbusLogs()
    {
        return $this->hasMany(ModbusLog::class, 'modbus_tag_id');
    }
}