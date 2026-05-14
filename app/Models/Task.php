<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use HasFactory;

    /**
     * 複数代入可能な属性
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'description',
        'priority',
        'is_completed',
        'start_date',
        'due_date',
        'google_calendar_event_id',
    ];

    /**
     * このタスクを所有するユーザーを取得
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * このタスクが属するカテゴリーを取得
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * 優先度のラベルを取得
     */
    public function getPriorityLabelAttribute(): string
    {
        return match ($this->priority) {
            1 => '低',
            2 => '中',
            default => '高',
        };
    }
    protected $casts = [
        'start_date' => 'date', // キャストを追加
        'due_date' => 'date',
        'is_completed' => 'boolean',
    ];
    public function assignee()
    {
        // assigned_to カラムを使って User モデルと紐付ける
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
