<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="welcome-shell">
        <div class="hero-grid">
            <div class="hero-copy">
                <div class="badge">CRM para equipas comerciais</div>
                <h1>CRM</h1>
                <p>
                    Um sistema moderno para gerir entidades, pessoas, negócios e atividades
                    num fluxo simples e rápido.
                </p>
                <div class="cta-row">
                    <Link href="/dashboard" class="cta primary">Entrar</Link>
                    <Link href="/ai/chat" class="cta ghost">Abrir chat IA</Link>
                </div>
                <div class="meta">
                    <span>Kanban dinâmico</span>
                    <span>Calendário integrado</span>
                    <span>Automatizações</span>
                </div>
            </div>

            <div class="hero-card">
                <div class="card-head">
                    <div class="dot"></div>
                    <div class="dot"></div>
                    <div class="dot"></div>
                </div>
                <div class="card-body">
                    <div class="metric">
                        <span>Pipeline em aberto</span>
                        <strong>€ 128k</strong>
                    </div>
                    <div class="metric">
                        <span>Negócios ativos</span>
                        <strong>24</strong>
                    </div>
                    <div class="metric">
                        <span>Atividades hoje</span>
                        <strong>7</strong>
                    </div>
                    <div class="progress">
                        <div class="bar" style="width: 62%"></div>
                    </div>
                    <p class="hint">Resumo rápido do desempenho comercial</p>
                </div>
            </div>
        </div>

        <div class="ribbon">
            <div class="ribbon-item">
                <strong>Dados claros</strong>
                <span>Histórico completo por negócio</span>
            </div>
            <div class="ribbon-item">
                <strong>IA na prática</strong>
                <span>Respostas rápidas e contexto real</span>
            </div>
            <div class="ribbon-item">
                <strong>Mais foco</strong>
                <span>Tarefas e follow-ups automáticos</span>
            </div>
        </div>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Fraunces:wght@600&family=Space+Grotesk:wght@400;600;700&display=swap');

.welcome-shell {
    min-height: 100vh;
    padding: 40px 24px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: 28px;
    background:
        radial-gradient(1000px 500px at 10% -10%, #e0e7ff 0%, transparent 55%),
        radial-gradient(900px 520px at 100% 10%, #ffedd5 0%, transparent 55%),
        linear-gradient(180deg, #f8fafc, #eef2ff);
    color: #0f172a;
    font-family: 'Space Grotesk', system-ui, -apple-system, sans-serif;
}

.hero-grid {
    display: grid;
    grid-template-columns: 1.1fr 0.9fr;
    gap: 32px;
    align-items: center;
    max-width: 1100px;
    margin: 0 auto;
    width: 100%;
}

.hero-copy h1 {
    font-family: 'Fraunces', serif;
    font-size: 3rem;
    margin: 10px 0 8px;
}

.hero-copy p {
    font-size: 1.05rem;
    color: #475569;
    line-height: 1.6;
    margin-bottom: 20px;
}

.badge {
    display: inline-flex;
    align-items: center;
    background: #0f172a;
    color: #fff;
    padding: 6px 12px;
    border-radius: 999px;
    font-size: 0.75rem;
    letter-spacing: 0.6px;
}

.cta-row {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    margin-bottom: 14px;
}

.cta {
    text-decoration: none;
    padding: 10px 16px;
    border-radius: 12px;
    font-weight: 600;
    font-size: 0.95rem;
}

.cta.primary {
    background: #3b82f6;
    color: #fff;
    box-shadow: 0 12px 28px rgba(59, 130, 246, 0.3);
}

.cta.ghost {
    background: #fff;
    border: 1px solid #c7d2fe;
    color: #1f2937;
}

.meta {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    font-size: 0.85rem;
    color: #64748b;
}

.hero-card {
    background: rgba(255, 255, 255, 0.8);
    border-radius: 18px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 20px 50px rgba(15, 23, 42, 0.12);
    backdrop-filter: blur(10px);
    overflow: hidden;
}

.card-head {
    display: flex;
    gap: 6px;
    padding: 12px 16px;
    border-bottom: 1px solid #e2e8f0;
}

.dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: #cbd5f5;
}

.card-body {
    padding: 18px;
    display: grid;
    gap: 12px;
}

.metric {
    display: flex;
    justify-content: space-between;
    font-size: 0.95rem;
}

.metric strong {
    font-size: 1.2rem;
}

.progress {
    height: 8px;
    background: #e2e8f0;
    border-radius: 999px;
    overflow: hidden;
}

.bar {
    height: 100%;
    background: linear-gradient(90deg, #3b82f6, #22c55e);
}

.hint {
    font-size: 0.8rem;
    color: #64748b;
}

.ribbon {
    max-width: 1100px;
    margin: 0 auto;
    width: 100%;
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 16px;
}

.ribbon-item {
    background: #fff;
    border-radius: 14px;
    padding: 14px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
}

.ribbon-item strong {
    display: block;
    margin-bottom: 4px;
}

.ribbon-item span {
    color: #64748b;
    font-size: 0.85rem;
}

@media (max-width: 960px) {
    .hero-grid {
        grid-template-columns: 1fr;
    }
    .ribbon {
        grid-template-columns: 1fr;
    }
}
</style>
