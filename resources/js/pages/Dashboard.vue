<script setup>
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps({
    hero: { type: Object, default: () => ({}) },
    summary: { type: Object, default: () => ({}) },
    stageDistribution: { type: Array, default: () => [] },
});

function formatCurrency(value) {
    return new Intl.NumberFormat('pt-PT', {
        style: 'currency',
        currency: 'EUR',
        maximumFractionDigits: 0,
    }).format(Number(value || 0));
}

function formatEventWhen(value) {
    if (!value) {
        return '-';
    }

    const date = new Date(value);
    const now = new Date();
    const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
    const tomorrow = new Date(today);
    tomorrow.setDate(today.getDate() + 1);
    const eventDay = new Date(date.getFullYear(), date.getMonth(), date.getDate());

    const hour = new Intl.DateTimeFormat('pt-PT', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: false,
    }).format(date);

    if (eventDay.getTime() === today.getTime()) {
        return `Hoje ${hour}`;
    }

    if (eventDay.getTime() === tomorrow.getTime()) {
        return `Amanha ${hour}`;
    }

    const day = new Intl.DateTimeFormat('pt-PT', {
        day: '2-digit',
        month: '2-digit',
    }).format(date);

    return `${day} ${hour}`;
}

function leadsDeltaText(delta) {
    const value = Number(delta || 0);

    if (value === 0) {
        return 'mesmo volume da semana anterior';
    }

    return value > 0
        ? `+${value} vs. semana anterior`
        : `${value} vs. semana anterior`;
}

function leadsDeltaClass(delta) {
    const value = Number(delta || 0);

    if (value > 0) {
        return 'delta up';
    }

    if (value < 0) {
        return 'delta down';
    }

    return 'delta';
}
</script>

<template>
    <AppLayout>
        <div class="dashboard-shell">
            <section class="hero">
                <div class="hero-copy">
                    <div class="badge">CRM em tempo real</div>
                    <h1>Dashboard</h1>
                    <p>
                        Uma vista moderna do funil, atividades e resultados. Tudo o que importa,
                        sem ruído.
                    </p>
                    <div class="hero-actions">
                        <a class="btn primary" href="/deals">Abrir pipeline</a>
                        <a class="btn ghost" href="/calendar">Ver calendário</a>
                    </div>
                </div>
                <div class="hero-panel">
                    <div class="panel-row">
                        <div class="panel-card">
                            <div class="label">Negócios ativos</div>
                            <div class="value">{{ hero.activeDeals || 0 }}</div>
                            <div :class="(hero.activeDealsThisWeek || 0) > 0 ? 'delta up' : 'delta'">
                                {{ (hero.activeDealsThisWeek || 0) > 0 ? `+${hero.activeDealsThisWeek}` : (hero.activeDealsThisWeek || 0) }} esta semana
                            </div>
                        </div>
                        <div class="panel-card">
                            <div class="label">Pipeline</div>
                            <div class="value">{{ formatCurrency(hero.openPipelineValue) }}</div>
                            <div class="delta">em aberto</div>
                        </div>
                    </div>
                    <div class="panel-row">
                        <div class="panel-card wide">
                            <div class="label">Próximas 48h</div>
                            <div v-if="hero.upcomingEvents?.length" class="mini-list">
                                <div v-for="event in hero.upcomingEvents" :key="event.id" class="mini-item">
                                    <span>{{ event.title }}</span>
                                    <em>{{ formatEventWhen(event.start_at) }}</em>
                                </div>
                            </div>
                            <div v-else class="empty-inline">Sem eventos agendados para as proximas 48h.</div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="grid">
                <div class="card">
                    <div class="card-title">Atividades de hoje</div>
                    <div class="card-kpi">{{ summary.activitiesToday || 0 }}</div>
                    <div class="card-foot">registadas no timeline hoje</div>
                </div>
                <div class="card">
                    <div class="card-title">Leads (7 dias)</div>
                    <div class="card-kpi">{{ summary.leadsLast7Days || 0 }}</div>
                    <div :class="leadsDeltaClass(summary.leadsDelta)">{{ leadsDeltaText(summary.leadsDelta) }}</div>
                </div>
                <div class="card">
                    <div class="card-title">Taxa de avanço</div>
                    <div class="card-kpi">{{ summary.advanceRate || 0 }}%</div>
                    <div class="card-foot">média 30 dias</div>
                </div>
                <div class="card">
                    <div class="card-title">Sugestoes pendentes</div>
                    <div class="card-kpi">{{ summary.pendingSuggestions || 0 }}</div>
                    <div class="card-foot">agente comercial</div>
                </div>
                <div class="card">
                    <div class="card-title">Automacoes ativas</div>
                    <div class="card-kpi">{{ summary.activeAutomationRules || 0 }}</div>
                    <div class="card-foot">de {{ summary.automationRulesTotal || 0 }} regras</div>
                </div>
                <div class="card">
                    <div class="card-title">Formularios ativos</div>
                    <div class="card-kpi">{{ summary.activePublicForms || 0 }}</div>
                    <div class="card-foot">de {{ summary.publicFormsTotal || 0 }} formularios</div>
                </div>
                <div class="card wide">
                    <div class="card-title">Pipeline por etapa</div>
                    <div v-if="stageDistribution?.length" class="stage-bar">
                        <div
                            v-for="stage in stageDistribution"
                            :key="stage.id"
                            class="stage"
                            :style="{ width: `${stage.percentage}%` }"
                        >
                            {{ stage.name }} · {{ stage.count }}
                        </div>
                    </div>
                    <div v-else class="empty-inline">Sem negocios para distribuir por etapa.</div>
                    <div class="card-foot">Produtos ativos no catalogo: {{ summary.activeProducts || 0 }}</div>
                </div>
            </section>
        </div>
    </AppLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Fraunces:wght@600&family=Space+Grotesk:wght@400;600;700&display=swap');

