<?php

namespace App\Http\Controllers;

use App\Models\PlcSetting;
use Illuminate\Http\Request;

class PlcSettingController extends Controller
{
    public function edit()
    {
        $setting = PlcSetting::firstOrCreate(
            ['id' => 1],
            [
                'plc_name' => 'Main PLC',
                'ip_address' => '127.0.0.1',
                'port' => 502,
                'unit_id' => 1,
                'polling_interval_ms' => 1000,
                'timeout_ms' => 3000,
                'is_polling_enabled' => true,
            ]
        );

        return view('modbus.settings.edit', compact('setting'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'plc_name'            => ['required', 'string', 'max:100'],
            'ip_address'          => ['required', 'ipv4'],
            'port'                => ['required', 'integer', 'between:1,65535'],
            'unit_id'             => ['required', 'integer', 'between:0,255'],
            'polling_interval_ms' => ['required', 'integer', 'min:100', 'max:3600000'],
            'timeout_ms'          => ['required', 'integer', 'min:100', 'max:60000'],
            'is_polling_enabled'  => ['nullable', 'boolean'],
        ]);

        $validated['is_polling_enabled'] = $request->has('is_polling_enabled');

        $setting = PlcSetting::findOrFail(1);
        $setting->update($validated);

        return redirect()->route('modbus.settings.edit')
                         ->with('success', 'PLC通信設定を保存しました。');
    }

    /**
     * ⚡ PLCへのTCPソケット接続テスト（疎通確認API）
     */
    public function testConnection(Request $request)
    {
        $validated = $request->validate([
            'ip_address' => ['required', 'ipv4'],
            'port'       => ['required', 'integer', 'between:1,65535'],
            'timeout_ms' => ['nullable', 'integer', 'min:100', 'max:60000'],
        ]);

        $ip = $validated['ip_address'];
        $port = (int) $validated['port'];
        // ミリ秒を秒（float）へ変換（未指定時は3秒）
        $timeoutSec = ($validated['timeout_ms'] ?? 3000) / 1000.0;

        $startTime = microtime(true);
        $errno = 0;
        $errstr = '';

        // ソケット接続試行（Warning抑制のため @ を付与し、$errno/$errstr で詳細取得）
        $socket = @fsockopen($ip, $port, $errno, $errstr, $timeoutSec);
        $elapsedMs = round((microtime(true) - $startTime) * 1000, 1);

        if ($socket) {
            fclose($socket);
            return response()->json([
                'success' => true,
                'message' => "接続成功：{$ip}:{$port} に疎通しました（応答時間: {$elapsedMs} ms）",
            ]);
        }

        // タイムアウトや接続拒否の判別メッセージ
        $reason = match ($errno) {
            110, 10060 => 'タイムアウト（PLCの電源・LANケーブル・IPアドレス・FWを確認してください）',
            111, 10061 => '接続拒否（対象ホストは起動していますが、ポート ' . $port . ' が閉じています）',
            113, 10065 => 'ホストへ到達不能（ネットワーク経路・サブネットを確認してください）',
            default    => "{$errstr} (コード: {$errno})",
        };

        return response()->json([
            'success' => false,
            'message' => "接続失敗：{$reason}",
        ]);
    }
}