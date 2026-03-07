<script setup>
const props = defineProps({
    round: { type: Number, required: true },
    maxRounds: { type: Number, required: true },
    score: { type: Number, required: true },
});

const width = Math.max(0, Math.min(100, (props.round / props.maxRounds) * 100));
</script>

<template>
    <div class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm">
        <div class="flex items-center justify-between text-xs font-semibold text-slate-600">
            <span>Rodada {{ round }} / {{ maxRounds }}</span>
            <span>Pontuação: {{ score }}</span>
        </div>
        <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-200">
            <div class="h-full rounded-full bg-amber-400 transition-all duration-500" :style="{ width: `${width}%` }" />
        </div>
    </div>
</template>
