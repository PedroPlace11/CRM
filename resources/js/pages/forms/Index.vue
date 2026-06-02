<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({ forms: Array });

const fieldTypes = [
    { value: 'text', label: 'Texto' },
    { value: 'email', label: 'Email' },
    { value: 'tel', label: 'Telefone' },
    { value: 'number', label: 'Numero' },
    { value: 'textarea', label: 'Area de texto' },
];

const showCreate = ref(false);
const newForm = useForm({
    name: '',
    success_message: 'Obrigado!',
    captcha_required: true,
    fields: [
        { key: 'name',  label: 'Nome',  type: 'text',  required: true },
        { key: 'email', label: 'Email', type: 'email', required: true },
        { key: 'phone', label: 'Telefone', type: 'tel', required: false },
        { key: 'message', label: 'Mensagem', type: 'textarea', required: false },
    ],
});

const create = () => newForm.post('/admin/forms', {
    onSuccess: () => { showCreate.value = false; newForm.reset('name'); },
});

const addField = (target) => {
    target.fields.push({ key: '', label: '', type: 'text', required: false });
};

const removeField = (target, idx) => {
    target.fields.splice(idx, 1);
};

const editingId = ref(null);
const editForm = useForm({ id: null, name: '', success_message: '', captcha_required: true, fields: [] });

const startEdit = (form) => {
    editingId.value = form.id;
    editForm.id = form.id;
    editForm.name = form.name;
    editForm.success_message = form.success_message || '';
    editForm.captcha_required = !!form.captcha_required;
    editForm.fields = JSON.parse(JSON.stringify(form.fields || []));
};

const cancelEdit = () => {
    editingId.value = null;
};

const saveEdit = () => editForm.patch(`/admin/forms/${editForm.id}`, {
    data: {
        name: editForm.name,
        success_message: editForm.success_message,
        captcha_required: editForm.captcha_required,
        fields: editForm.fields,
    },
    onSuccess: () => { editingId.value = null; },
});

const toggle = (form) => router.post(`/admin/forms/${form.id}/toggle`, {}, { preserveScroll: true });
const remove = (form) => {
    if (confirm(`Remover formulário "${form.name}"?`)) {
        router.delete(`/admin/forms/${form.id}`);
    }
};
const copyEmbed = (form) => {
    const url = `${location.origin}/forms/${form.slug}`;
    const snippet = `<iframe src="${url}" style="border:0;width:100%;min-height:520px"></iframe>`;
    navigator.clipboard.writeText(snippet);
};
</script>

<template>
    <AppLayout>
        <div class="page-shell">
            <header class="page-header">
                <div>
                    <h1>Formulários públicos</h1>
                    <p>Crie formulários embutíveis para captar leads.</p>
                </div>
                <button @click="showCreate = !showCreate" class="btn-primary">
                    {{ showCreate ? 'Cancelar' : 'Novo formulário' }}
                </button>
            </header>

            <form v-if="showCreate" @submit.prevent="create" class="form-card">
                <div class="field span-2">
                    <label>Nome do formulário</label>
                    <input v-model="newForm.name" placeholder="Ex: Pedido de contato" required />
                </div>
                <div class="field span-2">
                    <label>Mensagem de sucesso</label>
                    <input v-model="newForm.success_message" placeholder="Obrigado!" />
                </div>
                <label class="toggle span-2">
                    <input type="checkbox" v-model="newForm.captcha_required" />
                    <span>Exigir captcha (hCaptcha)</span>
                </label>

                <div class="fields-card span-2">
                    <div class="fields-head">
                        <span>Campos ({{ newForm.fields.length }})</span>
                        <button type="button" @click="addField(newForm)" class="btn-ghost">Adicionar campo</button>
                    </div>
                    <div class="fields-list">
                        <div v-for="(f, i) in newForm.fields" :key="i" class="field-row">
                            <input v-model="f.key" placeholder="chave" />
                            <input v-model="f.label" placeholder="label" />
                            <select v-model="f.type">
                                <option v-for="t in fieldTypes" :key="t.value" :value="t.value">{{ t.label }}</option>
                            </select>
                            <label class="mini-toggle">
                                <input type="checkbox" v-model="f.required" />
                                <span>Obrig.</span>
                            </label>
                            <button type="button" @click="removeField(newForm, i)" class="btn-danger">Remover</button>
                        </div>
                    </div>
                </div>

                <div class="form-actions span-2">
                    <button class="btn-success" :disabled="newForm.processing">Criar</button>
                </div>
            </form>

            <div class="list-card">
                <ul>
                    <li v-for="f in forms" :key="f.id" class="form-row">
                        <div class="form-info">
                            <div class="form-title">
                                <span>{{ f.name }}</span>
                                <span :class="['pill', f.active ? 'pill-on' : 'pill-off']">
                                    {{ f.active ? 'ativo' : 'inativo' }}
                                </span>
                                <span class="form-count">{{ f.submissions_count }} leads</span>
                            </div>
                            <div class="form-url">URL: <span>/forms/{{ f.slug }}</span></div>
                        </div>
                        <div class="row-actions">
                            <button @click="startEdit(f)" class="btn-ghost">Editar</button>
                            <button @click="copyEmbed(f)" class="btn-ghost">Copiar embed</button>
                            <button @click="toggle(f)" class="btn-ghost">
                                {{ f.active ? 'Desativar' : 'Ativar' }}
                            </button>
                            <button @click="remove(f)" class="btn-danger">Remover</button>
                        </div>
                    </li>
                    <li v-if="!forms?.length" class="empty">Nenhum formulário criado.</li>
                </ul>
            </div>
        </div>

        <div v-if="editingId" class="modal" @click.self="cancelEdit">
            <div class="modal-card">
                <div class="modal-head">
                    <h2>Editar formulário</h2>
                    <button @click="cancelEdit">✕</button>
                </div>
                <div class="modal-body">
                    <div class="field">
                        <label>Nome</label>
                        <input v-model="editForm.name" />
                    </div>
                    <div class="field">
                        <label>Mensagem de sucesso</label>
                        <input v-model="editForm.success_message" />
                    </div>
                    <label class="toggle">
                        <input type="checkbox" v-model="editForm.captcha_required" />
                        <span>Exigir captcha</span>
                    </label>

                    <div class="fields-card">
                        <div class="fields-head">
                            <span>Campos ({{ editForm.fields.length }})</span>
                            <button type="button" @click="addField(editForm)" class="btn-ghost">Adicionar campo</button>
                        </div>
                        <div class="fields-list">
                            <div v-for="(f, i) in editForm.fields" :key="i" class="field-row">
                                <input v-model="f.key" placeholder="chave" />
                                <input v-model="f.label" placeholder="label" />
                                <select v-model="f.type">
                                    <option v-for="t in fieldTypes" :key="t.value" :value="t.value">{{ t.label }}</option>
                                </select>
                                <label class="mini-toggle">
                                    <input type="checkbox" v-model="f.required" />
                                    <span>Obrig.</span>
                                </label>
                                <button type="button" @click="removeField(editForm, i)" class="btn-danger">Remover</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-actions">
                    <button @click="cancelEdit" class="btn-ghost">Cancelar</button>
                    <button @click="saveEdit" class="btn-primary" :disabled="editForm.processing">Guardar</button>
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

