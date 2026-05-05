<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\Task;
use App\Services\GoogleCalendarService;

class TaskController extends Controller
{
    /**
     * タスク一覧を表示
     */
    public function index(GoogleCalendarService $calendarService)
    {
        // 1. 既存のタスク取得ロジック
        $tasks = Task::where('user_id', auth()->id())
            ->orderByRaw('is_completed ASC')     // 未完了を上
            ->orderByRaw('due_date IS NULL ASC') // 期限ありを上
            ->orderBy('due_date', 'asc')         // 期限が近い順
            ->orderBy('priority', 'desc')        // 優先度高い順
            ->get();

        // 2. Googleカレンダーデータの取得（安全策を追加）
        $googleEvents = [];

        // ユーザーがトークンを持っている場合のみ実行
        if (auth()->user()->google_access_token) {
            try {
                $googleEvents = $calendarService->getEventsForFullCalendar();
            } catch (\Exception $e) {
                // エラーが起きたらログに記録し、空配列のまま続行
                \Log::error('Google Calendar Error: ' . $e->getMessage());

                // 任意：トークンが無効ならクリアするなどの処理
                if (str_contains($e->getMessage(), 'invalid_grant') || str_contains($e->getMessage(), 're-authentication')) {
                    auth()->user()->update([
                        'google_access_token' => null,
                        'google_refresh_token' => null,
                    ]);
                }
            }
        }

        // 3. 両方のデータをViewへ渡す
        return view('tasks.index', compact('tasks', 'googleEvents'));
    }

    /**
     * タスク作成フォームを表示
     */
    public function create()
    {
        $categories = Category::orderBy('name')->get();

        return view('tasks.create', compact('categories'));
    }

    /**
     * タスクを新規作成
     */
    public function store(Request $request, GoogleCalendarService $calendarService)
    {
        // 1. バリデーションを実行（ここが抜けていました）
        $validated = $request->validate([
            'title' => 'required|max:255',
            'description' => 'nullable',
            'due_date' => 'nullable|date',
            'category_id' => 'required|exists:categories,id',
            'priority' => 'required|integer|min:1|max:3',
        ]);

        // 2. $validated を使って作成
        $task = auth()->user()->tasks()->create($validated);

        // 3. デバッグ用のログ出し
        $hasToken = !empty(auth()->user()->google_access_token);
        $hasDueDate = !empty($task->due_date);

        if ($hasToken && $hasDueDate) {
            $calendarService->createEvent($task);
        } else {
            $reason = "トークン: " . ($hasToken ? 'OK' : 'なし') . " / 期限: " . ($hasDueDate ? 'OK' : 'なし');
            return redirect()->route('tasks.index')->with('error', '同期スキップされました。理由: ' . $reason);
        }

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

        return view('tasks.edit', compact('task', 'categories'));
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
}
