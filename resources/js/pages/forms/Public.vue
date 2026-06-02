<script setup>
import { useForm } from '@inertiajs/vue3';
import { onMounted, onUnmounted } from 'vue';

const props = defineProps({ form: Object, hcaptcha_sitekey: String });
const submission = useForm({ payload: {}, captcha_token: '' });
const submit = () => submission.post(`/forms/${props.form.slug}`);

// Lazy-load the hCaptcha script + expose a global callback that fills the token.
function onHcaptcha(token) { submission.captcha_token = token; }

onMounted(() => {
    if (!props.form.captcha_required || !props.hcaptcha_sitekey) return;
    if (document.querySelector('script[src*="hcaptcha.com/1/api.js"]')) return;
    window.onHcaptcha = onHcaptcha;
    const s = document.createElement('script');
    s.src = 'https://js.hcaptcha.com/1/api.js';
    s.async = true; s.defer = true;
    document.head.appendChild(s);
});
onUnmounted(() => { delete window.onHcaptcha; });
</script>

<template>
    <div class="max-w-md mx-auto p-6">
        <h1 class="text-xl font-semibold mb-4">{{ form.name }}</h1>
        <form @submit.prevent="submit" class="space-y-3">
            <div v-for="f in form.fields" :key="f.key">
                <label class="block text-sm mb-1">{{ f.label }}</label>
                <textarea v-if="f.type === 'textarea'" v-model="submission.payload[f.key]"
                    :required="f.required" class="border rounded px-3 py-2 w-full" rows="4"></textarea>
                <input v-else v-model="submission.payload[f.key]" :type="f.type || 'text'"
                    :required="f.required" class="border rounded px-3 py-2 w-full" />
            </div>

            <div v-if="form.captcha_required && hcaptcha_sitekey"
                class="h-captcha"
                :data-sitekey="hcaptcha_sitekey"
                data-callback="onHcaptcha"></div>
            <div v-if="submission.errors.captcha_token" class="text-sm text-red-600">
                {{ submission.errors.captcha_token }}
            </div>

            <button :disabled="submission.processing"
                class="bg-indigo-600 text-white px-4 py-2 rounded w-full disabled:opacity-50">
                Submeter
            </button>
        </form>
    </div>
</template>
