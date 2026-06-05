<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { router, usePage } from '@inertiajs/vue3';
import { computed, nextTick, onMounted, ref, watch } from 'vue';

const messages = ref([]);
const input = ref('');
const conversationId = ref(null);
const conversations = ref([]);
const streaming = ref(false);
const loadingHistory = ref(false);
const chatCardRef = ref(null);
const page = usePage();
const isEmbed = computed(() => page.url.includes('embed=1'));

const suggestions = [
    'Qual o volume de negocios no estado Em negociacao?',
    'Quantos negocios tenho no estado Proposta?',
    'Qual o telefone do Antonio Pinheiro?',
    'Qual o email do Joao Silva?',
];

const loadConversations = async () => {
    const res = await fetch('/ai/conversations', { headers: { Accept: 'application/json' } });
    conversations.value = await res.json();
};

const scrollToLastMessage = async () => {
    await nextTick();
    if (!chatCardRef.value) return;
    chatCardRef.value.scrollTop = chatCardRef.value.scrollHeight;
};

const loadMessages = async (id) => {
    if (!id) return;
    loadingHistory.value = true;
    try {
        const res = await fetch(`/ai/conversations/${id}`, { headers: { Accept: 'application/json' } });
        const data = await res.json();
        messages.value = data.map((m) => ({
            role: m.role,
            content: m.content,
            payload: m.meta?.payload || null,
        }));
        await scrollToLastMessage();
    } finally {
        loadingHistory.value = false;
    }
};

const startNewConversation = () => {
    conversationId.value = null;
    messages.value = [];
};

const send = async () => {
    if (!input.value.trim()) return;
    const userMsg = input.value.trim();
    messages.value.push({ role: 'user', content: userMsg });
    input.value = '';
    streaming.value = true;
    messages.value.push({ role: 'assistant', content: '', payload: null });

    const fallbackNoStream = async () => {
        const fallbackRes = await fetch('/ai/stream?no_stream=1', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ message: userMsg, conversation_id: conversationId.value }),
        });

        const data = await fallbackRes.json();
        if (!fallbackRes.ok) {
            throw new Error(data?.message || `HTTP ${fallbackRes.status}`);
        }

        messages.value[messages.value.length - 1].content = data.reply || '';
        if (data.conversation_id) conversationId.value = data.conversation_id;
        if (data.payload) messages.value[messages.value.length - 1].payload = data.payload;
        await scrollToLastMessage();
    };

    try {
        const res = await fetch('/ai/stream', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'text/event-stream',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ message: userMsg, conversation_id: conversationId.value }),
        });

        if (!res.ok || !res.body) {
            await fallbackNoStream();
            return;
        }

        const reader = res.body.getReader();
        const dec = new TextDecoder();
        let sseBuffer = '';
        let gotDelta = false;
        let gotError = false;

        while (true) {
            const { value, done } = await reader.read();
            if (done) break;
            sseBuffer += dec.decode(value, { stream: true });

            const lines = sseBuffer.split('\n');
            sseBuffer = lines.pop() || '';

            for (const line of lines) {
                if (!line.startsWith('data:')) continue;
                try {
                    const data = JSON.parse(line.slice(5).trim());
                    if (data.delta) {
                        messages.value[messages.value.length - 1].content += data.delta;
                        gotDelta = true;
                    }
                    if (data.conversation_id) conversationId.value = data.conversation_id;
                    if (data.payload) messages.value[messages.value.length - 1].payload = data.payload;
                    if (data.error) {
                        messages.value[messages.value.length - 1].content = `[erro: ${data.error}]`;
                        gotError = true;
                    }
                } catch {}
            }
        }

        if (!gotDelta && !gotError) {
            await fallbackNoStream();
        }
    } catch (e) {
        messages.value[messages.value.length - 1].content = '[erro: ' + e.message + ']';
    } finally {
        streaming.value = false;
    }
};

const runAction = (action) => {
    if (action.type === 'open_url') {
        router.visit(action.url);
        return;
    }
    if (action.type === 'create_activity') {
        router.post(`/deals/${action.deal_id}/activities`, {
            type: action.activity_type || 'task',
            title: action.title,
            body: action.body || '',
        }, { preserveScroll: true });
        return;
    }
    if (action.type === 'add_note') {
        router.post(`/deals/${action.deal_id}/activities`, {
            type: 'note',
            title: action.title || 'Nota',
            body: action.body || '',
        }, { preserveScroll: true });
    }
};

const sendSuggestion = (text) => {
    input.value = text;
    send();
};

onMounted(async () => {
    await loadConversations();
    if (conversations.value.length) {
        conversationId.value = conversations.value[0].id;
        await loadMessages(conversationId.value);
    }
    await scrollToLastMessage();
});

watch(
    () => messages.value.length,
    async () => {
        await scrollToLastMessage();
    }
);
</script>

