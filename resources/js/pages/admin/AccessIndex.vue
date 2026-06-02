<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { router, useForm } from '@inertiajs/vue3';

const props = defineProps({
    roles: { type: Array, default: () => [] },
    permissions: { type: Array, default: () => [] },
    permissionOptions: { type: Array, default: () => [] },
});

const roleForm = useForm({ name: '', permissions: [] });
const submitRole = () =>
    roleForm.post('/admin/access/roles', {
        preserveScroll: true,
        onSuccess: () => roleForm.reset('name', 'permissions'),
    });

const deleteRole = (role) => {
    if (!confirm(`Remover o cargo "${role.name}"?`)) return;
    router.delete(`/admin/access/roles/${role.id}`, { preserveScroll: true });
};

const deletePermission = (permission) => {
    if (!confirm(`Remover a permissao "${permission.name}"?`)) return;
    router.delete(`/admin/access/permissions/${permission.id}`, { preserveScroll: true });
};
</script>

<template>
    <AppLayout>
        <div class="page-shell">
            <header class="page-header">
                <div>
                    <h1>Cargos e Permissoes</h1>
                    <p>Gira os cargos e permissoes disponiveis no CRM.</p>
                </div>
            </header>

            <div class="grid">
                <section class="card">
                    <div class="card-header">
                        <h2>Cargos</h2>
                        <p>Crie cargos e defina permissoes associadas.</p>
                    </div>
                    <form class="form" @submit.prevent="submitRole">
                        <div class="field">
                            <label>Nome do cargo</label>
                            <input v-model="roleForm.name" placeholder="Ex: comercial" />
                            <small v-if="roleForm.errors.name" class="error">{{ roleForm.errors.name }}</small>
                        </div>
                        <div class="field">
                            <label>Permissoes</label>
                            <select v-model="roleForm.permissions" multiple>
                                <option v-for="permission in props.permissionOptions" :key="permission" :value="permission">
                                    {{ permission }}
                                </option>
                            </select>
                        </div>
                        <button class="btn-primary" :disabled="roleForm.processing">Criar cargo</button>
                    </form>

                    <div class="list" v-if="props.roles.length">
                        <div v-for="role in props.roles" :key="role.id" class="list-row">
                            <div>
                                <div class="list-title">{{ role.name }}</div>
                                <div class="list-subtitle">{{ role.permissions.length ? role.permissions.join(', ') : 'Sem permissoes' }}</div>
                            </div>
                            <button class="btn-ghost" type="button" @click="deleteRole(role)">Remover</button>
                        </div>
                    </div>
                    <div v-else class="empty">Sem cargos cadastrados.</div>
                </section>

                <section class="card">
                    <div class="card-header">
                        <h2>Permissoes</h2>
                        <p>Permissoes pre-criadas para usar nos cargos e utilizadores.</p>
                    </div>
                    <div class="list" v-if="props.permissions.length">
                        <div v-for="permission in props.permissions" :key="permission.id" class="list-row">
                            <div class="list-title">{{ permission.name }}</div>
                            <button class="btn-ghost" type="button" @click="deletePermission(permission)">Remover</button>
                        </div>
                    </div>
                    <div v-else class="empty">Sem permissoes cadastradas.</div>
                </section>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Fraunces:wght@600&family=Sora:wght@400;500;600;700&display=swap');

.page-shell {
    --ink: #0f172a;
    --muted: #64748b;
    --border: rgba(148, 163, 184, 0.45);
    --surface: #ffffff;
    --accent: #f97316;
    --accent-ink: #0f172a;
    background: radial-gradient(circle at top right, #fff1e6 0%, #f8fafc 45%, #eef2ff 100%);
    border: 1px solid rgba(226, 232, 240, 0.8);
    border-radius: 22px;
    padding: 22px;
    font-family: 'Sora', system-ui, -apple-system, sans-serif;
    max-width: 1120px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 30px 60px rgba(15, 23, 42, 0.08);
}

.page-shell::before {
    content: '';
    position: absolute;
    inset: 0;
    background: radial-gradient(circle at 15% 15%, rgba(249, 115, 22, 0.12), transparent 40%),
        radial-gradient(circle at 90% 20%, rgba(59, 130, 246, 0.12), transparent 45%);
    opacity: 0.9;
    pointer-events: none;
}

.page-header {
    position: relative;
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 18px;
    margin-bottom: 20px;
    animation: float-in 0.6s ease-out;
}

.page-header h1 {
    font-family: 'Fraunces', serif;
    font-size: 2.2rem;
    margin: 0 0 6px;
    color: var(--ink);
}

.page-header p {
    margin: 0;
    color: var(--muted);
    font-size: 0.98rem;
    max-width: 520px;
}

.grid {
    position: relative;
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 18px;
}

.card {
    background: rgba(255, 255, 255, 0.92);
    border: 1px solid var(--border);
    border-radius: 18px;
    padding: 18px;
    box-shadow: 0 16px 40px rgba(15, 23, 42, 0.08);
    display: grid;
    gap: 14px;
    animation: rise-in 0.7s ease-out;
}

.card-header h2 {
    margin: 0 0 4px;
    font-family: 'Fraunces', serif;
    font-size: 1.4rem;
    color: var(--ink);
}

.card-header p {
    margin: 0;
    color: var(--muted);
    font-size: 0.9rem;
}

.form {
    display: grid;
    gap: 12px;
}

.field {
    display: grid;
    gap: 8px;
}

.field label {
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.7px;
    color: var(--muted);
}

.field input,
.field select {
    border: 1px solid rgba(148, 163, 184, 0.5);
    border-radius: 12px;
    padding: 12px 14px;
    font-size: 0.95rem;
    background: #fff;
    transition: border 0.2s ease, box-shadow 0.2s ease;
}

.field input:focus,
.field select:focus {
    outline: none;
    border-color: rgba(249, 115, 22, 0.8);
    box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.15);
}

.field select {
    min-height: 120px;
}

.btn-primary {
    background: linear-gradient(135deg, #f97316, #fb923c);
    color: var(--accent-ink);
    border: none;
    padding: 10px 18px;
    border-radius: 14px;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: fit-content;
    box-shadow: 0 16px 28px rgba(249, 115, 22, 0.25);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.btn-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 20px 34px rgba(249, 115, 22, 0.3);
}

.list {
    display: grid;
    gap: 12px;
}

.list-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    background: #f8fafc;
    border: 1px solid rgba(226, 232, 240, 0.9);
    border-radius: 14px;
    padding: 12px 14px;
}

.list-title {
    font-weight: 600;
    color: var(--ink);
}

.list-subtitle {
    font-size: 0.85rem;
    color: var(--muted);
}

.btn-ghost {
    background: #fff;
    border: 1px solid rgba(148, 163, 184, 0.6);
    color: var(--ink);
    border-radius: 12px;
    padding: 6px 12px;
    cursor: pointer;
    transition: border 0.2s ease, color 0.2s ease, transform 0.2s ease;
}

.btn-ghost:hover {
    border-color: rgba(249, 115, 22, 0.6);
    color: #ea580c;
    transform: translateY(-1px);
}

.empty {
    font-size: 0.9rem;
    color: #94a3b8;
}

.error {
    font-size: 0.75rem;
    color: #dc2626;
}

@keyframes float-in {
    from {
        opacity: 0;
        transform: translateY(8px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes rise-in {
    from {
        opacity: 0;
        transform: translateY(12px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (max-width: 900px) {
    .grid {
        grid-template-columns: 1fr;
    }

    .btn-primary {
        width: 100%;
    }
}
</style>
