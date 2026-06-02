<script setup>
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { LogOut, MessageCircle } from 'lucide-vue-next';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const roles = computed(() => page.props.auth?.roles || []);
const isAdmin = computed(() => roles.value.includes('admin'));
const flash = computed(() => page.props.flash || {});
const showChat = ref(false);
const currentUrl = computed(() => page.url || '');

function logout() {
    router.post('/logout');
}

const navLinks = computed(() => {
    const links = [
        { name: 'Dashboard', href: '/dashboard' },
        { name: 'Entidades', href: '/entities' },
        { name: 'Pessoas', href: '/people' },
        { name: 'Negócios', href: '/deals' },
        { name: 'Calendário', href: '/calendar' },
        { name: 'Produtos', href: '/products/stats' },
        { name: 'Automatizações', href: '/automations' },
        { name: 'Formulários', href: '/admin/forms' },
        { name: 'IA - Chat', href: '/ai/chat' },
        { name: 'IA - Sugestões', href: '/ai/suggestions' },
    ];

    if (isAdmin.value) {
        links.push({ name: 'Admin - Utilizadores', href: '/admin/users' });
        links.push({ name: 'Admin - Cargos e Permissoes', href: '/admin/access' });
    }

    return links;
});

const isActive = (href) => currentUrl.value === href;
</script>

<template>
    <div class="min-h-screen flex">
        <aside class="sidebar">
            <div class="brand">CRM</div>
            <nav class="nav">
                <Link v-for="l in navLinks" :key="l.href" :href="l.href"
                    :class="['nav-link', isActive(l.href) ? 'active' : '']">
                    {{ l.name }}
                </Link>
            </nav>
            <div class="profile">
                <Link href="/profile" class="profile-name" title="Abrir perfil">{{ user?.name }}</Link>
                <button type="button" @click="logout" title="Sair" class="logout-btn">
                    <LogOut class="w-5 h-5" />
                </button>
            </div>
        </aside>

        <main class="flex-1">

            <div v-if="flash.success" class="bg-green-50 text-green-800 px-6 py-2 text-sm">
                {{ flash.success }}
            </div>
            <div v-if="flash.error" class="bg-red-50 text-red-800 px-6 py-2 text-sm">
                {{ flash.error }}
            </div>

            <div class="p-6">
                <slot />
            </div>
        </main>

        <button
            type="button"
            @click="showChat = true"
            class="fixed bottom-6 right-6 w-12 h-12 rounded-full bg-indigo-600 text-white flex items-center justify-center shadow-lg hover:bg-indigo-500">
            <MessageCircle class="w-5 h-5" />
        </button>

        <div v-if="showChat" class="fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4" @click.self="showChat = false">
            <div class="bg-white rounded shadow-lg w-full max-w-3xl h-[80vh] flex flex-col">
                <div class="flex items-center justify-between px-4 py-3 border-b">
                    <div class="font-semibold text-sm">Chat IA</div>
                    <button type="button" @click="showChat = false" class="text-gray-500">✕</button>
                </div>
                <iframe src="/ai/chat?embed=1" class="flex-1 w-full" title="Chat IA"></iframe>
            </div>
        </div>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;600;700&display=swap');

.sidebar {
    width: 260px;
    background: linear-gradient(180deg, #0f172a 0%, #111827 100%);
    color: #e2e8f0;
    padding: 20px 16px;
    display: flex;
    flex-direction: column;
    font-family: 'Space Grotesk', system-ui, -apple-system, sans-serif;
    border-right: 1px solid rgba(148, 163, 184, 0.12);
}

.brand {
    font-size: 1.2rem;
    font-weight: 700;
    letter-spacing: 0.4px;
    margin-bottom: 18px;
}

.nav {
    display: grid;
    gap: 6px;
    flex: 1;
}

.nav-link {
    padding: 10px 12px;
    border-radius: 12px;
    color: #cbd5f5;
    font-size: 0.9rem;
    text-decoration: none;
    transition: background 0.2s ease, color 0.2s ease;
}

.nav-link:hover {
    background: rgba(148, 163, 184, 0.12);
    color: #fff;
}

.nav-link.active {
    background: rgba(59, 130, 246, 0.2);
    color: #fff;
    box-shadow: inset 0 0 0 1px rgba(59, 130, 246, 0.3);
}

.profile {
    border-top: 1px solid rgba(148, 163, 184, 0.15);
    padding-top: 12px;
    margin-top: 12px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.profile-name {
    color: #cbd5f5;
    font-size: 0.85rem;
    text-decoration: none;
    flex: 1;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.profile-name:hover {
    color: #fff;
}

.logout-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border-radius: 10px;
    color: #cbd5f5;
    background: transparent;
    border: none;
    transition: background 0.2s ease, color 0.2s ease;
}

.logout-btn:hover {
    background: rgba(148, 163, 184, 0.12);
    color: #f87171;
}

@media (max-width: 900px) {
    .sidebar {
        width: 220px;
    }
}
</style>
