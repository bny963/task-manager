<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TaskController extends Controller
{
    /**
     * タスク一覧を取得
     */
    public function index(): AnonymousResourceCollection
    {
        $tasks = Task::with(['category', 'user'])
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return TaskResource::collection($tasks);
    }

    /**
     * タスク詳細を取得
     */
    public function show(Task $task): TaskResource
    {
        $task->load(['category', 'user']);

        return new TaskResource($task);
    }
}
