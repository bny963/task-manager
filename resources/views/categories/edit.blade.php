<x-app-layout>
    <x-slot name="title">カテゴリー編集</x-slot>
    
    <div class="bg-white rounded-lg shadow-md p-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">カテゴリー編集</h1>
        
        <form action="{{ route('categories.update', $category) }}" method="POST">
            @csrf
            @method('PUT')
            
            {{-- カテゴリー名 --}}
            <div class="mb-6">
                <label for="name" class="block text-gray-700 font-medium mb-2">カテゴリー名</label>
                <input type="text" name="name" id="name" value="{{ old('name', $category->name) }}"
                    class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:border-blue-500">
                @error('name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            {{-- ボタン --}}
            <div class="flex space-x-4">
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                    更新
                </button>
                <a href="{{ route('categories.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                    キャンセル
                </a>
            </div>
        </form>
    </div>
</x-app-layout>
