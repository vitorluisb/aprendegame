<script setup>
import EnemContextCard from '@/Components/Enem/EnemContextCard.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import axios from 'axios';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    question: { type: Object, required: true },
});

const selectedOption = ref('');
const feedback = ref(null);
const loading = ref(false);
const loadingNextQuestion = ref(false);

async function submit() {
    if (!selectedOption.value || loading.value) {
        return;
    }

    loading.value = true;

    try {
        const response = await axios.post(`/enem/questoes/${props.question.id}/responder`, {
            selected_option: selectedOption.value,
        });

        feedback.value = response.data;
    } finally {
        loading.value = false;
    }
}

function nextQuestion() {
    if (loadingNextQuestion.value) {
        return;
    }

    loadingNextQuestion.value = true;

    router.post('/enem/iniciar', {
        area: props.question.area,
        difficulty: props.question.difficulty,
    }, {
        preserveScroll: true,
        onFinish: () => {
            loadingNextQuestion.value = false;
        },
    });
}

function backToEnemMode() {
    router.get('/enem');
}
</script>

<template>
    <Head title="ENEM • Questão" />
    <AppLayout title="Modo ENEM">
        <section class="space-y-3">
            <div class="rounded-3xl border border-cyan-100 bg-gradient-to-br from-cyan-50 via-sky-50 to-blue-100 p-4 shadow-sm">
                <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-cyan-700">Prova ENEM</p>
                <p class="mt-1 text-sm font-black text-slate-900">{{ question.enem_code }} • {{ question.subject }}</p>
                <p class="mt-1 text-xs font-semibold text-slate-600">
                    Área: {{ question.area }} • Dificuldade: {{ question.difficulty }}
                    <span v-if="question.year_reference"> • {{ question.year_reference }}</span>
                </p>
            </div>

            <EnemContextCard :context-text="question.context_text" />

            <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-slate-500">Pergunta</p>
                <h2 class="mt-2 text-base font-extrabold leading-relaxed text-slate-900">{{ question.question_text }}</h2>

                <div class="mt-4 space-y-2.5">
                    <button
                        v-for="option in question.options"
                        :key="option.key"
                        type="button"
                        class="w-full rounded-2xl border px-3 py-3 text-left text-sm transition"
                        :class="selectedOption === option.key ? 'border-cyan-400 bg-cyan-50 text-cyan-900 shadow-sm' : 'border-slate-300 bg-white text-slate-700 hover:border-cyan-200 hover:bg-cyan-50/40'"
                        @click="selectedOption = option.key"
                    >
                        <strong>{{ option.key }})</strong> {{ option.text }}
                    </button>
                </div>

                <button type="button" class="mt-5 w-full rounded-2xl bg-cyan-700 px-4 py-3 text-sm font-extrabold text-white hover:bg-cyan-800 disabled:opacity-50" :disabled="!selectedOption || loading" @click="submit">
                    {{ loading ? 'Corrigindo...' : 'Responder' }}
                </button>
            </section>

            <section v-if="feedback" class="rounded-3xl border p-4 shadow-sm" :class="feedback.correct ? 'border-emerald-200 bg-emerald-50' : 'border-amber-200 bg-amber-50'">
                <p class="text-sm font-extrabold" :class="feedback.correct ? 'text-emerald-700' : 'text-amber-700'">
                    {{ feedback.correct ? 'Resposta correta!' : `Resposta incorreta. Correta: ${feedback.correct_option}` }}
                </p>
                <p class="mt-2 text-sm leading-relaxed text-slate-700">{{ feedback.explanation }}</p>
            </section>

            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                <button type="button" class="inline-flex items-center justify-center rounded-2xl border border-cyan-300 bg-cyan-50 px-4 py-3 text-sm font-extrabold text-cyan-800 hover:bg-cyan-100 disabled:opacity-60" :disabled="loadingNextQuestion" @click="nextQuestion">
                    {{ loadingNextQuestion ? 'Buscando questão...' : 'Nova questão' }}
                </button>
                <button type="button" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-extrabold text-slate-700 hover:bg-slate-50" @click="backToEnemMode">
                    Voltar ao modo ENEM
                </button>
            </div>
        </section>
    </AppLayout>
</template>
