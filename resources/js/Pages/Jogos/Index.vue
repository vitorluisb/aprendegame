<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Jogos" />
    <AppLayout title="Jogos">
        <section class="space-y-3">
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Modo especial</p>
                <h2 class="mt-1 text-2xl font-black text-slate-800">Quiz Mestre</h2>
                <p class="mt-2 text-sm text-slate-600">
                    Partidas rápidas com 30 rodadas, dificuldade progressiva e recompensa de XP + Neurons.
                </p>

                <Link
                    href="/jogos/quiz-mestre"
                    class="mt-4 inline-flex rounded-xl bg-indigo-600 px-4 py-2 text-sm font-bold text-white transition hover:bg-indigo-700"
                >
                    Jogar agora
                </Link>
            </article>

            <article class="rounded-2xl border border-amber-200 bg-gradient-to-br from-amber-50 to-orange-100 p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-amber-600">Novo modo</p>
                <h2 class="mt-1 text-2xl font-black text-slate-800">Sudoku</h2>
                <p class="mt-2 text-sm text-slate-700">
                    Quebra-cabeça 9x9 com níveis de dificuldade, controle de tempo, erros e recompensa ao concluir.
                </p>

                <Link
                    href="/jogos/sudoku"
                    class="mt-4 inline-flex rounded-xl bg-amber-500 px-4 py-2 text-sm font-bold text-white transition hover:bg-amber-600"
                >
                    Jogar Sudoku
                </Link>
            </article>
        </section>
    </AppLayout>
</template>
