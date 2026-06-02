<script setup>
import { useForm } from '@inertiajs/vue3';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

function submit() {
    form.post('/login');
}
</script>

<template>
    <div class="login-shell">
        <div class="login-card">
            <div class="brand">
                <div class="mark">CRM</div>
                <div class="tag">Acesso seguro</div>
            </div>

            <div class="title">
                <h1>Entrar</h1>
                <p>Gestao comercial com foco em produtividade.</p>
            </div>

            <form @submit.prevent="submit" class="form">
                <div class="field">
                    <label>Email</label>
                    <input v-model="form.email" type="email" required autofocus placeholder="nome@empresa.com" />
                    <div v-if="form.errors.email" class="error">{{ form.errors.email }}</div>
                </div>

                <div class="field">
                    <label>Password</label>
                    <input v-model="form.password" type="password" required placeholder="••••••••" />
                    <div v-if="form.errors.password" class="error">{{ form.errors.password }}</div>
                </div>

                <label class="remember">
                    <input type="checkbox" v-model="form.remember" />
                    <span>Lembrar-me</span>
                </label>

                <button type="submit" :disabled="form.processing" class="btn">
                    Entrar
                </button>
            </form>

        </div>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Fraunces:wght@600&family=Space+Grotesk:wght@400;600;700&display=swap');

.login-shell {
    min-height: 100vh;
    display: grid;
    place-items: center;
    padding: 32px 20px;
    background:
        radial-gradient(900px 500px at 10% -10%, #e0e7ff 0%, transparent 60%),
        radial-gradient(800px 460px at 100% 10%, #ffedd5 0%, transparent 55%),
        linear-gradient(180deg, #f8fafc, #eef2ff);
    font-family: 'Space Grotesk', system-ui, -apple-system, sans-serif;
    color: #0f172a;
}

.login-card {
    width: min(420px, 100%);
    background: rgba(255, 255, 255, 0.9);
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    padding: 26px;
    box-shadow: 0 24px 60px rgba(15, 23, 42, 0.12);
    backdrop-filter: blur(10px);
}

.brand {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 16px;
}

.mark {
    font-family: 'Fraunces', serif;
    font-size: 1.4rem;
}

.tag {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    color: #64748b;
}

.title h1 {
    font-size: 1.6rem;
    margin: 0 0 6px;
}

.title p {
    margin: 0 0 18px;
    color: #475569;
    font-size: 0.95rem;
}

.form {
    display: grid;
    gap: 14px;
}

.field {
    display: grid;
    gap: 6px;
}

.field label {
    font-size: 0.85rem;
    color: #334155;
}

.field input {
    width: 100%;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    padding: 10px 12px;
    font-size: 0.95rem;
    background: #fff;
}

.error {
    color: #dc2626;
    font-size: 0.8rem;
}

.remember {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.85rem;
    color: #475569;
}

.btn {
    border: none;
    background: #3b82f6;
    color: #fff;
    padding: 12px 16px;
    border-radius: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: transform 120ms ease, box-shadow 120ms ease;
    box-shadow: 0 14px 30px rgba(59, 130, 246, 0.25);
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    box-shadow: none;
}

.btn:hover:not(:disabled) {
    transform: translateY(-1px);
}

</style>
