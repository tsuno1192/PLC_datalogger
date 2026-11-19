<?php

namespace App\Services;

use App\Models\ModbusLog;
use App\Models\ModbusTag;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ModbusLogService
{
    /**
     * 取得したModbusデータをトランザクション内で安全に保存する
     *
     * @param array $validatedData
     * @return ModbusLog
     * @throws \Exception
     */
    public function storeLog(array $validatedData): ModbusLog
    {
        return DB::transaction(function () use ($validatedData) {
            // tag_code から該当する ModbusTag の ID を取得
            $tag = ModbusTag::where('tag_code', $validatedData['tag_code'])->firstOrFail();

            // ModbusLog モデルを使用して保存
            $log = ModbusLog::create([
                'modbus_tag_id' => $tag->id,
                'value'         => $validatedData['value'],
                'recorded_at'   => $validatedData['recorded_at'] ?? now(),
            ]);

            return $log;
        });
    }
}