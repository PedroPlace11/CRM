<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({ rules: Array, stages: Array });

const showCreate = ref(false);
const form = useForm({
    name: '',
    days: 7,
    stage_id: '',
    activity_type: 'task',
    priority: 'high',
    active: true,
});

const create = () => {
    form.post('/automations', {
        data: {
            name: form.name,
            active: form.active,
            trigger: { type: 'deal_inactive', days: Number(form.days), stage_id: form.stage_id || null },
            action: { type: 'create_activity', activity_type: form.activity_type, priority: form.priority },
        },
        onSuccess: () => { form.reset('name'); showCreate.value = false; },
    });
};

const toggle = (rule) => router.patch(`/automations/${rule.id}`, { active: !rule.active });
const remove = (rule) => { if (confirm(`Remover a regra "${rule.name}"?`)) router.delete(`/automations/${rule.id}`); };
</script>

<template>
    <AppLayout>
        <div class="page-shell">
            <header class="page-header">
                <div>
                    <h1>Automatizacoes</h1>
                    <p>Gatilho: negocio sem atividade · Acao: criar atividade no calendario.</p>
                </div>
                <button @click="showCreate = !showCreate" class="btn-primary">
                    {{ showCreate ? 'Cancelar' : 'Nova regra' }}
                </button>
            </header>

            <form v-if="showCreate" @submit.prevent="create" class="form-card">
                <div class="field span-2">
                    <label>Nome</label>
                    <input v-model="form.name" required />
                </div>
                <div class="field">
                    <label>Dias sem atividade</label>
                    <input v-model.number="form.days" type="number" min="1" />
                </div>
                <div class="field">
                    <label>Etapa</label>
                    <select v-model="form.stage_id">
                        <option value="">Todas</option>
                        <option v-for="s in stages" :key="s.id" :value="s.id">{{ s.name }}</option>
                    </select>
                </div>
                <div class="field">
                    <label>Tipo de atividade</label>
                    <select v-model="form.activity_type">
                        <option value="task">Tarefa</option>
                        <option value="call">Chamada</option>
                        <option value="meeting">Reuniao</option>
                    </select>
                </div>
                <div class="field">
                    <label>Prioridade</label>
                    <select v-model="form.priority">
                        <option value="normal">Normal</option>
                        <option value="high">Alta</option>
                    </select>
                </div>
                <div class="form-footer">
                    <label class="toggle">
                        <input type="checkbox" v-model="form.active" />
                        <span>Ativa</span>
                    </label>
                    <button class="btn-success" :disabled="form.processing">Criar regra</button>
                </div>
            </form>

            <div class="list-card">
                <ul>
                    <li v-for="r in rules" :key="r.id" class="rule">
                        <div class="rule-body">
                            <div class="rule-title">{{ r.name }}</div>
                            <div class="rule-meta">{{ JSON.stringify(r.trigger) }} → {{ JSON.stringify(r.action) }}</div>
                        </div>
                        <div class="rule-actions">
                            <span :class="r.active ? 'status on' : 'status'">{{ r.active ? 'Ativa' : 'Pausada' }}</span>
                            <button @click="toggle(r)" class="btn-ghost">{{ r.active ? 'Pausar' : 'Ativar' }}</button>
                            <button @click="remove(r)" class="btn-danger">Remover</button>
                        </div>
                    </li>
                    <li v-if="!rules?.length" class="empty">Sem regras.</li>
                </ul>
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
    border: none;
    padding: 10px 14px;
    border-radius: 12px;
    font-weight: 600;
    box-shadow: 0 12px 24px rgba(29, 78, 216, 0.25);
}

.form-card {
    display: grid;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: 12px;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 14px;
    margin-bottom: 14px;
    box-shadow: 0 12px 24px rgba(15, 23, 42, 0.06);
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

.form-footer {
    grid-column: span 5 / span 5;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.toggle {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 0.9rem;
    color: #475569;
}

.btn-success {
    background: #16a34a;
    color: #fff;
    border: none;
    padding: 10px 14px;
    border-radius: 12px;
    font-weight: 600;
}

.list-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    overflow: hidden;
}

.rule {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 14px 16px;
    border-bottom: 1px solid #e2e8f0;
}

.rule:last-child {
    border-bottom: none;
}

.rule-title {
    font-weight: 700;
}

.rule-meta {
    color: #64748b;
    font-size: 0.85rem;
}

.rule-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.85rem;
}

.status {
    color: #94a3b8;
    font-weight: 600;
}

.status.on {
    color: #16a34a;
}

.btn-ghost {
    background: #fff;
    border: 1px solid #e2e8f0;
    padding: 8px 10px;
    border-radius: 10px;
}

.btn-danger {
    background: #fff5f5;
    border: 1px solid #fecaca;
    color: #dc2626;
    padding: 8px 10px;
    border-radius: 10px;
}

.empty {
    padding: 20px;
    text-align: center;
    color: #94a3b8;
}

@media (max-width: 1100px) {
    .form-card {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .span-2 {
        grid-column: span 2 / span 2;
    }

    .form-footer {
        grid-column: span 2 / span 2;
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
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

    .rule {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>
