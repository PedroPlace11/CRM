<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { useForm, router } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';

const props = defineProps({ deal: Object, stages: Array });

const tab = ref('overview');

// Quick-create activity
const activityForm = useForm({ type: 'call', title: '', body: '', happened_at: '' });
const submitActivity = () => activityForm.post(`/deals/${props.deal.id}/activities`, {
    onSuccess: () => activityForm.reset(),
});

// Proposal upload
const proposalFile = ref(null);
const uploadProposal = () => {
    if (!proposalFile.value) return;
    const fd = new FormData();
    fd.append('file', proposalFile.value);
    useForm({}).post(`/deals/${props.deal.id}/proposals`, { forceFormData: true, data: fd });
};

// Send proposal email
const emailForm = useForm({
    to_email: props.deal.person?.email || props.deal.entity?.email || '',
    subject: `Proposta: ${props.deal.title}`,
    body: `Caro/a ${props.deal.person?.name || 'cliente'},\n\nSegue em anexo a proposta para o seu ${props.deal.title}.\nFico ao dispor para qualquer esclarecimento.\n\nCumprimentos.`,
});
const sendingProposalId = ref(null);
const sendProposal = (proposal) => {
    sendingProposalId.value = proposal.id;
    emailForm.post(`/deals/${props.deal.id}/proposals/${proposal.id}/send`, {
        onFinish: () => sendingProposalId.value = null,
    });
};

// Follow-up cancel
const cancelFollowUp = (reason) => {
    if (!confirm(reason === 'client_replied'
        ? 'Marcar como respondido pelo cliente e parar a sequencia?'
        : 'Cancelar a sequencia de follow-up?')) return;
    router.post(`/deals/${props.deal.id}/follow-up/cancel`, { reason }, { preserveScroll: true });
};
const activeFollowUp = computed(() =>
    props.deal.follow_up_sequence && props.deal.follow_up_sequence.status === 'active'
        ? props.deal.follow_up_sequence : null);

// Timeline (server-side filters)
const timeline = ref([]);
const filters = ref({ kinds: ['activity', 'email', 'event'], types: [], from: '', to: '' });
const loadingTimeline = ref(false);
let timelineTimer = null;
const loadTimeline = async () => {
    loadingTimeline.value = true;
    const params = new URLSearchParams();
    filters.value.kinds.forEach(k => params.append('kinds[]', k));
    filters.value.types.forEach(t => params.append('types[]', t));
    if (filters.value.from) params.set('from', filters.value.from);
    if (filters.value.to)   params.set('to', filters.value.to);
    try {
        const res = await fetch(`/deals/${props.deal.id}/timeline?${params}`, { headers: { Accept: 'application/json' } });
        timeline.value = await res.json();
    } finally { loadingTimeline.value = false; }
};
const startTimelinePolling = () => {
    if (timelineTimer) return;
    timelineTimer = setInterval(() => loadTimeline(), 30000);
};
const stopTimelinePolling = () => {
    if (!timelineTimer) return;
    clearInterval(timelineTimer);
    timelineTimer = null;
};

watch(() => tab.value, (t) => {
    if (t === 'timeline') {
        loadTimeline();
        startTimelinePolling();
    } else {
        stopTimelinePolling();
    }
});
onUnmounted(() => stopTimelinePolling());

// Modal preview
const previewEntry = ref(null);
const openPreview = (entry) => { previewEntry.value = entry; };
</script>

