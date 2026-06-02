<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import draggable from 'vuedraggable';

const props = defineProps({ stages: Array, columns: Object, filters: Object, owners: Array });

const cols = ref(JSON.parse(JSON.stringify(props.columns)));
watch(() => props.columns, (v) => { cols.value = JSON.parse(JSON.stringify(v)); }, { deep: true });

const form = ref({
    q: props.filters?.q || '',
    owner_id: props.filters?.owner_id || '',
    stage_id: props.filters?.stage_id || '',
    expected_from: props.filters?.expected_from || '',
    expected_to: props.filters?.expected_to || '',
    min_value: props.filters?.min_value || '',
    max_value: props.filters?.max_value || '',
});

const applyFilters = () => {
    router.get('/deals', { ...form.value }, { preserveState: true, preserveScroll: true });
};

const clearFilters = () => {
    form.value = { q: '', owner_id: '', stage_id: '', expected_from: '', expected_to: '', min_value: '', max_value: '' };
    applyFilters();
};

function onDrop(stageId, evt) {
    const moved = evt.added?.element;
    if (!moved) return;
    router.patch(`/deals/${moved.id}/move`, { stage_id: Number(stageId) }, {
        preserveScroll: true,
        preserveState: true,
        onError: () => router.reload({ only: ['columns'] }),
    });
}
</script>

<template>
    <AppLayout>
        <div class="page-shell">
            <header class="page-header">
                <div>
                    <h1>Pipeline</h1>
                    <p>Visao geral do funil comercial por etapa.</p>
                </div>
                <div class="header-actions">
                    <button type="button" class="btn-ghost" @click="clearFilters">Limpar</button>
                    <button type="button" class="btn-primary" @click="applyFilters">Aplicar</button>
                </div>
            </header>

            <form @submit.prevent="applyFilters" class="filters">
                <div class="field span-2">
                    <label>Pesquisa</label>
                    <input v-model="form.q" placeholder="Nome do negocio" />
                </div>
                <div class="field">
                    <label>Responsavel</label>
                    <select v-model="form.owner_id">
                        <option value="">Todos</option>
                        <option v-for="o in owners" :key="o.id" :value="o.id">{{ o.name }}</option>
                    </select>
                </div>
                <div class="field">
                    <label>Etapa</label>
                    <select v-model="form.stage_id">
                        <option value="">Todas</option>
                        <option v-for="s in stages" :key="s.id" :value="s.id">{{ s.name }}</option>
                    </select>
                </div>
                <div class="field">
                    <label>Fecho de</label>
                    <input type="date" v-model="form.expected_from" />
                </div>
                <div class="field">
                    <label>Fecho ate</label>
                    <input type="date" v-model="form.expected_to" />
                </div>
                <div class="field">
                    <label>Valor min</label>
                    <input type="number" v-model="form.min_value" />
                </div>
                <div class="field">
                    <label>Valor max</label>
                    <input type="number" v-model="form.max_value" />
                </div>
            </form>

            <div class="board-wrapper">
                <div class="board">
                    <div v-for="(col, stageId) in cols" :key="stageId" class="lane">
                        <div class="lane-header">
                            <div>
                                <h3>{{ col.stage.name }}</h3>
                                <span class="lane-meta">{{ col.deals?.length || 0 }} negocios</span>
                            </div>
                            <span class="lane-total">EUR {{ Number(col.total_value).toLocaleString('pt-PT') }}</span>
                        </div>
                        <draggable
                            :list="col.deals"
                            group="deals"
                            item-key="id"
                            class="lane-list"
                            ghost-class="ghost"
                            @change="(evt) => onDrop(stageId, evt)">
                            <template #item="{ element: d }">
                                <article class="deal-card" @click="router.visit(`/deals/${d.id}`)">
                                    <div class="deal-title">{{ d.title }}</div>
                                    <div class="deal-meta">
                                        <span>{{ d.entity?.name || '-' }}</span>
                                        <span>EUR {{ Number(d.value).toLocaleString('pt-PT') }}</span>
                                    </div>
                                </article>
                            </template>
                        </draggable>
                        <div v-if="!col.deals?.length" class="lane-empty">Sem negocios nesta etapa.</div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Fraunces:wght@600&family=Space+Grotesk:wght@400;600;700&display=swap');

.page-shell {
    background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
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

.header-actions {
    display: flex;
    gap: 8px;
}

.btn-primary {
    background: #1d4ed8;
    color: #fff;
    border: none;
    padding: 10px 14px;
    border-radius: 12px;
    font-weight: 600;
    box-shadow: 0 12px 24px rgba(29, 78, 216, 0.25);
}

.btn-ghost {
    background: #fff;
    color: #1e293b;
    border: 1px solid #e2e8f0;
    padding: 10px 14px;
    border-radius: 12px;
    font-weight: 600;
}

.filters {
    display: grid;
    grid-template-columns: repeat(7, minmax(0, 1fr));
    gap: 12px;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 12px;
    margin-bottom: 16px;
}

.field {
    display: grid;
    gap: 6px;
}

.field label {
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    color: #64748b;
}

.field input,
.field select {
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 8px 10px;
    font-size: 0.95rem;
    background: #fff;
}

.span-2 {
    grid-column: span 2 / span 2;
}

.board-wrapper {
    padding-bottom: 8px;
}

.board {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 14px;
}

.lane {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 12px;
    display: flex;
    flex-direction: column;
    min-height: 220px;
}

.lane-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 10px;
}

.lane-header h3 {
    margin: 0 0 4px;
    font-size: 1rem;
    font-weight: 700;
}

.lane-meta {
    color: #94a3b8;
    font-size: 0.8rem;
}

.lane-total {
    background: #e0f2fe;
    color: #0c4a6e;
    font-weight: 700;
    border-radius: 999px;
    padding: 4px 10px;
    font-size: 0.8rem;
}

.lane-list {
    display: grid;
    gap: 10px;
    min-height: 40px;
}

.deal-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 12px;
    font-size: 0.9rem;
    cursor: grab;
    box-shadow: 0 10px 18px rgba(15, 23, 42, 0.06);
}

.deal-card:active {
    cursor: grabbing;
}

.deal-title {
    font-weight: 700;
    margin-bottom: 6px;
}

.deal-meta {
    display: flex;
    justify-content: space-between;
    color: #64748b;
    font-size: 0.8rem;
}

.lane-empty {
    margin-top: 8px;
    color: #94a3b8;
    font-size: 0.85rem;
}

.ghost {
    opacity: 0.4;
}

@media (max-width: 1100px) {
    .filters {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .span-2 {
        grid-column: span 2 / span 2;
    }
}

@media (max-width: 720px) {
    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .header-actions {
        width: 100%;
    }

    .btn-primary,
    .btn-ghost {
        flex: 1;
        text-align: center;
    }
}
</style>
