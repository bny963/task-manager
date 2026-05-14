<x-app-layout>
    <x-slot name="title">タスク一覧</x-slot>

@php
$month = now()->month;
$season = match (true) {
    in_array($month, [3, 4, 5]) => 'spring',
    in_array($month, [6, 7, 8]) => 'summer',
    in_array($month, [9, 10, 11]) => 'autumn',
    default => 'winter',
};
$selectedUserId = request('user_id');
@endphp

    <div id="full-width-container" class="min-h-screen py-8 px-4 md:px-10 w-full season-bg bg-{{ $season }}">
        <div class="w-full max-w-none">

            {{-- 1. ヘッダー --}}
            <div class="flex flex-col mb-10 w-full px-2">
                <div class="flex justify-between items-end w-full">
                    <div>
                        <h1 class="text-5xl font-black text-gray-900 tracking-tighter text-shadow-sm">My Tasks</h1>
                        <p class="text-gray-500 mt-2 font-medium">
                            @if(auth()->user()->google_access_token)
                                <span class="text-green-600 font-bold">● Google Calendar Sync Active</span>
                            @else
                                <a href="{{ route('google.login') }}"
                                    class="text-blue-600 hover:underline font-bold">Google連携して予定を表示</a>
                            @endif
                        </p>
                    </div>
                    <a href="{{ route('tasks.create') }}"
                        class="px-8 py-4 bg-blue-600 text-white rounded-2xl font-black shadow-xl hover:bg-blue-700 transition-all active:scale-95 text-lg">
                        ＋ 新規タスク
                    </a>
                </div>

                {{-- カテゴリー絞り込みタブ --}}
                <div class="mt-8 flex flex-wrap gap-2">
                    {{-- ALLボタン --}}
                    <a href="{{ route('tasks.index') }}"
                        class="px-5 py-2 rounded-full text-[10px] font-black uppercase tracking-widest transition-all shadow-sm {{ !request('category_id') ? 'bg-gray-900 text-white scale-105' : 'bg-white text-gray-400 border border-gray-200 hover:border-gray-900 hover:text-gray-900' }}">
                        ALL
                    </a>

                    {{-- 各カテゴリーボタン --}}
                    @foreach($categories as $category)
                        <a href="{{ route('tasks.index', ['category_id' => $category->id]) }}"
                            class="px-5 py-2 rounded-full text-[10px] font-black uppercase tracking-widest transition-all shadow-sm {{ request('category_id') == $category->id ? 'bg-blue-600 text-white scale-105' : 'bg-white text-gray-400 border border-gray-200 hover:border-blue-600 hover:text-blue-600' }}">
                            {{ $category->name }}
                        </a>
                    @endforeach
                </div>
            </div>

<div class="mt-6 flex flex-col gap-3">
    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-2">Filter by User</span>
    <div class="flex flex-wrap gap-3">
        {{-- ALL（解除）ボタン --}}
        <a href="{{ route('tasks.index', request()->except('user_id')) }}"
            class="flex items-center gap-2 px-4 py-2 rounded-full border transition-all {{ !$selectedUserId ? 'bg-gray-900 text-white border-gray-900 shadow-md' : 'bg-white text-gray-600 border-gray-200 hover:border-gray-900' }}">
            <span class="text-xs font-bold">全員</span>
        </a>

        @foreach($users as $user)
            <a href="{{ route('tasks.index', array_merge(request()->query(), ['user_id' => $user->id])) }}"
                class="flex items-center gap-2 px-2 py-1.5 rounded-full border transition-all {{ $selectedUserId == $user->id ? 'bg-blue-600 text-white border-blue-600 shadow-md' : 'bg-white text-gray-600 border-gray-200 hover:border-blue-600' }}">
                {{-- ユーザーアイコン --}}
                <div
                    class="w-7 h-7 rounded-full bg-gray-100 flex items-center justify-center overflow-hidden border border-white">
                    @if($user->profile_photo_url)
                        <img src="{{ $user->profile_photo_url }}" class="w-full h-full object-cover">
                    @else
                        <span class="text-[10px] font-black uppercase">{{ mb_substr($user->name, 0, 1) }}</span>
                    @endif
                </div>
                <span class="text-xs font-bold pr-2">{{ $user->name }}</span>
            </a>
        @endforeach
    </div>