<template>
    <AppLayout>
        <div class="page-shell">
            <header class="hero">
                <div>
                    <p class="eyebrow">Pipeline</p>
                    <h1>{{ deal.title }}</h1>
                    <div class="meta">
                        <span v-if="deal.entity?.name">{{ deal.entity?.name }}</span>
                        <span v-if="deal.person?.name">{{ deal.person?.name }}</span>
                        <span>EUR {{ deal.value }}</span>
                        <span class="stage-pill">{{ deal.stage?.name }}</span>
                    </div>
                </div>
            </header>

            <div v-if="activeFollowUp" class="followup">
                <div>
                    <strong>Follow-up automatico ativo</strong>
                    - proximo envio: {{ activeFollowUp.next_send_at }}
                    - enviados: {{ activeFollowUp.sent_count }}
                </div>
                <div class="followup-actions">
                    <button @click="cancelFollowUp('client_replied')" class="btn-outline">Cliente respondeu</button>
                    <button @click="cancelFollowUp('cancelled_by_user')" class="btn-outline danger">Cancelar</button>
                </div>
            </div>

            <div class="tabs">
                <button v-for="t in ['overview', 'timeline', 'proposals']" :key="t"
                    @click="tab = t"
                    :class="['tab', tab === t ? 'active' : '']">
                    {{ t }}
                </button>
            </div>

            <section v-if="tab === 'overview'" class="grid">
                <div class="panel">
                    <h2>Detalhes</h2>
                    <dl class="details">
                        <div><dt>Probabilidade</dt><dd>{{ deal.probability }}%</dd></div>
                        <div><dt>Fecho previsto</dt><dd>{{ deal.expected_close_date }}</dd></div>
                        <div><dt>Responsavel</dt><dd>{{ deal.owner?.name }}</dd></div>
                    </dl>
                </div>

                <div class="panel">
                    <h2>Registo rapido</h2>
                    <form @submit.prevent="submitActivity" class="form">
                        <select v-model="activityForm.type">
                            <option value="call">Chamada</option>
                            <option value="task">Tarefa</option>
                            <option value="meeting">Reuniao</option>
                            <option value="note">Nota</option>
                            <option value="email">Email</option>
                        </select>
                        <input v-model="activityForm.title" placeholder="Titulo" />
                        <textarea v-model="activityForm.body" placeholder="Detalhes"></textarea>
                        <button class="btn-primary" :disabled="activityForm.processing">
                            Registar
                        </button>
                    </form>
                </div>
            </section>

            <section v-if="tab === 'timeline'" class="panel">
                <div class="timeline-header">
                    <h2>Cronologia</h2>
                    <div class="filters">
                        <label><input type="checkbox" value="activity" v-model="filters.kinds" /> Atividades</label>
                        <label><input type="checkbox" value="email" v-model="filters.kinds" /> Emails</label>
                        <label><input type="checkbox" value="event" v-model="filters.kinds" /> Eventos</label>
                        <input type="date" v-model="filters.from" />
                        <input type="date" v-model="filters.to" />
                        <button @click="loadTimeline" class="btn-outline">Aplicar</button>
                        <span v-if="loadingTimeline" class="hint">A carregar...</span>
                    </div>
                </div>

                <ul class="timeline-list">
                    <li v-for="(e, i) in timeline" :key="i" class="timeline-item">
                        <div class="timeline-main">
                            <span class="kind"
                                  :class="{
                                    activity: e.kind === 'activity',
                                    email: e.kind === 'email',
                                    event: e.kind === 'event',
                                  }">{{ e.kind }}</span>
                            <span class="title">{{ e.data.title || e.data.subject }}</span>
                            <div class="date">{{ e.at }}</div>
                        </div>
                        <button @click="openPreview(e)" class="link">Ver</button>
                    </li>
                    <li v-if="!timeline.length && !loadingTimeline" class="empty">Sem registos.</li>
                </ul>
            </section>

            <section v-if="tab === 'proposals'" class="panel proposals">
                <div class="panel-header">
                    <h2>Propostas</h2>
                    <div class="upload">
                        <input type="file" @change="proposalFile = $event.target.files[0]" />
                        <button @click="uploadProposal" class="btn-outline">Carregar</button>
                    </div>
                </div>

                <ul class="proposal-list">
                    <li v-for="p in deal.proposals" :key="p.id" class="proposal-row">
                        <div>
                            <div class="proposal-title">{{ p.title }}</div>
                            <div class="proposal-meta">
                                {{ Math.round(p.size_bytes / 1024) }} KB -
                                {{ p.sent_at ? `Enviada em ${p.sent_at}` : 'Nao enviada' }}
                            </div>
                        </div>
                        <div class="proposal-actions">
                            <a :href="`/deals/${deal.id}/proposals/${p.id}/download`" class="link">Descarregar</a>
                            <button @click="sendProposal(p)" :disabled="sendingProposalId === p.id" class="btn-primary">
                                Enviar ao cliente
                            </button>
                        </div>
                    </li>
                </ul>

                <div class="email-editor">
                    <h3>Email de envio (editavel)</h3>
                    <input v-model="emailForm.to_email" />
                    <input v-model="emailForm.subject" />
                    <textarea v-model="emailForm.body" rows="6"></textarea>
                </div>
            </section>

            <div v-if="previewEntry" class="modal" @click.self="previewEntry = null">
                <div class="modal-card">
                    <div class="modal-header">
                        <h3>{{ previewEntry.data.title || previewEntry.data.subject }}</h3>
                        <button @click="previewEntry = null" class="link">Fechar</button>
                    </div>
                    <div class="modal-meta">{{ previewEntry.kind }} · {{ previewEntry.at }}</div>
                    <pre class="modal-body">{{ previewEntry.data.body || previewEntry.data.description || '(sem conteudo)' }}</pre>
                    <div v-if="previewEntry.kind === 'email'" class="modal-meta">
                        Para: {{ previewEntry.data.to_email }}
                        <span v-if="previewEntry.data.replied_at"> · Respondido em {{ previewEntry.data.replied_at }}</span>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Fraunces:wght@600&family=Manrope:wght@400;500;600;700&display=swap');

