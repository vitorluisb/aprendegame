<script setup>
defineProps({
    correct: { type: Boolean, required: true },
    message: { type: String, required: true },
});
</script>

<template>
    <div
        class="rounded-2xl border px-4 py-3 text-sm font-semibold"
        :class="correct ? 'border-emerald-300 bg-emerald-50 text-emerald-700' : 'border-rose-300 bg-rose-50 text-rose-700'"
    >
        {{ message }}
    </div>
</template>
