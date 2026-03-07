<script setup>
defineProps({
    round: { type: Number, required: true },
    maxRounds: { type: Number, required: true },
});
</script>

<template>
    <div class="grid gap-1" :style="{ gridTemplateColumns: `repeat(${maxRounds}, minmax(0, 1fr))` }">
        <div
            v-for="step in maxRounds"
            :key="step"
            class="h-2 rounded-full"
            :class="step <= round ? 'bg-amber-400' : 'bg-slate-200'"
        />
    </div>
</template>
