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
                    <div>
                        <h2 class="text-lg font-semibold">
                            <a href="{{ route('tasks.show', $task) }}" class="text-gray-800 hover:text-blue-500">
                                {{ $task->title }}
                            </a>
                        </h2>
                        <p class="text-gray-600 text-sm mt-1">
                            カテゴリー: {{ $task->category->name ?? '未分類' }}
                        </p>
                        <div class="flex items-center mt-2">
                            {{-- 優先度表示 --}}
                            <span class="px-2 py-1 text-xs rounded 
                                @if($task->priority === 3) bg-red-100 text-red-800
                                @elseif($task->priority === 2) bg-yellow-100 text-yellow-800
                                @else bg-green-100 text-green-800
                                @endif">
                                @if($task->priority === 3) 高
                                @elseif($task->priority === 2) 中
                                @else 低
                                @endif
                            </span>
                        </div>
                    </div>
                    <a href="{{ route('tasks.edit', $task) }}" class="bg-gray-500 hover:bg-gray-600 text-white px-3 py-1 rounded text-sm">
                        編集
                    </a>
                </div>
            </div>
        @empty
            <p class="text-center text-gray-500 py-8">
                タスクがありません。「新規登録」ボタンからタスクを追加してください。
            </p>
        @endforelse
    </div>
</x-app-layout>
