<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. タグ設定マスタ
        Schema::create('modbus_tags', function (Blueprint $table) {
            $table->id();
            $table->string('tag_code')->unique()->comment('タグ識別コード');
            $table->string('tag_name')->comment('タグ名称'); // 'name' から 'tag_name' に変更
            $table->string('unit')->nullable()->comment('単位（例: MPa, ℃）');
            $table->unsignedInteger('register_address')->comment('Modbusレジスタアドレス'); 
            $table->string('data_type')->default('float')->comment('データ型 (int, floatなど)');
            $table->decimal('high_limit', 10, 2)->nullable()->comment('上限値');
            $table->boolean('is_active')->default(true)->comment('有効/無効');
            $table->timestamps();
        });

        // 2. 時系列ログテーブル（1秒ごと）
        Schema::create('modbus_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('modbus_tag_id')->constrained('modbus_tags')->cascadeOnDelete();
            $table->decimal('value', 12, 4)->comment('取得値');
            $table->timestamp('recorded_at')->index()->comment('取得日時（1秒刻み）');
            $table->timestamps();
        });

        // 3. 通信断・復旧の履歴テーブル
        Schema::create('communication_logs', function (Blueprint $table) {
            $table->id();
            $table->string('device_name')->comment('PLC機器名またはIPアドレス');
            $table->string('status')->comment('状態 (DISCONNECTED / CONNECTED)');
            $table->text('message')->nullable()->comment('詳細メッセージ');
            $table->timestamp('event_at')->comment('発生日時');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communication_logs');
        Schema::dropIfExists('modbus_logs');
        Schema::dropIfExists('modbus_tags');
    }
};