<template>
    <component :is="isEmbed ? 'div' : AppLayout" :class="isEmbed ? 'embed-shell' : ''">
        <div class="page-shell">
            <header class="page-header">
                <div>
                    <h1>Chat IA do CRM</h1>
                    <p>Assistente de dados, negocios e contactos em tempo real.</p>
                </div>
                <div class="header-actions">
                    <select v-if="conversations.length" v-model="conversationId" @change="loadMessages(conversationId)"
                        class="select">
                        <option v-for="c in conversations" :key="c.id" :value="c.id">{{ c.title || 'Conversa' }}</option>
                    </select>
                    <button @click="startNewConversation" class="btn-ghost">Nova conversa</button>
                </div>
            </header>

            <div class="suggestions">
                <button v-for="s in suggestions" :key="s" @click="sendSuggestion(s)" class="chip">
                    {{ s }}
                </button>
            </div>

            <div ref="chatCardRef" class="chat-card">
                <div v-if="loadingHistory" class="loading">A carregar historico...</div>
                <div v-for="(m, i) in messages" :key="i" :class="['bubble', m.role === 'user' ? 'user' : 'ai']">
                    <div class="bubble-head">
                        <span>{{ m.role === 'user' ? 'Tu' : 'IA' }}</span>
                    </div>
                    <div class="bubble-body">{{ m.content }}</div>

                    <div v-if="m.payload?.results?.length" class="results">
                        <div class="results-title">Resultados</div>
                        <ul class="results-list">
                            <li v-for="r in m.payload.results" :key="`${r.type}-${r.id}`">
                                <div>
                                    <span class="result-name">{{ r.name || r.title }}</span>
                                    <span v-if="r.value" class="result-meta"> · € {{ Number(r.value).toLocaleString('pt-PT') }}</span>
                                </div>
                                <div class="result-actions">
                                    <button v-if="r.type === 'deal'" @click="runAction({ type: 'create_activity', deal_id: r.id, title: 'Follow-up via chat', activity_type: 'task' })"
                                        class="link-green">Criar tarefa</button>
                                    <button v-if="r.url" @click="runAction({ type: 'open_url', url: r.url })"
                                        class="link-blue">Abrir</button>
                                </div>
                            </li>
                        </ul>
                    </div>

                    <div v-if="m.payload?.actions?.length" class="payload-actions">
                        <button v-for="(a, idx) in m.payload.actions" :key="idx" @click="runAction(a)"
                            class="chip tiny">
                            {{ a.label || 'Acao' }}
                        </button>
                    </div>
                </div>
            </div>

            <form @submit.prevent="send" class="composer">
                <input v-model="input" placeholder="Pergunta..." class="input" :disabled="streaming" />
                <button class="btn-primary" :disabled="streaming">Enviar</button>
            </form>
        </div>
    </component>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Fraunces:wght@600&family=Space+Grotesk:wght@400;600;700&display=swap');

.embed-shell {
    min-height: 100vh;
    background: #f8fafc;
    padding: 16px;
}

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
    margin-bottom: 12px;
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

.header-actions {
    display: flex;
    gap: 8px;
    align-items: center;
}

.btn-primary {
    color: #fff;
    border: none;
    padding: 10px 14px;
    border-radius: 12px;
    font-weight: 600;
    box-shadow: 0 12px 24px rgba(29, 78, 216, 0.25);
}

.btn-ghost {
    background: #fff;
    border: 1px solid #e2e8f0;
    padding: 8px 12px;
    border-radius: 10px;
    font-weight: 600;
}

.suggestions {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 12px;
}

.chip {
    background: #fff;
    border: 1px solid #e2e8f0;
    padding: 6px 10px;
    border-radius: 999px;
    font-size: 0.8rem;
    font-weight: 600;
}

.chip.tiny {
    font-size: 0.75rem;
}

.chat-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 12px;
    height: 60vh;
    overflow-y: auto;
    display: grid;
    gap: 12px;
    box-shadow: 0 12px 30px rgba(15, 23, 42, 0.06);
}

.loading {
    font-size: 0.85rem;
    color: #94a3b8;
}

.bubble {
    padding: 12px;
    border-radius: 14px;
    font-size: 0.95rem;
    line-height: 1.5;
    border: 1px solid transparent;
}

.bubble.user {
    background: #eef2ff;
    border-color: #c7d2fe;
}

.bubble.ai {
    background: #f8fafc;
    border-color: #e2e8f0;
}

.bubble-head {
    font-weight: 700;
    margin-bottom: 6px;
}

.bubble-body {
    white-space: pre-line;
}

.results {
    margin-top: 10px;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 10px;
}

.results-title {
    font-weight: 700;
    font-size: 0.85rem;
    margin-bottom: 6px;
    color: #475569;
}

.results-list {
    display: grid;
    gap: 6px;
    font-size: 0.85rem;
}

.results-list li {
    display: flex;
    justify-content: space-between;
    gap: 12px;
}

.result-name {
    font-weight: 600;
}

.result-meta {
    color: #94a3b8;
}

.result-actions {
    display: flex;
    gap: 8px;
}

.select {
    min-width: 280px;
    border: 1px solid #dbe2f0;
    border-radius: 10px;
    padding: 8px 10px;
    background: #fff;
    font-size: 0.95rem;
}
.link-green {
    color: #16a34a;
    font-weight: 600;
}

.link-blue {
    color: #1d4ed8;
    font-weight: 600;
}

.payload-actions {
    margin-top: 8px;
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.composer {
    display: flex;
    gap: 10px;
    margin-top: 12px;
}

.input {
    flex: 1;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 10px 12px;
    font-size: 0.95rem;
    background: #fff;
}

.helper {
    margin-top: 10px;
    font-size: 0.8rem;
    color: #94a3b8;
}

.mono {
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace;
}

@media (max-width: 900px) {
    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .header-actions {
        width: 100%;
        flex-direction: column;
        align-items: stretch;
    }

    .btn-ghost,
    .btn-primary {
        width: 100%;
        text-align: center;
    }
}
</style>
