<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModbusLog extends Model
{
    use HasFactory;

    protected $table = 'modbus_logs';

    protected $fillable = [
        'modbus_tag_id',
        'value',
        'recorded_at',
    ];

    /**
     * このログに紐づくタグ情報を取得（多対1）
     */
    public function modbusTag()
    {
        // 外部キーが 'modbus_tag_id' であることを明示
        return $this->belongsTo(ModbusTag::class, 'modbus_tag_id');
    }
}