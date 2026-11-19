<x-app-layout>
    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <!-- ヘッダー -->
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                📊 {{ $dashboard->title }}
            </h2>
            <a href="{{ route('modbus.dashboards.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-md text-sm hover:bg-gray-500 font-medium">
                ← 一覧へ戻る
            </a>
        </div>

        @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded" role="alert">
            <span>{{ session('success') }}</span>
        </div>
        @endif

        <!-- ① タグ選択・割り当てフォーム -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <form id="tagSelectionForm" action="{{ route('modbus.dashboards.updateTags', $dashboard) }}" method="POST">
                @csrf
                <div class="flex flex-wrap justify-between items-center gap-3 mb-4">
                    <div class="flex items-center gap-3">
                        <h3 class="text-sm font-bold text-gray-700">この画面に表示する信号（タグ）の選択</h3>
                        <span id="unsavedBadge" style="display: none;" class="px-2 py-0.5 text-xs font-semibold bg-amber-100 text-amber-800 rounded">
                            未保存の変更あり
                        </span>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" id="selectAllBtn" class="px-3 py-1.5 bg-gray-100 text-gray-700 border border-gray-300 rounded-md text-xs font-medium hover:bg-gray-200">
                            すべて選択
                        </button>
                        <button type="button" id="deselectAllBtn" class="px-3 py-1.5 bg-gray-100 text-gray-700 border border-gray-300 rounded-md text-xs font-medium hover:bg-gray-200">
                            すべて解除
                        </button>
                        <button type="submit" id="saveTagsBtn" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-semibold hover:bg-indigo-700">
                            💾 選択内容を保存して反映
                        </button>
                    </div>
                </div>

                <!-- 元の画面と同じグリッド指定（grid-cols-2 md:grid-cols-4 lg:grid-cols-5）を維持 -->
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-3 max-h-60 overflow-y-auto p-3 bg-gray-50 rounded border border-gray-200">
                    @foreach($allTags as $tag)
                    @php
                    $code = $tag->tag_name ?? $tag->tag_code ?? '';
                    $label = $tag->name ?? $tag->description ?? '';
                    @endphp
                    <label class="inline-flex items-center text-xs p-1.5 hover:bg-white rounded cursor-pointer">
                        <input type="checkbox" name="tag_ids[]" value="{{ $tag->id }}"
                            {{ in_array($tag->id, $selectedTagIds) ? 'checked' : '' }}
                            class="tag-checkbox rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                        <span class="ml-2 text-gray-700 font-medium">{{ $code }}</span>
                        @if($label !== '')
                        <span class="ml-1 text-gray-500">({{ $label }})</span>
                        @endif
                    </label>
                    @endforeach
                </div>
            </form>
        </div>

        <!-- ② トレンドグラフ表示＆操作エリア -->
        <div style="background-color: #111827; color: #f9fafb;" class="overflow-hidden shadow-sm sm:rounded-lg p-6 space-y-4">
            <div class="flex flex-wrap justify-between items-center gap-2 pb-3" style="border-bottom: 1px solid #374151;">
                <div class="flex items-center gap-3">
                    <h3 class="text-base font-bold text-white">リアルタイム・トレンドグラフ</h3>
                    <span id="liveStatusBadge" style="background-color: rgba(16, 185, 129, 0.2); color: #6ee7b7;" class="px-2.5 py-0.5 rounded-full text-xs font-semibold">
                        ● LIVE
                    </span>
                </div>
                <div class="text-xs text-gray-300">
                    選択中タグ数: <strong class="text-white">{{ count($selectedTagIds) }}</strong> 件
                    <span id="lastUpdatedText" class="ml-3 text-gray-400">最終更新: --:--:--</span>
                </div>
            </div>

            <!-- 操作バー -->
            <div style="background-color: #1f2937; border: 1px solid #374151;" class="p-4 rounded-lg text-xs space-y-3 md:space-y-0 md:flex md:flex-wrap md:items-end md:justify-between gap-4">
                <div class="flex flex-wrap items-end gap-3">
                    <div>
                        <label for="minutesSelect" class="block text-gray-300 font-medium mb-1">表示時間範囲</label>
                        <select id="minutesSelect" style="background-color: #111827; color: #f9fafb; border-color: #4b5563;" class="rounded-md px-3 py-1.5 text-xs">
                            <option value="5" selected>直近 5 分</option>
                            <option value="15">直近 15 分</option>
                            <option value="30">直近 30 分</option>
                            <option value="60">直近 60 分</option>
                        </select>
                    </div>

                    <div>
                        <label for="intervalSelect" class="block text-gray-300 font-medium mb-1">自動更新</label>
                        <select id="intervalSelect" style="background-color: #111827; color: #f9fafb; border-color: #4b5563;" class="rounded-md px-3 py-1.5 text-xs">
                            <option value="2000">2秒ごと</option>
                            <option value="5000" selected>5秒ごと</option>
                            <option value="10000">10秒ごと</option>
                            <option value="30000">30秒ごと</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-gray-300 font-medium mb-1">縦軸レンジ (Y軸)</label>
                        <div class="flex items-center gap-1.5">
                            <input type="number" step="any" id="yMinInput" placeholder="Min (自動)"
                                style="background-color: #111827; color: #f9fafb; border-color: #4b5563; width: 100px;"
                                class="rounded-md px-2 py-1.5 text-xs">
                            <span class="text-gray-400">〜</span>
                            <input type="number" step="any" id="yMaxInput" placeholder="Max (自動)"
                                style="background-color: #111827; color: #f9fafb; border-color: #4b5563; width: 100px;"
                                class="rounded-md px-2 py-1.5 text-xs">
                            <button type="button" id="applyYAxisBtn" class="px-3 py-1.5 bg-gray-600 hover:bg-gray-500 text-white rounded-md font-medium">
                                適用
                            </button>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" id="togglePauseBtn" class="px-3 py-1.5 bg-gray-600 hover:bg-gray-500 text-white rounded-md font-medium">
                        ⏸ 一時停止
                    </button>
                    <button type="button" id="manualRefreshBtn" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-md font-semibold">
                        🔄 更新する
                    </button>

                    <button type="button" id="exportCsvBtn"
                        class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-md font-semibold">
                        📥 CSV出力
                    </button>

                </div>
            </div>

            <!-- グラフ描画キャンバス -->
           <!-- グラフ描画キャンバス -->
           @if(count($selectedTagIds) === 0)
            <div style="height: 360px; border: 1px dashed #4b5563;" class="flex items-center justify-center rounded-lg text-gray-400 text-sm">
                表示するタグが選択されていません。上のチェックボックスからタグを選び、「選択内容を保存して反映」を押してください。
            </div>
            @else
            <div id="trendChartContainer"
                 data-tag-ids="{{ json_encode($selectedTagIds ?? []) }}"
                 data-data-url="{{ route('modbus.data') }}"
                 data-export-csv-url="{{ route('export.csv') }}"
                 style="position: relative; width: 100%; height: 500px;">
                <canvas id="dashboardTrendChart"></canvas>
            </div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 1. 必要なDOM要素・初期データを先にまとめて取得
            const checkboxes = document.querySelectorAll('.tag-checkbox');
            const unsavedBadge = document.getElementById('unsavedBadge');
            const selectAllBtn = document.getElementById('selectAllBtn');
            const deselectAllBtn = document.getElementById('deselectAllBtn');
            const exportCsvBtn = document.getElementById('exportCsvBtn');

            const container = document.getElementById('trendChartContainer');
            const canvasEl = document.getElementById('dashboardTrendChart');
            const minutesSelect = document.getElementById('minutesSelect');
            const intervalSelect = document.getElementById('intervalSelect');
            const yMinInput = document.getElementById('yMinInput');
            const yMaxInput = document.getElementById('yMaxInput');
            const applyYAxisBtn = document.getElementById('applyYAxisBtn');
            const togglePauseBtn = document.getElementById('togglePauseBtn');
            const manualRefreshBtn = document.getElementById('manualRefreshBtn');
            const liveStatusBadge = document.getElementById('liveStatusBadge');
            const lastUpdatedText = document.getElementById('lastUpdatedText');

            const tagIds = container ? JSON.parse(container.dataset.tagIds || '[]') : [];
            const dataUrl = container ? container.dataset.dataUrl : '/modbus/data';
            const exportCsvUrl = container ? container.dataset.exportCsvUrl : '/export-csv';

            // 2. タグ選択フォーム操作
            const markUnsaved = () => {
                if (unsavedBadge) unsavedBadge.style.display = 'inline-block';
            };

            checkboxes.forEach(cb => cb.addEventListener('change', markUnsaved));

            selectAllBtn?.addEventListener('click', () => {
                checkboxes.forEach(cb => cb.checked = true);
                markUnsaved();
            });

            deselectAllBtn?.addEventListener('click', () => {
                checkboxes.forEach(cb => cb.checked = false);
                markUnsaved();
            });

            // 3. CSV出力ボタン（tagIds / minutesSelect 宣言後に配置）
            exportCsvBtn?.addEventListener('click', function() {
                if (!Array.isArray(tagIds) || tagIds.length === 0) {
                    alert('出力対象のタグが選択されていません。');
                    return;
                }

                const params = new URLSearchParams();
                params.append('tag_ids', tagIds.join(','));
                if (minutesSelect && minutesSelect.value) {
                    params.append('minutes', minutesSelect.value);
                }

                window.location.href = `${exportCsvUrl}?${params.toString()}`;
            });

            // 4. グラフ描画（タグ未選択・Canvas非表示時はここで終了）
            if (!canvasEl || !Array.isArray(tagIds) || tagIds.length === 0) return;

            const ctx = canvasEl.getContext('2d');
            const colors = [
                '#38bdf8', '#34d399', '#f43f5e', '#fbbf24', '#a855f7',
                '#ec4899', '#6366f1', '#14b8a6', '#f97316', '#84cc16'
            ];

            let isPaused = false;
            let timerId = null;

            const chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: []
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    scales: {
                        x: {
                            ticks: {
                                color: '#9ca3af',
                                maxTicksLimit: 12
                            },
                            grid: {
                                color: 'rgba(55, 65, 81, 0.7)'
                            }
                        },
                        y: {
                            ticks: {
                                color: '#9ca3af'
                            },
                            grid: {
                                color: 'rgba(55, 65, 81, 0.7)'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                color: '#f3f4f6',
                                boxWidth: 16,
                                padding: 14
                            }
                        }
                    }
                }
            });

            function applyYAxisRange() {
                const minVal = yMinInput.value.trim();
                const maxVal = yMaxInput.value.trim();
                chart.options.scales.y.min = minVal !== '' ? Number(minVal) : undefined;
                chart.options.scales.y.max = maxVal !== '' ? Number(maxVal) : undefined;
                chart.update();
            }

            function fetchTrendData() {
                const params = new URLSearchParams();
                params.append('tag_ids', tagIds.join(','));
                params.append('minutes', minutesSelect ? minutesSelect.value : 5);

                fetch(`${dataUrl}?${params.toString()}`)
                    .then(response => {
                        if (!response.ok) throw new Error(`HTTP ${response.status}`);
                        return response.json();
                    })
                    .then(datasetsData => {
                        if (!Array.isArray(datasetsData) || datasetsData.length === 0) return;

                        const firstLogs = datasetsData[0].logs || [];
                        const labels = firstLogs.map(log => {
                            const date = new Date(log.recorded_at);
                            return isNaN(date.getTime()) ? log.recorded_at : date.toLocaleTimeString();
                        });

                        const chartDatasets = datasetsData.map((item, index) => {
                            const color = colors[index % colors.length];
                            const code = item.tag_code ?? item.tag_name ?? '';
                            const name = item.name ?? '';
                            return {
                                label: name ? `${code} (${name})` : code,
                                data: (item.logs || []).map(log => log.value),
                                borderColor: color,
                                backgroundColor: color,
                                borderWidth: 2,
                                tension: 0.15,
                                pointRadius: 1.5,
                                pointHoverRadius: 4
                            };
                        });

                        chart.data.labels = labels;
                        chart.data.datasets = chartDatasets;
                        chart.update('none');

                        if (lastUpdatedText) {
                            lastUpdatedText.textContent = `最終更新: ${new Date().toLocaleTimeString()}`;
                        }
                    })
                    .catch(error => console.error('Error fetching trend data:', error));
            }

            function restartPolling() {
                if (timerId) {
                    clearInterval(timerId);
                    timerId = null;
                }
                if (!isPaused) {
                    const intervalMs = Number(intervalSelect ? intervalSelect.value : 5000);
                    timerId = setInterval(fetchTrendData, intervalMs);
                }
            }

            function updatePauseUi() {
                if (isPaused) {
                    togglePauseBtn.textContent = '▶ 再開 (LIVE)';
                    if (liveStatusBadge) {
                        liveStatusBadge.textContent = '❚❚ PAUSED';
                        liveStatusBadge.style.backgroundColor = 'rgba(245, 158, 11, 0.2)';
                        liveStatusBadge.style.color = '#fcd34d';
                    }
                } else {
                    togglePauseBtn.textContent = '⏸ 一時停止';
                    if (liveStatusBadge) {
                        liveStatusBadge.textContent = '● LIVE';
                        liveStatusBadge.style.backgroundColor = 'rgba(16, 185, 129, 0.2)';
                        liveStatusBadge.style.color = '#6ee7b7';
                    }
                }
            }

            applyYAxisBtn?.addEventListener('click', applyYAxisRange);
            manualRefreshBtn?.addEventListener('click', fetchTrendData);
            minutesSelect?.addEventListener('change', fetchTrendData);
            intervalSelect?.addEventListener('change', restartPolling);
            togglePauseBtn?.addEventListener('click', () => {
                isPaused = !isPaused;
                updatePauseUi();
                restartPolling();
            });

            fetchTrendData();
            restartPolling();
        });
    </script>
    @endpush
</x-app-layout>