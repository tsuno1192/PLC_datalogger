<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('plc_settings', function (Blueprint $table) {
            $table->id();
        $table->string('plc_name')->default('Main PLC');       // 識別名（例: 1号機PLC）
        $table->string('ip_address')->default('127.0.0.1');    // PLC IPアドレス
        $table->unsignedInteger('port')->default(502);         // Modbus TCPポート（標準502）
        $table->unsignedTinyInteger('unit_id')->default(1);    // スレーブID / ユニットID
        $table->unsignedInteger('polling_interval_ms')->default(1000); // ポーリング周期（ミリ秒）
        $table->unsignedInteger('timeout_ms')->default(3000);  // 通信タイムアウト（ミリ秒）
        $table->boolean('is_polling_enabled')->default(true);  // 収集実行フラグ（開始/停止）
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plc_settings');
    }
};
