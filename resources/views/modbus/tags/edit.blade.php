<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Modbus タグ詳細・接続設定編集') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                @if (session('success'))
                <div class="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 rounded">
                    {{ session('success') }}
                </div>
                @endif

                @if ($errors->any())
                <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded">
                    <p class="font-bold mb-1">入力内容を確認してください：</p>
                    <ul class="list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form method="POST" action="{{ route('modbus.tags.update', $tag->id) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- タグ名称 -->
                    <div>
                        <x-input-label for="tag_name" :value="__('タグ名称 *')" />
                        <x-text-input id="tag_name" name="tag_name" type="text" class="mt-1 block w-full" :value="old('tag_name', $tag->tag_name)" required />
                        <x-input-error :messages="$errors->get('tag_name')" class="mt-2" />
                    </div>

                    <!-- タグコード & レジスタアドレス -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="tag_code" :value="__('タグ識別コード *')" />
                            <x-text-input id="tag_code" name="tag_code" type="text" class="mt-1 block w-full font-mono" :value="old('tag_code', $tag->tag_code)" required />
                            <x-input-error :messages="$errors->get('tag_code')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="register_address" :value="__('レジスタアドレス *')" />
                            <x-text-input id="register_address" name="register_address" type="number" min="0" class="mt-1 block w-full font-mono" :value="old('register_address', $tag->register_address)" required />
                            <x-input-error :messages="$errors->get('register_address')" class="mt-2" />
                        </div>
                    </div>

                    <!-- 単位・データ型・上限値 -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <x-input-label for="unit" :value="__('単位')" />
                            <x-text-input id="unit" name="unit" type="text" class="mt-1 block w-full" :value="old('unit', $tag->unit)" />
                            <x-input-error :messages="$errors->get('unit')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="data_type" :value="__('データ型')" />
                            <x-text-input id="data_type" name="data_type" type="text" class="mt-1 block w-full" :value="old('data_type', $tag->data_type)" />
                            <x-input-error :messages="$errors->get('data_type')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="high_limit" :value="__('警報上限値')" />
                            <x-text-input id="high_limit" name="high_limit" type="number" step="0.01" class="mt-1 block w-full font-mono" :value="old('high_limit', $tag->high_limit)" />
                            <x-input-error :messages="$errors->get('high_limit')" class="mt-2" />
                        </div>
                    </div>

                    <!-- データ収集の有効/無効スイッチ -->
                    <div class="border-t pt-4">
                        <label for="is_active" class="inline-flex items-center cursor-pointer">
                            <input
                                id="is_active"
                                name="is_active"
                                type="checkbox"
                                value="1"
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                {{ old('is_active', $tag->is_active) ? 'checked' : '' }}>
                            <span class="ms-2 text-sm text-gray-800 font-medium">
                                このタグのデータ収集を有効にする
                            </span>
                        </label>
                    </div>

                    <!-- ボタンエリア（キャンセル ＆ 更新） -->
                    <div class="flex flex-wrap items-center justify-between gap-4 border-t pt-4">
                        <a href="{{ route('modbus.tags.index') }}" class="text-sm text-gray-600 hover:underline">
                            ← タグ一覧に戻る
                        </a>

                        <x-primary-button>
                            {{ __('タグ情報を更新') }}
                        </x-primary-button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <script>
        document.getElementById('btnTestConnection').addEventListener('click', async function() {
            const btn = this;
            const resultBox = document.getElementById('testResultBox');
            const ipAddress = document.getElementById('ip_address').value;
            const port = document.getElementById('port').value;
            const timeoutMs = document.getElementById('timeout_ms').value;

            // テスト実行中のUI状態に変更
            btn.disabled = true;
            btn.textContent = '⏳ 接続確認中...';
            resultBox.className = 'mb-6 p-4 border-l-4 rounded text-sm font-medium bg-blue-50 border-blue-500 text-blue-700';
            resultBox.textContent = `${ipAddress}:${port} への接続を試行しています...`;
            resultBox.classList.remove('hidden');

            try {
                const response = await fetch('{{ route("modbus.settings.test") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        ip_address: ipAddress,
                        port: port,
                        timeout_ms: timeoutMs
                    })
                });

                const data = await response.json();

                if (!response.ok) {
                    // バリデーションエラー時（IP形式不正など）
                    const errorMessages = data.errors ? Object.values(data.errors).flat().join(' / ') : '入力値を確認してください。';
                    resultBox.className = 'mb-6 p-4 border-l-4 rounded text-sm font-medium bg-red-50 border-red-500 text-red-700';
                    resultBox.textContent = `入力エラー：${errorMessages}`;
                } else if (data.success) {
                    resultBox.className = 'mb-6 p-4 border-l-4 rounded text-sm font-medium bg-emerald-50 border-emerald-500 text-emerald-700';
                    resultBox.textContent = `✅ ${data.message}`;
                } else {
                    resultBox.className = 'mb-6 p-4 border-l-4 rounded text-sm font-medium bg-red-50 border-red-500 text-red-700';
                    resultBox.textContent = `❌ ${data.message}`;
                }
            } catch (error) {
                resultBox.className = 'mb-6 p-4 border-l-4 rounded text-sm font-medium bg-red-50 border-red-500 text-red-700';
                resultBox.textContent = '❌ 通信エラーが発生しました。サーバーの稼働状態を確認してください。';
            } finally {
                btn.disabled = false;
                btn.textContent = '🔌 接続テスト実行';
            }
        });
    </script>
</x-app-layout>