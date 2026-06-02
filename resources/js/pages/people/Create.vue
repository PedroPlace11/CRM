<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { useForm } from '@inertiajs/vue3';
const props = defineProps({ entities: Array });
const form = useForm({ name: '', email: '', phone: '', position: '', entity_id: '', notes: '' });
const submit = () => form.post('/people');
</script>

<template>
    <AppLayout>
        <div class="page-shell">
            <header class="page-header">
                <div>
                    <h1>Nova Pessoa</h1>
                    <p>Cadastre contatos com contexto e entidade associada.</p>
                </div>
            </header>

            <form @submit.prevent="submit" class="form-card">
                <div class="field">
                    <label>Nome</label>
                    <input v-model="form.name" placeholder="Nome" />
                </div>
                <div class="field">
                    <label>Email</label>
                    <input v-model="form.email" placeholder="Email" />
                </div>
                <div class="field">
                    <label>Telefone</label>
                    <input v-model="form.phone" placeholder="Telefone" />
                </div>
                <div class="field">
                    <label>Cargo</label>
                    <input v-model="form.position" placeholder="Cargo" />
                </div>
                <div class="field span-2">
                    <label>Entidade</label>
                    <select v-model="form.entity_id">
                        <option value="">— sem entidade —</option>
                        <option v-for="e in props.entities" :key="e.id" :value="e.id">{{ e.name }}</option>
                    </select>
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
.field select {
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
