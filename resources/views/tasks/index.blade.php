<x-app-layout>
    <x-slot name="title">タスク一覧</x-slot>
    
    <div class="bg-white rounded-lg shadow-md p-6">
        {{-- ヘッダー --}}
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">タスク一覧</h1>
            <a href="{{ route('tasks.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                新規登録
            </a>
        </div>
        
        {{-- タスクリスト --}}
@forelse($tasks as $task)
                <div class="border-b border-gray-200 py-4 last:border-b-0">
                    <div class="flex justify-between items-start">
                        <div class="flex items-start">
                            {{-- チェックボックス --}}
                            <form action="{{ route('tasks.toggle', $task) }}" method="POST" class="mr-3 mt-1">
                                @csrf
                                @method('PATCH')
                                <input type="checkbox" onChange="this.form.submit()" {{ $task->is_completed ? 'checked' : '' }}
                                    class="w-5 h-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500 cursor-pointer">
                            </form>

                            <div>
                                <h2 class="text-lg font-semibold">
                                    {{-- style属性を使って確実に線を引く --}}
                                    <a href="{{ route('tasks.show', $task) }}"
                                        style="{{ $task->is_completed ? 'text-decoration: line-through; color: #9ca3af;' : '' }}"
                                        class="hover:text-blue-500 {{ $task->is_completed ? '' : 'text-gray-800' }}">
                                        {{ $task->title }}
                                    </a>
                                </h2>

                                <p class="text-gray-600 text-sm mt-1">
                                    カテゴリー: {{ $task->category->name ?? '未分類' }}
                                </p>

                            {{-- 期限の表示 (アイコン付き) --}}
                            @if($task->due_date)
                                <div class="flex items-center mt-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                        style="width: 14px; height: 14px; margin-right: 4px;"
                                        class="{{ $task->due_date->isPast() && !$task->is_completed ? 'text-red-500' : 'text-gray-400' }}">
                                        <path
                                            d="M12.75 12.75a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM7.5 15.75a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5ZM8.25 17.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM9.75 15.75a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5ZM10.5 17.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM12 15.75a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5ZM12.75 17.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM14.25 15.75a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5ZM15 17.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM16.5 15.75a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5ZM15 12.75a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM16.5 13.5a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Z" />
                                        <path fill-rule="evenodd"
                                            d="M6.75 2.25A.75.75 0 0 1 7.5 3v1.5h9V3a.75.75 0 0 1 1.5 0v1.5h.75a3 3 0 0 1 3 3v12.75a3 3 0 0 1-3 3H5.25a3 3 0 0 1-3-3V7.5a3 3 0 0 1 3-3h.75V3a.75.75 0 0 1 .75-.75ZM3.75 7.5v12.75c0 .828.672 1.5 1.5 1.5H18.75c.828 0 1.5-.672 1.5-1.5V7.5H3.75Z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span
                                        class="text-xs {{ $task->due_date->isPast() && !$task->is_completed ? 'text-red-600 font-bold' : 'text-gray-500' }}">
                                        期限: {{ $task->due_date->format('Y/m/d') }}
                                    </span>
                                </div>
                            @endif

    {{-- 優先度の表示 --}}
    <div class="mt-2 flex">
        @if($task->priority === 3)
            {{-- 高：赤 --}}
            <span
                style="background-color: #fee2e2; color: #991b1b; border: 1px solid #fecaca; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: bold;">
                高
            </span>
        @elseif($task->priority === 2)
            {{-- 中：黄色 --}}
            <span
                style="background-color: #fef9c3; color: #854d0e; border: 1px solid #fef08a; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: bold;">
                中
            </span>
        @else
            {{-- 低：緑 --}}
            <span
                style="background-color: #dcfce7; color: #166534; border: 1px solid #bbf7d0; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: bold;">
                低
            </span>
        @endif
    </div>

                        <a href="{{ route('tasks.edit', $task) }}"
                            class="bg-gray-500 hover:bg-gray-600 text-white px-3 py-1 rounded text-sm">
                            編集
                        </a>
                    </div>
                </div>
@empty
    <p class="text-center text-gray-500 py-8">タスクがありません。</p>
@endforelse
    </div>
</x-app-layout>
