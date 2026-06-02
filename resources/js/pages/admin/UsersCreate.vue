<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    roles: { type: Array, default: () => [] },
    permissions: { type: Array, default: () => [] },
});

const form = useForm({
    name: '',
    email: '',
    password: '',
    roles: [],
    permissions: [],
});
const submit = () => form.post('/admin/users');
</script>

<template>
    <AppLayout>
        <div class="page-shell">
            <header class="page-header">
                <div>
                    <h1>Criar utilizador</h1>
                    <p>Apenas admins podem criar contas para aceder ao CRM.</p>
                </div>
            </header>

            <form @submit.prevent="submit" class="form-card">
                <div class="field">
                    <label>Nome</label>
                    <input v-model="form.name" placeholder="Nome" />
                </div>
                <div class="field">
                    <label>Email</label>
                    <input v-model="form.email" placeholder="Email" type="email" />
                </div>
                <div class="field span-2">
                    <label>Senha</label>
                    <input v-model="form.password" placeholder="Senha" type="password" />
                </div>
                <div class="field">
                    <label>Cargos</label>
                    <select v-model="form.roles" multiple required>
                        <option v-for="role in props.roles" :key="role" :value="role">{{ role }}</option>
                    </select>
                    <small v-if="form.errors.roles" class="error">{{ form.errors.roles }}</small>
                    <small v-if="!props.roles.length" class="hint">Sem cargos cadastrados.</small>
                </div>
                <div class="field">
                    <label>Permissoes</label>
                    <select v-model="form.permissions" multiple>
                        <option v-for="permission in props.permissions" :key="permission" :value="permission">
                            {{ permission }}
                        </option>
                    </select>
                    <small v-if="!props.permissions.length" class="hint">Sem permissoes cadastradas.</small>
                </div>
                <div class="actions span-2">
                    <button class="btn-primary" :disabled="form.processing">Criar conta</button>
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

.field input {
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 10px 12px;
    font-size: 0.95rem;
    background: #fff;
}

.field select {
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 10px 12px;
    font-size: 0.95rem;
    background: #fff;
    min-height: 110px;
}

.hint {
    font-size: 0.75rem;
    color: #94a3b8;
}

.error {
    font-size: 0.75rem;
    color: #dc2626;
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
