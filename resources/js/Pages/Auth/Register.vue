<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const selectedRole = ref(null);

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: '',
});

function selectRole(role) {
    selectedRole.value = role;
    form.role = role;
}

function resetRole() {
    selectedRole.value = null;
    form.role = '';
}

function submit() {
    form.post('/register');
}
</script>

<template>
    <Head title="Criar conta" />
    <GuestLayout>
        <h2 class="mb-1 text-xl font-bold text-slate-900">Crie sua conta</h2>
        <p class="mb-5 text-sm text-slate-500">Escolha como você quer se cadastrar.</p>

        <!-- Etapa 1: Seleção de perfil -->
        <div v-if="!selectedRole" class="space-y-3">
            <button
                type="button"
                class="flex w-full items-center gap-4 rounded-2xl border-2 border-indigo-200 bg-gradient-to-br from-indigo-50 to-sky-50 px-4 py-4 text-left transition hover:border-indigo-400 hover:shadow-md active:scale-[0.99]"
                @click="selectRole('student')"
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
                class="flex w-full items-center gap-4 rounded-2xl border-2 border-violet-200 bg-gradient-to-br from-violet-50 to-purple-50 px-4 py-4 text-left transition hover:border-violet-400 hover:shadow-md active:scale-[0.99]"
                @click="selectRole('guardian')"
            >
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-violet-100 text-3xl">👨‍👩‍👧‍👦</div>
                <div>
                    <p class="font-bold text-violet-900">Sou Responsável</p>
                    <p class="mt-0.5 text-xs text-violet-600">Quero acompanhar o progresso dos meus filhos</p>
                </div>
                <span class="ml-auto text-violet-400">→</span>
            </button>

            <div class="my-4 flex items-center gap-3">
                <div class="h-px flex-1 bg-slate-200" />
                <span class="text-xs text-slate-400">ou cadastre-se com</span>
                <div class="h-px flex-1 bg-slate-200" />
            </div>

            <a
                href="/auth/google"
                class="flex w-full items-center justify-center gap-3 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
            >
                <svg class="h-5 w-5" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                </svg>
                Cadastrar com Google
            </a>

            <p class="mt-5 text-center text-sm text-slate-500">
                Já tem conta?
                <Link href="/login" class="font-medium text-blue-600 hover:underline">Entrar</Link>
            </p>
        </div>

        <!-- Etapa 2: Formulário -->
        <div v-else>
            <div
                class="mb-4 flex items-center justify-between rounded-xl px-3 py-2"
                :class="selectedRole === 'student' ? 'bg-indigo-50' : 'bg-violet-50'"
            >
                <div class="flex items-center gap-2">
                    <span class="text-xl">{{ selectedRole === 'student' ? '🎓' : '👨‍👩‍👧‍👦' }}</span>
                    <span class="text-sm font-semibold" :class="selectedRole === 'student' ? 'text-indigo-800' : 'text-violet-800'">
                        {{ selectedRole === 'student' ? 'Cadastro de Aluno' : 'Cadastro de Responsável' }}
                    </span>
                </div>
                <button type="button" class="text-xs text-slate-400 underline hover:text-slate-600" @click="resetRole">
                    Trocar
                </button>
            </div>

            <form class="space-y-4" @submit.prevent="submit">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Nome completo</label>
                    <input
                        v-model="form.name"
                        type="text"
                        autocomplete="name"
                        placeholder="Seu nome"
                        :class="['w-full rounded-lg border px-3 py-2.5 text-sm transition focus:outline-none focus:ring-2',
                                 selectedRole === 'student' ? 'focus:ring-indigo-400' : 'focus:ring-violet-400',
                                 form.errors.name ? 'border-red-400 bg-red-50' : 'border-slate-300 bg-white']"
                    >
                    <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">E-mail</label>
                    <input
                        v-model="form.email"
                        type="email"
                        autocomplete="email"
                        placeholder="seu@email.com"
                        :class="['w-full rounded-lg border px-3 py-2.5 text-sm transition focus:outline-none focus:ring-2',
                                 selectedRole === 'student' ? 'focus:ring-indigo-400' : 'focus:ring-violet-400',
                                 form.errors.email ? 'border-red-400 bg-red-50' : 'border-slate-300 bg-white']"
                    >
                    <p v-if="form.errors.email" class="mt-1 text-xs text-red-600">{{ form.errors.email }}</p>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Senha</label>
                    <input
                        v-model="form.password"
                        type="password"
                        autocomplete="new-password"
                        placeholder="Mín. 8 caracteres"
                        :class="['w-full rounded-lg border px-3 py-2.5 text-sm transition focus:outline-none focus:ring-2',
                                 selectedRole === 'student' ? 'focus:ring-indigo-400' : 'focus:ring-violet-400',
                                 form.errors.password ? 'border-red-400 bg-red-50' : 'border-slate-300 bg-white']"
                    >
                    <p v-if="form.errors.password" class="mt-1 text-xs text-red-600">{{ form.errors.password }}</p>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Confirmar senha</label>
                    <input
                        v-model="form.password_confirmation"
                        type="password"
                        autocomplete="new-password"
                        placeholder="Repita a senha"
                        :class="['w-full rounded-lg border px-3 py-2.5 text-sm transition focus:outline-none focus:ring-2',
                                 selectedRole === 'student' ? 'focus:ring-indigo-400' : 'focus:ring-violet-400',
                                 form.errors.password_confirmation ? 'border-red-400 bg-red-50' : 'border-slate-300 bg-white']"
                    >
                    <p v-if="form.errors.password_confirmation" class="mt-1 text-xs text-red-600">{{ form.errors.password_confirmation }}</p>
                </div>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full rounded-lg px-4 py-2.5 text-sm font-semibold text-white transition disabled:opacity-60"
                    :class="selectedRole === 'student' ? 'bg-indigo-600 hover:bg-indigo-700' : 'bg-violet-600 hover:bg-violet-700'"
                >
                    <span v-if="form.processing">Criando conta...</span>
                    <span v-else>{{ selectedRole === 'student' ? 'Criar conta de aluno' : 'Criar conta de responsável' }}</span>
                </button>
            </form>

            <p class="mt-5 text-center text-sm text-slate-500">
                Já tem conta?
                <Link href="/login" class="font-medium text-blue-600 hover:underline">Entrar</Link>
            </p>
        </div>
    </GuestLayout>
</template>
