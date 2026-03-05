<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useUiSfx } from '@/Composables/useUiSfx';
import BrandLogo from '@/Components/BrandLogo.vue';

defineProps({ title: { type: String, default: '' } });

const page = usePage();
const user = computed(() => page.props.auth?.user ?? null);
const currentUrl = computed(() => page.url ?? '');
const { isEnabled, pop, toggle } = useUiSfx();

function isActive(path) {
    const url = currentUrl.value;
    return url ? url.startsWith(path) : false;
}

function onNavTap() {
    pop();
}
</script>

<template>
    <div class="relative min-h-screen overflow-hidden bg-[var(--color-game-bg)] pb-20 text-[var(--color-game-ink)]">
        <div class="pointer-events-none absolute -left-16 -top-20 h-56 w-56 rounded-full bg-[color:var(--color-game-teal)]/35 blur-3xl [animation:floatY_9s_ease-in-out_infinite]" />
        <div class="pointer-events-none absolute -right-20 top-24 h-64 w-64 rounded-full bg-[color:var(--color-game-deep)]/25 blur-3xl [animation:floatY_11s_ease-in-out_infinite]" />
        <div class="pointer-events-none absolute bottom-16 left-1/3 h-40 w-40 rounded-full bg-[color:var(--color-game-accent-3)]/45 blur-3xl [animation:glowPulse_6s_ease-in-out_infinite]" />

        <!-- Barra superior -->
        <header class="sticky top-0 z-10 border-b border-[color:var(--color-game-accent)]/20 bg-white/80 px-3 py-2 shadow-sm backdrop-blur md:px-4 md:py-3">
            <div class="mx-auto flex max-w-lg items-center justify-between">
                <div class="flex items-center gap-1.5 md:gap-2">
                    <BrandLogo compact :show-tagline="false" class="origin-left scale-[0.84] md:scale-100" />
                    <h1 v-if="title" class="hidden text-base font-semibold text-[var(--color-game-deep)] md:block">
                        {{ title }}
                    </h1>
                </div>
                <div class="flex items-center gap-1.5 text-sm text-[var(--color-game-deep)]/80 md:gap-3">
                    <span v-if="user" class="max-w-20 truncate text-xs font-medium sm:max-w-28 sm:text-sm">{{ user.name?.split(' ')?.[0] ?? user.name }}</span>
                    <button
                        type="button"
                        class="rounded-full border border-[color:var(--color-game-accent)]/25 bg-white px-2 py-1 text-[10px] font-semibold text-[var(--color-game-deep)] transition hover:bg-[var(--color-game-bg)] md:px-2.5 md:text-[11px]"
                        @click="toggle"
                    >
                        <span class="md:hidden">{{ isEnabled ? 'Áudio on' : 'Áudio off' }}</span>
                        <span class="hidden md:inline">{{ isEnabled ? 'Som: ligado' : 'Som: desligado' }}</span>
                    </button>
                </div>
            </div>
        </header>

        <!-- Conteúdo principal -->
        <main class="mx-auto max-w-lg px-4 py-5">
            <slot />
        </main>

        <!-- Navegação inferior -->
        <nav class="fixed bottom-0 left-0 right-0 z-10 border-t border-[color:var(--color-game-accent)]/20 bg-white/85 backdrop-blur-xl">
            <div class="mx-auto flex max-w-lg justify-around py-2">
                <Link
                    href="/dashboard"
                    class="flex flex-col items-center gap-0.5 px-4 py-1 text-xs transition-colors"
                    :class="isActive('/dashboard') ? 'text-[var(--color-game-accent)] font-semibold' : 'text-[var(--color-game-deep)]/60'"
                    @click="onNavTap"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Início
                </Link>

                <Link
                    href="/trilhas"
                    class="flex flex-col items-center gap-0.5 px-4 py-1 text-xs transition-colors"
                    :class="isActive('/trilhas') || isActive('/aulas') ? 'text-[var(--color-game-accent)] font-semibold' : 'text-[var(--color-game-deep)]/60'"
                    @click="onNavTap"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                    </svg>
                    Trilhas
                </Link>

                <Link
                    href="/perfil"
                    class="flex flex-col items-center gap-0.5 px-4 py-1 text-xs transition-colors"
                    :class="isActive('/perfil') ? 'text-[var(--color-game-accent)] font-semibold' : 'text-[var(--color-game-deep)]/60'"
                    @click="onNavTap"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Perfil
                </Link>
            </div>
        </nav>
    </div>
</template>
