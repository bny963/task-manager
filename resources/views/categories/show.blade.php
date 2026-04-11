<x-app-layout>
    <x-slot name="title">{{ $category->name }}</x-slot>

    <div class="bg-white rounded-lg shadow-md p-6">
        {{-- ヘッダー --}}
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">{{ $category->name }}</h1>
            <a href="{{ route('categories.index') }}" class="text-blue-500 hover:text-blue-600">
                ← 一覧に戻る
            </a>
        </div>

        {{-- このカテゴリーのタスク一覧 --}}
        <h2 class="text-lg font-semibold text-gray-700 mb-4">
            このカテゴリーのタスク（{{ $category->tasks->count() }}件）
        </h2>

        @forelse($category->tasks as $task)
            <div class="border-b border-gray-200 py-3">
                <a href="{{ route('tasks.show', $task) }}" class="text-blue-500 hover:text-blue-600">
                    {{ $task->title }}
                </a>
                <span class="ml-2 text-sm text-gray-500">
                    優先度: {{ $task->priority_label }}
                </span>
            </div>
        @empty
            <p class="text-gray-500">このカテゴリーにはタスクがありません。</p>
        @endforelse
    </div>
</x-app-layout>
