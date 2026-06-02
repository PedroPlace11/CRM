<script setup>
import { useForm } from '@inertiajs/vue3';

const form = useForm({ code: '' });
const submit = () => form.post('/2fa/challenge');
</script>

<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-50 p-6">
        <form @submit.prevent="submit" class="w-full max-w-sm space-y-4 bg-white p-6 rounded shadow">
            <h1 class="text-xl font-semibold">Verificação 2FA</h1>
            <p class="text-sm text-gray-500">Insira o código da sua app de autenticação ou um código de recuperação.</p>

            <div>
                <input v-model="form.code" autofocus class="border rounded px-3 py-2 w-full font-mono"
                    placeholder="000000" />
                <div v-if="form.errors.code" class="text-sm text-red-600 mt-1">{{ form.errors.code }}</div>
            </div>

            <button :disabled="form.processing" class="w-full bg-blue-600 text-white rounded px-4 py-2 hover:bg-blue-700 disabled:opacity-50">
                Verificar
            </button>
        </form>
    </div>
</template>
