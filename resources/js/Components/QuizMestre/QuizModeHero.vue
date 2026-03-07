<script setup>
defineProps({
    title: { type: String, required: true },
    subtitle: { type: String, required: true },
});
</script>

<template>
    <section class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-500 via-sky-500 to-cyan-500 p-5 text-white shadow-xl">
        <div class="game-shimmer pointer-events-none absolute inset-0 opacity-25" />
        <p class="text-xs font-semibold uppercase tracking-wide text-white/80">Jogos • Quiz Mestre</p>
        <h2 class="mt-1 text-2xl font-black">{{ title }}</h2>
        <p class="mt-1 text-sm text-white/90">{{ subtitle }}</p>
    </section>
</template>
