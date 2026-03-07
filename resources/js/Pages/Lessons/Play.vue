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

const phase = ref('playing');
const currentIndex = ref(0);
const selectedAnswer = ref(null);
const fillAnswer = ref('');
const startTime = ref(Date.now());
const feedback = ref(null);
const summary = ref(null);
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

const questionOptions = computed(() => {
    if (!question.value) {
        return [];
    }

    return (question.value.options ?? []).map((option, index) => ({
        key: String(option.key ?? optionTag(index)),
        text: String(option.text ?? ''),
    }));
});

const canSubmit = computed(() => {
    if (phase.value !== 'playing' || loading.value || currentLives.value <= 0) {
        return false;
    }

    if (question.value?.type === 'fill_blank') {
        return fillAnswer.value.trim().length > 0;
    }

    return Boolean(selectedAnswer.value || fillAnswer.value);
});

function pickOption(option) {
    if (phase.value !== 'playing') {
        return;
    }

    selectedAnswer.value = option.key;
    pop();
}

async function submitAnswer() {
    if (loading.value) {
        return;
    }

    const answer = question.value.type === 'fill_blank'
        ? fillAnswer.value.trim()
        : selectedAnswer.value;

    if (!answer) {
        return;
    }

    answerError.value = null;
    loading.value = true;

    const timeMs = Date.now() - startTime.value;

    try {
        const response = await fetch(`/runs/${props.run_id}/responder`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                Accept: 'application/json',
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
                Accept: 'application/json',
            },
            body: JSON.stringify({}),
        });

        summary.value = await response.json();
        phase.value = 'finished';
    } finally {
        loading.value = false;
    }
}

function optionClass(option) {
    if (phase.value === 'playing') {
        return selectedAnswer.value === option.key
            ? 'ring-2 ring-amber-500 border-amber-400 bg-amber-50 text-amber-900'
            : 'border-slate-200 bg-white text-slate-700 hover:border-amber-300 hover:bg-amber-50/40';
    }

    if (option.key === feedback.value?.correct_answer) {
        return 'ring-2 ring-emerald-500 border-emerald-400 bg-emerald-50 text-emerald-900';
    }

    if (option.key === selectedAnswer.value && !feedback.value?.correct) {
        return 'ring-2 ring-rose-500 border-rose-400 bg-rose-50 text-rose-900';
    }

    return 'border-slate-200 bg-white text-slate-500 opacity-60';
}

function scoreGrade(score) {
    if (score >= 90) {
        return { label: 'Incrível!', color: 'text-emerald-700', bg: 'bg-emerald-50' };
    }

    if (score >= 70) {
        return { label: 'Muito bem!', color: 'text-amber-700', bg: 'bg-amber-50' };
    }

    if (score >= 50) {
        return { label: 'Bom trabalho!', color: 'text-sky-700', bg: 'bg-sky-50' };
    }

    return { label: 'Continue praticando!', color: 'text-slate-700', bg: 'bg-slate-50' };
}

