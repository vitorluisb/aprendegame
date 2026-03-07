<script setup>
defineProps({
    elapsedLabel: { type: String, required: true },
    mistakes: { type: Number, required: true },
    rewardXp: { type: Number, required: true },
    rewardGems: { type: Number, required: true },
});
</script>

<template>
    <section class="rounded-2xl border border-emerald-200 bg-gradient-to-br from-emerald-50 to-teal-100 p-5 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Sudoku concluído</p>
        <h3 class="mt-1 text-2xl font-black text-slate-900">Parabéns!</h3>

        <div class="mt-4 grid grid-cols-2 gap-2 sm:grid-cols-4">
            <div class="rounded-xl bg-white/70 p-3 text-center">
                <p class="text-xs text-slate-500">Tempo</p>
                <p class="text-sm font-bold text-slate-800">{{ elapsedLabel }}</p>
            </div>
            <div class="rounded-xl bg-white/70 p-3 text-center">
                <p class="text-xs text-slate-500">Erros</p>
                <p class="text-sm font-bold text-rose-600">{{ mistakes }}</p>
            </div>
            <div class="rounded-xl bg-white/70 p-3 text-center">
                <p class="text-xs text-slate-500">XP</p>
                <p class="text-sm font-bold text-emerald-600">+{{ rewardXp }}</p>
            </div>
            <div class="rounded-xl bg-white/70 p-3 text-center">
                <p class="text-xs text-slate-500">Neurons</p>
                <p class="text-sm font-bold text-indigo-600">+{{ rewardGems }}</p>
            </div>
        </div>
    </section>
</template>
