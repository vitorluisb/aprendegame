<script setup>
import QuizFinalSummary from '@/Components/QuizMestre/QuizFinalSummary.vue';
import QuizModeHero from '@/Components/QuizMestre/QuizModeHero.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    session: { type: Object, required: true },
});

const roundReached = props.session.status === 'completed'
    ? props.session.max_rounds
    : props.session.current_round;
</script>

<template>
    <Head title="Quiz Mestre • Resultado" />
    <AppLayout title="Resultado">
        <QuizModeHero title="Fim de partida" subtitle="Confira seu desempenho no Quiz Mestre." />

        <div class="mt-4">
            <QuizFinalSummary
                :score="session.score"
                :round="roundReached"
                :reward-xp="session.reward_xp"
                :reward-gems="session.reward_gems"
                :status="session.status"
            />
        </div>

        <div class="mt-4 grid grid-cols-1 gap-2 sm:grid-cols-2">
            <form method="post" action="/jogos/quiz-mestre/sessoes">
                <button type="submit" class="w-full rounded-xl bg-amber-500 px-4 py-3 text-sm font-black text-white transition hover:bg-amber-600">
                    Jogar novamente
                </button>
            </form>
            <Link href="/jogos" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50">
                Voltar para Jogos
            </Link>
        </div>
    </AppLayout>
</template>
