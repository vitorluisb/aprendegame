<script setup>
defineProps({
    value: { type: Number, default: 0 },
    fixed: { type: Boolean, default: false },
    selected: { type: Boolean, default: false },
    highlighted: { type: Boolean, default: false },
    wrong: { type: Boolean, default: false },
});
</script>

<template>
    <button
        type="button"
        class="flex min-h-10 w-full items-center justify-center border text-xl font-black transition sm:min-h-11 sm:text-lg"
        :class="[
            fixed ? 'bg-slate-100 text-slate-700' : 'bg-white text-slate-900 hover:bg-amber-50',
            highlighted ? 'bg-amber-50/80' : '',
            selected ? 'ring-2 ring-amber-500 ring-inset' : '',
            wrong ? 'bg-rose-100 text-rose-700' : '',
        ]"
    >
        {{ value > 0 ? value : '' }}
    </button>
</template>