.btn-primary {
    background: #1d4ed8;
    color: #fff;
    border: none;
    padding: 10px 14px;
    border-radius: 12px;
    font-weight: 600;
    box-shadow: 0 12px 24px rgba(29, 78, 216, 0.25);
}

.btn-success {
    background: #16a34a;
    color: #fff;
    border: none;
    padding: 10px 14px;
    border-radius: 12px;
    font-weight: 600;
}

.btn-ghost {
    background: #fff;
    border: 1px solid #e2e8f0;
    padding: 8px 12px;
    border-radius: 10px;
    font-weight: 600;
}

.btn-danger {
    background: #fff5f5;
    border: 1px solid #fecaca;
    color: #dc2626;
    padding: 8px 12px;
    border-radius: 10px;
    font-weight: 600;
}

.form-card {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 14px;
    margin-bottom: 16px;
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
    padding: 8px 10px;
    font-size: 0.95rem;
    background: #fff;
}

.span-2 {
    grid-column: span 2 / span 2;
}

.toggle,
.mini-toggle {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 0.9rem;
    color: #475569;
}

.fields-card {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 12px;
}

.fields-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-weight: 600;
    color: #475569;
    font-size: 0.9rem;
}

.fields-list {
    margin-top: 10px;
    display: grid;
    gap: 8px;
}

.field-row {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr auto auto;
    gap: 8px;
    align-items: center;
}

.field-row input,
.field-row select {
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 6px 8px;
    font-size: 0.85rem;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
}

.list-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    overflow: hidden;
}

.form-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 14px 16px;
    border-bottom: 1px solid #e2e8f0;
}

.form-row:last-child {
    border-bottom: none;
}

.form-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 700;
}

.form-count {
    color: #94a3b8;
    font-size: 0.85rem;
}

.form-url {
    color: #64748b;
    font-size: 0.85rem;
}

.form-url span {
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace;
}

.pill {
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    padding: 4px 8px;
    border-radius: 999px;
}

.pill-on {
    background: #dcfce7;
    color: #15803d;
}

.pill-off {
    background: #f1f5f9;
    color: #64748b;
}

.row-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.empty {
    padding: 20px;
    text-align: center;
    color: #94a3b8;
}

.modal {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.45);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
    z-index: 50;
}

.modal-card {
    background: #fff;
    border-radius: 16px;
    padding: 16px;
    width: 100%;
    max-width: 720px;
    box-shadow: 0 20px 40px rgba(15, 23, 42, 0.2);
    display: grid;
    gap: 12px;
}

.modal-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.modal-head h2 {
    margin: 0;
    font-weight: 700;
}

.modal-head button {
    background: transparent;
    border: none;
    font-size: 1.2rem;
    color: #64748b;
}

.modal-body {
    display: grid;
    gap: 10px;
}

.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
}

@media (max-width: 900px) {
    .field-row {
        grid-template-columns: 1fr 1fr;
    }

    .row-actions {
        width: 100%;
    }
}

@media (max-width: 720px) {
    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .btn-primary {
        width: 100%;
        text-align: center;
    }

    .form-row {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>
