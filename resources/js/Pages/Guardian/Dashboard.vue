<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    students: { type: Array, default: () => [] },
});

function levelPercent(student) {
    return student.xp_in_level ?? 0;
}

function streakColor(days) {
    if (days >= 30) return 'text-red-500';
    if (days >= 7) return 'text-orange-500';
    return 'text-amber-500';
}
</script>

<template>
    <Head title="Painel dos Pais" />
    <AppLayout title="Painel dos Pais">

        <!-- Header -->
        <section class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-violet-600 via-purple-600 to-indigo-700 p-5 text-white shadow-xl">
            <div class="pointer-events-none absolute inset-0 opacity-20 [background:radial-gradient(ellipse_at_top_right,_#ffffff55_0%,_transparent_70%)]" />
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium opacity-80">Bem-vindo ao</p>
                    <h2 class="mt-0.5 text-2xl font-black tracking-tight">Painel dos Pais</h2>
                    <p class="mt-1 text-xs opacity-70">Acompanhe o progresso dos seus filhos</p>
                </div>
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white/20 text-3xl">
                    👨‍👩‍👧‍👦
                </div>
            </div>
        </section>

        <!-- Add student CTA -->
        <Link
            href="/responsavel/adicionar-filho"
            class="mt-4 flex items-center gap-3 rounded-2xl border-2 border-dashed border-violet-300 bg-violet-50 px-4 py-3 transition hover:border-violet-400 hover:bg-violet-100"
        >
            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-violet-200 text-xl">+</div>
            <div>
                <p class="text-sm font-bold text-violet-800">Adicionar filho</p>
                <p class="text-xs text-violet-500">Criar conta de aluno vinculada à sua conta</p>
            </div>
        </Link>

        <!-- Sem filhos vinculados -->
        <div v-if="students.length === 0" class="mt-4 rounded-2xl bg-white p-8 text-center shadow-sm">
            <p class="text-4xl">👧</p>
            <p class="mt-3 font-semibold text-slate-700">Nenhum filho vinculado</p>
            <p class="mt-1 text-sm text-slate-400">Use o botão acima para criar a conta do seu filho.</p>
        </div>

        <!-- Lista de filhos -->
        <div v-else class="mt-4 space-y-3">
            <h3 class="text-sm font-semibold text-slate-600">Meus filhos ({{ students.length }})</h3>

            <Link
                v-for="student in students"
                :key="student.id"
                :href="`/responsavel/${student.id}`"
                class="block overflow-hidden rounded-2xl bg-white shadow-sm transition hover:shadow-md active:scale-[0.99]"
            >
                <!-- Header do card -->
                <div class="flex items-center gap-3 bg-gradient-to-r from-slate-50 to-slate-100 px-4 py-3">
                    <div class="relative h-12 w-12 shrink-0">
                        <img
                            v-if="student.avatar_url"
                            :src="student.avatar_url"
                            :alt="student.name"
                            class="h-full w-full rounded-full object-cover ring-2 ring-violet-200"
                        >
                        <div
                            v-else
                            class="flex h-full w-full items-center justify-center rounded-full bg-violet-100 text-lg font-black text-violet-600 ring-2 ring-violet-200"
                        >
                            {{ student.name?.[0] ?? '?' }}
                        </div>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate font-bold text-slate-800">{{ student.name }}</p>
                        <p class="text-xs text-slate-500">{{ student.grade ?? 'Série não definida' }} · Nível {{ student.level }}</p>
                    </div>
                    <div class="flex flex-col items-end gap-1">
                        <span class="text-xs font-semibold text-violet-600">Ver detalhes →</span>
                        <span class="rounded-full bg-violet-100 px-2 py-0.5 text-[10px] font-bold text-violet-700">
                            {{ student.total_xp }} XP
                        </span>
                    </div>
                </div>

                <!-- Stats grid -->
                <div class="grid grid-cols-3 divide-x divide-slate-100 border-t border-slate-100">
                    <div class="px-3 py-2.5 text-center">
                        <p class="text-[10px] uppercase tracking-wide text-slate-400">Sequência</p>
                        <p class="text-sm font-bold" :class="streakColor(student.streak_current)">
                            🔥 {{ student.streak_current }}d
                        </p>
                    </div>
                    <div class="px-3 py-2.5 text-center">
                        <p class="text-[10px] uppercase tracking-wide text-slate-400">Aulas/semana</p>
                        <p class="text-sm font-bold text-indigo-600">📚 {{ student.lessons_this_week }}</p>
                    </div>
                    <div class="px-3 py-2.5 text-center">
                        <p class="text-[10px] uppercase tracking-wide text-slate-400">XP/semana</p>
                        <p class="text-sm font-bold text-amber-600">⭐ {{ student.xp_this_week }}</p>
                    </div>
                </div>
            </Link>
        </div>

    </AppLayout>
</template>
