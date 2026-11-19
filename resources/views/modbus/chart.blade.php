<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Modbus マルチトレンドグラフ') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- コントロールパネル（タグ選択 ＆ 時間・更新・付箋コントロール） -->
            <div class="p-4 sm:p-6 bg-white dark:bg-gray-800 shadow sm:rounded-lg space-y-4">
                <form id="chart-filter-form" class="space-y-4">
                    <div>
                        <label class="block font-medium text-sm text-gray-700 dark:text-gray-300 mb-2">表示するタグ（複数選択可）</label>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 max-h-40 overflow-y-auto p-3 border border-gray-200 dark:border-gray-700 rounded-md">
                            @foreach($tags as $tag)
                            <label class="flex items-center space-x-2 text-sm text-gray-600 dark:text-gray-400 cursor-pointer">
                                <input type="checkbox" name="tag_ids[]" value="{{ $tag->id }}" checked
                                    class="tag-checkbox rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <span class="font-mono font-semibold">{{ $tag->tag_code ?? $tag->id }}</span>
                                <span class="truncate">({{ $tag->name ?? $tag->tag_name }})</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-4">
                        <div>
                            <label for="minutes" class="block font-medium text-sm text-gray-700 dark:text-gray-300">表示時間範囲</label>
                            <select id="minutes" name="minutes" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:ring-indigo-500 sm:text-sm">
                                <option value="5" selected>直近 5 分</option>
                                <option value="15">直近 15 分</option>
                                <option value="30">直近 30 分</option>
                                <option value="60">直近 1 時間</option>
                                <option value="360">直近 6 時間</option>
                                <option value="1440">直近 1 日 (24時間)</option>
                                <option value="10080">直近 7 日間</option>
                                <option value="44640">直近 31 日間</option>
                            </select>
                        </div>

                        <div>
                            <label for="interval" class="block font-medium text-sm text-gray-700 dark:text-gray-300">自動更新</label>
                            <select id="interval" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:ring-indigo-500 sm:text-sm">
                                <option value="5000">5秒ごと</option>
                                <option value="10000" selected>10秒ごと</option>
                                <option value="30000">30秒ごと</option>
                                <option value="0">停止</option>
                            </select>
                        </div>

                        <!-- 縦軸レンジ設定コントロール -->
                        <div>
                            <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">縦軸レンジ (Y軸)</label>
                            <div class="mt-1 flex items-center space-x-2">
                                <input type="number" id="y-min" placeholder="Min (自動)" class="w-24 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:ring-indigo-500 sm:text-sm">
                                <span class="text-gray-500">～</span>
                                <input type="number" id="y-max" placeholder="Max (自動)" class="w-24 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:ring-indigo-500 sm:text-sm">
                                <button type="button" id="apply-range-btn" class="px-3 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 text-xs font-semibold rounded-md transition">
                                    適用
                                </button>
                            </div>
                        </div>

                        <!-- 🆕 一時停止＆付箋機能コントロールボタン -->
                        <div class="flex items-end space-x-2">
                            <button type="button" id="btn-toggle-freeze" class="inline-flex items-center px-4 py-2 bg-amber-500 hover:bg-amber-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest shadow transition">
                                ⏸ 一時停止 (Live)
                            </button>
                            <button type="button" id="btn-add-memo" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest shadow transition disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                                📝 付箋を追加
                            </button>
                        </div>

                        <div class="flex items-end self-end ml-auto">
                            <button type="button" id="update-chart-btn" class="inline-flex items-center px-4 py-2 bg-gray-700 dark:bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-800 focus:bg-gray-800 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">
                                更新する
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- グラフ描画エリア ＆ 付箋オーバーレイレイヤー -->
            <div class="p-6 bg-white dark:bg-gray-800 shadow sm:rounded-lg relative">
                <!-- 外側・内側の両方で高さを固定またはフルサイズに指定 -->
                <div id="chartContainer" class="relative w-full" style="height: 700px;">
                    <canvas id="trendChart"></canvas>
                    <div id="memoLayer" class="absolute inset-0 pointer-events-none overflow-hidden z-20"></div>
                </div>
            </div>

        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>
    <script>
        let trendChart = null;
        let autoRefreshTimer = null;
        let isFrozen = false; // トレンド一時停止フラグ
        let memoMode = false; // 付箋配置モードフラグ

        // カラーパレット（複数系列用）
        const colorPalette = [
            '#4f46e5', '#ef4444', '#10b981', '#f59e0b', '#3b82f6',
            '#ec4899', '#8b5cf6', '#14b8a6', '#f97316', '#6366f1'
        ];

        document.addEventListener('DOMContentLoaded', function() {
            initChart();
            fetchChartData();

            // 更新ボタン
            document.getElementById('update-chart-btn').addEventListener('click', function() {
                if (!isFrozen) fetchChartData();
            });

            // 縦軸レンジ適用ボタン
            document.getElementById('apply-range-btn').addEventListener('click', function() {
                applyYAxisRange();
            });

            // 自動更新セレクトボックス変更
            document.getElementById('interval').addEventListener('change', function() {
                if (!isFrozen) setupAutoRefresh();
            });

            // 🆕 一時停止（Freeze）ボタンの制御
            const btnToggleFreeze = document.getElementById('btn-toggle-freeze');
            const btnAddMemo = document.getElementById('btn-add-memo');
            const memoLayer = document.getElementById('memoLayer');

            btnToggleFreeze.addEventListener('click', function() {
                isFrozen = !isFrozen;
                if (isFrozen) {
                    if (autoRefreshTimer) {
                        clearInterval(autoRefreshTimer);
                        autoRefreshTimer = null;
                    }
                    btnToggleFreeze.textContent = '▶ 再開 (Paused)';
                    btnToggleFreeze.className = 'inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest shadow transition';
                    btnAddMemo.removeAttribute('disabled');
                } else {
                    btnToggleFreeze.textContent = '⏸ 一時停止 (Live)';
                    btnToggleFreeze.className = 'inline-flex items-center px-4 py-2 bg-amber-500 hover:bg-amber-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest shadow transition';

                    if (memoMode) {
                        memoMode = false;
                        btnAddMemo.textContent = '📝 付箋を追加';
                        btnAddMemo.classList.remove('ring-4', 'ring-indigo-300');
                        memoLayer.classList.add('pointer-events-none');
                    }
                    btnAddMemo.setAttribute('disabled', 'true');

                    setupAutoRefresh();
                    fetchChartData();
                }
            });

            // 🆕 「付箋を追加」ボタンの制御
            btnAddMemo.addEventListener('click', function() {
                if (!isFrozen) return;
                memoMode = !memoMode;
                if (memoMode) {
                    btnAddMemo.classList.add('ring-4', 'ring-indigo-300');
                    btnAddMemo.textContent = '📝 グラフをクリックして配置';
                    // 付箋モード中のみ、レイヤーのクリックを受け付けるようにする
                    memoLayer.classList.remove('pointer-events-none');
                    memoLayer.classList.add('pointer-events-auto', 'cursor-crosshair');
                } else {
                    btnAddMemo.classList.remove('ring-4', 'ring-indigo-300');
                    btnAddMemo.textContent = '📝 付箋を追加';
                    memoLayer.classList.add('pointer-events-none');
                    memoLayer.classList.remove('pointer-events-auto', 'cursor-crosshair');
                }
            });

            // 🆕 memoLayer をクリックして付箋を生成
            memoLayer.addEventListener('click', function(event) {
                if (!isFrozen || !memoMode) return;

                const rect = memoLayer.getBoundingClientRect();
                const clickX = event.clientX - rect.left;
                const clickY = event.clientY - rect.top;

                createMemoElement(clickX, clickY, "【要確認】ここに注目");

                // 配置後、自動的に付箋モードをオフにする
                memoMode = false;
                btnAddMemo.classList.remove('ring-4', 'ring-indigo-300');
                btnAddMemo.textContent = '📝 付箋を追加';
                memoLayer.classList.add('pointer-events-none');
                memoLayer.classList.remove('pointer-events-auto', 'cursor-crosshair');
            });

            setupAutoRefresh();
        });

        function initChart() {
            const ctx = document.getElementById('trendChart').getContext('2d');
            trendChart = new Chart(ctx, {
                type: 'line',
                data: {
                    datasets: []
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: false,
                    scales: {
                        x: {
                            type: 'category',
                            grid: {
                                color: 'rgba(200, 200, 200, 0.1)'
                            },
                            ticks: {
                                maxTicksLimit: 15
                            }
                        },
                        y: {
                            beginAtZero: false,
                            grid: {
                                color: 'rgba(200, 200, 200, 0.2)'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                        }
                    }
                }
            });
        }

        function applyYAxisRange() {
            if (!trendChart) return;

            const minVal = document.getElementById('y-min').value;
            const maxVal = document.getElementById('y-max').value;

            trendChart.options.scales.y.min = minVal !== '' ? parseFloat(minVal) : undefined;
            trendChart.options.scales.y.max = maxVal !== '' ? parseFloat(maxVal) : undefined;

            trendChart.update();
        }

        function fetchChartData() {
            if (isFrozen) return;

            const selectedTags = Array.from(document.querySelectorAll('.tag-checkbox:checked'))
                .map(cb => cb.value);

            if (selectedTags.length === 0) {
                trendChart.data.labels = [];
                trendChart.data.datasets = [];
                trendChart.update();
                return;
            }

            const minutes = document.getElementById('minutes').value;
            const url = `{{ route('modbus.data') }}?tag_ids=${selectedTags.join(',')}&minutes=${minutes}`;

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    updateChartData(data);
                })
                .catch(error => console.error('Error fetching trend data:', error));
        }

        function updateChartData(datasetsJson) {
            if (isFrozen) return;

            if (!datasetsJson || datasetsJson.length === 0) {
                trendChart.data.labels = [];
                trendChart.data.datasets = [];
                trendChart.update();
                return;
            }

            let labels = [];
            if (datasetsJson[0].logs && datasetsJson[0].logs.length > 0) {
                labels = datasetsJson[0].logs.map(log => log.recorded_at);
            }

            const datasets = datasetsJson.map((ds, index) => {
                const color = colorPalette[index % colorPalette.length];
                const dataValues = ds.logs.map(log => log.value);

                return {
                    label: `${ds.tag_code} (${ds.name})`,
                    data: dataValues,
                    borderColor: color,
                    backgroundColor: color,
                    borderWidth: 2,
                    pointRadius: 1,
                    tension: 0.1
                };
            });

            trendChart.data.labels = labels;
            trendChart.data.datasets = datasets;
            trendChart.update('none');
        }

        function setupAutoRefresh() {
            if (autoRefreshTimer) {
                clearInterval(autoRefreshTimer);
                autoRefreshTimer = null;
            }

            if (isFrozen) return;

            const intervalMs = parseInt(document.getElementById('interval').value, 10);
            if (intervalMs > 0) {
                autoRefreshTimer = setInterval(() => {
                    fetchChartData();
                }, intervalMs);
            }
        }

        // 🆕 付箋要素（DOM）の生成・ドラッグ移動・削除を管理する関数
        function createMemoElement(x, y, initialText) {
            const memoLayer = document.getElementById('memoLayer');

            const memoEl = document.createElement('div');
            // 付箋自体は常に操作可能 (pointer-events-auto) にする
            memoEl.className = 'absolute pointer-events-auto bg-amber-100 dark:bg-amber-900/95 border-l-4 border-amber-500 p-2 rounded shadow-xl text-xs w-52 cursor-move select-none z-30';
            memoEl.style.left = `${x}px`;
            memoEl.style.top = `${y}px`;

            memoEl.innerHTML = `
                <div class="flex justify-between items-center mb-1 text-amber-900 dark:text-amber-200 font-bold handle">
                    <span>📌 保全メモ付箋</span>
                    <button type="button" class="text-gray-500 hover:text-red-600 dark:hover:text-red-400 font-bold px-1 delete-btn">×</button>
                </div>
                <textarea class="w-full h-16 bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-200 border border-amber-300 dark:border-amber-700 rounded p-1 text-xs resize-none focus:outline-none focus:ring-1 focus:ring-amber-500">${initialText}</textarea>
            `;

            // 削除ボタンの挙動
            memoEl.querySelector('.delete-btn').addEventListener('click', () => {
                memoEl.remove();
            });

            // ドラッグ＆ドロップによる位置調整機能
            let isDragging = false;
            let startX, startY;

            memoEl.addEventListener('mousedown', (e) => {
                if (e.target.tagName.toLowerCase() === 'textarea') return;
                isDragging = true;
                startX = e.clientX - memoEl.offsetLeft;
                startY = e.clientY - memoEl.offsetTop;
                e.stopPropagation();
            });

            document.addEventListener('mousemove', (e) => {
                if (!isDragging) return;
                memoEl.style.left = `${e.clientX - startX}px`;
                memoEl.style.top = `${e.clientY - startY}px`;
            });

            document.addEventListener('mouseup', () => {
                isDragging = false;
            });

            memoLayer.appendChild(memoEl);
        }
    </script>
    @endpush
</x-app-layout>