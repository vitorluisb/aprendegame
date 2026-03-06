<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { computed, nextTick, onBeforeUnmount, ref, watch } from 'vue';
import { useUiSfx } from '@/Composables/useUiSfx';
import { useRewardEffects } from '@/Composables/useRewardEffects';

const props = defineProps({
    lesson: { type: Object, required: true },
    run_id: { type: Number, required: true },
    lives_current: { type: Number, required: true },
    lives_max: { type: Number, required: true },
    questions: { type: Array, required: true },
});

// ---- Estado ----
const phase = ref('playing'); // 'playing' | 'feedback' | 'finished'
const currentIndex = ref(0);
const selectedAnswer = ref(null);
const fillAnswer = ref('');
const startTime = ref(Date.now());
const feedback = ref(null); // { correct, explanation, correct_answer }
const summary = ref(null);  // { score, xp_earned, correct_count, total_count }
const loading = ref(false);
const answerError = ref(null);
const feedbackPulseKey = ref(0);
const animatedXp = ref(0);
const showCelebration = ref(false);
const hasLottieCelebration = ref(false);
const feedbackCardRef = ref(null);
const progressFillRef = ref(null);
const celebrationLottieRef = ref(null);
const currentLives = ref(props.lives_current);
const maxLives = ref(props.lives_max);

const { pop, success, error: errorSfx, victory, prefersReducedMotion } = useUiSfx();
const {
    animateXpCounter,
    animateProgressBar,
    animateFeedbackCard,
    playCelebration,
    cleanupCelebration,
} = useRewardEffects();

const question = computed(() => props.questions[currentIndex.value] ?? null);
const isLast = computed(() => currentIndex.value === props.questions.length - 1);
const progress = computed(() =>
    props.questions.length > 0
        ? Math.round((currentIndex.value / props.questions.length) * 100)
        : 0
);

// ---- Resposta ----
function pickOption(option) {
    if (phase.value !== 'playing') return;
    selectedAnswer.value = option;
    pop();
}

async function submitAnswer() {
    if (loading.value) return;

    const answer = question.value.type === 'fill_blank'
        ? fillAnswer.value.trim()
        : selectedAnswer.value;

    if (!answer) return;

    answerError.value = null;

    loading.value = true;
    const timeMs = Date.now() - startTime.value;

    try {
        const response = await fetch(`/runs/${props.run_id}/responder`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                question_id: question.value.id,
                answer,
                time_ms: timeMs,
            }),
        });

        const payload = await response.json();

        if (!response.ok) {
            currentLives.value = payload.remaining_lives ?? currentLives.value;
            maxLives.value = payload.lives_max ?? maxLives.value;
            answerError.value = payload.message ?? 'Não foi possível enviar a resposta.';

            return;
        }

        feedback.value = payload;
        currentLives.value = payload.remaining_lives ?? currentLives.value;
        maxLives.value = payload.lives_max ?? maxLives.value;
        phase.value = 'feedback';
    } finally {
        loading.value = false;
    }
}

async function nextQuestion() {
    if (isLast.value) {
        await finishLesson();
        return;
    }

    currentIndex.value++;
    selectedAnswer.value = null;
    fillAnswer.value = '';
    feedback.value = null;
    phase.value = 'playing';
    startTime.value = Date.now();
}

async function finishLesson() {
    loading.value = true;
    try {
        const response = await fetch(`/runs/${props.run_id}/finalizar`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({}),
        });

        summary.value = await response.json();
        phase.value = 'finished';
    } finally {
        loading.value = false;
    }
}

// ---- Helpers visuais ----
function optionClass(option) {
    if (phase.value === 'playing') {
        return selectedAnswer.value === option
            ? 'ring-2 ring-amber-500 bg-amber-50 border-amber-500'
            : 'bg-white border-slate-200 hover:border-amber-300 hover:bg-amber-50/50';
    }
    if (option === feedback.value?.correct_answer) {
        return 'ring-2 ring-green-500 bg-green-50 border-green-400';
    }
    if (option === selectedAnswer.value && !feedback.value?.correct) {
        return 'ring-2 ring-red-400 bg-red-50 border-red-400';
    }
    return 'bg-white border-slate-200 opacity-50';
}

