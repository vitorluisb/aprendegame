<script setup>
import QuizModeHero from '@/Components/QuizMestre/QuizModeHero.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    max_rounds: { type: Number, required: true },
});
</script>

<template>
    <Head title="Quiz Mestre" />
    <AppLayout title="Quiz Mestre">
        <QuizModeHero title="Quiz Mestre" subtitle="Acerte o máximo possível sem errar. A dificuldade sobe a cada rodada." />

        <section class="mt-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3 class="text-lg font-bold text-slate-800">Como funciona</h3>
            <ul class="mt-2 space-y-1 text-sm text-slate-600">
                <li>• {{ max_rounds }} rodadas por partida.</li>
                <li>• Rodadas 1-15: fácil | 16-25: médio | 26-30: difícil.</li>
                <li>• Errou uma, termina a partida.</li>
                <li>• Acertos geram XP e Neurons.</li>
            </ul>

            <Link
                href="/jogos/quiz-mestre/sessoes"
                method="post"
                as="button"
                type="button"
                class="mt-4 w-full rounded-xl bg-amber-500 px-4 py-3 text-sm font-black text-white transition hover:bg-amber-600"
            >
                Jogar agora
            </Link>

            <Link href="/jogos" class="mt-3 inline-flex text-sm font-semibold text-indigo-600 hover:underline">Voltar para Jogos</Link>
        </section>
    </AppLayout>
</template>
