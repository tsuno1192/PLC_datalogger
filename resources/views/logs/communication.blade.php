<x-app-layout>
    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <!-- ヘッダー -->
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                📡 通信ログ一覧
            </h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('modbus.settings.edit') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-500 font-medium">
                    ⚙ PLC通信設定
                </a>
                <a href="{{ route('dashboard') }}" class="px-4 py-2 bg-gray-600 text-white rounded-md text-sm hover:bg-gray-500 font-medium">
                    ← ダッシュボードへ戻る
                </a>
            </div>
        </div>

        <!-- 検索・フィルタフォーム -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <form method="GET" action="{{ route('logs.communication') }}" class="flex flex-wrap items-center gap-3">
                <input type="text" name="keyword" value="{{ request('keyword') }}"
                    placeholder="ステータスやメッセージで検索..."
                    class="w-full sm:w-80 rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-medium hover:bg-indigo-700">
                    検索
                </button>
                @if(request()->filled('keyword'))
                    <a href="{{ route('logs.communication') }}" class="px-3 py-2 bg-gray-100 text-gray-700 border border-gray-300 rounded-md text-sm hover:bg-gray-200">
                        クリア
                    </a>
                @endif
            </form>
        </div>

        <!-- 通信ログテーブル -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">ID</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">日時</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">ステータス</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">メッセージ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($logs as $log)
                            @php
                                $logTime = $log->recorded_at ?? $log->occurred_at ?? $log->created_at ?? '-';
                                $status = $log->status ?? $log->level ?? '-';
                                $message = $log->message ?? $log->detail ?? null;
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-gray-700 whitespace-nowrap">{{ $log->id }}</td>
                                <td class="px-4 py-3 text-gray-700 whitespace-nowrap">{{ $logTime }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="px-2 py-0.5 text-xs font-semibold rounded bg-gray-100 text-gray-800">
                                        {{ $status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-700 break-all">
                                    {{ $message ?? json_encode($log->getAttributes(), JSON_UNESCAPED_UNICODE) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-gray-500">
                                    表示できる通信ログはありません。
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $logs->links() }}
            </div>
        </div>
    </div>
</x-app-layout>