function scoreGrade(score) {
    if (score >= 90) return { label: 'Incrível!', color: 'text-green-600', bg: 'bg-green-50' };
    if (score >= 70) return { label: 'Muito bem!', color: 'text-amber-600', bg: 'bg-amber-50' };
    if (score >= 50) return { label: 'Bom trabalho!', color: 'text-blue-600', bg: 'bg-blue-50' };
    return { label: 'Continue praticando!', color: 'text-slate-600', bg: 'bg-slate-50' };
}

watch(progress, async (value) => {
    await nextTick();
    animateProgressBar(progressFillRef.value, value);
}, { immediate: true });

watch(feedback, async (value) => {
    if (!value || phase.value !== 'feedback') {
        return;
    }

    feedbackPulseKey.value += 1;
    await nextTick();
    animateFeedbackCard(feedbackCardRef.value, value.correct);

    if (value.correct) {
        success();
        return;
    }

    errorSfx();
});

watch(phase, (value) => {
    if (value !== 'finished' || !summary.value) {
        return;
    }

    if (prefersReducedMotion.value) {
        animatedXp.value = summary.value.xp_earned;
    } else {
        animateXpCounter(animatedXp, summary.value.xp_earned);
    }

    showCelebration.value = summary.value.score >= 85;
    hasLottieCelebration.value = false;

    if (summary.value.score >= 85) {
        victory();

        if (!prefersReducedMotion.value) {
            nextTick(() => {
                hasLottieCelebration.value = playCelebration(celebrationLottieRef.value);
            });
        }
    }
});

onBeforeUnmount(() => {
    cleanupCelebration();
});
</script>

