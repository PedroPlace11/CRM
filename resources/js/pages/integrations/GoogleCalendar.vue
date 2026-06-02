<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';

defineProps({ connected: Boolean, sync_enabled: Boolean, calendar_id: String, last_synced_at: String, configured: Boolean });

const connect = () => router.visit('/integrations/google/connect');
const disconnect = () => router.post('/integrations/google/disconnect');
const syncNow = () => router.post('/integrations/google/sync');
</script>

<template>
    <AppLayout>
        <h1 class="text-2xl font-semibold mb-4">Google Calendar</h1>

        <div class="bg-white rounded shadow-sm p-4 text-sm space-y-2">
            <div><strong>Estado:</strong> {{ connected ? 'Ligado' : 'Nao ligado' }}</div>
            <div><strong>Calendario:</strong> {{ calendar_id || 'primary' }}</div>
            <div><strong>Ultima sincronizacao:</strong> {{ last_synced_at || 'Nunca' }}</div>

            <div v-if="!configured" class="text-xs text-red-600">
                Configura GOOGLE_CLIENT_ID, GOOGLE_CLIENT_SECRET e GOOGLE_REDIRECT_URI no .env.
            </div>

            <div class="flex gap-2">
                <button v-if="!connected" @click="connect" class="bg-indigo-600 text-white px-3 py-1.5 rounded text-sm" :disabled="!configured">Ligar conta</button>
                <button v-else @click="syncNow" class="bg-green-600 text-white px-3 py-1.5 rounded text-sm">Sincronizar agora</button>
                <button v-if="connected" @click="disconnect" class="px-3 py-1.5 rounded border text-sm">Desligar</button>
            </div>
        </div>
    </AppLayout>
</template>