function optionTag(index) {
    return String.fromCharCode(65 + index);
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

    <div class="min-h-screen bg-gradient-to-b from-slate-100 via-slate-50 to-white">
        <header class="sticky top-0 z-20 border-b border-slate-200 bg-white/95 px-4 py-3 backdrop-blur">
            <div class="mx-auto max-w-2xl">
                <div class="flex items-center gap-3">
                    <Link href="/trilhas" class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 text-slate-500 transition hover:border-slate-300 hover:text-slate-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </Link>

                    <div class="min-w-0 flex-1">
                        <div class="mb-1 flex items-center justify-between gap-2">
                            <p class="truncate text-sm font-black text-slate-700">{{ lesson.title }}</p>
                            <div class="flex items-center gap-2 text-xs font-bold">
                                <span v-if="phase !== 'finished'" class="rounded-full bg-slate-100 px-2 py-1 text-slate-600">{{ currentIndex + 1 }}/{{ questions.length }}</span>
                                <span class="rounded-full bg-rose-50 px-2 py-1 text-rose-600">❤️ {{ currentLives }}/{{ maxLives }}</span>
                            </div>
                        </div>

                        <div class="h-2.5 overflow-hidden rounded-full bg-slate-200">
                            <div
                                ref="progressFillRef"
                                class="h-full rounded-full bg-gradient-to-r from-amber-400 via-orange-400 to-rose-400 transition-all duration-500"
                                :style="{ width: `${phase === 'finished' ? 100 : progress}%` }"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-2xl px-4 py-6 pb-28">
            <template v-if="phase !== 'finished' && question">
                <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                    <div class="mb-3 flex items-center justify-between gap-2">
                        <p class="text-xs font-black uppercase tracking-wider text-slate-400">Questão {{ currentIndex + 1 }}</p>
                        <span class="rounded-full bg-amber-50 px-2 py-1 text-[11px] font-bold text-amber-700">Responda para avançar</span>
                    </div>
                    <p class="text-lg font-semibold leading-relaxed text-slate-800">
                        {{ question.prompt }}
                    </p>
                </section>

                <section class="mt-4 space-y-3">
                    <template v-if="question.type === 'multiple_choice' || question.type === 'true_false'">
                        <button
                            v-for="(option, index) in questionOptions"
                            :key="option.key"
                            class="option-card flex w-full items-center gap-3 rounded-2xl border-2 px-4 py-3.5 text-left transition"
                            :class="optionClass(option)"
                            :disabled="phase === 'feedback'"
                            @click="pickOption(option)"
                        >
                            <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-current text-xs font-black">
                                {{ option.key || optionTag(index) }}
                            </span>
                            <span class="text-sm font-semibold">{{ option.text }}</span>
                        </button>
                    </template>

                    <template v-else-if="question.type === 'fill_blank'">
                        <input
                            v-model="fillAnswer"
                            type="text"
                            placeholder="Digite sua resposta"
                            class="w-full rounded-2xl border-2 border-slate-200 bg-white px-4 py-3.5 text-sm font-semibold text-slate-700 outline-none transition focus:border-amber-400"
                            :disabled="phase === 'feedback'"
                            @keyup.enter="phase === 'playing' ? submitAnswer() : nextQuestion()"
                        />
                    </template>

                    <template v-else>
                        <textarea
                            v-model="fillAnswer"
                            rows="4"
                            placeholder="Digite sua resposta"
                            class="w-full rounded-2xl border-2 border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-700 outline-none transition focus:border-amber-400"
                            :disabled="phase === 'feedback'"
                        />
                    </template>
                </section>

                <p v-if="answerError" class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-sm font-semibold text-rose-700">
                    {{ answerError }}
                </p>

                <Transition name="feedback-pop">
                    <section
                        v-if="phase === 'feedback' && feedback"
                        :key="feedbackPulseKey"
                        ref="feedbackCardRef"
                        class="mt-4 rounded-2xl border p-4"
                        :class="feedback.correct ? 'border-emerald-200 bg-emerald-50' : 'border-rose-200 bg-rose-50'"
                    >
                        <p class="text-base font-black" :class="feedback.correct ? 'text-emerald-700' : 'text-rose-700'">
                            {{ feedback.correct ? 'Correto!' : 'Incorreto' }}
                        </p>
                        <p v-if="!feedback.correct" class="mt-1 text-sm font-semibold text-rose-700">
                            Resposta correta: <strong>{{ questionOptions.find((option) => option.key === feedback.correct_answer)?.text ?? feedback.correct_answer }}</strong>
                        </p>
                        <p v-if="feedback.explanation" class="mt-2 text-sm text-slate-700">
                            {{ feedback.explanation }}
                        </p>
                    </section>
                </Transition>
            </template>

            <template v-else-if="phase === 'finished' && summary">
                <section class="relative overflow-hidden rounded-3xl border border-slate-200 bg-white p-6 text-center shadow-sm">
                    <div v-if="showCelebration && (prefersReducedMotion || !hasLottieCelebration)" class="lesson-confetti" />
                    <div
                        v-if="showCelebration && !prefersReducedMotion && hasLottieCelebration"
                        ref="celebrationLottieRef"
                        class="pointer-events-none absolute inset-x-0 top-0 h-36"
                    />

                    <div class="mx-auto mb-4 flex h-24 w-24 items-center justify-center rounded-full text-4xl font-extrabold" :class="scoreGrade(summary.score).bg">
                        <span :class="scoreGrade(summary.score).color">{{ summary.score }}%</span>
                    </div>

                    <h2 class="text-2xl font-black text-slate-800">{{ scoreGrade(summary.score).label }}</h2>
                    <p class="mt-1 text-sm font-semibold text-slate-500">{{ summary.correct_count }} de {{ summary.total_count }} questões corretas</p>

                    <div class="mt-5 inline-flex items-center gap-2 rounded-2xl bg-amber-100 px-5 py-3">
                        <span class="text-2xl font-black text-amber-700">+{{ animatedXp }}</span>
                        <span class="text-sm font-bold text-amber-800">XP ganhos</span>
                    </div>

                    <div v-if="summary.neurons_earned > 0" class="mt-3 inline-flex items-center gap-2 rounded-2xl bg-sky-100 px-5 py-3">
                        <span class="text-2xl font-black text-sky-700">+{{ summary.neurons_earned }}</span>
                        <span class="text-sm font-bold text-sky-800">Neurons da parada</span>
                    </div>

                    <div class="mt-8 grid gap-3">
                        <Link href="/trilhas" class="block w-full rounded-2xl bg-amber-500 py-4 text-base font-black text-white shadow transition hover:bg-amber-600">
                            Continuar estudando
                        </Link>
                        <Link href="/dashboard" class="block w-full rounded-2xl border border-slate-200 bg-white py-4 text-base font-black text-slate-700 transition hover:bg-slate-50">
                            Ver meu progresso
                        </Link>
                    </div>
                </section>
            </template>
        </main>

        <footer
            v-if="phase === 'playing' || phase === 'feedback'"
            class="fixed inset-x-0 bottom-0 z-20 border-t border-slate-200 bg-white/95 p-4 backdrop-blur"
        >
            <div class="mx-auto max-w-2xl">
                <button
                    v-if="phase === 'playing'"
                    class="w-full rounded-2xl py-4 text-base font-black text-white shadow transition"
                    :class="canSubmit ? 'submit-cta bg-amber-500 hover:bg-amber-600 active:scale-[0.99]' : 'cursor-not-allowed bg-slate-300'"
                    :disabled="!canSubmit"
                    @click="submitAnswer"
                >
                    {{ loading ? 'Verificando...' : 'Verificar' }}
                </button>

                <button
                    v-else-if="phase === 'feedback'"
                    class="w-full rounded-2xl bg-amber-500 py-4 text-base font-black text-white shadow transition hover:bg-amber-600 active:scale-[0.99]"
                    :disabled="loading"
                    @click="nextQuestion"
                >
                    {{ loading ? 'Carregando...' : isLast ? 'Ver resultado' : 'Próxima questão' }}
                </button>
            </div>
        </footer>
    </div>
</template>

<style scoped>
.option-card {
    animation: option-enter 260ms ease both;
}

.option-card:nth-child(2) {
    animation-delay: 30ms;
}

.option-card:nth-child(3) {
    animation-delay: 60ms;
}

.option-card:nth-child(4) {
    animation-delay: 90ms;
}

.submit-cta {
    animation: cta-pulse 1.8s ease-in-out infinite;
}

.feedback-pop-enter-active,
.feedback-pop-leave-active {
    transition: all 220ms ease;
}

.feedback-pop-enter-from,
.feedback-pop-leave-to {
    opacity: 0;
    transform: translateY(8px);
}

@keyframes option-enter {
    from {
        opacity: 0;
        transform: translateY(6px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes cta-pulse {
    0%,
    100% {
        box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.32);
    }
    50% {
        box-shadow: 0 0 0 8px rgba(245, 158, 11, 0);
    }
}
</style>
