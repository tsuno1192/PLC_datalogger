<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlcSetting extends Model
{
    protected $fillable = [
        'plc_name',
        'ip_address',
        'port',
        'unit_id',
        'polling_interval_ms',
        'timeout_ms',
        'is_polling_enabled',
    ];

    protected $casts = [
        'port' => 'integer',
        'unit_id' => 'integer',
        'polling_interval_ms' => 'integer',
        'timeout_ms' => 'integer',
        'is_polling_enabled' => 'boolean',
    ];
}