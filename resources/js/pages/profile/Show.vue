<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    user: Object,
    two_factor: Object,
});

const page = usePage();
const flashedCodes = computed(() => page.props.flash?.recovery_codes || null);

const initials = computed(() => (props.user?.name || '?')
    .split(' ').filter(Boolean).slice(0, 2).map(p => p[0].toUpperCase()).join(''));

// Profile form
const profileForm = useForm({ name: props.user.name, email: props.user.email });
const submitProfile = () => profileForm.patch('/profile', { preserveScroll: true });

// Password form
const passwordForm = useForm({
    current_password: '', password: '', password_confirmation: '',
});
const submitPassword = () => passwordForm.put('/profile/password', {
    preserveScroll: true,
    onSuccess: () => passwordForm.reset(),
});

// 2FA forms
const enableForm  = useForm({ password: '' });
const confirmForm = useForm({ code: '' });
const disableForm = useForm({ password: '' });

const enable2fa = () => enableForm.post('/profile/2fa/enable', {
    preserveScroll: true,
    onSuccess: () => enableForm.reset(),
});
const confirm2fa = () => confirmForm.post('/profile/2fa/confirm', {
    preserveScroll: true,
    onSuccess: () => confirmForm.reset(),
});
const disable2fa = () => disableForm.delete('/profile/2fa', {
    preserveScroll: true,
    onSuccess: () => disableForm.reset(),
});
</script>

