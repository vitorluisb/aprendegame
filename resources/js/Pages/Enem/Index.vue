<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    areas: { type: Array, required: true },
});

const page = usePage();
const selectedDifficulty = ref('medium');

function start(area) {
    router.post('/enem/iniciar', {
        area,
        difficulty: selectedDifficulty.value,
    });
}
</script>

<template>
    <Head title="ENEM" />
    <AppLayout title="Modo ENEM">
        <section class="relative overflow-hidden rounded-3xl border border-cyan-100 bg-gradient-to-br from-[#032a39] via-[#0a4f6b] to-[#1385a6] p-5 text-white shadow-sm sm:p-6">
            <div class="pointer-events-none absolute -right-12 -top-16 h-48 w-48 rounded-full bg-cyan-200/25 blur-2xl" />
            <div class="pointer-events-none absolute -bottom-10 left-8 h-40 w-40 rounded-full bg-sky-200/20 blur-2xl" />
            <div class="relative">
                <p class="text-xs font-extrabold uppercase tracking-[0.22em] text-cyan-100/90">Treino estratégico</p>
                <h2 class="mt-1 text-2xl font-black sm:text-3xl">Questões ENEM com foco real de prova</h2>
                <p class="mt-2 max-w-2xl text-sm text-white/85">
                    Treine por área e dificuldade em questões no formato oficial: contexto, pergunta e alternativas A–E.
                </p>
            </div>
        </section>

        <section class="mt-4 rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-slate-500">Dificuldade</p>
            <div class="mt-3 grid grid-cols-3 gap-2">
                <button type="button" class="rounded-xl border px-3 py-2.5 text-sm font-extrabold transition" :class="selectedDifficulty === 'easy' ? 'border-emerald-400 bg-emerald-50 text-emerald-700' : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-50'" @click="selectedDifficulty = 'easy'">Fácil</button>
                <button type="button" class="rounded-xl border px-3 py-2.5 text-sm font-extrabold transition" :class="selectedDifficulty === 'medium' ? 'border-amber-400 bg-amber-50 text-amber-700' : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-50'" @click="selectedDifficulty = 'medium'">Médio</button>
                <button type="button" class="rounded-xl border px-3 py-2.5 text-sm font-extrabold transition" :class="selectedDifficulty === 'hard' ? 'border-rose-400 bg-rose-50 text-rose-700' : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-50'" @click="selectedDifficulty = 'hard'">Difícil</button>
            </div>
        </section>

        <section v-if="page.props.errors?.enem" class="mt-3 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">
            {{ page.props.errors.enem }}
        </section>

        <section class="mt-4 grid gap-3 sm:grid-cols-2">
            <article v-for="area in areas" :key="area.key" class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <p class="text-sm font-black text-slate-800">{{ area.label }}</p>
                <button type="button" class="mt-3 w-full rounded-2xl bg-cyan-700 px-4 py-2.5 text-sm font-extrabold text-white hover:bg-cyan-800" @click="start(area.key)">Praticar esta área</button>
            </article>
        </section>

        <Link href="/dashboard" class="mt-4 inline-flex text-sm font-semibold text-slate-600 hover:text-slate-900 hover:underline">Voltar ao painel</Link>
    </AppLayout>
</template>
