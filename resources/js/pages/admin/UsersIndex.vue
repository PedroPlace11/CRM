<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    users: { type: Array, default: () => [] },
});

const formatDate = (value) => (value ? new Date(value).toLocaleDateString('pt-PT') : '-');
const formatRoles = (roles) => (roles && roles.length ? roles.join(', ') : '-');
const formatPermissions = (permissions) => (permissions && permissions.length ? permissions.join(', ') : '-');
</script>

<template>
    <AppLayout>
        <div class="page-shell">
            <header class="page-header">
                <div>
                    <h1>Utilizadores</h1>
                    <p>Lista de contas criadas para aceder ao CRM.</p>
                </div>
                <Link class="btn-primary" href="/admin/users/create">Criar nova conta</Link>
            </header>

            <div v-if="props.users.length" class="table-card">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Roles</th>
                            <th>Permissoes</th>
                            <th>Criado em</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="user in props.users" :key="user.id">
                            <td>{{ user.name }}</td>
                            <td>{{ user.email }}</td>
                            <td>{{ formatRoles(user.roles) }}</td>
                            <td>{{ formatPermissions(user.permissions) }}</td>
                            <td>{{ formatDate(user.created_at) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-else class="empty-state">
                <div class="empty-card">
                    <h2>Nenhuma conta criada</h2>
                    <p>Crie a primeira conta para dar acesso ao CRM.</p>
                    <Link class="btn-outline" href="/admin/users/create">Criar nova conta</Link>
                </div>
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
    max-width: 980px;
}

.page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 14px;
    flex-wrap: wrap;
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

.table-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 10px 14px;
    box-shadow: 0 12px 24px rgba(15, 23, 42, 0.06);
    overflow-x: auto;
}

.table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.95rem;
}

.table th,
.table td {
    text-align: left;
    padding: 12px 10px;
    border-bottom: 1px solid #e2e8f0;
}

.table th {
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    color: #64748b;
}

.table tr:last-child td {
    border-bottom: none;
}

.empty-state {
    display: flex;
    justify-content: center;
    padding: 18px 0;
}

.empty-card {
    background: #fff;
    border: 1px dashed #cbd5f5;
    border-radius: 16px;
    padding: 20px;
    text-align: center;
    max-width: 420px;
}

.empty-card h2 {
    font-family: 'Fraunces', serif;
    margin: 0 0 6px;
}

.empty-card p {
    margin: 0 0 12px;
    color: #64748b;
}

.btn-primary {
    background: #1d4ed8;
    color: #fff;
    border: none;
    padding: 10px 16px;
    border-radius: 12px;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 12px 24px rgba(29, 78, 216, 0.25);
}

.btn-outline {
    background: transparent;
    color: #1d4ed8;
    border: 1px solid #c7d2fe;
    padding: 10px 16px;
    border-radius: 12px;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

@media (max-width: 720px) {
    .page-header {
        align-items: flex-start;
    }

    .btn-primary,
    .btn-outline {
        width: 100%;
    }
}
</style>
