<?php

namespace App\Http\Controllers;

use App\Services\GoogleCalendarService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GoogleCalendarController extends Controller
{
    protected $calendarService;

    public function __construct(GoogleCalendarService $calendarService)
    {
        $this->calendarService = $calendarService;
    }

    // Google認証画面へリダイレクト
    public function redirectToGoogle()
    {
        return redirect($this->calendarService->getAuthUrl());
    }

    // Googleからのコールバック処理
    public function handleGoogleCallback(Request $request, GoogleCalendarService $calendarService)
    {
        if ($request->has('code')) {
            // ここでしっかり Service クラスの authenticate を呼んでいるか？
            $calendarService->authenticate($request->get('code'));

            return redirect()->route('tasks.index')->with('success', 'Google連携に成功しました！');
        }

        return redirect()->route('tasks.index')->with('error', '認証コードが見つかりません。');
    }
    public function createEvent($task)
    {
        $user = auth()->user();

        // ログ出力（storage/logs/laravel.log に記録されます）
        \Log::info('Google Calendar Sync Started for Task: ' . $task->title);

        if (!$user->google_access_token) {
            \Log::error('No Access Token found for User: ' . $user->id);
            return;
        }

        $this->client->setAccessToken($user->google_access_token);

        // トークンリフレッシュのログ
        if ($this->client->isAccessTokenExpired()) {
            \Log::info('Token expired, refreshing...');
            $newToken = $this->client->fetchAccessTokenWithRefreshToken($user->google_refresh_token);
            $user->update(['google_access_token' => $newToken['access_token']]);
        }

        try {
            $calendar = new \Google\Service\Calendar($this->client);

            $event = new Event([
                'summary' => $task->title,
                'description' => $task->description,
                'start' => ['date' => $task->due_date->format('Y-m-d'), 'timeZone' => 'Asia/Tokyo'],
                'end' => ['date' => $task->due_date->format('Y-m-d'), 'timeZone' => 'Asia/Tokyo'],
            ]);

            $createdEvent = $calendar->events->insert('primary', $event);

            \Log::info('Event Created Successfully. ID: ' . $createdEvent->getId());

            $task->update(['google_calendar_event_id' => $createdEvent->getId()]);

            return $createdEvent;

        } catch (\Exception $e) {
            \Log::error('Google Calendar Error: ' . $e->getMessage());
        }
    }
}