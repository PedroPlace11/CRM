<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({ event: Object });

const typeMap = {
    meeting: 'Reuniao',
    call: 'Chamada',
    task: 'Tarefa',
    note: 'Nota',
};

const priorityMap = {
    high: 'Alta',
    normal: 'Normal',
    low: 'Baixa',
};

const eventTypeLabel = computed(() => typeMap[props.event?.type] || 'Evento');
const priorityLabel = computed(() => priorityMap[props.event?.priority] || 'Normal');

const eventStatusLabel = computed(() => (props.event?.completed ? 'Concluido' : 'Pendente'));

const eventableLabel = computed(() => {
    const type = props.event?.eventable_type;
    if (!type) {
        return 'Sem vinculo';
    }

    if (type.includes('Entity')) {
        return 'Empresa';
    }
    if (type.includes('Person')) {
        return 'Pessoa';
    }
    if (type.includes('Deal')) {
        return 'Negocio';
    }

    return 'Registro';
});

const eventableName = computed(() => props.event?.eventable?.name || props.event?.eventable?.title || 'Nao informado');
const ownerName = computed(() => props.event?.owner?.name || 'Nao informado');
const locationText = computed(() => props.event?.location || 'Nao definido');
const descriptionText = computed(() => props.event?.description || 'Sem descricao para este evento.');

function formatDateTime(value) {
    if (!value) {
        return '-';
    }

    const date = new Date(value);

    return new Intl.DateTimeFormat('pt-PT', {
        weekday: 'short',
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).format(date);
}

const startAtText = computed(() => formatDateTime(props.event?.start_at));
const endAtText = computed(() => formatDateTime(props.event?.end_at));
</script>

<template>
    <AppLayout>
        <section class="event-page">
            <header class="event-hero">
                <div class="event-hero__top">
                    <Link href="/calendar" class="event-hero__back">Voltar ao calendario</Link>
                    <span class="event-chip event-chip--status">{{ eventStatusLabel }}</span>
                </div>

                <h1 class="event-title">{{ event.title }}</h1>

                <div class="event-meta-row">
                    <span class="event-chip event-chip--type">{{ eventTypeLabel }}</span>
                    <span class="event-chip event-chip--priority">Prioridade {{ priorityLabel }}</span>
                    <span class="event-chip event-chip--location">Local: {{ locationText }}</span>
                </div>

                <div class="event-time">
                    <div class="event-time__item">
                        <span class="event-time__label">Inicio</span>
                        <strong>{{ startAtText }}</strong>
                    </div>
                    <span class="event-time__divider">→</span>
                    <div class="event-time__item">
                        <span class="event-time__label">Fim</span>
                        <strong>{{ endAtText }}</strong>
                    </div>
                </div>
            </header>

            <div class="event-grid">
                <article class="event-card">
                    <p class="event-card__eyebrow">Vinculo</p>
                    <h2>{{ eventableLabel }}</h2>
                    <p>{{ eventableName }}</p>
                </article>

                <article class="event-card">
                    <p class="event-card__eyebrow">Responsavel</p>
                    <h2>{{ ownerName }}</h2>
                    <p>ID do evento: #{{ event.id }}</p>
                </article>

                <article class="event-card event-card--full">
                    <p class="event-card__eyebrow">Descricao</p>
                    <p class="event-description">{{ descriptionText }}</p>
                </article>
            </div>
        </section>
    </AppLayout>
</template>

<style scoped>
.event-page {
    max-width: 1080px;
    margin: 0 auto;
    display: grid;
    gap: 1.25rem;
    padding: 0.25rem 0 1rem;
}

.event-hero {
    padding: 1.5rem;
    border-radius: 1rem;
    color: #07213f;
    border: 1px solid #bfdcff;
    background:
        radial-gradient(circle at 15% 0%, #d8efff 0%, transparent 42%),
        radial-gradient(circle at 92% 18%, #dff6ed 0%, transparent 36%),
        linear-gradient(135deg, #f7fcff 0%, #eef6ff 50%, #f2fff6 100%);
    box-shadow: 0 12px 28px -22px rgba(24, 95, 172, 0.65);
}

.event-hero__top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    margin-bottom: 0.75rem;
}

.event-hero__back {
    color: #0a447d;
    font-weight: 700;
    font-size: 0.86rem;
    letter-spacing: 0.01em;
    text-decoration: none;
}

.event-hero__back:hover {
    text-decoration: underline;
}

.event-title {
    margin: 0;
    font-size: clamp(1.5rem, 1.2rem + 1.1vw, 2.15rem);
    line-height: 1.15;
    font-weight: 800;
    letter-spacing: -0.02em;
}

.event-meta-row {
    margin-top: 0.95rem;
    display: flex;
    flex-wrap: wrap;
    gap: 0.55rem;
}

.event-chip {
    display: inline-flex;
    align-items: center;
    border-radius: 999px;
    padding: 0.36rem 0.72rem;
    font-size: 0.78rem;
    font-weight: 700;
    border: 1px solid transparent;
}

.event-chip--status {
    background: #eaf7ef;
    border-color: #b8e0c6;
    color: #155e34;
}

.event-chip--type {
    background: #e9f3ff;
    border-color: #c0ddff;
    color: #114a84;
}

.event-chip--priority {
    background: #fff3e3;
    border-color: #ffd8a8;
    color: #8a4e0f;
}

.event-chip--location {
    background: #f2f5fb;
    border-color: #d9e1ef;
    color: #384a66;
}

.event-time {
    margin-top: 1rem;
    display: flex;
    align-items: center;
    gap: 0.8rem;
    flex-wrap: wrap;
}

.event-time__item {
    display: grid;
    gap: 0.2rem;
}

.event-time__label {
    font-size: 0.73rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #48617f;
}

.event-time__item strong {
    font-size: 0.95rem;
    color: #0b2340;
}

.event-time__divider {
    color: #48617f;
    font-weight: 700;
}

.event-grid {
    display: grid;
    gap: 1rem;
    grid-template-columns: repeat(2, minmax(0, 1fr));
}

.event-card {
    padding: 1rem 1.1rem;
    border-radius: 0.95rem;
    border: 1px solid #e4e9f2;
    background: #fff;
    box-shadow: 0 10px 22px -24px rgba(22, 34, 51, 0.55);
}

.event-card__eyebrow {
    margin: 0;
    font-size: 0.71rem;
    text-transform: uppercase;
    letter-spacing: 0.09em;
    color: #596e88;
    font-weight: 700;
}

.event-card h2 {
    margin: 0.4rem 0 0.25rem;
    font-size: 1.05rem;
    color: #11243c;
}

.event-card p {
    margin: 0;
    color: #4a5f79;
}

.event-card--full {
    grid-column: 1 / -1;
}

.event-description {
    margin-top: 0.45rem;
    white-space: pre-line;
    line-height: 1.55;
}

@media (max-width: 860px) {
    .event-grid {
        grid-template-columns: 1fr;
    }

    .event-card--full {
        grid-column: auto;
    }
}
</style>
