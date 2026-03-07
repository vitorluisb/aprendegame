<script setup>
defineProps({
    text: { type: String, required: true },
});
</script>

<template>
    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-lg font-bold leading-relaxed text-slate-800">{{ text }}</p>
    </article>
</template>
