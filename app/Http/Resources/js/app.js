import './bootstrap';

import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';

// ブラウザ側から呼び出せるように window オブジェクトに登録
window.initCalendar = function (el, events) {
    const calendar = new Calendar(el, {
        plugins: [dayGridPlugin],
        initialView: 'dayGridMonth',
        locale: 'ja',
        events: events,
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: ''
        }
    });
    calendar.render();
};