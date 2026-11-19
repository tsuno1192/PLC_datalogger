<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ModbusTag;
use App\Models\ModbusLog;
use App\Models\CommunicationLog;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\ModbusDashboard;
use Illuminate\Support\Facades\Auth;

class ModbusDashboardController extends Controller
{
    // 1. ダッシュボード（トレンドグラフ表示画面）
    public function index()
    {
        $tags = ModbusTag::where('is_active', true)->get();
        return view('dashboard', compact('tags'));
    }

    // 2. タグ設定一覧画面
    public function tagsIndex()
    {
        $tags = ModbusTag::all();
        return view('tags.index', compact('tags'));
    }

    // 3. タグ設定の更新（名称・単位・上限値など）
    public function tagsUpdate(Request $request, $id)
    {
        $tag = ModbusTag::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'unit' => 'nullable|string|max:50',
            'high_limit' => 'nullable|numeric',
        ]);

        $tag->update([
            'name' => $request->name,
            'unit' => $request->unit,
            'high_limit' => $request->high_limit,
        ]);

        return redirect()->route('tags.index')->with('success', 'タグ設定を更新しました。');
    }

    // 3-2. タグの新規登録処理
    public function tagsStore(Request $request)
    {
        $request->validate([
            'tag_code' => 'required|string|max:50|unique:modbus_tags,tag_code',
            'name' => 'required|string|max:255',
            'unit' => 'nullable|string|max:50',
            'high_limit' => 'nullable|numeric',
        ]);

        ModbusTag::create([
            'tag_code' => $request->tag_code,
            'name' => $request->name,
            'unit' => $request->unit,
            'high_limit' => $request->high_limit,
            'is_active' => true,
        ]);

        return redirect()->route('tags.index')->with('success', '新しいタグを登録しました。');
    }

    // 4. トレンドグラフ用データAPI（選択式周期に対応）
    public function getTrendData(Request $request)
    {
        $tagId = $request->input('tag_id', 1);
        $seconds = $request->input('seconds', 60);

        $tag = ModbusTag::find($tagId);
        $logs = ModbusLog::where('modbus_tag_id', $tagId)
            ->where('recorded_at', '>=', now()->subSeconds($seconds))
            ->orderBy('recorded_at', 'asc')
            ->get(['value', 'recorded_at']);

        // タグの上限値とログデータをまとめてJSON返却
        return response()->json([
            'tag' => $tag,
            'logs' => $logs
        ]);
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $tagId  = $request->query('tag_id');
        $tagIds = $request->query('tag_ids');
        $from   = $request->query('from');
        $to     = $request->query('to');
    
        // minutes 指定がある場合のフォールバック（例: 直近5分など）
        if (empty($from) && $request->filled('minutes')) {
            $from = now()->subMinutes((int) $request->query('minutes'))->format('Y-m-d H:i:s');
        }
    
        $fileName = 'modbus_logs_' . now()->format('Ymd_His') . '.csv';
    
        return response()->streamDownload(function () use ($tagId, $tagIds, $from, $to) {
            $handle = fopen('php://output', 'w');
    
            // Excel文字化け対策のUTF-8 BOM
            fwrite($handle, "\xEF\xBB\xBF");
    
            fputcsv($handle, [
                '記録日時',
                'タグコード',
                'タグ名称',
                'レジスタアドレス',
                '測定値',
                '単位',
            ]);
    
            $query = ModbusLog::with('tag')->orderBy('recorded_at', 'asc');
    
            if (!empty($tagIds)) {
                $ids = is_array($tagIds)
                    ? $tagIds
                    : array_filter(explode(',', (string) $tagIds));
                if (!empty($ids)) {
                    $query->whereIn('modbus_tag_id', $ids);
                }
            } elseif (!empty($tagId)) {
                $query->where('modbus_tag_id', $tagId);
            }
    
            if (!empty($from)) {
                $query->where('recorded_at', '>=', $from);
            }
            if (!empty($to)) {
                $query->where('recorded_at', '<=', $to);
            }
    
            $query->chunk(1000, function ($logs) use ($handle) {
                foreach ($logs as $log) {
                    fputcsv($handle, [
                        $log->recorded_at,
                        $log->tag->tag_code ?? '',
                        $log->tag->name ?? '',
                        $log->tag->register_address ?? '',
                        $log->value,
                        $log->tag->unit ?? '',
                    ]);
                }
            });
    
            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    // ==========================================
    // 5. カスタムダッシュボード（複数ページ）機能
    // ==========================================

    // ① ダッシュボード一覧 兼 新規作成画面
    public function dashboardsIndex()
    {
        $dashboards = ModbusDashboard::where('user_id', Auth::id())->get();
        return view('modbus.dashboards.index', compact('dashboards'));
    }

    // ② 新規ダッシュボードの保存
    public function dashboardsStore(Request $request)
    {
        $request->validate(['title' => 'required|string|max:255']);

        ModbusDashboard::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
        ]);

        return redirect()->route('modbus.dashboards.index')->with('success', '新しいトレンド画面を作成しました。');
    }

    // ③ 個別ダッシュボード画面（タグ選択 ＆ マルチトレンド表示）
    public function dashboardsShow(ModbusDashboard $dashboard)
    {
        // 認可確認（自分の作成したものか）
        if ($dashboard->user_id !== Auth::id()) {
            abort(403);
        }

        $allTags = ModbusTag::where('is_active', true)->get();
        // このダッシュボードに紐づくタグIDの一覧
        $selectedTagIds = $dashboard->tags()->pluck('modbus_tags.id')->toArray();

        return view('modbus.dashboards.show', compact('dashboard', 'allTags', 'selectedTagIds'));
    }

    // ④ ダッシュボードに紐づくタグの保存（更新）
    public function dashboardsUpdateTags(Request $request, ModbusDashboard $dashboard)
    {
        if ($dashboard->user_id !== Auth::id()) {
            abort(403);
        }

        // チェックされたタグを中間テーブルに同期（sync）
        $dashboard->tags()->sync($request->input('tag_ids', []));

        return redirect()->route('modbus.dashboards.show', $dashboard)->with('success', '表示タグを更新しました。');
    }

    // 6. 通信ログ一覧画面
    public function communicationLogsIndex(Request $request)
    {
        $query = CommunicationLog::query();

        // 日時カラムがある場合の並び順（recorded_at がなければ created_at 等に変更）
        $query->orderByDesc('id');

        // 検索・フィルタ条件がある場合の例（必要に応じて調整）
        if ($request->filled('keyword')) {
            $keyword = $request->input('keyword');
            $query->where(function ($q) use ($keyword) {
                $q->where('message', 'like', "%{$keyword}%")
                    ->orWhere('status', 'like', "%{$keyword}%");
            });
        }

        $logs = $query->paginate(50)->withQueryString();

        // 既存の通信ログビュー名に合わせて指定（例: resources/views/logs/communication.blade.php）
        return view('logs.communication', compact('logs'));
    }
}
