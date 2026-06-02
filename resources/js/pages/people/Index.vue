<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({ people: Object, filters: Object });
const q = ref(props.filters?.q ?? '');
const entityId = ref(props.filters?.entity_id ?? '');

let timer = null;
function applyFilters() {
    clearTimeout(timer);
    timer = setTimeout(() => {
        router.get('/people',
            { q: q.value || undefined, entity_id: entityId.value || undefined },
            { preserveState: true, preserveScroll: true, replace: true });
    }, 250);
}

watch([q, entityId], applyFilters);
</script>

<template>
    <AppLayout>
        <div class="page-shell">
            <header class="page-header">
                <div>
                    <h1>Pessoas</h1>
                    <p>Contactos individuais com historico de entidades e negocios.</p>
                </div>
                <Link href="/people/create" class="btn-primary">+ Nova</Link>
            </header>

            <section class="filters">
                <div class="field">
                    <label>Pesquisa</label>
                    <input v-model="q" placeholder="Pesquisar por nome ou email" />
                </div>
                <div class="field">
                    <label>Entidade</label>
                    <input v-model="entityId" placeholder="ID da entidade (opcional)" />
                </div>
                <div class="hint">Resultados: {{ people.data?.length || 0 }}</div>
            </section>

            <section class="table-card">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Entidade</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="p in people.data" :key="p.id">
                            <td>
                                <Link :href="`/people/${p.id}`" class="link">{{ p.name }}</Link>
                            </td>
                            <td>{{ p.email || '—' }}</td>
                            <td>{{ p.entity?.name || '—' }}</td>
                        </tr>
                        <tr v-if="!people.data?.length">
                            <td colspan="3" class="empty">Sem pessoas.</td>
                        </tr>
                    </tbody>
                </table>
            </section>
        </div>
    </AppLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Fraunces:wght@600&family=Space+Grotesk:wght@400;600;700&display=swap');

.page-shell {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
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
    background: #3b82f6;
    color: #fff;
    text-decoration: none;
    padding: 10px 14px;
    border-radius: 12px;
    font-weight: 600;
    box-shadow: 0 10px 24px rgba(59, 130, 246, 0.25);
}

.filters {
    display: grid;
    grid-template-columns: 2fr 1fr auto;
    gap: 12px;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 12px;
    margin-bottom: 14px;
}

.field {
    display: grid;
    gap: 6px;
}

.field label {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.7px;
    color: #64748b;
}

.field input {
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 8px 10px;
    font-size: 0.95rem;
    background: #fff;
}

.hint {
    align-self: end;
    color: #94a3b8;
    font-size: 0.85rem;
}

.table-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    overflow: hidden;
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
    text-decoration: none;
    font-weight: 600;
}

.empty {
    text-align: center;
    color: #94a3b8;
    padding: 20px;
}

@media (max-width: 900px) {
    .filters {
        grid-template-columns: 1fr;
    }
}
</style>
