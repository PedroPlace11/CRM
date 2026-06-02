<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { useForm } from '@inertiajs/vue3';
const props = defineProps({ entity: Object });
const form = useForm({ ...props.entity });
const submit = () => form.put(`/entities/${props.entity.id}`);
</script>

<template>
    <AppLayout>
        <h1 class="text-2xl font-semibold mb-4">Editar Entidade</h1>
        <form @submit.prevent="submit" class="space-y-3 bg-white p-4 rounded shadow-sm max-w-xl">
            <input v-model="form.name" class="border rounded px-3 py-2 w-full" />
            <input v-model="form.vat" class="border rounded px-3 py-2 w-full" />
            <input v-model="form.email" class="border rounded px-3 py-2 w-full" />
            <input v-model="form.phone" class="border rounded px-3 py-2 w-full" />
            <button class="bg-indigo-600 text-white px-4 py-2 rounded">Guardar</button>
        </form>
    </AppLayout>
</template>
