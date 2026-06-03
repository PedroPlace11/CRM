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
    <div class="app-shell">
        <aside class="sidebar">
            <div class="brand-block">
                <img class="brand-mark" src="/favicon.svg" alt="CRM" />
                <div>
                    <div class="brand-label">Workspace</div>
                    <div class="brand-name">Pipeline OS</div>
                </div>
            </div>

            <nav class="nav">
                <Link v-for="l in navLinks" :key="l.href" :href="l.href"
                    :class="['nav-link', isActive(l.href) ? 'active' : '']">
                    {{ l.name }}
                </Link>
            </nav>

            <div class="profile">
                <div class="profile-meta">
                    <div class="profile-label">Conta</div>
                    <Link href="/profile" class="profile-name" title="Abrir perfil">{{ user?.name }}</Link>
                </div>
                <button type="button" @click="logout" title="Sair" class="logout-btn">
                    <LogOut class="w-4 h-4" />
                </button>
            </div>
        </aside>

        <main class="main-area">
            <div class="main-frame">
                <div v-if="flash.success" class="flash flash-success">
                    {{ flash.success }}
                </div>
                <div v-if="flash.error" class="flash flash-error">
                    {{ flash.error }}
                </div>

                <div class="content-surface">
                    <slot />
                </div>
            </div>
        </main>

        <button type="button" @click="showChat = true" class="chat-launcher">
            <MessageCircle class="w-5 h-5" />
        </button>

        <div v-if="showChat" class="chat-backdrop" @click.self="showChat = false">
            <div class="chat-panel">
                <div class="chat-header">
                    <div>
                        <div class="chat-eyebrow">Assistente</div>
                        <div class="chat-title">Chat IA</div>
                    </div>
                    <button type="button" @click="showChat = false" class="chat-close">✕</button>
                </div>
                <iframe src="/ai/chat?embed=1" class="chat-frame" title="Chat IA"></iframe>
            </div>
        </div>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;600;700&display=swap');

