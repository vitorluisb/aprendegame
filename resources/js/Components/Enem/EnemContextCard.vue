<script setup>
defineProps({
    contextText: { type: String, required: true },
});
</script>

<template>
    <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Texto de apoio</p>
        <p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-slate-700">
            {{ contextText }}
        </p>
    </section>
</template>
