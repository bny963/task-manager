<?php

namespace App\Services;

use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use Carbon\Carbon;

class GoogleCalendarService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client();
        $this->client->setClientId(config('services.google.client_id'));
        $this->client->setClientSecret(config('services.google.client_secret'));
        $this->client->setRedirectUri(config('services.google.redirect_uri'));
        $this->client->addScope(Calendar::CALENDAR_EVENTS);
        $this->client->setAccessType('offline');
        $this->client->setPrompt('select_account consent');
    }

    public function getAuthUrl()
    {
        return $this->client->createAuthUrl();
    }

    public function authenticate($code)
    {
        $token = $this->client->fetchAccessTokenWithAuthCode($code);
        $user = auth()->user();

        if ($user) {
            $user->fill([
                'google_access_token' => $token['access_token'],
                'google_refresh_token' => $token['refresh_token'] ?? $user->google_refresh_token,
                'google_token_expires_at' => now()->addSeconds($token['expires_in']),
            ])->save();

            \Log::info('User ID ' . $user->id . ' has been updated with Google Token.');
        } else {
            \Log::error('No authenticated user found during Google Callback!');
        }
    }

    /**
     * トークンの有効性を確認し、Calendarサービスを返す共通メソッド
     */
    private function getValidClient()
    {
        $user = auth()->user();
        $this->client->setAccessToken($user->google_access_token);

        if ($this->client->isAccessTokenExpired()) {
            $newToken = $this->client->fetchAccessTokenWithRefreshToken($user->google_refresh_token);
            $user->update([
                'google_access_token' => $newToken['access_token'],
                'google_token_expires_at' => now()->addSeconds($newToken['expires_in']),
            ]);
            $this->client->setAccessToken($newToken);
        }

        return new Calendar($this->client);
    }

    // 新規作成
    public function createEvent($task)
    {
        $calendar = $this->getValidClient();

        $event = new Event([
            'summary' => $task->title,
            'description' => $task->description,
            'start' => [
                'date' => $task->due_date->format('Y-m-d'),
                'timeZone' => 'Asia/Tokyo',
            ],
            'end' => [
                'date' => $task->due_date->copy()->addDay()->format('Y-m-d'),
                'timeZone' => 'Asia/Tokyo',
            ],
        ]);

        $createdEvent = $calendar->events->insert('primary', $event);
        $task->update(['google_calendar_event_id' => $createdEvent->getId()]);

        return $createdEvent;
    }

    // 更新
    public function updateEvent($task)
    {
        if (!$task->google_calendar_event_id) {
            return $this->createEvent($task);
        }

        $calendar = $this->getValidClient();

        try {
            $event = $calendar->events->get('primary', $task->google_calendar_event_id);

            $event->setSummary($task->title);
            $event->setDescription($task->description);
            $event->setStart(new \Google\Service\Calendar\EventDateTime([
                'date' => $task->due_date->format('Y-m-d'),
                'timeZone' => 'Asia/Tokyo',
            ]));
            $event->setEnd(new \Google\Service\Calendar\EventDateTime([
                'date' => $task->due_date->copy()->addDay()->format('Y-m-d'),
                'timeZone' => 'Asia/Tokyo',
            ]));

            return $calendar->events->update('primary', $event->getId(), $event);
        } catch (\Exception $e) {
            \Log::error('Google Calendar Update Error: ' . $e->getMessage());
        }
    }

    // 削除
    public function deleteEvent($task)
    {
        // チェック1: IDが渡ってきているか？
        if (!$task->google_calendar_event_id) {
            // IDがない場合はここで処理が止まり、画面にメッセージが出る
            dd('Debug: Google Calendar Event ID is empty for Task ID: ' . $task->id);
            return;
        }

        $calendar = $this->getValidClient();

        try {
            $calendar->events->delete('primary', $task->google_calendar_event_id);
            // 成功した場合はログに残す
            \Log::info('Google Event Deleted: ' . $task->google_calendar_event_id);
        } catch (\Exception $e) {
            // チェック2: Google APIからエラーが返ってきたか？
            // ここで止まれば、Google側のエラー内容（権限不足、IDが見つからない等）が表示される
            dd('Google Delete API Error: ' . $e->getMessage());
        }
    }
    public function getEventsForFullCalendar()
    {
        $user = auth()->user();
        if (!$user->google_access_token) return [];

        $client = new Client();
        $client->setAccessToken($user->google_access_token);

        // トークン期限切れ時のリフレッシュ処理は省略（必要に応じて実装）

        $service = new Calendar($client);
        
        // 取得範囲（例：前後1ヶ月分）
        $optParams = [
            'orderBy' => 'startTime',
            'singleEvents' => true,
            'timeMin' => Carbon::now()->startOfMonth()->subMonth()->toRfc3339String(),
            'timeMax' => Carbon::now()->endOfMonth()->addMonth()->toRfc3339String(),
        ];

        $results = $service->events->listEvents('primary', $optParams);
        $events = [];

        foreach ($results->getItems() as $event) {
            $events[] = [
                'title' => $event->getSummary() ?? '(タイトルなし)',
                'start' => $event->start->dateTime ?? $event->start->date,
                'end'   => $event->end->dateTime ?? $event->end->date,
                // FullCalendar用の追加プロパティ
                'extendedProps' => [
                    'description' => $event->getDescription(),
                    'location'    => $event->getLocation(),
                ],
                'backgroundColor' => '#3b82f6', // Tailwindのblue-600相当
                'borderColor'     => '#2563eb',
            ];
        }

        return $events;
    }
}