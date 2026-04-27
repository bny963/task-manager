<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GoogleCalendarController;

Route::get('/login/google', [GoogleCalendarController::class, 'redirectToGoogle'])->name('google.login');
Route::get('/login/google/callback', [GoogleCalendarController::class, 'handleGoogleCallback']);
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
