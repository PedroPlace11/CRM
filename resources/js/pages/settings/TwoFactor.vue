<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    enabled: Boolean,
    has_secret: Boolean,
    recovery_codes: Array,
});

const page = usePage();
const setup = computed(() => page.props.flash?.two_factor_setup || null);
const flashedCodes = computed(() => page.props.flash?.recovery_codes || null);

const enableForm = useForm({});
const enable = () => enableForm.post('/settings/2fa/enable', { preserveScroll: true });

const confirmForm = useForm({ code: '' });
const confirm = () => confirmForm.post('/settings/2fa/confirm', {
    preserveScroll: true,
    onSuccess: () => confirmForm.reset(),
});

const disableForm = useForm({ password: '' });
const disable = () => disableForm.delete('/settings/2fa', {
    preserveScroll: true,
    onSuccess: () => disableForm.reset(),
});
</script>

<template>
    <AppLayout>
        <h1 class="text-2xl font-semibold mb-4">Autenticação de dois fatores</h1>

        <div v-if="enabled" class="bg-white rounded shadow-sm p-4 space-y-4 max-w-lg">
            <p class="text-sm text-green-700">2FA ativo na sua conta.</p>

            <div v-if="recovery_codes?.length || flashedCodes?.length">
                <h2 class="font-semibold text-sm mb-1">Códigos de recuperação</h2>
                <p class="text-xs text-gray-500 mb-2">Guarde-os em local seguro. Cada código só pode ser usado uma vez.</p>
                <ul class="font-mono text-sm bg-gray-50 rounded p-3 grid grid-cols-2 gap-1">
                    <li v-for="c in (flashedCodes || recovery_codes)" :key="c">{{ c }}</li>
                </ul>
            </div>

            <form @submit.prevent="disable" class="border-t pt-3 space-y-2">
                <label class="block text-sm font-medium">Para desativar, confirme a password:</label>
                <input v-model="disableForm.password" type="password" class="border rounded px-2 py-1 w-full text-sm" />
                <div v-if="disableForm.errors.password" class="text-xs text-red-600">{{ disableForm.errors.password }}</div>
                <button class="bg-red-600 text-white px-3 py-1.5 rounded text-sm" :disabled="disableForm.processing">
                    Desativar 2FA
                </button>
            </form>
        </div>

        <div v-else class="bg-white rounded shadow-sm p-4 space-y-4 max-w-lg">
            <p class="text-sm text-gray-600">
                Adicione uma camada extra de segurança através de uma app de autenticação (Google Authenticator, 1Password, Authy).
            </p>

            <button v-if="!has_secret && !setup" @click="enable"
                class="bg-indigo-600 text-white px-3 py-1.5 rounded text-sm">
                Iniciar configuração
            </button>

            <div v-if="setup" class="space-y-3">
                <div class="text-sm">
                    <p>1. Adicione esta chave na sua app de autenticação:</p>
                    <code class="block bg-gray-50 p-2 rounded font-mono text-xs mt-1 break-all">{{ setup.secret }}</code>
                </div>
                <div class="text-sm">
                    <p>2. Ou use o URL <code>otpauth://</code>:</p>
                    <code class="block bg-gray-50 p-2 rounded font-mono text-xs mt-1 break-all">{{ setup.otp_url }}</code>
                </div>
                <form @submit.prevent="confirm" class="space-y-2">
                    <label class="block text-sm font-medium">3. Insira o código gerado:</label>
                    <input v-model="confirmForm.code" inputmode="numeric" maxlength="6"
                        class="border rounded px-2 py-1 w-full text-sm font-mono" placeholder="000000" />
                    <div v-if="confirmForm.errors.code" class="text-xs text-red-600">{{ confirmForm.errors.code }}</div>
                    <button class="bg-green-600 text-white px-3 py-1.5 rounded text-sm" :disabled="confirmForm.processing">
                        Confirmar e ativar
                    </button>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
