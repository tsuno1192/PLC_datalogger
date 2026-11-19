<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ModbusDashboard extends Model
{
    protected $fillable = ['user_id', 'title'];

    // 紐づくタグ一覧
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(ModbusTag::class, 'modbus_dashboard_tags', 'modbus_dashboard_id', 'modbus_tag_id');
    }
}