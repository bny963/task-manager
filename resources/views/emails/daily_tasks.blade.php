<h1>おはようございます！今日の予定です</h1>

@if(count($tasks) > 0)
    <h2>📝 今日の期限タスク</h2>
    <ul>
        @foreach($tasks as $task)
            <li>{{ $task->title }} (優先度: {{ $task->priority_label }})</li>
        @endforeach
    </ul>
@endif

@if(count($googleEvents) > 0)
    <h2>📅 Googleカレンダーの予定</h2>
    <ul>
        @foreach($googleEvents as $event)
            <li>{{ data_get($event, 'summary') }}</li>
        @endforeach
    </ul>
@endif