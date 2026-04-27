<x-app-layout>
    <x-slot name="title">タスク一覧</x-slot>

    {{-- ページ全体の背景色を微調整し、全体を囲う --}}
    <div class="min-h-screen bg-gray-50 py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            
            {{-- ヘッダー部分 --}}
            <div class="flex justify-between items-end mb-10">
                <div>
                    <h1 class="text-4xl font-black text-gray-900 tracking-tight">My Tasks</h1>
                    <p class="text-gray-500 mt-2 font-medium">今日は何を終わらせますか？</p>
                </div>
                <a href="{{ route('tasks.create') }}"
                    class="inline-flex items-center px-5 py-2.5 bg-blue-600 border border-transparent rounded-xl font-bold text-white hover:bg-blue-700 focus:outline-none transition all duration-200 shadow-lg shadow-blue-200 active:transform active:scale-95">
                    <svg xmlns="http://www.w3.org/2000/svg" style="width: 20px; height: 20px; margin-right: 4px;" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    新規タスク
                </a>
            </div>
@if(!auth()->user()->google_access_token)
    <a href="{{ route('google.login') }}"
        class="text-xs bg-white border border-gray-300 px-3 py-1 rounded-lg hover:bg-gray-50 transition-colors font-bold text-gray-600">
        Googleカレンダーと連携
    </a>
@else
    <span class="text-xs text-green-600 font-bold">● Googleカレンダー連携済み</span>
@endif
            {{-- カードグリッド --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                @forelse($tasks as $task)
                                            <div class="flex flex-col bg-white rounded-xl overflow-hidden transition-all duration-300"
                                                style="border: 1px solid #e5e7eb; min-height: 220px; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);"
                                                onmouseover="this.style.boxShadow='0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04)'; this.style.transform='translateY(-4px)'; this.style.borderColor='#3b82f6';"
                                                onmouseout="this.style.boxShadow='0 1px 2px 0 rgba(0, 0, 0, 0.05)'; this.style.transform='translateY(0)'; this.style.borderColor='#e5e7eb';">

                                                {{-- カードメイン --}}
                                                <div class="p-6 flex-grow">
                                                    <div class="flex items-start gap-4">
                                                        {{-- チェックボックス --}}
                                                        <div class="flex items-center" style="height: 28px;">
                                                            <form action="{{ route('tasks.toggle', $task) }}" method="POST" class="flex">
                                                                @csrf
                                                                @method('PATCH')
                                                                <input type="checkbox" onChange="this.form.submit()" {{ $task->is_completed ? 'checked' : '' }}
                                                                    style="width: 20px; height: 20px; cursor: pointer;"
                                                                    class="text-blue-600 rounded-md border-gray-300 focus:ring-blue-500 transition-all">
                                                            </form>
                                                        </div>

                                                        {{-- タイトル --}}
                                                        <div class="flex-1">
                                                            <h2 class="text-xl font-bold" style="line-height: 28px;">
                                                                <a href="{{ route('tasks.show', $task) }}"
                                                                    style="{{ $task->is_completed ? 'text-decoration: line-through; color: #9ca3af;' : '' }}"
                                                                    class="{{ $task->is_completed ? '' : 'text-gray-800' }} group-hover:text-blue-600 break-all transition-colors">
                                                                    {{ $task->title }}
                                                                </a>
                                                            </h2>
                                                        </div>
                                                    </div>
                        {{-- ここから：説明文の追加 --}}
                        @if($task->description)
                            <p class="text-sm text-gray-500 mt-2 line-clamp-2"
                                style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                {{ $task->description }}
                            </p>
                        @endif
                                                    {{-- カテゴリー & 期限 --}}
                                                    <div style="margin-left: 36px;" class="mt-4 space-y-3">
                                                        <div>
                                                            <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-bold bg-gray-100 text-gray-600 border border-gray-200">
                                                                {{ $task->category->name ?? '未分類' }}
                                                            </span>
                                                        </div>

                                                        @if($task->due_date)
                                                            <div class="flex items-center text-sm font-medium">
                                                                <svg xmlns="http://www.w3.org/2000/svg" style="width: 16px; height: 16px; margin-right: 8px;"
                                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                                    class="{{ $task->due_date->isPast() && !$task->is_completed ? 'text-red-500' : 'text-gray-400' }}">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                </svg>
                                                                <span class="{{ $task->due_date->isPast() && !$task->is_completed ? 'text-red-600' : 'text-gray-500' }}">
                                                                    {{ $task->due_date->format('Y/m/d') }}
                                                                </span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>

                                                {{-- カードフッター --}}
                    <div class="px-5 py-3 bg-gray-50 mt-auto"
                        style="border-top: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center;">
                                                    {{-- 優先度バッジ --}}
                                                    <div class="flex items-center gap-2">
                                                        @if($task->priority === 3)
                                                            <span style="background-color: #fee2e2; color: #991b1b; border: 1px solid #fecaca; padding: 3px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 800;">高</span>
                                                        @elseif($task->priority === 2)
                                                            <span style="background-color: #fef9c3; color: #854d0e; border: 1px solid #fef08a; padding: 3px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 800;">中</span>
                                                        @else
                                                            <span style="background-color: #dcfce7; color: #166534; border: 1px solid #bbf7d0; padding: 3px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 800;">低</span>
                                                        @endif
                                                    </div>

                                                    <a href="{{ route('tasks.edit', $task) }}"
                                                        class="text-xs font-bold text-gray-400 hover:text-blue-600 transition-colors uppercase tracking-widest">
                                                        Edit →
                                                    </a>

                                                    {{-- 削除ボタン（フォーム形式） --}}
                                                    <form action="{{ route('tasks.destroy', $task) }}" method="POST" onsubmit="return confirm('本当に削除しますか？');"
                                                        class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-xs font-bold text-gray-400 hover:text-red-600 transition-colors">
                                                            削除
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                @empty
                    <div class="col-span-full py-20 bg-white rounded-3xl border-2 border-dashed border-gray-200 flex flex-col items-center justify-center">
                        <p class="text-gray-400 font-bold text-lg">タスクがありません</p>
                        <a href="{{ route('tasks.create') }}" class="mt-4 text-blue-500 font-bold hover:underline">新しいタスクを追加する</a>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>