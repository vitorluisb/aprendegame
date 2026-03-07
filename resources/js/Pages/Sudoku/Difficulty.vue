<script setup>
import SudokuDifficultyCard from '@/Components/Sudoku/SudokuDifficultyCard.vue';
import SudokuModeHero from '@/Components/Sudoku/SudokuModeHero.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';

const props = defineProps({
    availability: { type: Object, default: () => ({ easy: 0, medium: 0, hard: 0 }) },
});

const page = usePage();
</script>

<template>
    <Head title="Sudoku • Dificuldade" />
    <AppLayout title="Sudoku">
        <SudokuModeHero
            title="Escolha a dificuldade"
            subtitle="Cada nível usa puzzles ativos diferentes, com recompensas proporcionais ao desafio."
        />

        <section class="mt-4 space-y-3">
            <div
                v-if="page.props.errors?.difficulty"
                class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-sm font-semibold text-rose-700"
            >
                {{ page.props.errors.difficulty }}
            </div>

            <SudokuDifficultyCard
                title="Fácil"
                description="Mais pistas no tabuleiro. Ideal para partidas rápidas e treino de lógica."
                difficulty="easy"
                clues="30+ pistas"
                accent="amber"
                :available-count="props.availability.easy"
            />
            <SudokuDifficultyCard
                title="Médio"
                description="Equilíbrio entre desafio e velocidade. Bom para subir consistência."
                difficulty="medium"
                clues="24-29 pistas"
                accent="sky"
                :available-count="props.availability.medium"
            />
            <SudokuDifficultyCard
                title="Difícil"
                description="Menos pistas e decisões mais estratégicas. Maior recompensa ao concluir."
                difficulty="hard"
                clues="17-23 pistas"
                accent="rose"
                :available-count="props.availability.hard"
            />

            <Link href="/jogos/sudoku" class="inline-flex text-sm font-semibold text-indigo-600 hover:underline">
                Voltar
            </Link>
        </section>
    </AppLayout>
</template>
