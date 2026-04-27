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

                    <div class="flex items-center mt-2">
                        <span class="px-2 py-1 text-xs rounded 
                                @if($task->priority === 3) bg-red-100 text-red-800
                                @elseif($task->priority === 2) bg-yellow-100 text-yellow-800
                                @else bg-green-100 text-green-800
                                @endif">
                            @if($task->priority === 3) 高 @elseif($task->priority === 2) 中 @else 低 @endif
                        </span>
                    </div>
                </div>
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
