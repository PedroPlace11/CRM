<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';
defineProps({ suggestions: Array });

const decide = (id, decision) => {
    router.post(`/ai/suggestions/${id}`, { decision });
};
</script>

<template>
    <AppLayout>
        <div class="page-shell">
            <header class="page-header">
                <div>
                    <h1>Sugestoes do Agente Comercial</h1>
                    <p>Prioridades sugeridas para acelerar o funil.</p>
                </div>
                <div class="pill">{{ suggestions?.length || 0 }} sugestoes</div>
            </header>

            <ul class="list">
                <li v-for="s in suggestions" :key="s.id" class="card">
                    <div class="card-body">
                        <div class="card-title">{{ s.title }}</div>
                        <div class="card-reason">{{ s.reason }}</div>
                        <div class="card-meta">
                            <span>Acao: {{ s.action_type }}</span>
                            <span>Prioridade: {{ s.priority }}</span>
                            <span v-if="s.suggested_date">Sugerido para {{ s.suggested_date }}</span>
                        </div>
                    </div>
                    <div class="card-actions">
                        <button @click="decide(s.id, 'accepted')" class="btn-success">Aceitar</button>
                        <button @click="decide(s.id, 'snoozed')" class="btn-warning">Adiar</button>
                        <button @click="decide(s.id, 'dismissed')" class="btn-ghost">Arquivar</button>
                    </div>
                </li>
                <li v-if="!suggestions?.length" class="empty">Sem sugestoes no momento.</li>
            </ul>
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

.pill {
    background: #e0f2fe;
    color: #0c4a6e;
    font-weight: 700;
    border-radius: 999px;
    padding: 6px 12px;
    font-size: 0.8rem;
}

.list {
    display: grid;
    gap: 12px;
}

.card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 14px 16px;
    display: flex;
    justify-content: space-between;
    gap: 16px;
    box-shadow: 0 12px 24px rgba(15, 23, 42, 0.06);
}

.card-title {
    font-weight: 700;
    margin-bottom: 4px;
}

.card-reason {
    color: #475569;
    margin-bottom: 6px;
}

.card-meta {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    font-size: 0.85rem;
    color: #94a3b8;
}

.card-actions {
    display: flex;
    gap: 8px;
    align-items: center;
}

.btn-success {
    background: #16a34a;
    color: #fff;
    border: none;
    padding: 8px 12px;
    border-radius: 10px;
    font-weight: 600;
}

.btn-warning {
    background: #f59e0b;
    color: #fff;
    border: none;
    padding: 8px 12px;
    border-radius: 10px;
    font-weight: 600;
}

.btn-ghost {
    background: #fff;
    border: 1px solid #e2e8f0;
    padding: 8px 12px;
    border-radius: 10px;
    font-weight: 600;
    color: #475569;
}

.empty {
    padding: 20px;
    text-align: center;
    color: #94a3b8;
    background: #fff;
    border: 1px dashed #e2e8f0;
    border-radius: 12px;
}

@media (max-width: 900px) {
    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .card {
        flex-direction: column;
        align-items: flex-start;
    }

    .card-actions {
        width: 100%;
        flex-wrap: wrap;
    }
}
</style>
