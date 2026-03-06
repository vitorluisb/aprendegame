<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    birth_date: '',
});

function submit() {
    form.post('/responsavel/adicionar-filho', {
        onError: () => {},
    });
}
</script>

<template>
    <Head title="Adicionar Filho" />
    <AppLayout title="Adicionar Filho">

        <!-- Voltar -->
        <Link href="/responsavel" class="mb-3 inline-flex items-center gap-1 text-sm text-slate-500 hover:text-slate-700">
            ← Voltar
        </Link>

        <!-- Header -->
        <section class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-violet-600 via-purple-600 to-indigo-700 p-5 text-white shadow-xl">
            <div class="pointer-events-none absolute inset-0 opacity-20 [background:radial-gradient(ellipse_at_top_right,_#ffffff55_0%,_transparent_70%)]" />
            <div class="flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/20 text-2xl">👧</div>
                <div>
                    <h2 class="text-xl font-black">Criar conta do filho</h2>
                    <p class="mt-0.5 text-sm opacity-75">A conta ficará vinculada ao seu painel</p>
                </div>
            </div>
        </section>

        <!-- Formulário -->
        <form class="mt-4 space-y-4" @submit.prevent="submit">

            <!-- Nome -->
            <div>
                <label class="mb-1 block text-xs font-semibold text-slate-600">Nome completo do filho *</label>
                <input
                    v-model="form.name"
                    type="text"
                    placeholder="Ex: Maria Silva"
                    autocomplete="name"
                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm placeholder-slate-400 outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100"
                    :class="{ 'border-red-400': form.errors.name }"
                    required
                >
                <p v-if="form.errors.name" class="mt-1 text-xs text-red-500">{{ form.errors.name }}</p>
            </div>

            <!-- E-mail -->
            <div>
                <label class="mb-1 block text-xs font-semibold text-slate-600">E-mail do filho *</label>
                <input
                    v-model="form.email"
                    type="email"
                    placeholder="Ex: maria@email.com"
                    autocomplete="email"
                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm placeholder-slate-400 outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100"
                    :class="{ 'border-red-400': form.errors.email }"
                    required
                >
                <p v-if="form.errors.email" class="mt-1 text-xs text-red-500">{{ form.errors.email }}</p>
            </div>

            <!-- Senha -->
            <div>
                <label class="mb-1 block text-xs font-semibold text-slate-600">Senha *</label>
                <input
                    v-model="form.password"
                    type="password"
                    placeholder="Mínimo 8 caracteres"
                    autocomplete="new-password"
                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm placeholder-slate-400 outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100"
                    :class="{ 'border-red-400': form.errors.password }"
                    required
                >
                <p v-if="form.errors.password" class="mt-1 text-xs text-red-500">{{ form.errors.password }}</p>
            </div>

            <!-- Confirmar senha -->
            <div>
                <label class="mb-1 block text-xs font-semibold text-slate-600">Confirmar senha *</label>
                <input
                    v-model="form.password_confirmation"
                    type="password"
                    placeholder="Repita a senha"
                    autocomplete="new-password"
                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm placeholder-slate-400 outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100"
                    required
                >
            </div>

            <!-- Data de nascimento -->
            <div>
                <label class="mb-1 block text-xs font-semibold text-slate-600">Data de nascimento <span class="font-normal text-slate-400">(opcional)</span></label>
                <input
                    v-model="form.birth_date"
                    type="date"
                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100"
                    :class="{ 'border-red-400': form.errors.birth_date }"
                >
                <p v-if="form.errors.birth_date" class="mt-1 text-xs text-red-500">{{ form.errors.birth_date }}</p>
            </div>

            <!-- Info -->
            <div class="flex items-start gap-2 rounded-xl border border-indigo-200 bg-indigo-50 px-3 py-2.5">
                <span class="text-base">ℹ️</span>
                <p class="text-xs text-indigo-700">
                    A conta será criada com acesso ao app AprendeGame e ficará automaticamente vinculada ao seu painel de responsável.
                </p>
            </div>

            <!-- Submit -->
            <button
                type="submit"
                :disabled="form.processing"
                class="w-full rounded-2xl bg-violet-600 py-3.5 text-center text-sm font-bold text-white shadow-lg transition hover:bg-violet-700 active:scale-[0.99] disabled:opacity-60"
            >
                <span v-if="form.processing">Criando conta...</span>
                <span v-else>Criar conta do filho</span>
            </button>

        </form>

    </AppLayout>
</template>
