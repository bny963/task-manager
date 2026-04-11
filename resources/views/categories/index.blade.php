<x-app-layout>
    <x-slot name="title">カテゴリー一覧</x-slot>
    
    <div class="bg-white rounded-lg shadow-md p-6">
        {{-- ヘッダー --}}
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">カテゴリー一覧</h1>
            <a href="{{ route('categories.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                新規登録
            </a>
        </div>
        
        {{-- カテゴリーリスト --}}
        @forelse($categories as $category)
            <div class="border-b border-gray-200 py-4 last:border-b-0">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800">
                            {{ $category->name }}
                        </h2>
                        <p class="text-gray-500 text-sm mt-1">
                            タスク数: {{ $category->tasks_count ?? $category->tasks->count() }}件
                        </p>
                    </div>
                    <div class="flex space-x-2">
                        <a href="{{ route('categories.edit', $category) }}" class="bg-gray-500 hover:bg-gray-600 text-white px-3 py-1 rounded text-sm">
                            編集
                        </a>
                        <form action="{{ route('categories.destroy', $category) }}" method="POST" onsubmit="return confirm('本当に削除しますか？');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm">
                                削除
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <p class="text-center text-gray-500 py-8">
                カテゴリーがありません。「新規登録」ボタンからカテゴリーを追加してください。
            </p>
        @endforelse
    </div>
</x-app-layout>
