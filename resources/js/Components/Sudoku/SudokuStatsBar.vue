<script setup>
defineProps({
    difficulty: { type: String, required: true },
    elapsedLabel: { type: String, required: true },
    mistakes: { type: Number, required: true },
    rewardXp: { type: Number, required: true },
    rewardGems: { type: Number, required: true },
});

function difficultyLabel(value) {
    if (value === 'easy') {
        return 'Fácil';
    }

    if (value === 'medium') {
        return 'Médio';
    }

    return 'Difícil';
}
</script>

<template>
    <section class="grid grid-cols-2 gap-2 sm:grid-cols-5">
        <div class="rounded-xl border border-slate-200 bg-white p-2 text-center shadow-sm">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Modo</p>
            <p class="text-sm font-bold text-slate-800">{{ difficultyLabel(difficulty) }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-2 text-center shadow-sm">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Tempo</p>
            <p class="text-sm font-bold text-slate-800">{{ elapsedLabel }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-2 text-center shadow-sm">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Erros</p>
            <p class="text-sm font-bold text-rose-600">{{ mistakes }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-2 text-center shadow-sm">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">XP</p>
            <p class="text-sm font-bold text-emerald-600">+{{ rewardXp }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-2 text-center shadow-sm">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Neurons</p>
            <p class="text-sm font-bold text-indigo-600">+{{ rewardGems }}</p>
        </div>
    </section>
</template>
