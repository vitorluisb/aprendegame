<script setup>
defineProps({
    title: { type: String, required: true },
    subtitle: { type: String, required: true },
});
</script>

<template>
    <section class="overflow-hidden rounded-3xl border border-amber-200/70 bg-gradient-to-br from-amber-400 via-orange-400 to-rose-400 px-5 py-6 text-white shadow-lg shadow-orange-200/40">
        <p class="text-xs font-semibold uppercase tracking-wide text-white/85">Jogos • Sudoku</p>
        <h2 class="mt-2 text-3xl font-black leading-tight">{{ title }}</h2>
        <p class="mt-2 text-sm text-white/90">{{ subtitle }}</p>
    </section>
</template>
