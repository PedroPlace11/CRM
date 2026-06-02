<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({ templates: Array });

const showCreate = ref(false);
const form = useForm({ name: '', subject: '', body: '', active: true });

const create = () => form.post('/follow-ups/templates', {
    onSuccess: () => { showCreate.value = false; form.reset(); },
});

const editingId = ref(null);
const editForm = useForm({ id: null, name: '', subject: '', body: '', active: true });

const startEdit = (t) => {
    editingId.value = t.id;
    editForm.id = t.id;
    editForm.name = t.name;
    editForm.subject = t.subject;
    editForm.body = t.body;
    editForm.active = !!t.active;
};

const saveEdit = () => editForm.patch(`/follow-ups/templates/${editForm.id}`, {
    data: {
        name: editForm.name,
        subject: editForm.subject,
        body: editForm.body,
        active: editForm.active,
    },
    onSuccess: () => { editingId.value = null; },
});

const remove = (t) => {
    if (confirm(`Remover o template "${t.name}"?`)) {
        router.delete(`/follow-ups/templates/${t.id}`);
    }
};
</script>

<template>
    <AppLayout>
        <div class="flex justify-between items-center mb-4">
            <h1 class="text-2xl font-semibold">Templates de Follow-up</h1>
            <button @click="showCreate = !showCreate" class="bg-indigo-600 text-white px-3 py-1.5 rounded text-sm">
                {{ showCreate ? 'Cancelar' : 'Novo template' }}
            </button>
        </div>

        <form v-if="showCreate" @submit.prevent="create" class="bg-white rounded shadow-sm p-4 mb-4 space-y-3">
            <input v-model="form.name" placeholder="Nome" class="border rounded px-2 py-1 w-full text-sm" required />
            <input v-model="form.subject" placeholder="Assunto" class="border rounded px-2 py-1 w-full text-sm" required />
            <textarea v-model="form.body" placeholder="Corpo do email" rows="6" class="border rounded px-2 py-1 w-full text-sm" required></textarea>
            <label class="text-sm flex items-center gap-2">
                <input type="checkbox" v-model="form.active" /> Ativo
            </label>
            <button class="bg-green-600 text-white px-3 py-1.5 rounded text-sm" :disabled="form.processing">Criar</button>
        </form>

        <ul class="bg-white rounded shadow-sm divide-y text-sm">
            <li v-for="t in templates" :key="t.id" class="px-4 py-3 flex items-center justify-between gap-4">
                <div class="flex-1">
                    <div class="font-medium">{{ t.name }}</div>
                    <div class="text-xs text-gray-500">{{ t.subject }}</div>
                </div>
                <div class="flex items-center gap-2 text-xs">
                    <span :class="t.active ? 'text-green-700' : 'text-gray-400'">{{ t.active ? 'Ativo' : 'Inativo' }}</span>
                    <button @click="startEdit(t)" class="px-2 py-1 rounded border">Editar</button>
                    <button @click="remove(t)" class="px-2 py-1 rounded border text-red-600">Remover</button>
                </div>
            </li>
            <li v-if="!templates?.length" class="px-4 py-6 text-center text-gray-500">Sem templates.</li>
        </ul>

        <div v-if="editingId" class="fixed inset-0 bg-black/40 flex items-center justify-center p-4 z-50" @click.self="editingId = null">
            <div class="bg-white rounded shadow-lg max-w-2xl w-full p-5 space-y-3">
                <div class="flex items-center justify-between">
                    <h2 class="font-semibold">Editar template</h2>
                    <button @click="editingId = null" class="text-gray-500">✕</button>
                </div>
                <input v-model="editForm.name" class="border rounded px-2 py-1 w-full text-sm" />
                <input v-model="editForm.subject" class="border rounded px-2 py-1 w-full text-sm" />
                <textarea v-model="editForm.body" rows="6" class="border rounded px-2 py-1 w-full text-sm"></textarea>
                <label class="text-sm flex items-center gap-2">
                    <input type="checkbox" v-model="editForm.active" /> Ativo
                </label>
                <div class="flex justify-end gap-2">
                    <button @click="editingId = null" class="px-3 py-1.5 rounded border">Cancelar</button>
                    <button @click="saveEdit" class="bg-indigo-600 text-white px-3 py-1.5 rounded" :disabled="editForm.processing">Guardar</button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
