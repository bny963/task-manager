<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\Task;
use App\Services\GoogleCalendarService;
use App\Models\User;

class TaskController extends Controller
{
    /**
     * タスク一覧を表示
     */

    public function index(Request $request, GoogleCalendarService $calendarService)
    {
        // 0. 絞り込みの選択肢として全カテゴリーと全ユーザーを取得
        $categories = \App\Models\Category::all();
        $users = \App\Models\User::all(); // ★追加：Viewのボタン表示用

        // 1. 既存のタスク取得ロジック
        $query = Task::with(['user', 'category']);

        // --- 絞り込みの適用 ---
        // カテゴリーIDで絞り込み
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // ユーザーIDで絞り込み ★追加
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $tasks = $query->orderByRaw('is_completed ASC')
            ->orderByRaw('due_date IS NULL ASC')
            ->orderBy('due_date', 'asc')
            ->orderBy('priority', 'desc')
            ->get();

        // 2. Googleカレンダーデータの取得
        $googleEvents = [];

        // 判定条件：カテゴリーが未選択 or 仕事(ID:1) 
        // かつ、ユーザーが未選択（全員） or ログインユーザー自身を選択している時
        $isJobCategory = !$request->filled('category_id') || $request->category_id == 1;
        $isSelfOrAll = !$request->filled('user_id') || $request->user_id == auth()->id();

        if ($isJobCategory && $isSelfOrAll) {
            if (auth()->user()->google_access_token) {
                try {
                    $googleEvents = $calendarService->getEventsForFullCalendar();
                } catch (\Exception $e) {
                    \Log::error('Google Calendar Error: ' . $e->getMessage());
                    if (str_contains($e->getMessage(), 'invalid_grant') || str_contains($e->getMessage(), 're-authentication')) {
                        auth()->user()->update([
                            'google_access_token' => null,
                            'google_refresh_token' => null,
                        ]);
                    }
                }
            }
        }

        // 3. 全てのデータをViewへ渡す（usersを追加）
        return view('tasks.index', compact('tasks', 'googleEvents', 'categories', 'users'));
    }

    /**
     * タスク作成フォームを表示
     */
    public function create()
    {
        $categories = Category::orderBy('name')->get();
        $categories = Category::all();
        $users = User::all(); // ユーザー一覧を取得

        return view('tasks.create', compact('categories', 'users'));
    }

    /**
     * タスクを新規作成
     */
    public function store(Request $request, GoogleCalendarService $calendarService)
    {
        // 1. バリデーションを実行
        $validated = $request->validate([
            'title' => 'required|max:255',
            'description' => 'nullable',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:start_date',
            'category_id' => 'required|exists:categories,id',
            'priority' => 'required|integer|min:1|max:3',
        ]);

        // 2. 作成
        $task = auth()->user()->tasks()->create($validated);

        // 3. Googleカレンダー同期ロジック
        if (auth()->user()->google_access_token) {
            if ($task->due_date) {
                try {
                    $calendarService->createEvent($task);
                    return redirect()->route('tasks.index')->with('success', 'タスクを作成し、Googleカレンダーに同期しました！');
                } catch (\Exception $e) {
                    \Log::error('Google Calendar Sync Failed: ' . $e->getMessage());
                    return redirect()->route('tasks.index')->with('warning', 'タスクは作成されましたが、カレンダー同期に失敗しました。');
                }
            }
            // 期限がない場合は同期できないので、普通の成功メッセージ
            return redirect()->route('tasks.index')->with('success', 'タスクを作成しました（期限未設定のため同期なし）。');
        }

        // Google連携していないユーザーにはシンプルな成功メッセージ
        return redirect()->route('tasks.index')->with('success', 'タスクを作成しました！');
    }
    /**
     * タスク詳細を表示
     */
    public function show(Task $task)
    {
        // Policyによる認可チェック
        $this->authorize('view', $task);

        $task->load('category');

        return view('tasks.show', compact('task'));
    }

    /**
     * タスク編集フォームを表示
     */
    public function edit(Task $task)
    {
        // Policyによる認可チェック
        $this->authorize('update', $task);

        $categories = Category::orderBy('name')->get();

        // ★ ここを追加：全ユーザーを取得
        $users = \App\Models\User::orderBy('name')->get();

        // ★ compact に 'users' を追加
        return view('tasks.edit', compact('task', 'categories', 'users'));
    }

    /**
     * タスクを更新
     */
    public function update(TaskRequest $request, Task $task, GoogleCalendarService $calendarService)
    {
        $this->authorize('update', $task);

        // 1. まずDBの値を最新にする
        $task->update($request->validated());

        // 2. 更新後の値を使ってカレンダー同期
        if (auth()->user()->google_access_token && $task->due_date) {
            $calendarService->updateEvent($task);
        }

        return redirect()->route('tasks.index')
            ->with('success', 'タスクを更新し、カレンダーにも反映しました。');
    }

    /**
     * タスクを削除
     */
    public function destroy(Task $task, GoogleCalendarService $calendarService)
    {
        $this->authorize('delete', $task);

        // デバッグログ: ここが実行されているか確認
        \Log::info('Destroy method called for Task ID: ' . $task->id);

        $user = auth()->user();

        // 条件判定を一つずつチェック
        if ($user->google_access_token) {
            if ($task->google_calendar_event_id) {
                \Log::info('Attempting to delete Google Event ID: ' . $task->google_calendar_event_id);
                $calendarService->deleteEvent($task);
            } else {
                \Log::warning('Task has no Google Event ID. Skipping Google delete.');
            }
        } else {
            \Log::warning('User has no Google Access Token. Skipping Google delete.');
        }

        // 最後にDBから削除
        $task->delete();

        return redirect()->route('tasks.index')
            ->with('success', 'タスクを削除しました。');
    }
    public function toggle(Task $task)
    {
        // 現在の値を反転させる (! を使うと true ↔ false が入れ替わります)
        $task->update([
            'is_completed' => !$task->is_completed,
        ]);

        return back()->with('success', 'タスクのステータスを更新しました。');
    }
    public function duplicate(Task $task)
    {
        // 1. 既存のタスクをコピー（メモリ上）
        $newTask = $task->replicate();

        // 2. タイトルに「(コピー)」を付与して分かりやすくする
        $newTask->title = $task->title . ' (コピー)';

        // 3. 未完了状態で保存
        $newTask->is_completed = false;

        // 4. Googleカレンダー連携IDなどはリセット（新しい予定として扱うため）
        if (isset($newTask->google_calendar_event_id)) {
            $newTask->google_calendar_event_id = null;
        }

        $newTask->save();

        return redirect()->route('tasks.index')->with('success', 'タスクを複製しました！');
    }
}
