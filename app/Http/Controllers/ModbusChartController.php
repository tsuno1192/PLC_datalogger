<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class ModbusChartController extends Controller
{
    /**
     * マルチトレンドグラフ表示画面のビューを返す
     */
    public function index()
    {
        // 有効なタグ一覧を取得
        $tags = DB::table('modbus_tags')
            ->where('is_active', 1)
            ->get();

        return view('modbus.chart', compact('tags'));
    }

    /**
     * 複数タグのログデータをJSON形式で返す（非同期・リアルタイム更新用）
     */
    public function getData(Request $request)
    {
        $tagIdsInput = $request->input('tag_ids', '');
        $tagIds = is_array($tagIdsInput) ? $tagIdsInput : explode(',', $tagIdsInput);
        $tagIds = array_filter(array_map('intval', $tagIds));

        if (empty($tagIds)) {
            return response()->json([]);
        }

        $minutes = (int)$request->input('minutes', 5);
        $limit = $request->input('limit', null);

        // 1. 対象のタグマスターを一括取得
        $tags = DB::table('modbus_tags')
            ->whereIn('id', $tagIds)
            ->get()
            ->keyBy('id');

        if ($tags->isEmpty()) {
            return response()->json([]);
        }

        // 2. ログデータの期間判定
        $hasTimeRange = $request->has('start_time') && !empty($request->input('start_time'));
        $startTime = $hasTimeRange ? $request->input('start_time') : now()->subMinutes($minutes);
        $endTime = $hasTimeRange ? $request->input('end_time') : null;

        // 3. ログを一括取得（必要なタグIDすべてを対象）
        $query = DB::table('modbus_logs')
            ->whereIn('modbus_tag_id', $tagIds);

        if ($hasTimeRange && $endTime) {
            $query->whereBetween('recorded_at', [$startTime, $endTime]);
        } else {
            $query->where('recorded_at', '>=', $startTime);
        }

        $query->orderBy('recorded_at', 'asc');

        $allLogs = $query->get()->groupBy('modbus_tag_id');

        // 4. フロントエンドが扱いやすい構造に整形 ＆ 長期データの間引き処理
        $datasets = [];
        foreach ($tagIds as $tagId) {
            if (!isset($tags[$tagId])) continue;
            
            $tag = $tags[$tagId];
            $logs = $allLogs->get($tagId, collect());

            // データ数が1000件を超える場合（長期データなど）、自動で間引いて軽量化
            if ($logs->count() > 1000) {
                $step = ceil($logs->count() / 1000);
                $logs = $logs->values()->filter(function ($item, $index) use ($step) {
                    return $index % $step === 0;
                });
            }

            if ($limit && $logs->count() > $limit) {
                $logs = $logs->take($limit);
            }

            $datasets[] = [
                'tag_id' => $tag->id,
                'tag_code' => $tag->tag_code ?? $tag->id,
                'name' => $tag->name ?? $tag->tag_name,
                'logs' => $logs->values()
            ];
        }

        return response()->json($datasets);
    }
}
