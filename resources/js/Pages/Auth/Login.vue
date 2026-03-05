<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({ email: '', password: '', remember: false });
const submit = () => form.post('/login');
</script>

<template>
    <Head title="Entrar" />
    <GuestLayout>
        <div class="mb-6">
            <div class="mb-3 inline-flex items-center gap-2 rounded-full bg-[color:var(--color-game-accent)]/10 px-3 py-1 text-xs font-semibold text-[var(--color-game-accent)]">
                <span>⭐</span>
                <span>Sua jornada continua</span>
            </div>
            <h2 class="mb-1 text-2xl font-black tracking-tight text-[var(--color-game-deep)]">Bem-vindo de volta!</h2>
            <p class="text-sm text-[var(--color-game-deep)]/70">Entre na sua conta para continuar estudando e subir de nível.</p>
        </div>

        <!-- Erro geral -->
        <div v-if="form.errors.email && !form.errors.password" class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ form.errors.email }}
        </div>

        <form class="space-y-4" @submit.prevent="submit">
            <div>
                <label class="mb-1 block text-sm font-medium text-[var(--color-game-deep)]">E-mail</label>
                <input
                    v-model="form.email"
                    type="email"
                    autocomplete="email"
                    placeholder="seu@email.com"
                    :class="[
                        'w-full rounded-xl border px-3 py-2.5 text-sm transition outline-none focus:ring-2 focus:ring-[color:var(--color-game-accent)]/40',
                        form.errors.email ? 'border-red-400 bg-red-50' : 'border-slate-200 bg-white',
                    ]"
                />
                <p v-if="form.errors.email" class="mt-1 text-xs text-red-600">{{ form.errors.email }}</p>
            </div>

            <div>
                <div class="mb-1 flex items-center justify-between">
                    <label class="block text-sm font-medium text-[var(--color-game-deep)]">Senha</label>
                    <Link href="/forgot-password" class="text-xs font-semibold text-[var(--color-game-accent)] hover:underline">Esqueceu a senha?</Link>
                </div>
                <input
                    v-model="form.password"
                    type="password"
                    autocomplete="current-password"
                    placeholder="••••••••••••"
                    :class="[
                        'w-full rounded-xl border px-3 py-2.5 text-sm transition outline-none focus:ring-2 focus:ring-[color:var(--color-game-accent)]/40',
                        form.errors.password ? 'border-red-400 bg-red-50' : 'border-slate-200 bg-white',
                    ]"
                />
                <p v-if="form.errors.password" class="mt-1 text-xs text-red-600">{{ form.errors.password }}</p>
            </div>

            <button
                type="submit"
                :disabled="form.processing"
                class="game-cta game-shimmer w-full bg-[var(--color-game-accent)] px-4 py-2.5 text-sm disabled:opacity-60"
            >
                <span v-if="form.processing">Entrando...</span>
                <span v-else>Entrar</span>
            </button>
        </form>

        <div class="my-5 flex items-center gap-3">
            <div class="h-px flex-1 bg-slate-200" />
            <span class="text-xs text-slate-400">ou continue com</span>
            <div class="h-px flex-1 bg-slate-200" />
        </div>

        <a
            href="/auth/google"
            class="flex w-full items-center justify-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
        >
            <svg class="h-5 w-5" viewBox="0 0 24 24">
                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z"/>
                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
            </svg>
            Entrar com Google
        </a>

        <p class="mt-6 text-center text-sm text-slate-500">
            Não tem conta?
            <Link href="/register" class="font-semibold text-[var(--color-game-accent)] hover:underline">Criar conta grátis</Link>
        </p>
    </GuestLayout>
</template>
