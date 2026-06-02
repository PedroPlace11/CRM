<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { useForm } from '@inertiajs/vue3';
const props = defineProps({ deal: Object, stages: Array, entities: Array, people: Array });
const form = useForm({ ...props.deal });
const submit = () => form.put(`/deals/${props.deal.id}`);
</script>

<template>
    <AppLayout>
        <h1 class="text-2xl font-semibold mb-4">Editar Negócio</h1>
        <form @submit.prevent="submit" class="space-y-3 bg-white p-4 rounded shadow-sm max-w-xl">
            <input v-model="form.title" class="border rounded px-3 py-2 w-full" />
            <input v-model.number="form.value" type="number" step="0.01" class="border rounded px-3 py-2 w-full" />
            <select v-model="form.stage_id" class="border rounded px-3 py-2 w-full">
                <option v-for="s in stages" :key="s.id" :value="s.id">{{ s.name }}</option>
            </select>
            <button class="bg-indigo-600 text-white px-4 py-2 rounded">Guardar</button>
        </form>
    </AppLayout>
</template>
