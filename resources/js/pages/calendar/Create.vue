<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { useForm } from '@inertiajs/vue3';
const props = defineProps({ entities: Array, people: Array, deals: Array });
const form = useForm({
    title: '', description: '', type: 'meeting',
    start_at: '', end_at: '', location: '',
    eventable_type: '', eventable_id: '',
});
const submit = () => form.post('/calendar');
</script>

<template>
    <AppLayout>
        <div class="page-shell">
            <header class="page-header">
                <div>
                    <h1>Novo Evento</h1>
                    <p>Agende compromissos com contexto e notas.</p>
                </div>
            </header>

            <form @submit.prevent="submit" class="form-card">
                <div class="field span-2">
                    <label>Titulo</label>
                    <input v-model="form.title" placeholder="Titulo" />
                </div>
                <div class="field">
                    <label>Tipo</label>
                    <select v-model="form.type">
                        <option value="meeting">Reuniao</option>
                        <option value="call">Chamada</option>
                        <option value="task">Tarefa</option>
                        <option value="note">Nota</option>
                    </select>
                </div>
                <div class="field">
                    <label>Inicio</label>
                    <input v-model="form.start_at" type="datetime-local" />
                </div>
                <div class="field">
                    <label>Fim</label>
                    <input v-model="form.end_at" type="datetime-local" />
                </div>
                <div class="field">
                    <label>Local</label>
                    <input v-model="form.location" placeholder="Local" />
                </div>
                <div class="field span-2">
                    <label>Descricao</label>
                    <textarea v-model="form.description" placeholder="Descricao" rows="4"></textarea>
                </div>
                <div class="actions span-2">
                    <button class="btn-primary">Criar</button>
                </div>
            </form>
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
    max-width: 920px;
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

.form-card {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 16px;
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
.field select,
.field textarea {
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 10px 12px;
    font-size: 0.95rem;
    background: #fff;
}

.span-2 {
    grid-column: span 2 / span 2;
}

.actions {
    display: flex;
    justify-content: flex-end;
}

.btn-primary {
    background: #1d4ed8;
    color: #fff;
    border: none;
    padding: 10px 16px;
    border-radius: 12px;
    font-weight: 600;
    box-shadow: 0 12px 24px rgba(29, 78, 216, 0.25);
}

@media (max-width: 720px) {
    .form-card {
        grid-template-columns: 1fr;
    }

    .span-2 {
        grid-column: span 1 / span 1;
    }

    .actions {
        justify-content: stretch;
    }

    .btn-primary {
        width: 100%;
    }
}
</style>
