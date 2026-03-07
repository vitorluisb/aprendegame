<script setup>
defineProps({
    optionKey: { type: String, required: true },
    text: { type: String, required: true },
    disabled: { type: Boolean, default: false },
});

defineEmits(['select']);
</script>

<template>
    <button
        type="button"
        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-left text-sm font-semibold text-slate-700 shadow-sm transition hover:border-indigo-300 hover:bg-indigo-50 disabled:cursor-not-allowed disabled:opacity-60"
        :disabled="disabled"
        @click="$emit('select', optionKey)"
    >
        <span class="mr-2 inline-flex h-6 w-6 items-center justify-center rounded-full bg-slate-100 text-xs font-bold text-slate-600">{{ optionKey }}</span>
        {{ text }}
    </button>
</template>
