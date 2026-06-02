<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({ stats: Array, filters: Object, stages: Array, owners: Array });

const form = ref({
    from: props.filters?.from || '',
    to: props.filters?.to || '',
    stage_id: props.filters?.stage_id || '',
    owner_id: props.filters?.owner_id || '',
});

const applyFilters = () => {
    router.get('/products/stats', { ...form.value }, { preserveState: true, preserveScroll: true });
};

const exportUrl = computed(() => {
    const params = new URLSearchParams();
    Object.entries(form.value).forEach(([k, v]) => { if (v) params.set(k, v); });
    const qs = params.toString();
    return qs ? `/products/stats/export?${qs}` : '/products/stats/export';
});
</script>

<template>
    <AppLayout>
        <div class="page-shell">
            <header class="page-header">
                <div>
                    <h1>Estatisticas de Produtos</h1>
                    <p>Analise de vendas por produto e periodo.</p>
                </div>
                <a :href="exportUrl" class="btn-dark">Exportar XLSX</a>
            </header>

            <form @submit.prevent="applyFilters" class="filters">
                <div class="field">
                    <label>De</label>
                    <input type="date" v-model="form.from" />
                </div>
                <div class="field">
                    <label>Ate</label>
                    <input type="date" v-model="form.to" />
                </div>
                <div class="field">
                    <label>Estado</label>
                    <select v-model="form.stage_id">
                        <option value="">Todos</option>
                        <option v-for="s in stages" :key="s.id" :value="s.id">{{ s.name }}</option>
                    </select>
                </div>
                <div class="field">
                    <label>Responsavel</label>
                    <select v-model="form.owner_id">
                        <option value="">Todos</option>
                        <option v-for="o in owners" :key="o.id" :value="o.id">{{ o.name }}</option>
                    </select>
                </div>
                <div class="actions">
                    <button class="btn-primary">Aplicar</button>
                </div>
            </form>

            <div class="table-card">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Quantidade</th>
                            <th>Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="s in stats" :key="s.id">
                            <td>
                                <a :href="`/products/${s.id}`" class="link">{{ s.name }}</a>
                            </td>
                            <td>{{ s.quantity }}</td>
                            <td>€ {{ s.total_value.toLocaleString('pt-PT') }}</td>
                        </tr>
                        <tr v-if="!stats?.length">
                            <td colspan="3" class="empty">Sem dados para o periodo.</td>
                        </tr>
                    </tbody>
                </table>
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

.btn-dark {
    background: #0f172a;
    color: #fff;
    text-decoration: none;
    padding: 10px 14px;
    border-radius: 12px;
    font-weight: 600;
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

.filters {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 12px;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 14px;
    margin-bottom: 14px;
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

.actions {
    grid-column: span 4 / span 4;
    display: flex;
    justify-content: flex-end;
}

.table-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 12px 24px rgba(15, 23, 42, 0.06);
}

.table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.95rem;
}

.table thead {
    background: #f8fafc;
    text-align: left;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    color: #64748b;
}

.table th,
.table td {
    padding: 12px 14px;
    border-bottom: 1px solid #e2e8f0;
}

.table tbody tr:hover {
    background: #f8fafc;
}

.link {
    color: #1d4ed8;
    font-weight: 600;
    text-decoration: none;
}

.empty {
    text-align: center;
    color: #94a3b8;
    padding: 18px;
}

@media (max-width: 900px) {
    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .btn-dark {
        width: 100%;
        text-align: center;
    }

    .filters {
        grid-template-columns: 1fr 1fr;
    }

    .actions {
        grid-column: span 2 / span 2;
    }
}

@media (max-width: 640px) {
    .filters {
        grid-template-columns: 1fr;
    }

    .actions {
        grid-column: span 1 / span 1;
    }
}
</style>
