<script setup>
import SudokuModeHero from '@/Components/Sudoku/SudokuModeHero.vue';
import SudokuResultSummary from '@/Components/Sudoku/SudokuResultSummary.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    session: { type: Object, required: true },
});

const elapsedLabel = computed(() => {
    const total = props.session.elapsed_seconds ?? 0;
    const minutes = Math.floor(total / 60).toString().padStart(2, '0');
    const seconds = (total % 60).toString().padStart(2, '0');

    return `${minutes}:${seconds}`;
});
</script>

<template>
    <Head title="Sudoku • Resultado" />
    <AppLayout title="Resultado">
        <SudokuModeHero
            title="Resultado final"
            subtitle="Sua sessão foi concluída. Veja desempenho e recompensas."
        />

        <div class="mt-4">
            <SudokuResultSummary
                :elapsed-label="elapsedLabel"
                :mistakes="session.mistakes_count"
                :reward-xp="session.reward_xp"
                :reward-gems="session.reward_gems"
            />
        </div>

        <div class="mt-4 grid grid-cols-1 gap-2 sm:grid-cols-2">
            <Link
                href="/jogos/sudoku/dificuldade"
                class="inline-flex items-center justify-center rounded-xl bg-amber-500 px-4 py-3 text-sm font-black text-white transition hover:bg-amber-600"
            >
                Jogar novamente
            </Link>
            <Link
                href="/jogos"
                class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50"
            >
                Voltar para Jogos
            </Link>
        </div>
    </AppLayout>
</template>
