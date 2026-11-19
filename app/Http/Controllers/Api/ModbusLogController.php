<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ModbusLogService;

class ModbusLogController extends Controller
{
    protected ModbusLogService $logService;

    public function __construct(ModbusLogService $logService)
    {
        $this->logService = $logService;
    }

    /**
     * Pythonや外部から送信されたModbusログを受け取って保存する
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'tag_code'    => 'required|string|exists:modbus_tags,tag_code',
            'value'       => 'required|numeric',
            'recorded_at' => 'nullable|date',
        ]);

        try {
            $log = $this->logService->storeLog($validatedData);

            return response()->json([
                'status' => 'success',
                'data'   => $log
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}