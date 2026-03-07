<script setup>
import QuizAnswerButton from '@/Components/QuizMestre/QuizAnswerButton.vue';
import QuizCategoryBadge from '@/Components/QuizMestre/QuizCategoryBadge.vue';
import QuizQuestionCard from '@/Components/QuizMestre/QuizQuestionCard.vue';
import QuizResultBanner from '@/Components/QuizMestre/QuizResultBanner.vue';
import QuizRoundStepper from '@/Components/QuizMestre/QuizRoundStepper.vue';
import QuizScoreBar from '@/Components/QuizMestre/QuizScoreBar.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import axios from 'axios';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    session: { type: Object, required: true },
    question: { type: Object, required: true },
});

const currentSession = ref({ ...props.session });
const currentQuestion = ref({ ...props.question });
const feedback = ref(null);
const loading = ref(false);
const questionStart = ref(Date.now());

async function selectAnswer(optionKey) {
    if (loading.value) {
        return;
    }

    loading.value = true;

    try {
        const response = await axios.post(`/jogos/quiz-mestre/sessoes/${currentSession.value.id}/responder`, {
            question_id: currentQuestion.value.id,
            selected_option: optionKey,
            response_time_ms: Date.now() - questionStart.value,
        }, {
            headers: { Accept: 'application/json' },
        });

        const payload = response.data;
        const result = payload.result;

        feedback.value = {
            correct: !!result.is_correct,
            message: result.is_correct
                ? 'Boa! Resposta correta.'
                : `Resposta incorreta. Correta: ${result.correct_option} - ${result.correct_text}`,
        };

        currentSession.value = {
            ...currentSession.value,
            score: result.score,
            current_round: result.current_round,
            reward_xp: result.reward_xp,
            reward_gems: result.reward_gems,
            status: result.status,
        };

        if (payload.finished || !payload.next_question) {
            setTimeout(() => {
                window.location.href = payload.redirect;
            }, 800);

            return;
        }

        setTimeout(() => {
            currentQuestion.value = payload.next_question;
            feedback.value = null;
            questionStart.value = Date.now();
        }, 500);
    } catch (error) {
        feedback.value = {
            correct: false,
            message: error?.response?.data?.message ?? 'Não foi possível enviar a resposta.',
        };
    } finally {
        loading.value = false;
    }
}

function exitQuiz() {
    router.visit('/jogos/quiz-mestre');
}
</script>

<template>
    <Head title="Quiz Mestre • Jogar" />
    <AppLayout title="Quiz Mestre">
        <section class="space-y-3">
            <div class="flex items-center justify-between">
                <QuizCategoryBadge :label="currentQuestion.category" :difficulty="currentQuestion.difficulty" />
                <button type="button" class="rounded-lg border border-slate-300 px-3 py-1 text-xs font-semibold text-slate-600" @click="exitQuiz">
                    Sair
                </button>
            </div>

            <QuizScoreBar :round="currentQuestion.round" :max-rounds="currentSession.max_rounds" :score="currentSession.score" />
            <QuizRoundStepper :round="currentQuestion.round" :max-rounds="currentSession.max_rounds" />
            <QuizQuestionCard :text="currentQuestion.question_text" />

            <div class="grid grid-cols-1 gap-2">
                <QuizAnswerButton
                    v-for="option in currentQuestion.options"
                    :key="option.key"
                    :option-key="option.key"
                    :text="option.text"
                    :disabled="loading"
                    @select="selectAnswer"
                />
            </div>

            <QuizResultBanner v-if="feedback" :correct="feedback.correct" :message="feedback.message" />

            <div class="rounded-2xl border border-slate-200 bg-white p-3 text-xs text-slate-600">
                Recompensas acumuladas: <strong>+{{ currentSession.reward_xp }} XP</strong> • <strong>+{{ currentSession.reward_gems }} Neurons</strong>
            </div>
        </section>
    </AppLayout>
</template>