<template>
    <Head :title="`Aula: ${lesson.title}`" />

    <div class="min-h-screen bg-slate-50">

        <!-- Barra de topo com progresso -->
        <header class="sticky top-0 z-10 bg-white border-b px-4 py-3">
            <div class="mx-auto max-w-lg">
                <div class="flex items-center gap-3">
                    <Link href="/trilhas" class="shrink-0 text-slate-400 hover:text-slate-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </Link>
                    <div class="flex-1">
                        <div class="flex items-center justify-between text-xs text-slate-400 mb-1">
                            <span class="truncate font-medium text-slate-600">{{ lesson.title }}</span>
                            <div class="flex items-center gap-2">
                                <span v-if="phase !== 'finished'">{{ currentIndex + 1 }}/{{ questions.length }}</span>
                                <span class="font-semibold text-rose-500">❤ {{ currentLives }}/{{ maxLives }}</span>
                            </div>
                        </div>
                        <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                            <div
                                ref="progressFillRef"
                                class="h-full rounded-full bg-amber-400 transition-all duration-500"
                                :style="{ width: (phase === 'finished' ? 100 : progress) + '%' }"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-lg px-4 py-6">

            <!-- Tela de questão -->
            <template v-if="phase !== 'finished' && question">

                <!-- Enunciado -->
                <div class="mb-5 rounded-2xl bg-white p-5 shadow-sm">
                    <p class="text-base font-medium text-slate-800 leading-relaxed">
                        {{ question.prompt }}
                    </p>
                </div>

                <!-- Opções múltipla escolha / verdadeiro-falso -->
                <div v-if="question.type === 'multiple_choice' || question.type === 'true_false'" class="space-y-2.5">
                    <button
                        v-for="option in (question.options ?? (question.type === 'true_false' ? ['Verdadeiro', 'Falso'] : []))"
                        :key="option"
                        class="w-full rounded-2xl border-2 px-4 py-3.5 text-left text-sm font-medium text-slate-700 transition-all"
                        :class="optionClass(option)"
                        :disabled="phase === 'feedback'"
                        @click="pickOption(option)"
                    >
                        {{ option }}
                    </button>
                </div>

                <!-- Preenchimento de lacuna -->
                <div v-else-if="question.type === 'fill_blank'">
                    <input
                        v-model="fillAnswer"
                        type="text"
                        placeholder="Digite sua resposta..."
                        class="w-full rounded-2xl border-2 border-slate-200 px-4 py-3.5 text-sm font-medium text-slate-700 outline-none transition-all focus:border-amber-400 focus:ring-0"
                        :disabled="phase === 'feedback'"
                        @keyup.enter="phase === 'playing' ? submitAnswer() : nextQuestion()"
                    />
                </div>

                <!-- Outros tipos (drag_drop, order_steps) — simplificado -->
                <div v-else>
                    <textarea
                        v-model="fillAnswer"
                        placeholder="Digite sua resposta em JSON..."
                        rows="3"
                        class="w-full rounded-2xl border-2 border-slate-200 px-4 py-3 text-sm text-slate-700 outline-none transition-all focus:border-amber-400"
                        :disabled="phase === 'feedback'"
                    />
                </div>

                <!-- Feedback de resposta -->
                <p v-if="answerError" class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700">
                    {{ answerError }}
                </p>

                <Transition name="feedback-pop">
                    <div
                        v-if="phase === 'feedback' && feedback"
                        :key="feedbackPulseKey"
                        ref="feedbackCardRef"
                        class="mt-4 rounded-2xl border p-4 transition-all"
                        :class="feedback.correct ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50'"
                    >
                        <p class="font-bold" :class="feedback.correct ? 'text-green-700' : 'text-red-700'">
                            {{ feedback.correct ? 'Correto!' : 'Incorreto' }}
                        </p>
                        <p v-if="!feedback.correct" class="mt-1 text-sm" :class="feedback.correct ? 'text-green-600' : 'text-red-600'">
                            Resposta correta: <strong>{{ feedback.correct_answer }}</strong>
                        </p>
                        <p v-if="feedback.explanation" class="mt-1.5 text-sm text-slate-600">
                            {{ feedback.explanation }}
                        </p>
                    </div>
                </Transition>

                <!-- Botões de ação -->
                <div class="mt-6">
                    <button
                        v-if="phase === 'playing'"
                        class="w-full rounded-2xl py-4 text-base font-bold text-white shadow transition-all disabled:opacity-50"
                        :class="(selectedAnswer || fillAnswer) && currentLives > 0 ? 'bg-amber-500 active:scale-[0.98]' : 'bg-slate-300 cursor-not-allowed'"
                        :disabled="(!selectedAnswer && !fillAnswer) || loading || currentLives <= 0"
                        @click="submitAnswer"
                    >
                        {{ loading ? 'Verificando...' : 'Verificar' }}
                    </button>

                    <button
                        v-else-if="phase === 'feedback'"
                        class="w-full rounded-2xl bg-amber-500 py-4 text-base font-bold text-white shadow transition-all active:scale-[0.98]"
                        :disabled="loading"
                        @click="nextQuestion"
                    >
                        {{ loading ? 'Carregando...' : isLast ? 'Ver resultado' : 'Próxima questão' }}
                    </button>
                </div>
            </template>

            <!-- Tela de resultado final -->
            <template v-else-if="phase === 'finished' && summary">
                <div class="relative overflow-hidden text-center">
                    <div v-if="showCelebration && (prefersReducedMotion || !hasLottieCelebration)" class="lesson-confetti" />
                    <div
                        v-if="showCelebration && !prefersReducedMotion && hasLottieCelebration"
                        ref="celebrationLottieRef"
                        class="pointer-events-none absolute inset-x-0 top-0 h-36"
                    />
                    <div
                        class="mx-auto mb-4 flex h-24 w-24 items-center justify-center rounded-full text-4xl font-extrabold"
                        :class="scoreGrade(summary.score).bg"
                    >
                        <span :class="scoreGrade(summary.score).color">
                            {{ summary.score }}%
                        </span>
                    </div>

                    <h2 class="text-2xl font-extrabold text-slate-800">
                        {{ scoreGrade(summary.score).label }}
                    </h2>
                    <p class="mt-1 text-slate-500">
                        {{ summary.correct_count }} de {{ summary.total_count }} questões corretas
                    </p>

                    <!-- XP ganho -->
                    <div class="mt-5 inline-flex items-center gap-2 rounded-2xl bg-amber-100 px-5 py-3">
                        <span class="text-2xl font-extrabold text-amber-600">+{{ animatedXp }}</span>
                        <span class="text-sm font-semibold text-amber-700">XP ganhos</span>
                    </div>

                    <!-- Ações -->
                    <div class="mt-8 space-y-3">
                        <Link
                            href="/trilhas"
                            class="block w-full rounded-2xl bg-amber-500 py-4 text-base font-bold text-white shadow"
                        >
                            Continuar estudando
                        </Link>
                        <Link
                            href="/dashboard"
                            class="block w-full rounded-2xl bg-white py-4 text-base font-bold text-slate-700 shadow-sm ring-1 ring-slate-200"
                        >
                            Ver meu progresso
                        </Link>
                    </div>
                </div>
            </template>

        </main>
    </div>
</template>
