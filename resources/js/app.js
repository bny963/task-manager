import './bootstrap';

// ライブラリをインポート
import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';

// window.initCalendar として定義することで、Blade側から呼び出せるようにする
window.initCalendar = function (el, events) {
    if (!el) return;

    const calendar = new Calendar(el, {
        plugins: [dayGridPlugin, interactionPlugin],
        initialView: 'dayGridMonth',
        locale: 'ja',
        height: 'auto',
        events: events, // PHPから渡されたイベント
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: ''
        },
        // 予定がある場合にドットを表示
        eventDisplay: 'block',
    });

    calendar.render();
    console.log('FullCalendar rendered!'); // 確認用ログ
};