<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useUiSfx } from '@/Composables/useUiSfx';

const props = defineProps({
    role: { type: String, required: true },
    student: { type: Object, default: null },
    students: { type: Array, default: () => [] },
});

const xpPercent = computed(() => props.student ? props.student.xp_in_level : 0);
const { pop, success } = useUiSfx();

function streakColor(days) {
    if (days >= 30) return 'text-red-500';
    if (days >= 7) return 'text-orange-500';
    return 'text-amber-500';
}

function difficultyLabel(score) {
    if (score >= 70) return 'Dominada';
    if (score >= 40) return 'Aprendendo';
    return 'Revisar agora';
}

function pathTypeLabel(type) {
    const map = {
        regular: 'Regular',
        enem: 'ENEM',
        vestibular_fuvest: 'FUVEST',
        vestibular_unicamp: 'UNICAMP',
    };

    return map[type] ?? type;
}
</script>

<template>
    <Head title="Início" />
    <AppLayout title="Início">

        <!-- Dashboard do aluno -->
        <template v-if="role === 'student' && student">
            <!-- Boas-vindas + XP -->
            <section class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-amber-400 via-orange-500 to-rose-500 p-5 text-white shadow-xl">
                <div class="game-shimmer pointer-events-none absolute inset-0 opacity-30" />
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="h-12 w-12 overflow-hidden rounded-full border border-white/40 bg-white/20">
                            <img v-if="student.avatar_url" :src="student.avatar_url" alt="Avatar do aluno" class="h-full w-full object-cover">
                            <div v-else class="flex h-full w-full items-center justify-center text-lg font-bold text-white">
                                {{ student.name?.[0] ?? '?' }}
                            </div>
                        </div>
                        <div>
                            <p class="text-sm font-medium opacity-90">Bem-vindo de volta,</p>
                            <h2 class="mt-0.5 text-2xl font-bold">{{ student.name?.split(' ')?.[0] ?? student.name }}</h2>
                        </div>
                    </div>

                    <Link
                        href="/logout"
                        method="post"
                        as="button"
                        class="game-cta bg-white/25 px-3 py-1.5 text-xs text-white shadow-none hover:bg-white/35"
                        @click="success"
                    >
                        Sair
                    </Link>
                </div>

                <div class="mt-4">
                    <div class="flex items-end justify-between text-sm">
                        <span class="font-semibold">Nível {{ student.level }}</span>
                        <span class="opacity-80">{{ student.xp_in_level }}/100 XP</span>
                    </div>
                    <div class="mt-1.5 h-2.5 overflow-hidden rounded-full bg-white/30">
                        <div
                            class="h-full rounded-full bg-white transition-all duration-700"
                            :style="{ width: xpPercent + '%' }"
                        />
                    </div>
                    <p class="mt-1 text-xs opacity-75">{{ student.total_xp }} XP no total</p>
                </div>
            </section>

            <!-- Sequência e revisões -->
            <div class="mt-4 grid grid-cols-2 gap-3">
                <!-- Sequência -->
                <div class="game-surface p-4">
                    <p class="text-xs font-medium text-slate-500">Sequência</p>
                    <p class="mt-1 text-3xl font-bold" :class="streakColor(student.streak_current)">
                        {{ student.streak_current }}
                    </p>
                    <p class="text-xs text-slate-400">
                        dias consecutivos
                    </p>
                    <p class="mt-1 text-xs text-slate-400">
                        Melhor: {{ student.streak_best }} dias
                    </p>
                </div>

                <!-- Revisões pendentes -->
                <Link href="/trilhas" class="game-surface block p-4 transition-colors hover:bg-white" @click="pop">
                    <p class="text-xs font-medium text-slate-500">Para revisar</p>
                    <p class="mt-1 text-3xl font-bold text-indigo-600">
                        {{ student.due_reviews_count }}
                    </p>
                    <p class="text-xs text-slate-400">habilidades pendentes</p>
                    <p class="mt-1 text-xs text-indigo-500 font-medium">Ver trilhas →</p>
                </Link>
            </div>

            <Link
                href="/ranking"
                class="game-surface mt-3 flex items-center justify-between px-4 py-3 transition-colors hover:bg-white"
                @click="pop"
            >
                <div>
                    <p class="text-xs font-medium text-slate-500">Liga semanal</p>
                    <p class="text-sm font-semibold text-slate-800">Ver ranking da semana</p>
                </div>
                <span class="text-sm font-bold text-indigo-600">→</span>
            </Link>

            <Link
                href="/loja"
                class="game-surface mt-3 flex items-center justify-between px-4 py-3 transition-colors hover:bg-white"
                @click="pop"
            >
                <div>
                    <p class="text-xs font-medium text-slate-500">Loja de itens</p>
                    <p class="text-sm font-semibold text-slate-800">Gastar gems em itens</p>
                </div>
                <span class="text-sm font-bold text-cyan-600">→</span>
            </Link>

            <section v-if="student.recommended_paths?.length" class="mt-4">
                <h3 class="mb-2 text-sm font-semibold text-slate-700">Recomendadas para você</h3>
                <div class="space-y-2">
                    <Link
                        v-for="path in student.recommended_paths"
                        :key="path.id"
                        :href="`/trilhas/${path.id}`"
                        class="game-surface flex items-center justify-between px-4 py-3 transition-colors hover:bg-white"
                        @click="pop"
                    >
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-slate-800">{{ path.title }}</p>
                            <p class="text-xs text-slate-500">{{ path.grade.name }} · {{ path.subject.name }}</p>
                        </div>
                        <span class="rounded-full bg-indigo-50 px-2 py-0.5 text-xs font-semibold text-indigo-700">
                            {{ pathTypeLabel(path.path_type) }}
                        </span>
                    </Link>
                </div>
            </section>

            <!-- Revisões próximas -->
            <section v-if="student.due_reviews.length > 0" class="mt-4">
                <h3 class="mb-2 text-sm font-semibold text-slate-700">Revisar agora</h3>
                <div class="space-y-2">
                    <div
                        v-for="review in student.due_reviews"
                        :key="review.skill_code"
                        class="game-surface flex items-center justify-between px-4 py-3"
                    >
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-bold text-slate-400">{{ review.skill_code }}</p>
                            <p class="mt-0.5 truncate text-sm text-slate-700">{{ review.skill_description }}</p>
                        </div>
                        <span
                            class="ml-3 shrink-0 rounded-full px-2 py-0.5 text-xs font-medium"
                            :class="review.mastery_score < 40 ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700'"
                        >
                            {{ difficultyLabel(review.mastery_score) }}
                        </span>
                    </div>
                </div>
            </section>

            <!-- CTA -->
            <div class="mt-6">
                <Link
                    href="/trilhas"
                    class="game-cta block w-full bg-amber-500 py-4 text-center text-base"
                    @click="success"
                >
                    Estudar agora
                </Link>
            </div>
        </template>

        <!-- Aluno sem perfil -->
        <template v-else-if="role === 'student' && !student">
            <div class="rounded-2xl bg-white p-6 text-center shadow">
                <p class="text-slate-600">Seu perfil de aluno ainda não foi configurado.</p>
                <p class="mt-1 text-sm text-slate-400">Entre em contato com sua escola.</p>
            </div>
        </template>

        <!-- Dashboard do responsável -->
        <template v-else-if="role === 'guardian'">
            <h2 class="mb-3 text-lg font-bold text-slate-900">Meus filhos</h2>
            <div v-if="students.length === 0" class="rounded-2xl bg-white p-6 text-center shadow">
                <p class="text-slate-500">Nenhum aluno vinculado à sua conta.</p>
            </div>
            <div v-else class="space-y-3">
                <div
                    v-for="s in students"
                    :key="s.id"
                    class="flex items-center gap-4 rounded-2xl bg-white px-4 py-3 shadow-sm"
                >
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-700 font-bold text-sm">
                        {{ s.name[0] }}
                    </div>
                    <div>
                        <p class="font-semibold text-slate-800">{{ s.name }}</p>
                        <p v-if="s.streak" class="text-xs text-slate-400">
                            Sequência: {{ s.streak.current }} dias
                        </p>
                    </div>
                </div>
            </div>
        </template>

    </AppLayout>
</template>
