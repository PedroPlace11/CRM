<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({ product: Object, deals: Array });

const totalUnits = computed(() => props.deals.reduce((sum, deal) => {
    const qty = Number(deal.products?.[0]?.pivot?.quantity || 0);
    return sum + qty;
}, 0));

const totalRevenue = computed(() => props.deals.reduce((sum, deal) => {
    const pivot = deal.products?.[0]?.pivot;
    const qty = Number(pivot?.quantity || 0);
    const unitPrice = Number(pivot?.unit_price || props.product?.unit_price || 0);
    const discount = Number(pivot?.discount || 0);
    const lineTotal = qty * unitPrice * (1 - discount / 100);
    return sum + lineTotal;
}, 0));

const averageTicket = computed(() => {
    if (!props.deals.length) {
        return 0;
    }

    return totalRevenue.value / props.deals.length;
});

function formatCurrency(value) {
    return new Intl.NumberFormat('pt-PT', {
        style: 'currency',
        currency: 'EUR',
        maximumFractionDigits: 2,
    }).format(Number(value || 0));
}

function stageClass(stageName) {
    const value = (stageName || '').toLowerCase();

    if (value.includes('ganho') || value.includes('won')) {
        return 'deal-stage deal-stage--won';
    }

    if (value.includes('perdido') || value.includes('lost')) {
        return 'deal-stage deal-stage--lost';
    }

    return 'deal-stage deal-stage--open';
}
</script>

<template>
    <AppLayout>
        <section class="product-shell">
            <header class="product-hero">
                <div class="product-hero__top">
                    <Link href="/products/stats" class="back-link">Voltar para estatisticas</Link>
                    <span class="product-status" :class="product.active ? 'active' : 'inactive'">
                        {{ product.active ? 'Ativo' : 'Inativo' }}
                    </span>
                </div>

                <h1>{{ product.name }}</h1>

                <p class="subtitle">
                    SKU {{ product.sku || 'sem SKU' }}
                    <span class="dot">•</span>
                    Preco base {{ formatCurrency(product.unit_price) }}
                </p>

                <div class="kpi-grid">
                    <article class="kpi-card">
                        <p>Negocios</p>
                        <strong>{{ deals.length }}</strong>
                    </article>

                    <article class="kpi-card">
                        <p>Unidades vendidas</p>
                        <strong>{{ totalUnits }}</strong>
                    </article>

                    <article class="kpi-card">
                        <p>Receita total</p>
                        <strong>{{ formatCurrency(totalRevenue) }}</strong>
                    </article>

                    <article class="kpi-card">
                        <p>Ticket medio</p>
                        <strong>{{ formatCurrency(averageTicket) }}</strong>
                    </article>
                </div>
            </header>

            <section class="deals-panel">
                <div class="deals-panel__head">
                    <h2>Negocios relacionados</h2>
                    <span class="deals-count">{{ deals.length }} registro(s)</span>
                </div>

                <div v-if="deals.length" class="deal-list">
                    <article v-for="d in deals" :key="d.id" class="deal-card">
                        <div class="deal-card__main">
                            <h3>{{ d.title }}</h3>
                            <p>{{ d.entity?.name || 'Sem empresa' }} · {{ d.owner?.name || 'Sem responsavel' }}</p>
                        </div>

                        <div class="deal-card__meta">
                            <span :class="stageClass(d.stage?.name)">{{ d.stage?.name || 'Sem etapa' }}</span>
                            <span class="deal-chip">Qtd {{ d.products?.[0]?.pivot?.quantity || 0 }}</span>
                            <span class="deal-chip">Valor {{ formatCurrency((Number(d.products?.[0]?.pivot?.quantity || 0) * Number(d.products?.[0]?.pivot?.unit_price || product.unit_price || 0)) * (1 - Number(d.products?.[0]?.pivot?.discount || 0) / 100)) }}</span>
                        </div>
                    </article>
                </div>

                <div v-else class="empty-state">
                    <p>Ainda nao existem negocios vinculados a este produto.</p>
                </div>
            </section>
        </section>
    </AppLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Fraunces:wght@600;700&family=Space+Grotesk:wght@400;500;700&display=swap');

.product-shell {
    max-width: 1120px;
    margin: 0 auto;
    display: grid;
    gap: 1rem;
    font-family: 'Space Grotesk', system-ui, -apple-system, sans-serif;
}

