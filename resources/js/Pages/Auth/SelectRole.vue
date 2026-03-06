<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    pending_name: { type: String, default: null },
});

const form = useForm({ role: '' });

function select(role) {
    form.role = role;
    form.post('/selecionar-perfil');
}
</script>

<template>
    <Head title="Como você quer usar o AprendeGame?" />
    <GuestLayout>
        <div class="mb-6">
            <h2 class="mb-1 text-xl font-bold text-slate-900">
                {{ pending_name ? `Olá, ${pending_name.split(' ')[0]}! 👋` : 'Bem-vindo! 👋' }}
            </h2>
            <p class="text-sm text-slate-500">Como você quer usar o AprendeGame?</p>
        </div>

        <div class="space-y-3">
            <button
                type="button"
                :disabled="form.processing"
                class="flex w-full items-center gap-4 rounded-2xl border-2 border-indigo-200 bg-gradient-to-br from-indigo-50 to-sky-50 px-4 py-4 text-left transition hover:border-indigo-400 hover:shadow-md active:scale-[0.99] disabled:opacity-60"
                @click="select('student')"
            >
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-indigo-100 text-3xl">🎓</div>
                <div>
                    <p class="font-bold text-indigo-900">Sou Aluno</p>
                    <p class="mt-0.5 text-xs text-indigo-600">Quero aprender, ganhar XP e completar trilhas</p>
                </div>
                <span class="ml-auto text-indigo-400">→</span>
            </button>

            <button
                type="button"
                :disabled="form.processing"
                class="flex w-full items-center gap-4 rounded-2xl border-2 border-violet-200 bg-gradient-to-br from-violet-50 to-purple-50 px-4 py-4 text-left transition hover:border-violet-400 hover:shadow-md active:scale-[0.99] disabled:opacity-60"
                @click="select('guardian')"
            >
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-violet-100 text-3xl">👨‍👩‍👧‍👦</div>
                <div>
                    <p class="font-bold text-violet-900">Sou Responsável</p>
                    <p class="mt-0.5 text-xs text-violet-600">Quero acompanhar o progresso dos meus filhos</p>
                </div>
                <span class="ml-auto text-violet-400">→</span>
            </button>
        </div>

        <p v-if="form.errors.role" class="mt-3 text-center text-xs text-red-500">{{ form.errors.role }}</p>

        <div v-if="form.processing" class="mt-4 flex items-center justify-center gap-2 text-sm text-slate-400">
            <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
            </svg>
            Configurando sua conta...
        </div>
    </GuestLayout>
</template>
