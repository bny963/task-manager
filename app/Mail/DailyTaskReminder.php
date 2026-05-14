<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DailyTaskReminder extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * プロパティを定義（publicにすると自動的にBladeで使えます）
     */
    public $tasks;
    public $googleEvents;

    /**
     * インスタンス作成時にタスクとGoogle予定を受け取る
     */
    public function __construct($tasks, $googleEvents = [])
    {
        $this->tasks = $tasks;
        $this->googleEvents = $googleEvents;
    }

    /**
     * メールの件名を設定
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '【リマインド】本日のタスクと予定の確認',
        );
    }

    /**
     * 使用するViewファイルを指定
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.daily_tasks', // resources/views/emails/daily_tasks.blade.php を作成してください
        );
    }

    /**
     * 添付ファイル（今回はなし）
     */
    public function attachments(): array
    {
        return [];
    }
}