.product-hero {
    border-radius: 18px;
    border: 1px solid #dae6f5;
    padding: 1.3rem;
    background:
        radial-gradient(circle at 10% 0%, #e6f2ff 0%, transparent 38%),
        radial-gradient(circle at 92% 15%, #e7faed 0%, transparent 35%),
        linear-gradient(140deg, #f9fbff 0%, #f2f7ff 55%, #f4fbf6 100%);
    box-shadow: 0 22px 40px -34px rgba(20, 56, 92, 0.55);
}

.product-hero__top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 0.65rem;
    gap: 0.75rem;
}

.back-link {
    text-decoration: none;
    color: #0f467d;
    font-size: 0.86rem;
    font-weight: 700;
}

.back-link:hover {
    text-decoration: underline;
}

.product-status {
    padding: 0.34rem 0.75rem;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 700;
    border: 1px solid transparent;
}

.product-status.active {
    background: #eaf8ee;
    border-color: #bee4c8;
    color: #155a31;
}

.product-status.inactive {
    background: #fbeceb;
    border-color: #f2c4bf;
    color: #8d2f24;
}

.product-hero h1 {
    margin: 0;
    font-family: 'Fraunces', serif;
    font-size: clamp(1.6rem, 1.35rem + 1vw, 2.25rem);
    color: #0e2742;
    letter-spacing: -0.02em;
}

.subtitle {
    margin: 0.45rem 0 0;
    color: #4f6784;
    font-size: 0.94rem;
}

.dot {
    margin: 0 0.45rem;
}

.kpi-grid {
    margin-top: 1rem;
    display: grid;
    gap: 0.75rem;
    grid-template-columns: repeat(4, minmax(0, 1fr));
}

.kpi-card {
    border-radius: 14px;
    border: 1px solid #dfe8f4;
    background: rgba(255, 255, 255, 0.92);
    padding: 0.85rem;
}

.kpi-card p {
    margin: 0;
    color: #516987;
    font-size: 0.78rem;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    font-weight: 700;
}

.kpi-card strong {
    margin-top: 0.36rem;
    display: block;
    color: #102a46;
    font-size: 1.25rem;
    line-height: 1.15;
}

.deals-panel {
    border-radius: 18px;
    border: 1px solid #dde6f1;
    background: #ffffff;
    padding: 1rem;
    box-shadow: 0 20px 36px -36px rgba(25, 36, 56, 0.55);
}

.deals-panel__head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    margin-bottom: 0.8rem;
}

.deals-panel__head h2 {
    margin: 0;
    font-size: 1.06rem;
    color: #102741;
}

.deals-count {
    font-size: 0.8rem;
    color: #556f8f;
    font-weight: 700;
}

.deal-list {
    display: grid;
    gap: 0.72rem;
}

.deal-card {
    border-radius: 14px;
    border: 1px solid #e2e9f3;
    background: linear-gradient(180deg, #ffffff 0%, #f9fbfe 100%);
    padding: 0.9rem;
    display: flex;
    gap: 0.7rem;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
}

.deal-card__main h3 {
    margin: 0;
    font-size: 0.98rem;
    color: #112a46;
}

.deal-card__main p {
    margin: 0.3rem 0 0;
    color: #5c7390;
    font-size: 0.86rem;
}

.deal-card__meta {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.45rem;
}

.deal-stage,
.deal-chip {
    border-radius: 999px;
    padding: 0.3rem 0.68rem;
    font-size: 0.75rem;
    font-weight: 700;
    border: 1px solid transparent;
}

.deal-stage--open {
    background: #e9f2ff;
    border-color: #bfd8ff;
    color: #164a82;
}

.deal-stage--won {
    background: #e7f8ee;
    border-color: #bae6cb;
    color: #175f37;
}

.deal-stage--lost {
    background: #fbebea;
    border-color: #f5c3bf;
    color: #8f2f25;
}

.deal-chip {
    background: #f4f7fb;
    border-color: #d9e3f1;
    color: #304760;
}

.empty-state {
    border: 1px dashed #ccd9ea;
    border-radius: 14px;
    padding: 1.15rem;
    text-align: center;
    color: #5b7290;
    background: #fbfdff;
}

@media (max-width: 980px) {
    .kpi-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 680px) {
    .kpi-grid {
        grid-template-columns: 1fr;
    }

    .deals-panel__head {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>
