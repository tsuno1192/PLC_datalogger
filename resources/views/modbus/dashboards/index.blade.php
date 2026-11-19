<x-app-layout>
    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                📈 マイ・トレンド画面一覧
            </h2>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        <!-- 新規作成フォーム -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h3 class="text-md font-bold text-gray-700 mb-3">＋ 新しいトレンド監視画面を作成</h3>
            <form action="{{ route('modbus.dashboards.store') }}" method="POST" class="flex gap-4">
                @csrf
                <input type="text" name="title" placeholder="画面名（例: ボイラー系統・第1プラント監視）" 
                    class="flex-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 font-medium text-sm">
                    作成する
                </button>
            </form>
        </div>

        <!-- 作成済みダッシュボード一覧グリッド -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @forelse($dashboards as $dashboard)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 flex flex-col justify-between border border-gray-100 hover:shadow-md transition">
                    <div>
                        <h3 class="font-bold text-lg text-gray-800 mb-1">{{ $dashboard->title }}</h3>
                        <p class="text-xs text-gray-500">登録タグ数: {{ $dashboard->tags()->count() }} 件</p>
                        <p class="text-xs text-gray-400 mt-1">作成日時: {{ $dashboard->created_at->format('Y-m-d H:i') }}</p>
                    </div>
                    <div class="mt-6">
                        <a href="{{ route('modbus.dashboards.show', $dashboard) }}" class="block text-center w-full px-4 py-2 bg-gray-800 text-white rounded-md text-sm font-medium hover:bg-gray-700 transition">
                            📊 画面を開く・グラフ表示
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-3 bg-white p-6 rounded-lg text-center text-gray-500">
                    カスタムトレンド画面がまだありません。上のフォームから新しい画面を作成してください。
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>