<template>
    <AppLayout>
        <div class="space-y-6 text-slate-100">
            <!-- Banner -->
            <section class="rounded-xl bg-gradient-to-r from-indigo-700 via-blue-700 to-blue-600 p-6 flex flex-wrap items-center gap-6 shadow">
                <div class="w-14 h-14 rounded-lg bg-white/20 grid place-items-center text-xl font-semibold">
                    {{ initials }}
                </div>
                <div class="flex-1 min-w-[14rem]">
                    <div class="text-xs uppercase tracking-wide text-white/70">Conta pessoal</div>
                    <h1 class="text-2xl font-semibold">{{ user.name }}</h1>
                    <p class="text-sm text-white/80">{{ user.email }}</p>
                </div>
                <div class="text-right space-y-1 max-w-xs">
                    <span class="inline-flex items-center gap-1 text-xs px-2 py-1 rounded-full bg-white/15 backdrop-blur">
                        {{ two_factor.enabled ? '2FA ativada' : '2FA desativada' }}
                    </span>
                    <p class="text-xs text-white/80">
                        Aqui pode alterar o nome, email, password e ativar a autenticação em dois fatores.
                    </p>
                </div>
            </section>

            <!-- Profile + Password -->
            <div class="grid md:grid-cols-2 gap-6">
                <section class="rounded-xl border border-slate-700 bg-slate-900/60 p-5">
                    <h2 class="font-semibold mb-4">Dados do perfil</h2>
                    <form @submit.prevent="submitProfile" class="space-y-3">
                        <div>
                            <label class="block text-xs mb-1 text-slate-400">Nome</label>
                            <input v-model="profileForm.name"
                                class="w-full rounded bg-slate-800 border border-slate-700 px-3 py-2 text-sm" />
                            <div v-if="profileForm.errors.name" class="text-xs text-red-400 mt-1">{{ profileForm.errors.name }}</div>
                        </div>
                        <div>
                            <label class="block text-xs mb-1 text-slate-400">Email</label>
                            <input v-model="profileForm.email" type="email"
                                class="w-full rounded bg-slate-800 border border-slate-700 px-3 py-2 text-sm" />
                            <div v-if="profileForm.errors.email" class="text-xs text-red-400 mt-1">{{ profileForm.errors.email }}</div>
                        </div>
                        <div class="flex justify-end">
                            <button class="bg-blue-600 hover:bg-blue-500 text-white text-sm px-4 py-1.5 rounded"
                                :disabled="profileForm.processing">Guardar perfil</button>
                        </div>
                    </form>
                </section>

                <section class="rounded-xl border border-slate-700 bg-slate-900/60 p-5">
                    <h2 class="font-semibold mb-4">Alterar password</h2>
                    <form @submit.prevent="submitPassword" class="space-y-3">
                        <div>
                            <label class="block text-xs mb-1 text-slate-400">Password atual</label>
                            <input v-model="passwordForm.current_password" type="password"
                                class="w-full rounded bg-slate-800 border border-slate-700 px-3 py-2 text-sm" />
                            <div v-if="passwordForm.errors.current_password" class="text-xs text-red-400 mt-1">{{ passwordForm.errors.current_password }}</div>
                        </div>
                        <div>
                            <label class="block text-xs mb-1 text-slate-400">Nova password</label>
                            <input v-model="passwordForm.password" type="password"
                                class="w-full rounded bg-slate-800 border border-slate-700 px-3 py-2 text-sm" />
                            <div v-if="passwordForm.errors.password" class="text-xs text-red-400 mt-1">{{ passwordForm.errors.password }}</div>
                        </div>
                        <div>
                            <label class="block text-xs mb-1 text-slate-400">Confirmar password</label>
                            <input v-model="passwordForm.password_confirmation" type="password"
                                class="w-full rounded bg-slate-800 border border-slate-700 px-3 py-2 text-sm" />
                        </div>
                        <div class="flex justify-end">
                            <button class="bg-blue-600 hover:bg-blue-500 text-white text-sm px-4 py-1.5 rounded"
                                :disabled="passwordForm.processing">Alterar password</button>
                        </div>
                    </form>
                </section>
            </div>

            <!-- 2FA -->
            <section class="rounded-xl border border-slate-700 bg-slate-900/60 p-5">
                <h2 class="font-semibold mb-4">Segurança e 2FA</h2>

                <div class="grid md:grid-cols-2 gap-6">
                    <!-- Status / actions -->
                    <div>
                        <div class="text-xs uppercase tracking-wide text-slate-400">Estado atual</div>
                        <div class="text-lg font-semibold mb-2">
                            {{ two_factor.enabled ? '2FA ativada' : '2FA desativada' }}
                        </div>

                        <p class="text-sm text-slate-400 mb-4">
                            A 2FA usa códigos de 6 dígitos gerados por uma app autenticadora (Google Authenticator, 1Password, Authy).
                        </p>

                        <!-- Enable: needs password -->
                        <form v-if="!two_factor.enabled" @submit.prevent="enable2fa" class="space-y-2">
                            <label class="block text-xs text-slate-400">Password atual</label>
                            <input v-model="enableForm.password" type="password" placeholder="Confirme para ativar a 2FA"
                                class="w-full rounded bg-slate-800 border border-slate-700 px-3 py-2 text-sm" />
                            <div v-if="enableForm.errors.password" class="text-xs text-red-400">{{ enableForm.errors.password }}</div>
                            <button class="bg-blue-600 hover:bg-blue-500 text-white text-sm px-4 py-1.5 rounded"
                                :disabled="enableForm.processing">
                                {{ two_factor.has_secret ? 'Regenerar segredo' : 'Ativar 2FA' }}
                            </button>
                        </form>

                        <!-- Confirm step shown whenever a secret exists but 2FA still disabled -->
                        <form v-if="!two_factor.enabled && two_factor.has_secret" @submit.prevent="confirm2fa" class="space-y-2 mt-4">
                            <label class="block text-xs text-slate-400">Código gerado pela app</label>
                            <input v-model="confirmForm.code" inputmode="numeric" maxlength="6" placeholder="000000"
                                class="w-full rounded bg-slate-800 border border-slate-700 px-3 py-2 text-sm font-mono" />
                            <div v-if="confirmForm.errors.code" class="text-xs text-red-400">{{ confirmForm.errors.code }}</div>
                            <button class="bg-green-600 hover:bg-green-500 text-white text-sm px-4 py-1.5 rounded"
                                :disabled="confirmForm.processing">Confirmar e ativar</button>
                        </form>

                        <!-- Disable -->
                        <form v-if="two_factor.enabled" @submit.prevent="disable2fa" class="space-y-2">
                            <label class="block text-xs text-slate-400">Password atual</label>
                            <input v-model="disableForm.password" type="password"
                                class="w-full rounded bg-slate-800 border border-slate-700 px-3 py-2 text-sm" />
                            <div v-if="disableForm.errors.password" class="text-xs text-red-400">{{ disableForm.errors.password }}</div>
                            <button class="bg-red-600 hover:bg-red-500 text-white text-sm px-4 py-1.5 rounded"
                                :disabled="disableForm.processing">Desativar 2FA</button>
                        </form>

                        <!-- Recovery codes -->
                        <div v-if="two_factor.enabled && (flashedCodes?.length || two_factor.recovery_codes?.length)" class="mt-5">
                            <div class="text-xs uppercase tracking-wide text-slate-400 mb-1">Códigos de recuperação</div>
                            <p class="text-xs text-slate-500 mb-2">Guarde-os em local seguro. Cada código só funciona uma vez.</p>
                            <ul class="font-mono text-xs bg-slate-800 rounded p-3 grid grid-cols-2 gap-1">
                                <li v-for="c in (flashedCodes || two_factor.recovery_codes)" :key="c">{{ c }}</li>
                            </ul>
                        </div>
                    </div>

                    <!-- QR -->
                    <div>
                        <div class="text-xs uppercase tracking-wide text-slate-400 mb-2">QR code</div>

                        <div v-if="two_factor.qr_svg && !two_factor.enabled"
                             class="bg-white rounded-lg p-3 inline-block">
                            <div v-html="two_factor.qr_svg" class="w-48 h-48"></div>
                        </div>
                        <p v-else-if="two_factor.enabled" class="text-sm text-slate-400">
                            2FA ativa. Para regenerar a chave desative primeiro.
                        </p>
                        <p v-else class="text-sm text-slate-400">Ative a 2FA para gerar o QR code.</p>

                        <div v-if="two_factor.secret && !two_factor.enabled" class="mt-3 text-xs text-slate-400 space-y-1">
                            <div>Chave manual:</div>
                            <code class="block bg-slate-800 px-2 py-1 rounded break-all">{{ two_factor.secret }}</code>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
