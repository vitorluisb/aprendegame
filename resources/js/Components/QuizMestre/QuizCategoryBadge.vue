<script setup>
defineProps({
    label: { type: String, required: true },
    difficulty: { type: String, required: true },
});
</script>

<template>
    <div class="inline-flex items-center gap-2 rounded-full border border-indigo-200 bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">
        <span>{{ label }}</span>
        <span class="rounded-full bg-white px-2 py-0.5 text-[10px] uppercase">{{ difficulty }}</span>
    </div>
</template>
