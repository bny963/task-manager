<x-app-layout>
    <x-slot name="title">タスク一覧</x-slot>

    <div id="full-width-container" class="min-h-screen bg-gray-50 py-8 px-4 md:px-10 w-full">
        <div class="w-full max-w-none">

            {{-- 1. ヘッダー --}}
            <div class="flex justify-between items-end mb-10 w-full px-2">
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

            {{-- 2. メインレイアウト --}}
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 w-full items-start">

                {{-- 左側：タスク一覧 (9/12) --}}
                <div class="lg:col-span-8 xl:col-span-9">
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 3xl:grid-cols-5 gap-6">
                        @forelse($tasks as $task)
                            <div
                                class="flex flex-col bg-white rounded-[2rem] border border-gray-200 shadow-sm hover:shadow-2xl transition-all duration-300 group overflow-hidden">

                                {{-- メインコンテンツ --}}
                                <div class="p-7 flex-grow">
                                    <div class="flex items-start gap-4">
                                        <form action="{{ route('tasks.toggle', $task) }}" method="POST">
                                            @csrf @method('PATCH')
                                            <input type="checkbox" onChange="this.form.submit()" {{ $task->is_completed ? 'checked' : '' }}
                                                class="w-6 h-6 text-blue-600 rounded-lg border-gray-300 cursor-pointer focus:ring-blue-500">
                                        </form>
                                        <div class="flex-1 min-w-0">
                                            <h2
                                                class="text-xl font-bold leading-tight break-words {{ $task->is_completed ? 'line-through text-gray-400' : 'text-gray-800' }}">
                                                <a href="{{ route('tasks.show', $task) }}"
                                                    class="hover:text-blue-600">{{ $task->title }}</a>
                                            </h2>
                                            <div class="mt-4 flex flex-wrap gap-2">
                                                <span
                                                    class="px-3 py-1 bg-gray-100 text-gray-600 text-xs font-bold rounded-full border border-gray-200">
                                                    {{ $task->category->name ?? '未分類' }}
                                                </span>
                                                @if($task->priority_label)
                                                    <span
                                                        class="px-3 py-1 bg-orange-50 text-orange-600 text-xs font-bold rounded-full border border-orange-100">
                                                        {{ $task->priority_label }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- 下部：ボタンエリア (ここで削除・編集ボタンを確実に表示) --}}
                                <div
                                    class="px-7 py-5 bg-gray-50 border-t border-gray-100 flex justify-between items-center">
                                    <span class="text-[10px] font-black uppercase tracking-widest text-gray-300">Quick
                                        Actions</span>
                                    <div class="flex items-center gap-5">
                                        <a href="{{ route('tasks.edit', $task) }}"
                                            class="text-sm font-bold text-gray-400 hover:text-blue-600 transition-colors">
                                            編集
                                        </a>
                                        <form action="{{ route('tasks.destroy', $task) }}" method="POST"
                                            onsubmit="return confirm('削除してよろしいですか？');" class="inline">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                class="text-sm font-bold text-gray-400 hover:text-red-600 transition-colors">
                                                削除
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div
                                class="col-span-full py-40 bg-white rounded-[3rem] border-4 border-dashed border-gray-100 text-center">
                                <p class="text-3xl font-black text-gray-200 uppercase tracking-widest">No Active Tasks</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- 右側：カレンダー (3/12) --}}
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
            margin-left: 0 !important;
            margin-right: 0 !important;
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

        /* ウルトラワイド対応 */
        @media (min-width: 2000px) {
            .3xl\:grid-cols-5 {
                grid-template-columns: repeat(5, minmax(0, 1fr)) !important;
            }
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
</x-app-layout>