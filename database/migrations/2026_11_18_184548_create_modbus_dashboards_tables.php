<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // カスタムダッシュボード（画面）マスター
        Schema::create('modbus_dashboards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title'); // 画面名（例: "第1プラント監視"）
            $table->timestamps();
        });

        // 画面とタグの多対多の中間テーブル
        Schema::create('modbus_dashboard_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('modbus_dashboard_id')->constrained('modbus_dashboards')->cascadeOnDelete();
            $table->foreignId('modbus_tag_id')->constrained('modbus_tags')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modbus_dashboard_tags');
        Schema::dropIfExists('modbus_dashboards');
    }
};