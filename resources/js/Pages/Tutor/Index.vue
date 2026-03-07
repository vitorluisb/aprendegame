<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import axios from 'axios';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    messages: { type: Array, default: () => [] },
    daily_limit: { type: Number, required: true },
    remaining_messages: { type: Number, required: true },
});

const page = usePage();
const localMessages = ref([...props.messages]);
const localRemaining = ref(props.remaining_messages);
const localTutorError = ref(null);
const tutorError = computed(() => localTutorError.value ?? page.props.errors?.tutor ?? null);

const form = useForm({
    message: '',
});

async function submit() {
    if (form.processing) {
        return;
    }

    localTutorError.value = null;
    form.clearErrors();
    form.processing = true;

    try {
        const response = await axios.post('/tutor/mensagens', {
            message: form.message,
        }, {
            headers: {
                Accept: 'application/json',
            },
        });

        if (response.data?.student_message?.id) {
            localMessages.value.push(response.data.student_message);
        }

        if (response.data?.tutor_message?.id) {
            localMessages.value.push(response.data.tutor_message);
        }

        if (typeof response.data?.remaining_messages === 'number') {
            localRemaining.value = response.data.remaining_messages;
        }

        form.reset('message');
    } catch (error) {
        const response = error?.response;
        const message = response?.data?.message;

        if (response?.status === 422 && typeof message === 'string') {
            localTutorError.value = message;
        } else {
            localTutorError.value = 'Não foi possível enviar agora. Tente novamente.';
        }
    } finally {
        form.processing = false;
    }
}

function roleLabel(role) {
    return role === 'student' ? 'Você' : 'Tutor IA';
}
</script>

<template>
    <Head title="Tutor IA" />

    <AppLayout title="Tutor IA">
        <section class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-500 via-teal-500 to-cyan-600 p-5 text-white shadow-xl">
            <div class="game-shimmer pointer-events-none absolute inset-0 opacity-20" />
            <p class="text-xs font-semibold uppercase tracking-wide text-white/80">Tutor IA • Limite diário</p>
            <p class="mt-1 text-3xl font-black">{{ daily_limit - localRemaining }}/{{ daily_limit }}</p>
            <p class="mt-1 text-xs text-white/80">Restantes: {{ localRemaining }}</p>
            <div class="mt-3 h-2 overflow-hidden rounded-full bg-white/30">
                <div class="h-full rounded-full bg-white transition-all" :style="{ width: `${Math.max(0, Math.min(100, ((daily_limit - localRemaining) / daily_limit) * 100))}%` }" />
            </div>
        </section>

        <p v-if="tutorError" class="mt-3 rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-sm font-semibold text-red-700">
            {{ tutorError }}
        </p>

        <section class="mt-4 rounded-2xl border border-slate-200 bg-white p-3 shadow-sm">
            <div class="mb-2 flex items-center justify-between">
                <h2 class="text-sm font-bold text-slate-700">Conversa</h2>
                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-600">toque para rolar</span>
            </div>

            <div class="max-h-[48vh] space-y-2 overflow-y-auto pr-1">
            <article
                v-for="entry in localMessages"
                :key="entry.id"
                class="rounded-2xl border px-3 py-2"
                :class="entry.role === 'student' ? 'ml-6 border-indigo-200 bg-indigo-50' : 'mr-6 border-emerald-200 bg-emerald-50'"
            >
                <p class="text-[11px] font-bold uppercase tracking-wide text-slate-500">{{ roleLabel(entry.role) }}</p>
                <p class="mt-1 whitespace-pre-line text-sm text-slate-700">{{ entry.content }}</p>
                <p v-if="entry.blocked && entry.blocked_reason" class="mt-1 text-xs text-red-600">{{ entry.blocked_reason }}</p>
            </article>

            <div v-if="!localMessages.length" class="rounded-xl border border-slate-200 bg-slate-50 p-5 text-center text-sm text-slate-500">
                Faça sua primeira pergunta para o tutor.
            </div>
            </div>
        </section>

        <form class="mt-4 rounded-2xl border border-cyan-200 bg-gradient-to-br from-cyan-50 to-sky-100 p-4" @submit.prevent="submit">
            <label for="message" class="mb-2 block text-xs font-bold uppercase tracking-wide text-cyan-700">Sua dúvida</label>
            <textarea
                id="message"
                v-model="form.message"
                rows="4"
                class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-cyan-400 focus:outline-none"
                placeholder="Ex.: me explica fração com um exemplo simples"
            />
            <p v-if="form.errors.message" class="mt-1 text-xs text-red-600">{{ form.errors.message }}</p>
            <button
                type="submit"
                :disabled="form.processing"
                class="mt-3 w-full rounded-xl bg-cyan-600 px-4 py-2.5 text-sm font-bold text-white transition active:scale-[0.99] hover:bg-cyan-700 disabled:cursor-not-allowed disabled:opacity-60"
            >
                {{ form.processing ? 'Enviando...' : 'Enviar pergunta ↗' }}
            </button>
        </form>
    </AppLayout>
</template>
