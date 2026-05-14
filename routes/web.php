<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GoogleCalendarController;
use App\Mail\DailyTaskReminder;
use Illuminate\Support\Facades\Mail;
use App\Models\Task;

Route::get('/login/google', [GoogleCalendarController::class, 'redirectToGoogle'])->name('google.login');
Route::get('/login/google/callback', [GoogleCalendarController::class, 'handleGoogleCallback']);
Route::post('/tasks/{task}/duplicate', [TaskController::class, 'duplicate'])->name('tasks.duplicate');
Route::get('/', function () {
    return redirect()->route('login');
});

// 認証が必要なルート
Route::middleware('auth')->group(function () {
    // カテゴリーのCRUDルート
    Route::resource('categories', CategoryController::class);

    // タスクのCRUDルート（仮ルートから置き換え）
    Route::resource('tasks', TaskController::class);
    Route::patch('/tasks/{task}/toggle', [TaskController::class, 'toggle'])->name('tasks.toggle');
});

Route::get('/test-mail', function () {
    $user = auth()->user();

    if (!$user) {
        return 'ログインしてください。';
    }

    // 💡 修正ポイント: 
    // 1. whereDate() を使うことで、DBが 2026-05-08 12:00:00 でも 05-08 と判定される。
    // 2. now()->toDateString() で「今日の年月日」の文字列(2026-05-08)を生成して比較する。
    $todayTasks = \App\Models\Task::where('user_id', $user->id)
        ->where('is_completed', false)
        ->whereDate('due_date', \Carbon\Carbon::today())
        ->get();

    // デバッグ用：何件取得できたか確認する
    // dump($todayTasks->toArray()); 

    // Google予定（これはテスト用に手動で渡すか、Serviceから取得）
    $testEvents = [
        ['summary' => 'テスト用：今日の打ち合わせ'],
    ];

    Mail::to($user->email)->send(new \App\Mail\DailyTaskReminder($todayTasks, $testEvents));

    return '今日のタスク ' . $todayTasks->count() . ' 件を送信しました！';
})->middleware(['auth']);