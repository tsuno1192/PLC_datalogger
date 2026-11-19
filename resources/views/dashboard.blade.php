<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('PLC データロガー ダッシュボード') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- ナビゲーション・ショートカットエリア --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">システム概要・操作メニュー</h3>
                        <p class="text-sm text-gray-600 mt-1">
                            登録済みModbusタグ数：<span class="font-semibold text-gray-900">{{ isset($tags) ? $tags->count() : 0 }}</span> 件
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <a href="{{ route('modbus.chart') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition">
                            📈 トレンドグラフへ
                        </a>
                        <a href="{{ route('modbus.tags.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition">
                            🏷️ タグ設定管理へ
                        </a>
                    </div>
                </div>

                {{-- クイックアクセスカード --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 border-t pt-6">
                    <a href="{{ route('modbus.chart') }}" class="block p-5 border border-gray-200 rounded-lg hover:bg-indigo-50 hover:border-indigo-300 transition">
                        <div class="font-semibold text-indigo-700 text-base mb-1">トレンドグラフ監視</div>
                        <p class="text-sm text-gray-600">収集したPLCレジスタ値の時系列チャート・リアルタイム推移を確認します。</p>
                    </a>

                    <a href="{{ route('modbus.tags.index') }}" class="block p-5 border border-gray-200 rounded-lg hover:bg-gray-50 hover:border-gray-300 transition">
                        <div class="font-semibold text-gray-800 text-base mb-1">Modbus タグマスター管理</div>
                        <p class="text-sm text-gray-600">ポーリング対象のレジスタアドレス、単位、有効/無効フラグを登録・編集します。</p>
                    </a>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">📥 ロギングデータ CSV出力</h3>

                    <form method="GET" action="{{ route('export.csv') }}" class="flex flex-wrap items-end gap-4">
                        <!-- タグ選択 -->
                        <div>
                            <label for="csv_tag_id" class="block text-sm font-medium text-gray-700">対象タグ</label>
                            <select id="csv_tag_id" name="tag_id" class="mt-1 block rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">すべてのタグ</option>
                                @foreach($tags as $tag)
                                <option value="{{ $tag->id }}">{{ $tag->tag_code }} : {{ $tag->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- 開始日時 -->
                        <div>
                            <label for="csv_from" class="block text-sm font-medium text-gray-700">開始日時</label>
                            <input type="datetime-local" id="csv_from" name="from" class="mt-1 block rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>

                        <!-- 終了日時 -->
                        <div>
                            <label for="csv_to" class="block text-sm font-medium text-gray-700">終了日時</label>
                            <input type="datetime-local" id="csv_to" name="to" class="mt-1 block rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>

                        <!-- ダウンロードボタン -->
                        <div>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700 transition">
                                CSVダウンロード
                            </button>
                        </div>
                    </form>
                </div>


            </div>

        </div>
    </div>
</x-app-layout>