.dashboard-shell {
    --bg-1: #f4f6fb;
    --bg-2: #eef2ff;
    --ink: #0f172a;
    --muted: #64748b;
    --primary: #3b82f6;
    --accent: #f97316;
    --card: #ffffff;
    font-family: 'Space Grotesk', system-ui, -apple-system, sans-serif;
    color: var(--ink);
    background:
        radial-gradient(1200px 600px at 10% -10%, #e0e7ff 0%, transparent 60%),
        radial-gradient(900px 500px at 110% 10%, #ffedd5 0%, transparent 55%),
        linear-gradient(180deg, var(--bg-1), var(--bg-2));
    border-radius: 18px;
    padding: 22px;
    min-height: 70vh;
    animation: fadeUp 420ms ease-out;
}

.hero {
    display: grid;
    grid-template-columns: 1.1fr 1fr;
    gap: 24px;
    align-items: center;
    margin-bottom: 24px;
}

.hero h1 {
    font-family: 'Fraunces', serif;
    font-size: 2.4rem;
    margin: 8px 0 6px;
}

.hero p {
    color: var(--muted);
    font-size: 1rem;
    line-height: 1.6;
    margin: 0 0 18px;
}

.badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #0f172a;
    color: #fff;
    padding: 6px 10px;
    border-radius: 999px;
    font-size: 0.75rem;
    letter-spacing: 0.4px;
}

.hero-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.btn {
    text-decoration: none;
    padding: 10px 14px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.9rem;
}

.btn.primary {
    background: var(--primary);
    color: #fff;
    box-shadow: 0 10px 24px rgba(59, 130, 246, 0.25);
}

.btn.ghost {
    border: 1px solid #cbd5f5;
    color: #1f2937;
    background: #fff;
}

.hero-panel {
    background: rgba(255, 255, 255, 0.7);
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 16px;
    backdrop-filter: blur(8px);
}

.panel-row {
    display: grid;
    gap: 12px;
}

.panel-row:first-child {
    grid-template-columns: 1fr 1fr;
    margin-bottom: 12px;
}

.panel-card {
    background: var(--card);
    border-radius: 12px;
    padding: 14px;
    border: 1px solid #eef2ff;
}

.panel-card.wide {
    min-height: 120px;
}

.label {
    color: var(--muted);
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.8px;
}

.value {
    font-size: 1.6rem;
    font-weight: 700;
    margin-top: 6px;
}

.delta {
    color: var(--muted);
    font-size: 0.8rem;
    margin-top: 6px;
}

.delta.up {
    color: #16a34a;
}

.delta.down {
    color: #dc2626;
}

.mini-list {
    display: grid;
    gap: 6px;
    margin-top: 8px;
}

.mini-item {
    display: flex;
    justify-content: space-between;
    font-size: 0.85rem;
    color: #1f2937;
}

.mini-item em {
    color: var(--muted);
    font-style: normal;
}

.grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 14px;
}

.card {
    background: var(--card);
    border-radius: 14px;
    padding: 16px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 12px 30px rgba(15, 23, 42, 0.05);
}

.card.wide {
    grid-column: span 3;
}

.card-title {
    color: var(--muted);
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.8px;
}

.card-kpi {
    font-size: 2rem;
    font-weight: 700;
    margin: 8px 0 4px;
}

.card-foot {
    color: var(--muted);
    font-size: 0.85rem;
}

.stage-bar {
    display: flex;
    gap: 6px;
    margin: 10px 0 6px;
    flex-wrap: wrap;
}

.stage {
    background: #e0e7ff;
    color: #1e3a8a;
    padding: 6px 8px;
    border-radius: 8px;
    font-size: 0.8rem;
    text-align: center;
    min-width: 70px;
}

.empty-inline {
    margin-top: 8px;
    color: var(--muted);
    font-size: 0.86rem;
}

@media (max-width: 1024px) {
    .hero {
        grid-template-columns: 1fr;
    }
    .grid {
        grid-template-columns: 1fr 1fr;
    }
    .card.wide {
        grid-column: span 2;
    }
}

@media (max-width: 720px) {
    .grid {
        grid-template-columns: 1fr;
    }
    .card.wide {
        grid-column: span 1;
    }
    .panel-row:first-child {
        grid-template-columns: 1fr;
    }
}

@keyframes fadeUp {
    from { opacity: 0; transform: translateY(8px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
