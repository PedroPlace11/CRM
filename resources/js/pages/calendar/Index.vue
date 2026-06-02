<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import { Link } from '@inertiajs/vue3';

const props = defineProps({ events: Array });

const calendarOptions = {
    plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
    initialView: 'dayGridMonth',
    headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,timeGridDay',
    },
    events: props.events.map(e => ({
        id: e.id,
        title: e.title,
        start: e.start_at,
        end: e.end_at,
        url: `/calendar/${e.id}`,
    })),
    selectable: true,
    editable: false,
    locale: 'pt',
};
</script>

<template>
    <AppLayout>
        <div class="page-shell">
            <header class="page-header">
                <div>
                    <h1>Calendário</h1>
                    <p>Agenda central com visao mensal, semanal e diaria.</p>
                </div>
                <Link href="/calendar/create" class="btn-primary">+ Novo evento</Link>
            </header>
            <div class="calendar-card">
                <FullCalendar :options="calendarOptions" />
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Fraunces:wght@600&family=Space+Grotesk:wght@400;600;700&display=swap');

.page-shell {
    background: linear-gradient(180deg, #f8fafc 0%, #eef2ff 100%);
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    padding: 18px;
    font-family: 'Space Grotesk', system-ui, -apple-system, sans-serif;
}

.page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 14px;
}

.page-header h1 {
    font-family: 'Fraunces', serif;
    font-size: 2rem;
    margin: 0 0 4px;
}

.page-header p {
    margin: 0;
    color: #64748b;
    font-size: 0.95rem;
}

.btn-primary {
    background: #1d4ed8;
    color: #fff;
    text-decoration: none;
    padding: 10px 14px;
    border-radius: 12px;
    font-weight: 600;
    box-shadow: 0 12px 24px rgba(29, 78, 216, 0.25);
}

.calendar-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 12px;
    box-shadow: 0 12px 30px rgba(15, 23, 42, 0.06);
}

:deep(.fc) {
    font-family: 'Space Grotesk', system-ui, -apple-system, sans-serif;
}

:deep(.fc .fc-toolbar-title) {
    font-weight: 700;
    font-size: 1.2rem;
}

:deep(.fc .fc-button) {
    background: #0f172a;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    text-transform: lowercase;
}

:deep(.fc .fc-button-primary:not(:disabled).fc-button-active) {
    background: #1d4ed8;
}

:deep(.fc .fc-daygrid-day-frame) {
    background: #fff;
    border-radius: 10px;
}

:deep(.fc .fc-daygrid-day.fc-day-today) {
    background: rgba(59, 130, 246, 0.08);
}

:deep(.fc .fc-col-header-cell) {
    background: #f8fafc;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #64748b;
}

:deep(.fc .fc-daygrid-event) {
    border-radius: 8px;
    border: none;
    background: #1d4ed8;
    color: #fff;
    padding: 2px 6px;
}

@media (max-width: 720px) {
    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .btn-primary {
        width: 100%;
        text-align: center;
    }
}
</style>
