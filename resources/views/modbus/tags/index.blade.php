<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Modbus タグマスター管理') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- フラッシュメッセージ -->
            @if (session('success'))
            <div class="p-4 mb-4 text-sm text-green-700 bg-green-100 dark:bg-green-900 dark:text-green-200 rounded-lg shadow" role="alert">
                <span class="font-medium">Success!</span> {{ session('success') }}
            </div>
            @endif

            <!-- 新規登録ボタンへの導線 -->
            <div class="flex justify-between items-center">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    トレンドグラフやデータロギングの対象となるModbusタグの設定・有効化を管理します。
                </p>
                <a href="{{ route('modbus.tags.create') }}"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs rounded-md shadow-sm transition">
                    + 新規タグ登録
                </a>
            </div>

            <!-- タグ一覧テーブル -->
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">ID / コード</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">タグ名称</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">レジスタアドレス</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">単位</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">状態 (有効/無効)</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">操作</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($tags as $tag)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <!-- IDとタグコード -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-mono font-semibold text-gray-900 dark:text-gray-100">
                                    {{ $tag->id }}<br>
                                    <span class="text-xs font-normal text-gray-500 dark:text-gray-400">{{ $tag->tag_code ?? '-' }}</span>
                                </td>

                                <!-- タグ名称 -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ $tag->name }}
                                </td>

                                <!-- レジスタアドレス -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-600 dark:text-gray-300">
                                    {{ $tag->register_address }}
                                </td>

                                <!-- 単位 -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                                    {{ $tag->unit ?? '-' }}
                                </td>

                                <!-- 状態 (有効/無効) -->
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if($tag->is_active)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                        有効
                                    </span>
                                    @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                        無効
                                    </span>
                                    @endif
                                </td>

                                <!-- 操作（編集画面へのリンク） -->
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('modbus.tags.edit', $tag->id) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 bg-indigo-50 dark:bg-indigo-950/50 px-3 py-1.5 rounded-md border border-indigo-200 dark:border-indigo-800 transition">
                                        編集
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500 dark:text-gray-400">
                                    登録されているModbusタグはありません。「新規タグ登録」から追加してください。
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- ページネーションリンク -->
                @if ($tags->hasPages())
                <div class="p-4 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-200 dark:border-gray-700">
                    {{ $tags->links() }}
                </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>