.page-shell {
    font-family: 'Manrope', system-ui, -apple-system, sans-serif;
    background: radial-gradient(circle at 12% 0%, #eef2ff 0%, #f8fafc 45%, #fff7ed 100%);
    border: 1px solid rgba(226, 232, 240, 0.9);
    border-radius: 22px;
    padding: 22px;
    max-width: 1180px;
    box-shadow: 0 24px 60px rgba(15, 23, 42, 0.08);
}

.hero {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    gap: 16px;
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
}

.meta span {
    display: inline-flex;
    align-items: center;
    max-width: 200px;
    padding: 6px 10px;
    border-radius: 999px;
    background: #fff;
    border: 1px solid rgba(148, 163, 184, 0.4);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.stage-pill {
    background: #0f172a;
    color: #fff;
    border: none;
    max-width: 140px;
}

.followup {
    margin-bottom: 12px;
    background: rgba(255, 255, 255, 0.9);
    border: 1px solid rgba(226, 232, 240, 0.9);
    border-radius: 16px;
    padding: 12px 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    box-shadow: 0 16px 32px rgba(15, 23, 42, 0.06);
}

.followup-actions {
    display: flex;
    gap: 8px;
}

.tabs {
    display: flex;
    gap: 10px;
    border-bottom: 1px solid rgba(226, 232, 240, 0.9);
    margin-bottom: 18px;
}

.tab {
    padding: 10px 14px;
    font-size: 0.9rem;
    color: #64748b;
    background: transparent;
    border: none;
    border-bottom: 2px solid transparent;
    cursor: pointer;
}

.tab.active {
    color: #0f172a;
    border-bottom-color: #f97316;
    font-weight: 600;
}

.grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 18px;
}

.panel {
    background: rgba(255, 255, 255, 0.95);
    border: 1px solid rgba(226, 232, 240, 0.9);
    border-radius: 18px;
    padding: 16px;
    box-shadow: 0 16px 40px rgba(15, 23, 42, 0.08);
}

.panel h2 {
    font-size: 1.1rem;
    margin: 0 0 12px;
    color: #0f172a;
}

.details {
    display: grid;
    gap: 8px;
    font-size: 0.95rem;
}

.details dt {
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.12em;
    font-size: 0.65rem;
}

.details dd {
    margin: 2px 0 0;
    color: #0f172a;
}

.form {
    display: grid;
    gap: 10px;
}

.form input,
.form select,
.form textarea,
.email-editor input,
.email-editor textarea,
.timeline-header input {
    border: 1px solid rgba(148, 163, 184, 0.5);
    border-radius: 12px;
    padding: 10px 12px;
    font-size: 0.95rem;
    background: #fff;
}

.btn-primary {
    background: linear-gradient(135deg, #f97316, #fb923c);
    color: #0f172a;
    border: none;
    padding: 10px 16px;
    border-radius: 12px;
    font-weight: 600;
    cursor: pointer;
    box-shadow: 0 16px 28px rgba(249, 115, 22, 0.25);
}

.btn-outline {
    background: #fff;
    border: 1px solid rgba(148, 163, 184, 0.6);
    color: #0f172a;
    border-radius: 12px;
    padding: 8px 12px;
    cursor: pointer;
    font-weight: 600;
}

.btn-outline.danger {
    color: #b91c1c;
}

.timeline-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
}

.filters {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
    font-size: 0.9rem;
    color: #64748b;
}

.hint {
    font-size: 0.8rem;
    color: #94a3b8;
}

.timeline-list {
    margin-top: 12px;
    display: grid;
    gap: 10px;
}

.timeline-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 10px 12px;
    border-radius: 12px;
    background: #f8fafc;
    border: 1px solid rgba(226, 232, 240, 0.9);
}

