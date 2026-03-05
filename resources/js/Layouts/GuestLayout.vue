<script setup>
import BrandLogo from '@/Components/BrandLogo.vue';
</script>

<template>
    <main class="relative flex min-h-screen items-center justify-center overflow-hidden bg-gradient-to-br from-[var(--color-game-deep)] via-[#0A1628] to-[var(--color-game-accent)] px-4 py-12">
        <div class="pointer-events-none absolute -left-20 -top-20 h-72 w-72 rounded-full bg-[color:var(--color-game-teal)]/35 blur-3xl [animation:floatY_9s_ease-in-out_infinite]" />
        <div class="pointer-events-none absolute -right-20 top-24 h-72 w-72 rounded-full bg-[color:var(--color-game-accent)]/30 blur-3xl [animation:floatY_11s_ease-in-out_infinite]" />
        <div class="pointer-events-none absolute bottom-10 left-1/2 h-56 w-56 -translate-x-1/2 rounded-full bg-[color:var(--color-game-accent-3)]/35 blur-3xl [animation:glowPulse_6s_ease-in-out_infinite]" />

        <div class="relative w-full max-w-md">
            <div class="mb-8 text-center">
                <div class="mb-4 inline-flex rounded-2xl border border-white/30 bg-white/20 px-4 py-3 backdrop-blur">
                    <BrandLogo light />
                </div>
                <p class="mt-2 text-sm text-[color:var(--color-game-accent-3)]">Estude jogando. Evolua todo dia.</p>
            </div>

            <div class="rounded-3xl border border-white/70 bg-[var(--color-game-bg)]/95 p-8 shadow-2xl backdrop-blur-xl">
                <slot />
            </div>
        </div>
    </main>
</template>
