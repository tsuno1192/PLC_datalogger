<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Modbus タグ新規登録') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6">
                
                <!-- バリデーションエラーの表示 -->
                @if ($errors->any())
                    <div class="mb-4 p-4 bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-200 rounded-lg text-sm">
                        <p class="font-semibold mb-1">入力内容にエラーがあります。以下の項目を確認してください。</p>
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- 新規登録フォーム -->
                <form action="{{ route('modbus.tags.store') }}" method="POST">
                    @csrf

                    <!-- タグ識別コード (tag_code) -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">タグ識別コード (tag_code) <span class="text-red-500">*</span></label>
                        <input type="text" name="tag_code" value="{{ old('tag_code') }}" required placeholder="例: PV_PRESSURE_01"
                               class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:ring-indigo-500">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">システム内で一意となる識別コードを入力してください。</p>
                    </div>

                    <!-- タグ名称 (name) -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">タグ名称 (name) <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" required placeholder="例: 圧力センサ1"
                               class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:ring-indigo-500">
                    </div>

                    <!-- レジスタアドレス (register_address) -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">レジスタアドレス (register_address) <span class="text-red-500">*</span></label>
                        <input type="number" name="register_address" value="{{ old('register_address', 0) }}" min="0" required
                               class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:ring-indigo-500">
                    </div>

                    <!-- 単位 (unit) -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">単位 (unit)</label>
                        <input type="text" name="unit" value="{{ old('unit') }}" placeholder="例: MPa, ℃, %"
                               class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:ring-indigo-500">
                    </div>

                    <!-- データ型 (data_type) -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">データ型 (data_type)</label>
                        <select name="data_type" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:ring-indigo-500">
                            <option value="float" {{ old('data_type', 'float') === 'float' ? 'selected' : '' }}>float (浮動小数点)</option>
                            <option value="int" {{ old('data_type') === 'int' ? 'selected' : '' }}>int (整数)</option>
                        </select>
                    </div>

                    <!-- 上限値 (high_limit) -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">上限値 (high_limit)</label>
                        <input type="number" step="0.01" name="high_limit" value="{{ old('high_limit') }}" placeholder="例: 100.00"
                               class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:ring-indigo-500">
                    </div>

                    <!-- 有効/無効 (is_active) -->
                    <div class="mb-6">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">このタグを有効にする (is_active)</span>
                        </label>
                    </div>

                    <!-- ボタン類 -->
                    <div class="flex items-center justify-end space-x-3">
                        <a href="{{ route('modbus.tags.index') }}" class="px-4 py-2 bg-gray-300 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-md text-sm hover:bg-gray-400 transition">
                            キャンセル
                        </a>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700 transition">
                            登録する
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>