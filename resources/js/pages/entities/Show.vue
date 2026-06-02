<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
defineProps({ entity: Object });
</script>

<template>
    <AppLayout>
        <div class="page-shell">
            <header class="hero">
                <div>
                    <p class="eyebrow">Detalhe da entidade</p>
                    <h1>{{ entity.name }}</h1>
                    <div class="meta">
                        <span v-if="entity.vat">{{ entity.vat }}</span>
                        <span v-if="entity.email">{{ entity.email }}</span>
                        <span v-if="entity.phone">{{ entity.phone }}</span>
                    </div>
                </div>
                <div class="status-card">
                    <div class="status-label">Estado</div>
                    <div class="status-chip" :data-status="entity.status || 'active'">{{ entity.status || 'active' }}</div>
                </div>
            </header>

            <section class="panel">
                <div class="panel-header">
                    <h2>Pessoas associadas</h2>
                    <span class="count">{{ entity.people?.length || 0 }}</span>
                </div>
                <ul v-if="entity.people?.length" class="list">
                    <li v-for="p in entity.people" :key="p.id" class="list-row">
                        <div>
                            <div class="list-title">{{ p.name }}</div>
                            <div class="list-subtitle">{{ p.email || 'Sem email' }}</div>
                        </div>
                        <div class="list-meta">{{ p.position || '-' }}</div>
                    </li>
                </ul>
                <div v-else class="empty">Sem pessoas associadas.</div>
            </section>

            <section class="panel">
                <div class="panel-header">
                    <h2>Negócios</h2>
                    <span class="count">{{ entity.deals?.length || 0 }}</span>
                </div>
                <ul v-if="entity.deals?.length" class="list">
                    <li v-for="d in entity.deals" :key="d.id" class="list-row">
                        <div>
                            <div class="list-title">{{ d.title }}</div>
                            <div class="list-subtitle">{{ d.stage?.name || 'Sem etapa' }}</div>
                        </div>
                        <div class="value">EUR {{ d.value }}</div>
                    </li>
                </ul>
                <div v-else class="empty">Sem negócios.</div>
            </section>
        </div>
    </AppLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Fraunces:wght@600&family=Manrope:wght@400;500;600;700&display=swap');

.page-shell {
    font-family: 'Manrope', system-ui, -apple-system, sans-serif;
    background: radial-gradient(circle at 10% 0%, #eef2ff 0%, #f8fafc 45%, #fff7ed 100%);
    border: 1px solid rgba(226, 232, 240, 0.9);
    border-radius: 22px;
    padding: 22px;
    max-width: 1100px;
    box-shadow: 0 24px 60px rgba(15, 23, 42, 0.08);
}

.hero {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    gap: 18px;
    margin-bottom: 18px;
}

.eyebrow {
    text-transform: uppercase;
    letter-spacing: 0.12em;
    font-size: 0.7rem;
    color: #94a3b8;
    margin: 0 0 8px;
}

.hero h1 {
    font-family: 'Fraunces', serif;
    font-size: 2.2rem;
    margin: 0 0 10px;
    color: #0f172a;
}

.meta {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    color: #475569;
    font-size: 0.95rem;
}

.meta span {
    padding: 6px 10px;
    border-radius: 999px;
    background: #fff;
    border: 1px solid rgba(148, 163, 184, 0.4);
}

.status-card {
    background: #fff;
    border: 1px solid rgba(148, 163, 184, 0.35);
    border-radius: 16px;
    padding: 14px 16px;
    min-width: 160px;
    box-shadow: 0 16px 32px rgba(15, 23, 42, 0.08);
}

.status-label {
    font-size: 0.7rem;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: #94a3b8;
    margin-bottom: 8px;
}

.status-chip {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 6px 12px;
    border-radius: 999px;
    font-weight: 600;
    background: #e2e8f0;
    color: #0f172a;
}

.status-chip[data-status='active'] {
    background: #dcfce7;
    color: #166534;
}

.status-chip[data-status='inactive'] {
    background: #fee2e2;
    color: #991b1b;
}

.panel {
    background: rgba(255, 255, 255, 0.94);
    border: 1px solid rgba(226, 232, 240, 0.9);
    border-radius: 18px;
    padding: 16px;
    margin-bottom: 16px;
    box-shadow: 0 16px 40px rgba(15, 23, 42, 0.08);
}

.panel-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
}

.panel-header h2 {
    font-size: 1.1rem;
    margin: 0;
    color: #0f172a;
}

.count {
    background: #0f172a;
    color: #fff;
    border-radius: 999px;
    padding: 4px 10px;
    font-size: 0.8rem;
}

.list {
    display: grid;
    gap: 10px;
}

.list-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 10px 12px;
    border-radius: 12px;
    background: #f8fafc;
    border: 1px solid rgba(226, 232, 240, 0.9);
}

.list-title {
    font-weight: 600;
    color: #0f172a;
}

.list-subtitle {
    font-size: 0.85rem;
    color: #64748b;
}

.list-meta {
    font-size: 0.85rem;
    color: #475569;
}

.value {
    font-weight: 600;
    color: #0f172a;
}

.empty {
    color: #94a3b8;
    font-size: 0.9rem;
}

@media (max-width: 900px) {
    .hero {
        flex-direction: column;
        align-items: flex-start;
    }

    .status-card {
        width: 100%;
    }
}
</style>
