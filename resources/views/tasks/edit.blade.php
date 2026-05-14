<x-app-layout>
    <x-slot name="title">タスク編集</x-slot>
    
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">タスク編集</h1>
            {{-- 担当者の現在のアイコンを表示（視覚的補助） --}}
            @if($task->user)
                <div class="flex items-center gap-2 px-3 py-1 bg-gray-50 rounded-full border border-gray-200">
                    <span class="text-xs text-gray-500">現在の担当:</span>
                    <div class="w-6 h-6 rounded-full bg-blue-500 flex items-center justify-center text-[10px] text-white font-bold">
                        {{ mb_substr($task->user->name, 0, 1) }}
                    </div>
                    <span class="text-sm font-medium text-gray-700">{{ $task->user->name }}</span>
                </div>
            @endif
        </div>
        
        <form action="{{ route('tasks.update', $task) }}" method="POST">
            @csrf
            @method('PUT')
            
            {{-- タイトル --}}
            <div class="mb-4">
                <label for="title" class="block text-gray-700 font-medium mb-2">タイトル</label>
                <input type="text" name="title" id="title" value="{{ old('title', $task->title) }}"
                    class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:border-blue-500">
                @error('title')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- カテゴリー --}}
                <div class="mb-4">
                    <label for="category_id" class="block text-gray-700 font-medium mb-2">カテゴリー</label>
                    <select name="category_id" id="category_id"
                        class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:border-blue-500">
                        <option value="">選択してください</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id', $task->category_id) == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- ★担当者（ユーザー）の選択を追加★ --}}
                <div class="mb-4">
                    <label for="user_id" class="block text-gray-700 font-medium mb-2">担当者</label>
                    <select name="user_id" id="user_id"
                        class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:border-blue-500">
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('user_id', $task->user_id) == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} {{ $user->id == auth()->id() ? '（自分）' : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('user_id')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            {{-- 開始日 --}}
            <div>
                <label for="start_date" class="block text-gray-700 font-medium mb-2">開始日</label>
                <input type="date" name="start_date" id="start_date"
                    value="{{ old('start_date', optional($task->start_date)->format('Y-m-d')) }}"
                    class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:border-blue-500">
                @error('start_date')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            {{-- 終了期限 --}}
            <div>
                <label for="due_date" class="block text-gray-700 font-medium mb-2">終了期限</label>
                <input type="date" name="due_date" id="due_date"
                    value="{{ old('due_date', optional($task->due_date)->format('Y-m-d')) }}"
                    class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:border-blue-500">
                @error('due_date')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            </div>

            {{-- 優先度 --}}
            <div class="mb-4">
                <label for="priority" class="block text-gray-700 font-medium mb-2">優先度</label>
                <select name="priority" id="priority"
                    class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:border-blue-500">
                    <option value="1" {{ old('priority', $task->priority) == 1 ? 'selected' : '' }}>低</option>
                    <option value="2" {{ old('priority', $task->priority) == 2 ? 'selected' : '' }}>中</option>
                    <option value="3" {{ old('priority', $task->priority) == 3 ? 'selected' : '' }}>高</option>
                </select>
                @error('priority')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            {{-- 説明 --}}
            <div class="mb-6">
                <label for="description" class="block text-gray-700 font-medium mb-2">説明</label>
                <textarea name="description" id="description" rows="5"
                    class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:border-blue-500">{{ old('description', $task->description) }}</textarea>
                @error('description')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            {{-- ボタン --}}
            <div class="flex space-x-4">
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded font-bold shadow-sm transition-colors">
                    更新
                </button>
                <a href="{{ route('tasks.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded font-bold shadow-sm transition-colors">
                    キャンセル
                </a>
            </div>
        </form>
    </div>
</x-app-layout>