.timeline-main {
    display: grid;
    gap: 4px;
}

.kind {
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    padding: 4px 8px;
    border-radius: 999px;
    width: fit-content;
    background: #e2e8f0;
}

.kind.activity {
    background: #e0e7ff;
    color: #3730a3;
}

.kind.email {
    background: #dbeafe;
    color: #1d4ed8;
}

.kind.event {
    background: #dcfce7;
    color: #166534;
}

.title {
    font-weight: 600;
    color: #0f172a;
}

.date {
    font-size: 0.8rem;
    color: #94a3b8;
}

.link {
    color: #2563eb;
    font-weight: 600;
    background: transparent;
    border: none;
    cursor: pointer;
}

.proposal-list {
    display: grid;
    gap: 12px;
    margin-top: 10px;
}

.proposal-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 12px;
    border-radius: 14px;
    background: #f8fafc;
    border: 1px solid rgba(226, 232, 240, 0.9);
}

.proposal-title {
    font-weight: 600;
}

.proposal-meta {
    font-size: 0.85rem;
    color: #64748b;
}

.proposal-actions {
    display: flex;
    gap: 10px;
    align-items: center;
}

.email-editor {
    border-top: 1px solid rgba(226, 232, 240, 0.9);
    padding-top: 12px;
    display: grid;
    gap: 10px;
}

.email-editor h3 {
    margin: 0;
    font-size: 0.95rem;
}

.modal {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.4);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
    z-index: 50;
}

.modal-card {
    background: #fff;
    border-radius: 18px;
    padding: 18px;
    max-width: 640px;
    width: 100%;
    box-shadow: 0 24px 60px rgba(15, 23, 42, 0.2);
    display: grid;
    gap: 12px;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-meta {
    font-size: 0.85rem;
    color: #94a3b8;
}

.modal-body {
    background: #f8fafc;
    border-radius: 12px;
    padding: 12px;
    white-space: pre-wrap;
    max-height: 380px;
    overflow: auto;
    font-size: 0.9rem;
}

.empty {
    color: #94a3b8;
    text-align: center;
    padding: 12px 0;
}

@media (max-width: 960px) {
    .grid {
        grid-template-columns: 1fr;
    }

    .hero {
        flex-direction: column;
        align-items: flex-start;
    }

    .followup {
        flex-direction: column;
        align-items: flex-start;
    }

    .proposal-row {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>
