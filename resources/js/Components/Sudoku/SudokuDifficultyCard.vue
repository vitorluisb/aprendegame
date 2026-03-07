<script setup>
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    title: { type: String, required: true },
    description: { type: String, required: true },
    difficulty: { type: String, required: true },
    clues: { type: String, required: true },
    accent: { type: String, default: 'amber' },
    availableCount: { type: Number, default: 0 },
});

const accentClassMap = {
    amber: 'border-amber-200 bg-amber-50/80 text-amber-700',
    sky: 'border-sky-200 bg-sky-50/80 text-sky-700',
    rose: 'border-rose-200 bg-rose-50/80 text-rose-700',
};

const accentClass = accentClassMap[props.accent] ?? accentClassMap.amber;
const loading = ref(false);

function startGame() {
    if (loading.value) {
        return;
    }

    if (props.availableCount <= 0) {
        window.alert('Ainda não há puzzle ativo para este nível.');

        return;
    }

    loading.value = true;

    router.post('/jogos/sudoku/sessoes', { difficulty: props.difficulty }, {
        onFinish: () => {
            loading.value = false;
        },
    });
}
</script>

<template>
    <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex items-center justify-between gap-2">
            <h3 class="text-lg font-black text-slate-800">{{ title }}</h3>
            <span class="rounded-full border px-2.5 py-1 text-xs font-bold" :class="accentClass">{{ clues }}</span>
        </div>

        <p class="mt-2 text-sm text-slate-600">{{ description }}</p>

        <button
            type="button"
            class="mt-4 w-full rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-slate-700"
            :disabled="loading || availableCount <= 0"
            @click="startGame"
        >
            {{ loading ? 'Iniciando...' : availableCount > 0 ? `Jogar ${title.toLowerCase()}` : 'Sem puzzle ativo' }}
        </button>
    </section>
</template>
