<x-app-layout>
    <x-slot name="title">タスク登録</x-slot>

    <div class="max-w-4xl mx-auto bg-white rounded-[2.5rem] shadow-sm border border-gray-100 p-10 mt-10">
        <h1 class="text-3xl font-black text-gray-900 mb-8 tracking-tighter">新規タスク登録</h1>

        <form action="{{ route('tasks.store') }}" method="POST">
            @csrf

            {{-- タイトル --}}
            <div class="mb-6">
                <label for="title"
                    class="block text-sm font-black text-gray-700 uppercase tracking-widest mb-2">タイトル</label>
                <input type="text" name="title" id="title" value="{{ old('title') }}"
                    class="w-full px-5 py-4 rounded-2xl border-2 border-gray-100 focus:border-blue-500 focus:ring-0 outline-none transition-all font-bold text-gray-700"
                    placeholder="何をしますか？">
                @error('title')
                    <p class="text-red-500 text-xs mt-2 font-bold">{{ $message }}</p>
                @enderror
            </div>

            {{-- 期限設定（開始日と終了日を横並び） --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                {{-- 開始期限 --}}
                <div>
                    <label for="start_date"
                        class="block text-sm font-black text-gray-700 uppercase tracking-widest mb-2">開始期限</label>
                    <input type="date" name="start_date" id="start_date" value="{{ old('start_date') }}"
                        class="w-full px-5 py-4 rounded-2xl border-2 border-gray-100 focus:border-blue-500 focus:ring-0 outline-none transition-all font-bold text-gray-700">
                    @error('start_date')
                        <p class="text-red-500 text-xs mt-2 font-bold">{{ $message }}</p>
                    @enderror
                </div>

                {{-- 終了期限 --}}
                <div>
                    <label for="due_date"
                        class="block text-sm font-black text-gray-700 uppercase tracking-widest mb-2">終了期限</label>
                    <input type="date" name="due_date" id="due_date" value="{{ old('due_date') }}"
                        class="w-full px-5 py-4 rounded-2xl border-2 border-gray-100 focus:border-blue-500 focus:ring-0 outline-none transition-all font-bold text-gray-700">
                    @error('due_date')
                        <p class="text-red-500 text-xs mt-2 font-bold">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                

                {{-- 優先度 --}}
                <div>
                    <label for="priority"
                        class="block text-sm font-black text-gray-700 uppercase tracking-widest mb-2">優先度</label>
                    <div class="flex gap-4">
                        @foreach([1 => '低', 2 => '中', 3 => '高'] as $value => $label)
                            <label class="flex-1 cursor-pointer">
                                <input type="radio" name="priority" value="{{ $value }}" class="hidden peer" {{ old('priority', 2) == $value ? 'checked' : '' }}>
                                <div
                                    class="py-3 text-center rounded-xl border-2 border-gray-100 font-bold text-gray-400 peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:text-blue-600 transition-all text-sm">
                                    {{ $label }}
                                </div>
                            </label>
                        @endforeach
                    </div>
                    @error('priority')
                        <p class="text-red-500 text-xs mt-2 font-bold">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- カテゴリー & 担当者 を横並びに（もし画面が狭ければ縦に） --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                {{-- カテゴリー --}}
                <div>
                    <label for="category_id"
                        class="block text-sm font-black text-gray-700 uppercase tracking-widest mb-2">カテゴリー</label>
                    <div class="relative">
                        <select name="category_id" id="category_id"
                            class="w-full px-5 py-4 rounded-2xl border-2 border-gray-100 focus:border-blue-500 focus:ring-0 outline-none transition-all font-bold text-gray-700 appearance-none bg-white">
                            <option value="">選択してください</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        {{-- カスタム矢印 --}}
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-5 text-gray-400">
                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z" />
                            </svg>
                        </div>
                    </div>
                    @error('category_id')
                        <p class="text-red-500 text-xs mt-2 font-bold">{{ $message }}</p>
                    @enderror
                </div>
            
                {{-- 担当者 --}}
                <div>
                    <label for="assigned_to"
                        class="block text-sm font-black text-gray-700 uppercase tracking-widest mb-2">担当者</label>
                    <div class="relative">
                        <select name="assigned_to" id="assigned_to"
                            class="w-full px-5 py-4 rounded-2xl border-2 border-gray-100 focus:border-blue-500 focus:ring-0 outline-none transition-all font-bold text-gray-700 appearance-none bg-white">
                            <option value="">担当者なし（未割り当て）</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('assigned_to') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                        {{-- カスタム矢印 --}}
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-5 text-gray-400">
                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z" />
                            </svg>
                        </div>
                    </div>
                    @error('assigned_to')
                        <p class="text-red-500 text-xs mt-2 font-bold">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- 説明 --}}
            <div class="mb-8">
                <label for="description"
                    class="block text-sm font-black text-gray-700 uppercase tracking-widest mb-2">説明</label>
                <textarea name="description" id="description" rows="4"
                    class="w-full px-5 py-4 rounded-2xl border-2 border-gray-100 focus:border-blue-500 focus:ring-0 outline-none transition-all font-bold text-gray-700"
                    placeholder="詳細を入力してください">{{ old('description') }}</textarea>
                @error('description')
                    <p class="text-red-500 text-xs mt-2 font-bold">{{ $message }}</p>
                @enderror
            </div>

            {{-- ボタン --}}
            <div class="flex items-center gap-4">
                <button type="submit"
                    class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-black py-4 rounded-2xl shadow-lg shadow-blue-200 transition-all active:scale-95">
                    タスクを登録する
                </button>
                <a href="{{ route('tasks.index') }}"
                    class="px-8 py-4 bg-gray-100 text-gray-500 font-bold rounded-2xl hover:bg-gray-200 transition-all">
                    戻る
                </a>
            </div>
        </form>
    </div>
</x-app-layout>