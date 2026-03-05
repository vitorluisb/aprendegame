<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';

const form = useForm({});
const page = usePage();
const submit = () => form.post('/email/verification-notification');
</script>

<template>
    <Head title="Verificar e-mail" />
    <GuestLayout>
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-blue-100 mb-4">
                <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
            <h2 class="text-xl font-bold text-slate-900 mb-1">Verifique seu e-mail</h2>
            <p class="text-slate-500 text-sm">Enviamos um link de verificação para o seu e-mail. Clique no link para liberar o acesso.</p>
        </div>

        <div v-if="page.props.flash?.status === 'verification-link-sent'" class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
            Um novo link de verificação foi enviado para o seu e-mail.
        </div>

        <button
            type="button"
            :disabled="form.processing"
            class="w-full rounded-lg bg-blue-600 hover:bg-blue-700 disabled:opacity-60 px-4 py-2.5 text-sm font-semibold text-white transition"
            @click="submit"
        >
            <span v-if="form.processing">Enviando...</span>
            <span v-else>Reenviar e-mail de verificação</span>
        </button>

        <p class="mt-4 text-center text-xs text-slate-400">
            Não recebeu? Verifique sua caixa de spam.
        </p>
    </GuestLayout>
</template>
