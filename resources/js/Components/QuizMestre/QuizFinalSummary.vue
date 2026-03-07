<script setup>
defineProps({
    score: { type: Number, required: true },
    round: { type: Number, required: true },
    rewardXp: { type: Number, required: true },
    rewardGems: { type: Number, required: true },
    status: { type: String, required: true },
});
</script>

<template>
    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Resultado final</p>
        <h3 class="mt-1 text-2xl font-black" :class="status === 'completed' ? 'text-emerald-600' : 'text-rose-600'">
            {{ status === 'completed' ? 'Run completa!' : 'Você perdeu esta rodada' }}
        </h3>

        <div class="mt-4 grid grid-cols-2 gap-3">
            <div class="rounded-xl bg-slate-50 p-3">
                <p class="text-xs text-slate-500">Pontuação</p>
                <p class="text-xl font-black text-slate-800">{{ score }}</p>
            </div>
            <div class="rounded-xl bg-slate-50 p-3">
                <p class="text-xs text-slate-500">Rodada atingida</p>
                <p class="text-xl font-black text-slate-800">{{ round }}</p>
            </div>
            <div class="rounded-xl bg-emerald-50 p-3">
                <p class="text-xs text-emerald-600">XP ganho</p>
                <p class="text-xl font-black text-emerald-700">+{{ rewardXp }}</p>
            </div>
            <div class="rounded-xl bg-amber-50 p-3">
                <p class="text-xs text-amber-600">Neurons</p>
                <p class="text-xl font-black text-amber-700">+{{ rewardGems }}</p>
            </div>
        </div>
    </section>
</template>