.app-shell {
    min-height: 100vh;
    display: flex;
    background:
        radial-gradient(circle at top left, rgba(37, 99, 235, 0.14), transparent 28%),
        radial-gradient(circle at bottom right, rgba(249, 115, 22, 0.10), transparent 26%),
        linear-gradient(180deg, #f8fafc 0%, #eef2ff 100%);
    color: #0f172a;
    font-family: 'Space Grotesk', system-ui, -apple-system, sans-serif;
}

.sidebar {
    width: 286px;
    flex-shrink: 0;
    background: linear-gradient(180deg, rgba(15, 23, 42, 0.98) 0%, rgba(17, 24, 39, 0.98) 100%);
    color: #e2e8f0;
    padding: 22px 16px;
    display: flex;
    flex-direction: column;
    border-right: 1px solid rgba(148, 163, 184, 0.12);
    box-shadow: 16px 0 40px rgba(15, 23, 42, 0.12);
}

.brand-block {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 8px 10px 18px;
    margin-bottom: 8px;
}

.brand-mark {
    width: 44px;
    height: 44px;
    border-radius: 14px;
    display: block;
    object-fit: contain;
    background: #fff;
    box-shadow: 0 14px 24px rgba(37, 99, 235, 0.18);
}

.brand-label {
    font-size: 0.68rem;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: rgba(226, 232, 240, 0.58);
    margin-bottom: 4px;
}

.brand-name {
    font-size: 1rem;
    font-weight: 700;
    color: #fff;
}

.nav {
    display: grid;
    gap: 8px;
    flex: 1;
}

.nav-link {
    padding: 11px 12px;
    border-radius: 14px;
    color: #cbd5f5;
    font-size: 0.92rem;
    text-decoration: none;
    transition: background 0.2s ease, color 0.2s ease, transform 0.2s ease;
    border: 1px solid transparent;
}

.nav-link:hover {
    background: rgba(148, 163, 184, 0.12);
    color: #fff;
    transform: translateX(2px);
}

.nav-link.active {
    background: linear-gradient(135deg, rgba(37, 99, 235, 0.28), rgba(79, 70, 229, 0.26));
    color: #fff;
    border-color: rgba(96, 165, 250, 0.28);
    box-shadow: inset 0 0 0 1px rgba(96, 165, 250, 0.14);
}

.profile {
    border-top: 1px solid rgba(148, 163, 184, 0.16);
    padding-top: 16px;
    margin-top: 12px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
}

.profile-meta {
    min-width: 0;
    display: grid;
    gap: 4px;
}

.profile-label {
    font-size: 0.68rem;
    letter-spacing: 0.16em;
    text-transform: uppercase;
    color: rgba(226, 232, 240, 0.6);
}

.profile-name {
    color: #e2e8f0;
    font-size: 0.9rem;
    text-decoration: none;
    font-weight: 700;
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
    width: 38px;
    height: 38px;
    border-radius: 12px;
    color: #cbd5f5;
    background: transparent;
    border: none;
    transition: background 0.2s ease, color 0.2s ease, transform 0.2s ease;
}

.logout-btn:hover {
    background: rgba(148, 163, 184, 0.12);
    color: #f87171;
    transform: translateY(-1px);
}

.main-area {
    flex: 1;
    min-width: 0;
    padding: 22px;
}

.main-frame {
    display: grid;
    gap: 16px;
}

.flash {
    border-radius: 16px;
    padding: 12px 16px;
    font-size: 0.92rem;
    font-weight: 600;
    box-shadow: 0 14px 30px rgba(15, 23, 42, 0.08);
}

.flash-success {
    background: #ecfdf5;
    color: #166534;
    border: 1px solid #bbf7d0;
}

.flash-error {
    background: #fef2f2;
    color: #991b1b;
    border: 1px solid #fecaca;
}

.content-surface {
    border-radius: 28px;
    background: rgba(255, 255, 255, 0.68);
    border: 1px solid rgba(226, 232, 240, 0.95);
    box-shadow: 0 24px 54px rgba(15, 23, 42, 0.08);
    padding: 22px;
    min-height: calc(100vh - 88px);
}

.chat-launcher {
    position: fixed;
    right: 24px;
    bottom: 24px;
    width: 54px;
    height: 54px;
    border-radius: 18px;
    border: none;
    background: linear-gradient(135deg, #4f46e5, #2563eb);
    color: #fff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 18px 34px rgba(37, 99, 235, 0.32);
    transition: transform 0.15s ease, box-shadow 0.15s ease;
    z-index: 30;
}

.chat-launcher:hover {
    transform: translateY(-2px);
    box-shadow: 0 22px 40px rgba(37, 99, 235, 0.36);
}

.chat-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.55);
    backdrop-filter: blur(8px);
    z-index: 50;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 18px;
}

.chat-panel {
    width: min(1120px, 100%);
    height: min(84vh, 860px);
    background: #fff;
    border-radius: 24px;
    overflow: hidden;
    box-shadow: 0 30px 90px rgba(15, 23, 42, 0.28);
    display: flex;
    flex-direction: column;
}

.chat-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 16px 18px;
    border-bottom: 1px solid #e2e8f0;
    background: linear-gradient(180deg, #fff, #f8fafc);
}

.chat-eyebrow {
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.14em;
    color: #94a3b8;
    font-weight: 700;
}

.chat-title {
    font-size: 1rem;
    font-weight: 700;
    color: #0f172a;
}

.chat-close {
    width: 38px;
    height: 38px;
    border-radius: 12px;
    border: none;
    background: #f1f5f9;
    color: #334155;
    font-size: 1rem;
}

.chat-frame {
    flex: 1;
    width: 100%;
    border: 0;
    background: #fff;
}

@media (max-width: 900px) {
    .sidebar {
        width: 240px;
    }

    .main-area {
        padding: 16px;
    }

    .content-surface {
        padding: 16px;
        border-radius: 22px;
        min-height: calc(100vh - 80px);
    }
}

@media (max-width: 760px) {
    .app-shell {
        flex-direction: column;
    }

    .sidebar {
        width: 100%;
        padding-bottom: 16px;
    }

    .nav {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .content-surface {
        min-height: auto;
    }

    .chat-panel {
        height: min(88vh, 820px);
        border-radius: 20px;
    }
}

@media (max-width: 520px) {
    .nav {
        grid-template-columns: 1fr;
    }

    .chat-launcher {
        right: 16px;
        bottom: 16px;
    }
}
</style>
