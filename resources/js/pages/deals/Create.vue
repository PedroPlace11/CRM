<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { useForm } from '@inertiajs/vue3';
const props = defineProps({ stages: Array, entities: Array, people: Array });
const form = useForm({
    title: '', value: 0, probability: 0, stage_id: props.stages[0]?.id,
    entity_id: '', person_id: '', expected_close_date: '', notes: '',
});
const submit = () => form.post('/deals');
</script>

<template>
    <AppLayout>
        <h1 class="text-2xl font-semibold mb-4">Novo Negócio</h1>
        <form @submit.prevent="submit" class="space-y-3 bg-white p-4 rounded shadow-sm max-w-xl">
            <input v-model="form.title" placeholder="Título" class="border rounded px-3 py-2 w-full" />
            <input v-model.number="form.value" type="number" step="0.01" placeholder="Valor" class="border rounded px-3 py-2 w-full" />
            <input v-model.number="form.probability" type="number" min="0" max="100" placeholder="Probabilidade %" class="border rounded px-3 py-2 w-full" />
            <select v-model="form.stage_id" class="border rounded px-3 py-2 w-full">
                <option v-for="s in stages" :key="s.id" :value="s.id">{{ s.name }}</option>
            </select>
            <select v-model="form.entity_id" class="border rounded px-3 py-2 w-full">
                <option value="">— sem entidade —</option>
                <option v-for="e in entities" :key="e.id" :value="e.id">{{ e.name }}</option>
            </select>
            <select v-model="form.person_id" class="border rounded px-3 py-2 w-full">
                <option value="">— sem pessoa —</option>
                <option v-for="p in people" :key="p.id" :value="p.id">{{ p.name }}</option>
            </select>
            <input v-model="form.expected_close_date" type="date" class="border rounded px-3 py-2 w-full" />
            <button class="bg-indigo-600 text-white px-4 py-2 rounded">Criar</button>
        </form>
    </AppLayout>
</template>
