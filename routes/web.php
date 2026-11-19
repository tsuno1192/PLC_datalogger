<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ModbusDashboardController;
use App\Http\Controllers\ModbusTagController;
use App\Http\Controllers\ModbusChartController;
use App\Http\Controllers\PlcSettingController;

Route::get('/', function () {
    return view('welcome');
});

// 認証・認可が必要なグループ
Route::middleware(['auth', 'verified'])->group(function () {
    // ダッシュボード
    Route::get('/dashboard', [ModbusDashboardController::class, 'index'])->name('dashboard');

    // プロフィール関連
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // 既存のModbusダッシュボード・ログ・CSV関連（必要に応じて段階的移行）
    Route::get('/tags', [ModbusDashboardController::class, 'tagsIndex'])->name('tags.index');
    Route::post('/tags', [ModbusDashboardController::class, 'tagsStore'])->name('tags.store');
    Route::post('/tags/{id}', [ModbusDashboardController::class, 'tagsUpdate'])->name('tags.update');
    Route::get('/api/trend-data', [ModbusDashboardController::class, 'getTrendData'])->name('api.trend');
    Route::get('/export-csv', [ModbusDashboardController::class, 'exportCsv'])->name('export.csv');
    Route::get('/logs/communication', [ModbusDashboardController::class, 'communicationLogsIndex'])->name('logs.communication');

    // 📊 カスタムダッシュボード（複数ページ・トレンド監視画面の作成・管理）
    Route::get('/modbus/dashboards', [ModbusDashboardController::class, 'dashboardsIndex'])->name('modbus.dashboards.index');
    Route::post('/modbus/dashboards', [ModbusDashboardController::class, 'dashboardsStore'])->name('modbus.dashboards.store');
    Route::get('/modbus/dashboards/{dashboard}', [ModbusDashboardController::class, 'dashboardsShow'])->name('modbus.dashboards.show');
    Route::post('/modbus/dashboards/{dashboard}/tags', [ModbusDashboardController::class, 'dashboardsUpdateTags'])->name('modbus.dashboards.updateTags');

    // 🏷️ 信号選択・タグ管理（ModbusTagController：リソースルートでCRUDを網羅）
    Route::resource('modbus/tags', ModbusTagController::class)->names([
        'index'   => 'modbus.tags.index',
        'create'  => 'modbus.tags.create',
        'store'   => 'modbus.tags.store',
        'edit'    => 'modbus.tags.edit',
        'update'  => 'modbus.tags.update',
        'destroy' => 'modbus.tags.destroy',
    ]);

    // 📈 トレンドグラフ専用画面 ＆ データ取得API（ModbusChartController担当）
    // ※コントローラー側のメソッド名が index() の場合
    Route::get('/modbus/chart', [ModbusChartController::class, 'index'])->name('modbus.chart');
    Route::get('/modbus/data', [ModbusChartController::class, 'getData'])->name('modbus.data');

    // ⚙️ PLC通信設定（IPアドレス・ポーリング定義）
    Route::get('/modbus/settings', [PlcSettingController::class, 'edit'])->name('modbus.settings.edit');
    Route::put('/modbus/settings', [PlcSettingController::class, 'update'])->name('modbus.settings.update');

    // ⚙️ PLC通信設定（IPアドレス・ポーリング定義・接続テスト）
    Route::get('/modbus/settings', [PlcSettingController::class, 'edit'])->name('modbus.settings.edit');
    Route::put('/modbus/settings', [PlcSettingController::class, 'update'])->name('modbus.settings.update');
    Route::post('/modbus/settings/test', [PlcSettingController::class, 'testConnection'])->name('modbus.settings.test');

    
});

require __DIR__ . '/auth.php';
