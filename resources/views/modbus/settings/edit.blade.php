<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('PLC 通信定義・接続設定') }}
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

                <form method="POST" action="{{ route('modbus.settings.update') }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- 設備・PLC識別名 -->
                    <div>
                        <x-input-label for="plc_name" :value="__('接続対象 PLC名称 *')" />
                        <x-text-input id="plc_name" name="plc_name" type="text" class="mt-1 block w-full" :value="old('plc_name', $setting->plc_name)" required />
                        <x-input-error :messages="$errors->get('plc_name')" class="mt-2" />
                    </div>

                    <!-- IPアドレス & ポート番号 -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-2">
                            <x-input-label for="ip_address" :value="__('PLC IPアドレス (IPv4) *')" />
                            <x-text-input id="ip_address" name="ip_address" type="text" placeholder="192.168.1.10" class="mt-1 block w-full font-mono" :value="old('ip_address', $setting->ip_address)" required />
                            <x-input-error :messages="$errors->get('ip_address')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="port" :value="__('ポート番号 *')" />
                            <x-text-input id="port" name="port" type="number" min="1" max="65535" class="mt-1 block w-full font-mono" :value="old('port', $setting->port)" required />
                            <p class="text-xs text-gray-500 mt-1">標準: 502</p>
                            <x-input-error :messages="$errors->get('port')" class="mt-2" />
                        </div>
                    </div>

                    <!-- ユニットID・ポーリング周期・タイムアウト -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <x-input-label for="unit_id" :value="__('ユニットID (Slave ID) *')" />
                            <x-text-input id="unit_id" name="unit_id" type="number" min="0" max="255" class="mt-1 block w-full font-mono" :value="old('unit_id', $setting->unit_id)" required />
                            <x-input-error :messages="$errors->get('unit_id')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="polling_interval_ms" :value="__('ポーリング周期 (ms) *')" />
                            <x-text-input id="polling_interval_ms" name="polling_interval_ms" type="number" min="100" step="100" class="mt-1 block w-full font-mono" :value="old('polling_interval_ms', $setting->polling_interval_ms)" required />
                            <p class="text-xs text-gray-500 mt-1">1000ms = 1秒間隔</p>
                            <x-input-error :messages="$errors->get('polling_interval_ms')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="timeout_ms" :value="__('タイムアウト (ms) *')" />
                            <x-text-input id="timeout_ms" name="timeout_ms" type="number" min="100" step="100" class="mt-1 block w-full font-mono" :value="old('timeout_ms', $setting->timeout_ms)" required />
                            <x-input-error :messages="$errors->get('timeout_ms')" class="mt-2" />
                        </div>
                    </div>

                    <!-- データ収集の有効/無効スイッチ -->
                    <div class="border-t pt-4">
                        <label for="is_polling_enabled" class="inline-flex items-center cursor-pointer">
                            <input
                                id="is_polling_enabled"
                                name="is_polling_enabled"
                                type="checkbox"
                                value="1"
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                {{ old('is_polling_enabled', $setting->is_polling_enabled) ? 'checked' : '' }}
                            >
                            <span class="ms-2 text-sm text-gray-800 font-medium">
                                ポーリング（データ自動収集）を有効にする
                            </span>
                        </label>
                    </div>

                    <!-- 保存ボタン -->
                    <div class="flex justify-end border-t pt-4">
                        <x-primary-button>
                            {{ __('通信設定を保存') }}
                        </x-primary-button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>