</div>

            {{-- 2. グラフ表示セクション --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10">
                {{-- 進捗円グラフ --}}
                <div
                    class="bg-white p-6 rounded-[2rem] border border-gray-200 shadow-sm h-64 flex flex-col items-center">
                    <h3 class="text-sm font-black text-gray-400 uppercase tracking-widest mb-2">Progress</h3>
                    <canvas id="progressChart"></canvas>
                </div>

                {{-- カテゴリー別棒グラフ --}}
                <div
                    class="bg-white p-6 rounded-[2rem] border border-gray-200 shadow-sm h-64 flex flex-col items-center">
                    <h3 class="text-sm font-black text-gray-400 uppercase tracking-widest mb-2">Categories</h3>
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>

            {{-- 2. メインレイアウト --}}
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 w-full items-start">
            
                {{-- 左側：タスク一覧 --}}
                <div class="lg:col-span-8 xl:col-span-9">
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 3xl:grid-cols-5 gap-6 items-stretch">
                        @forelse($tasks as $task)
                                                                            @php
                            $start = null;
                            $due = null;
                            $isGoogleEvent = !($task instanceof \App\Models\Task);
                            $isMyTask = !$isGoogleEvent && $task->user_id === auth()->id();
                            if (!$isGoogleEvent) {
                                // 1. 通常のタスク（DB保存分）
                                $start = $task->start_date;
                                $due = $task->due_date;
                            } else {
                                // 2. Googleカレンダーの予定
                                // 開始日の取得
                                $rawStart = data_get($task, 'start.date') ?? data_get($task, 'start.dateTime') ?? data_get($task, 'start_date');
                                // 終了日の取得
                                $rawDue = data_get($task, 'end.date') ?? data_get($task, 'end.dateTime') ?? data_get($task, 'due_date');

                                if ($rawStart) {
                                    $start = \Carbon\Carbon::parse($rawStart);
                                }

                                if ($rawDue) {
                                    $due = \Carbon\Carbon::parse($rawDue);
                                    // Googleの終日予定（YYYY-MM-DD形式）は終了日が「翌日の0時」になる仕様なので補正
                                    if (is_string($rawDue) && strlen($rawDue) <= 10) {
                                        $due = $due->subDay();
                                    }
                                }
                            }

                            // --- 判定ロジック ---
                            $isCompleted = data_get($task, 'is_completed', false);
                            $todayStr = now()->format('Y-m-d');
                            $dueStr = $due ? $due->format('Y-m-d') : null;

                            $isOverdue = $dueStr && ($dueStr < $todayStr) && !$isCompleted;
                            $isToday = $dueStr && ($dueStr === $todayStr) && !$isCompleted;

                            // デザイン決定
                            $badgeClass = 'bg-blue-50 text-blue-600 border-blue-100'; // デフォルト(青)
                            $labelPrefix = '📅';

                            if ($isOverdue) {
                                $badgeClass = 'bg-red-600 text-white border-red-700 animate-pulse'; // 赤（期限切れ）
                                $labelPrefix = '⚠️ 期限切れ';
                            } elseif ($isToday) {
                                $badgeClass = 'bg-red-50 text-red-600 border-red-200 shadow-sm'; // 薄赤（今日）
                                $labelPrefix = '🔥 今日まで';
                            }
                                                                            @endphp
                                                                            <div
                                                                                class="flex flex-col bg-white rounded-[2rem] border border-gray-200 shadow-sm hover:shadow-2xl transition-all duration-300 group relative overflow-hidden h-full">

                                                                                {{-- 右上：ユーザーアイコン（絶対配置） --}}
                                                                                <div class="absolute top-6 right-6">
                                                                                    @php
                            $displayName = '';
                            $bgColor = 'bg-gray-100';
                            $textColor = 'text-gray-500';

                            // 注意：ここで Auth::user()->name を使ってしまうと全員自分になってしまいます
                            if (!$isGoogleEvent) {
                                // 1. 自社タスクの場合：$taskに紐付いている user モデルから取得
                                // ※ $task->user が null の場合は「未設定」とする
                                $displayName = optional($task->user)->name ?? '未設定';
                                $bgColor = 'bg-blue-50';
                                $textColor = 'text-blue-600';
                            } else {
                                // 2. Googleイベントの場合
                                // creator（作成者）または organizer（主催者）から取得
                                $displayName = data_get($task, 'creator.displayName')
                                    ?? data_get($task, 'organizer.displayName')
                                    ?? data_get($task, 'creator.email')
                                    ?? 'Google予定';

                                $bgColor = 'bg-green-50';
                                $textColor = 'text-green-600';
                            }
                                                                                    @endphp

                                                                                        <div class="flex flex-col items-end gap-1">
                                                                                            <div class="w-8 h-8 rounded-full {{ $bgColor }} flex items-center justify-center text-[10px] font-black {{ $textColor }} border border-current shadow-sm overflow-hidden"
                                                                                                title="{{ $displayName }}">
                                                                                                @if(!$isGoogleEvent && optional($task->user)->profile_photo_url)
                                                                                                    <img src="{{ $task->user->profile_photo_url }}"
                                                                                                        class="w-full h-full object-cover">
                                                                                                @else
                                                                                                    {{ mb_substr($displayName, 0, 1) }}
                                                                                                @endif
                                                                                            </div>

                                                                                            {{-- ここで表示される名前が「担当者」です --}}
                                                                                            <span
                                                                                                class="text-[8px] {{ $textColor }} font-bold leading-none bg-white/80 px-1 rounded">
                                                                                                {{ $displayName }}
                                                                                            </span>
                                                                                        </div>
                                                                                    </div>

                                                                                    {{-- メインコンテンツ --}}
                                                                                    <div class="p-7 flex-grow">
                                                                                        <div class="flex items-start gap-4">
                                                                                            {{-- チェックボックス --}}
                                                                                            <div class="flex-shrink-0 pt-1">
                                                                                                <form action="{{ route('tasks.toggle', $task) }}" method="POST">
                                                                                                    @csrf @method('PATCH')
                                                                                                    <input type="checkbox" onChange="this.form.submit()" {{ $task->is_completed ? 'checked' : '' }}
                                                                                                        class="w-6 h-6 text-blue-600 rounded-lg border-gray-300 cursor-pointer focus:ring-blue-500 transition-transform hover:scale-110">
                                                                                                </form>
                                                                                            </div>

                                                                                            {{-- テキストコンテンツ --}}
                                                                                            <div class="flex-1 min-w-0 pr-8"> {{-- アイコンと重ならないよう右余白を確保 --}}
                                                                                                <h2
                                                                                                    class="text-xl font-bold leading-tight break-words {{ $task->is_completed ? 'line-through text-gray-400' : 'text-gray-800' }}">
                                                                                                    <a href="{{ route('tasks.show', $task) }}"
                                                                                                        class="hover:text-blue-600 transition-colors">
                                                                                                        {{ $task->title }}
                                                                                                    </a>
                                                                                                </h2>

                                                                                                {{-- ラベルエリア --}}
                                                                                                <div class="mt-4 flex flex-wrap gap-2 items-center">
                                                                                                    <span
                                                                                                        class="px-3 py-1 bg-gray-100 text-gray-600 text-[10px] font-black uppercase rounded-lg border border-gray-200">
                                                                                                        {{ $task->category->name ?? '未分類' }}
                                                                                                    </span>

                                                                                                    @if($task->priority_label)
                                                                                                        <span
                                                                                                            class="px-3 py-1 bg-orange-50 text-orange-600 text-[10px] font-black uppercase rounded-lg border border-orange-100">
                                                                                                            {{ $task->priority_label }}
                                                                                                        </span>
                                                                                                    @endif

                                                                                                    {{-- 期限表示エリア --}}
                                                                                                    @if($start || $due)
                                                                                                        <span
                                                                                                            class="px-3 py-1 text-[10px] font-black uppercase rounded-lg border {{ $badgeClass }} flex items-center gap-1">
                                                                                                            <span>{{ $labelPrefix }}</span>

                                                                                                            @if($start && $due)
                                                                                                                {{ $start->format('m/d') }} - {{ $due->format('m/d') }}
                                                                                                            @elseif($start)
                                                                                                                {{ $start->format('m/d') }} 〜
                                                                                                            @elseif($due)
                                                                                                                〜 {{ $due->format('m/d') }}
                                                                                                            @endif
                                                                                                        </span>
                                                                                                    @endif
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>

                                                                                    {{-- 下部：ボタンエリア --}}
                                                                                    @if($isMyTask)
                                                                                    <div
                                                                                        class="px-7 py-5 bg-gray-50 border-t border-gray-100 flex justify-between items-center mt-auto">
                                                                                        <div class="flex items-center gap-4">
                                                                                            {{-- コピーボタン --}}
                                                                                            <form action="{{ route('tasks.duplicate', $task) }}" method="POST" class="inline">
                                                                                                @csrf
                                                                                                <button type="submit"
                                                                                                    class="text-xs font-black uppercase tracking-widest text-gray-400 hover:text-green-600 transition-colors">
                                                                                                    Copy
                                                                                                </button>
                                                                                            </form>
                                                                                            <a href="{{ route('tasks.edit', $task) }}"
                                                                                                class="text-xs font-black uppercase tracking-widest text-gray-400 hover:text-blue-600 transition-colors">
                                                                                                Edit
                                                                                            </a>
                                                                                        </div>
                                                                                        <form action="{{ route('tasks.destroy', $task) }}" method="POST"
                                                                                            onsubmit="return confirm('削除してよろしいですか？');" class="inline">
                                                                                            @csrf @method('DELETE')
                                                                                            <button type="submit"
                                                                                                class="text-xs font-black uppercase tracking-widest text-gray-400 hover:text-red-600 transition-colors">
                                                                                                Delete
                                                                                            </button>
                                                                                        </form>
                                                                                    </div>
                                                                                    @else
                                                                                        {{-- 他人のタスクの場合は、ボタンエリアを表示しないか、別の情報を出す --}}
                                                                                        <div class="px-7 py-3 bg-gray-50/50 border-t border-gray-100 flex justify-end items-center mt-auto">
                                                                                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Read Only</span>
                                                                                        </div>
                                                                                    @endif
                                                                                </div>
                        @empty
                            <div
                                class="col-span-full py-40 bg-white rounded-[3rem] border-4 border-dashed border-gray-100 text-center">
                                <p class="text-3xl font-black text-gray-200 uppercase tracking-widest">No Active Tasks</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- 右側：カレンダー --}}
                <div class="lg:col-span-4 xl:col-span-3 sticky top-8">
                    <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-gray-200">
                        <div class="flex items-center justify-between mb-8">
                            <h2 class="text-2xl font-black text-gray-900 tracking-tighter">Schedule</h2>
                        </div>
                        <div id="calendar-wrapper" class="w-full">
                            <div id="calendar"></div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <style>
        /* 物理的に幅制限を解除 */
        html,
        body,
        main,
        #full-width-container {
            width: 100% !important;
            max-width: none !important;
            margin: 0 !important;
        }
    
        /* カレンダーの微調整 */
        #calendar {
            font-size: 0.85rem;
            min-height: 500px;
        }
    
        .fc .fc-toolbar-title {
            font-size: 1rem !important;
            font-weight: 900;
        }
    
        .fc .fc-button {
            border-radius: 12px !important;
            text-transform: capitalize;
        }
    
        /* グリッドの列を強制固定 */
        @media (min-width: 2000px) {
            .3xl\:grid-cols-5 {
                grid-template-columns: repeat(5, minmax(0, 1fr)) !important;
            }
        }
    
        /* 春：桜色と柔らかなピンクのグラデーション */
        .bg-spring {
            background: linear-gradient(135deg, #fff5f7 0%, #fce4ec 100%);
            background-image: url('https://images.unsplash.com/photo-1522383225653-ed111181a951?auto=format&fit=crop&q=80&w=2000');
        }
    
        /* 夏：爽やかな青空と海 */
        .bg-summer {
            background: linear-gradient(135deg, #e0f2f1 0%, #b2dfdb 100%);
            background-image: url('https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&q=80&w=2000');
        }
    
        /* 秋：紅葉と落ち着いたオレンジ */
        .bg-autumn {
            background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);
            background-image: url('https://images.unsplash.com/photo-1507783548227-544c3b8fc065?auto=format&fit=crop&q=80&w=2000');
        }
    
        /* 冬：静かな雪景色とグレーブルー */
        .bg-winter {
            background: linear-gradient(135deg, #eceff1 0%, #cfd8dc 100%);
            background-image: url('https://images.unsplash.com/photo-1483664852095-d6cc6870702d?auto=format&fit=crop&q=80&w=2000');
        }
    
        /* 背景の共通設定（画像を見やすくし、コンテンツを邪魔しないようにする） */
        .season-bg {
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            /* スクロールしても背景を固定 */
            background-blend-mode: overlay;
            /* 背景色と画像を馴染ませる */
        }
    </style>

    @push('scripts')
        <script>
            window.onload = function () {
                const calendarEl = document.getElementById('calendar');
                if (calendarEl && typeof window.initCalendar === 'function') {
                    window.initCalendar(calendarEl, @json($googleEvents ?? []));
                }
            };
        </script>
    @endpush
    {{-- Chart.js の導入 --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // PHPからデータを取得（完了・未完了の数）
            const completedCount = {{ $tasks->where('is_completed', true)->count() }};
            const pendingCount = {{ $tasks->where('is_completed', false)->count() }};

            // --- 進捗円グラフ ---
            new Chart(document.getElementById('progressChart'), {
                type: 'doughnut',
                data: {
                    labels: ['Completed', 'Pending'],
                    datasets: [{
                        data: [completedCount, pendingCount],
                        backgroundColor: ['#2563eb', '#e5e7eb'],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    cutout: '70%',
                    plugins: {
                        legend: { position: 'bottom', labels: { font: { weight: 'bold' } } }
                    }
                }
            });

            // --- カテゴリー棒グラフ ---
            @php
// カテゴリーごとの集計
$categoryData = $tasks->groupBy(function ($task) {
    return $task instanceof \App\Models\Task ? ($task->category->name ?? 'Uncategorized') : 'Google';
})->map->count();
            @endphp

            new Chart(document.getElementById('categoryChart'), {
                type: 'bar',
                data: {
                    labels: {!! json_encode($categoryData->keys()) !!},
                    datasets: [{
                        label: 'Tasks',
                        data: {!! json_encode($categoryData->values()) !!},
                        backgroundColor: '#3b82f6',
                        borderRadius: 8
                    }]
                },
                options: {
                    scales: {
                        y: { beginAtZero: true, grid: { display: false } },
                        x: { grid: { display: false } }
                    },
                    plugins: { legend: { display: false } }
                }
            });
    });
        </script>

{{-- 固定配置の新規作成ボタン --}}
<a href="{{ route('tasks.create') }}" 
   class="fixed bottom-10 right-10 z-50 flex items-center justify-center w-16 h-16 bg-blue-600 text-white rounded-full shadow-2xl hover:bg-blue-700 hover:scale-110 transition-all active:scale-95 group"
   title="新規タスクを作成">
    {{-- プラスアイコン --}}
    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4" />
    </svg>
    
    {{-- ホバー時にテキストを出す（お好みで） --}}
    <span class="absolute right-20 bg-gray-900 text-white text-xs px-3 py-1.5 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">
        新規タスクを追加
    </span>
</a>

</x-app-layout>