<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({ person: Object });

const showMerge = ref(false);
const mergeForm = useForm({ duplicate_id: '' });
const merge = () => {
    if (!mergeForm.duplicate_id) return;
    if (!confirm('Combinar a pessoa duplicada nesta? A duplicada sera removida.')) return;
    mergeForm.post(`/people/${props.person.id}/merge`, {
        onSuccess: () => showMerge.value = false,
    });
};
</script>

<template>
    <AppLayout>
        <div class="page-shell">
            <header class="hero">
                <div>
                    <p class="eyebrow">Perfil de contacto</p>
                    <h1>{{ person.name }}</h1>
                    <div class="meta">
                        <span v-if="person.email">{{ person.email }}</span>
                        <span v-if="person.phone">{{ person.phone }}</span>
                        <span v-if="person.position">{{ person.position }}</span>
                    </div>
                    <div class="entity-chip">
                        <span class="label">Entidade</span>
                        <strong>{{ person.entity?.name || '-' }}</strong>
                    </div>
                </div>
                <button @click="showMerge = !showMerge" class="btn-outline">
                    {{ showMerge ? 'Cancelar' : 'Combinar duplicado' }}
                </button>
            </header>

            <form v-if="showMerge" @submit.prevent="merge" class="merge-card">
                <div class="merge-field">
                    <label>ID da pessoa duplicada</label>
                    <input v-model="mergeForm.duplicate_id" type="number" required />
                </div>
                <button class="btn-warn" :disabled="mergeForm.processing">Combinar</button>
            </form>
            <div v-if="mergeForm.errors.duplicate_id" class="error">{{ mergeForm.errors.duplicate_id }}</div>

            <section class="panel">
                <div class="panel-header">
                    <h2>Negocios</h2>
                    <span class="count">{{ person.deals?.length || 0 }}</span>
                </div>
                <ul v-if="person.deals?.length" class="list">
                    <li v-for="d in person.deals" :key="d.id" class="list-row">
                        <div>
                            <div class="list-title">{{ d.title }}</div>
                            <div class="list-subtitle">{{ d.stage?.name || 'Sem etapa' }}</div>
                        </div>
                    </li>
                </ul>
                <div v-else class="empty">Sem negocios.</div>
            </section>
        </div>
    </AppLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Fraunces:wght@600&family=Manrope:wght@400;500;600;700&display=swap');

.page-shell {
    font-family: 'Manrope', system-ui, -apple-system, sans-serif;
    background: radial-gradient(circle at 8% 0%, #eef2ff 0%, #f8fafc 45%, #fff7ed 100%);
    border: 1px solid rgba(226, 232, 240, 0.9);
    border-radius: 22px;
    padding: 22px;
    max-width: 1100px;
    box-shadow: 0 24px 60px rgba(15, 23, 42, 0.08);
}

.hero {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 18px;
    margin-bottom: 16px;
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
    margin-bottom: 12px;
}

.meta span {
    padding: 6px 10px;
    border-radius: 999px;
    background: #fff;
    border: 1px solid rgba(148, 163, 184, 0.4);
}

.entity-chip {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 12px;
    border-radius: 999px;
    background: #0f172a;
    color: #fff;
    font-size: 0.85rem;
}

.entity-chip .label {
    text-transform: uppercase;
    letter-spacing: 0.1em;
    font-size: 0.65rem;
    color: rgba(255, 255, 255, 0.65);
}

.btn-outline {
    background: #fff;
    border: 1px solid rgba(148, 163, 184, 0.6);
    color: #0f172a;
    border-radius: 12px;
    padding: 8px 14px;
    font-weight: 600;
    cursor: pointer;
    transition: border 0.2s ease, transform 0.2s ease;
}

.btn-outline:hover {
    border-color: rgba(59, 130, 246, 0.7);
    transform: translateY(-1px);
}

.merge-card {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-end;
    gap: 12px;
    background: rgba(255, 255, 255, 0.9);
    border: 1px solid rgba(226, 232, 240, 0.9);
    border-radius: 16px;
    padding: 14px;
    margin-bottom: 8px;
    box-shadow: 0 16px 32px rgba(15, 23, 42, 0.08);
}

.merge-field {
    display: grid;
    gap: 6px;
    flex: 1;
    min-width: 200px;
}

.merge-field label {
    font-size: 0.7rem;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: #64748b;
}

.merge-field input {
    border: 1px solid rgba(148, 163, 184, 0.5);
    border-radius: 12px;
    padding: 10px 12px;
    font-size: 0.95rem;
}

.btn-warn {
    background: linear-gradient(135deg, #f97316, #fb923c);
    border: none;
    color: #0f172a;
    border-radius: 12px;
    padding: 10px 16px;
    font-weight: 600;
    cursor: pointer;
    box-shadow: 0 16px 28px rgba(249, 115, 22, 0.25);
}

.panel {
    background: rgba(255, 255, 255, 0.94);
    border: 1px solid rgba(226, 232, 240, 0.9);
    border-radius: 18px;
    padding: 16px;
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

.empty {
    color: #94a3b8;
    font-size: 0.9rem;
}

.error {
    font-size: 0.8rem;
    color: #dc2626;
    margin-bottom: 8px;
}

@media (max-width: 900px) {
    .hero {
        flex-direction: column;
        align-items: flex-start;
    }

    .btn-outline {
        width: 100%;
    }
}
</style>
