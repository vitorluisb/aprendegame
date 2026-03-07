<script setup>
import SudokuModeHero from '@/Components/Sudoku/SudokuModeHero.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Sudoku" />
    <AppLayout title="Sudoku">
        <SudokuModeHero
            title="Sudoku"
            subtitle="Complete o tabuleiro 9x9 com foco total, ganhe XP e Neurons ao finalizar cada partida."
        />

        <section class="mt-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3 class="text-lg font-bold text-slate-800">Como funciona</h3>
            <ul class="mt-2 space-y-1 text-sm text-slate-600">
                <li>• Escolha a dificuldade e comece uma nova sessão.</li>
                <li>• Preencha apenas células vazias com números de 1 a 9.</li>
                <li>• O jogo acompanha tempo, erros e recompensa final.</li>
            </ul>

            <Link
                href="/jogos/sudoku/dificuldade"
                class="mt-4 inline-flex w-full items-center justify-center rounded-xl bg-amber-500 px-4 py-3 text-sm font-black text-white transition hover:bg-amber-600"
            >
                Escolher dificuldade
            </Link>

            <Link href="/jogos" class="mt-3 inline-flex text-sm font-semibold text-indigo-600 hover:underline">
                Voltar para Jogos
            </Link>
        </section>
    </AppLayout>
</template>
