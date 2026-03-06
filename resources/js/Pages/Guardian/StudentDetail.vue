<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    student: { type: Object, required: true },
    chart_data: { type: Array, default: () => [] },
    recent_activity: { type: Array, default: () => [] },
});

// --- SVG Chart helpers ---
const CHART_W = 300;
const CHART_H = 80;
const PAD = 6;

function buildPolyline(data, key) {
    const values = data.map((d) => d[key]);
    const max = Math.max(...values, 1);
    const step = (CHART_W - PAD * 2) / (data.length - 1 || 1);

    return data
        .map((d, i) => {
            const x = PAD + i * step;
            const y = CHART_H - PAD - ((d[key] / max) * (CHART_H - PAD * 2));
            return `${x},${y}`;
        })
        .join(' ');
}

function buildArea(data, key) {
    const values = data.map((d) => d[key]);
    const max = Math.max(...values, 1);
    const step = (CHART_W - PAD * 2) / (data.length - 1 || 1);
    const lastX = PAD + (data.length - 1) * step;

    const line = data
        .map((d, i) => {
            const x = PAD + i * step;
            const y = CHART_H - PAD - ((d[key] / max) * (CHART_H - PAD * 2));
            return `${x},${y}`;
        })
        .join(' ');

    return `${PAD},${CHART_H - PAD} ${line} ${lastX},${CHART_H - PAD}`;
}

const xpPolyline = computed(() => buildPolyline(props.chart_data, 'xp'));
const xpArea = computed(() => buildArea(props.chart_data, 'xp'));
const lessonsPolyline = computed(() => buildPolyline(props.chart_data, 'lessons'));
const lessonsArea = computed(() => buildArea(props.chart_data, 'lessons'));

const totalXpChart = computed(() => props.chart_data.reduce((s, d) => s + d.xp, 0));
const totalLessonsChart = computed(() => props.chart_data.reduce((s, d) => s + d.lessons, 0));

// Day labels (show every 3rd day)
const dayLabels = computed(() =>
    props.chart_data.map((d, i) => ({
        label: i % 4 === 0 ? d.date.slice(5) : '',
        x: PAD + (i * (CHART_W - PAD * 2)) / (props.chart_data.length - 1 || 1),
    }))
);

function formatDate(iso) {
    if (!iso) return '';
    return new Intl.DateTimeFormat('pt-BR', { dateStyle: 'short', timeStyle: 'short' }).format(new Date(iso));
}

function scoreColor(score) {
    if (score >= 80) return 'text-emerald-600 bg-emerald-50';
    if (score >= 50) return 'text-amber-600 bg-amber-50';
    return 'text-rose-600 bg-rose-50';
}
</script>

<template>
    <Head :title="`Progresso - ${student.name}`" />
    <AppLayout :title="student.name">

        <!-- Voltar -->
        <Link href="/responsavel" class="mb-3 inline-flex items-center gap-1 text-sm text-slate-500 hover:text-slate-700">
            ← Voltar para meus filhos
        </Link>

        <!-- Hero do aluno -->
        <section class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-violet-600 via-purple-600 to-indigo-700 p-5 text-white shadow-xl">
            <div class="pointer-events-none absolute inset-0 opacity-20 [background:radial-gradient(ellipse_at_top_right,_#ffffff55_0%,_transparent_70%)]" />
            <div class="flex items-center gap-4">
                <div class="h-16 w-16 shrink-0 overflow-hidden rounded-full bg-white/20 ring-2 ring-white/40">
                    <img v-if="student.avatar_url" :src="student.avatar_url" :alt="student.name" class="h-full w-full object-cover">
                    <div v-else class="flex h-full w-full items-center justify-center text-2xl font-black">
                        {{ student.name?.[0] ?? '?' }}
                    </div>
                </div>
                <div>
                    <h2 class="text-xl font-black">{{ student.name }}</h2>
                    <p class="text-sm opacity-80">{{ student.grade ?? 'Série não definida' }}</p>
                    <div class="mt-2 flex items-center gap-3 text-sm">
                        <span class="rounded-full bg-white/20 px-2 py-0.5 text-xs font-bold">Nível {{ student.level }}</span>
                        <span class="text-xs opacity-75">{{ student.total_xp }} XP total</span>
                    </div>
                </div>
            </div>

            <!-- XP Progress -->
            <div class="mt-4">
                <div class="flex justify-between text-xs opacity-80">
                    <span>Progresso no nível {{ student.level }}</span>
                    <span>{{ student.xp_in_level }}/100 XP</span>
                </div>
                <div class="mt-1 h-2.5 overflow-hidden rounded-full bg-white/25">
                    <div class="h-full rounded-full bg-white transition-all duration-700" :style="{ width: student.xp_in_level + '%' }" />
                </div>
            </div>
        </section>

        <!-- Stats cards -->
        <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
            <div class="rounded-2xl bg-white p-3 shadow-sm text-center">
                <p class="text-[10px] uppercase tracking-wide text-slate-400">Sequência atual</p>
                <p class="mt-1 text-2xl font-black text-orange-500">🔥 {{ student.streak_current }}</p>
                <p class="text-xs text-slate-500">dias seguidos</p>
            </div>
            <div class="rounded-2xl bg-white p-3 shadow-sm text-center">
                <p class="text-[10px] uppercase tracking-wide text-slate-400">Melhor sequência</p>
                <p class="mt-1 text-2xl font-black text-amber-500">⭐ {{ student.streak_best }}</p>
                <p class="text-xs text-slate-500">dias</p>
            </div>
            <div class="rounded-2xl bg-white p-3 shadow-sm text-center">
                <p class="text-[10px] uppercase tracking-wide text-slate-400">Aulas concluídas</p>
                <p class="mt-1 text-2xl font-black text-indigo-600">📚 {{ student.total_lessons }}</p>
                <p class="text-xs text-slate-500">no total</p>
            </div>
            <div class="rounded-2xl bg-white p-3 shadow-sm text-center">
                <p class="text-[10px] uppercase tracking-wide text-slate-400">XP total</p>
                <p class="mt-1 text-2xl font-black text-violet-600">💎 {{ student.total_xp }}</p>
                <p class="text-xs text-slate-500">pontos</p>
            </div>
        </div>

        <!-- Chart XP (14 dias) -->
        <section class="mt-4 overflow-hidden rounded-2xl bg-white p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-800">XP ganho — últimos 14 dias</h3>
                    <p class="text-xs text-slate-400">Total: {{ totalXpChart }} XP no período</p>
                </div>
                <span class="rounded-full bg-violet-100 px-2 py-0.5 text-xs font-bold text-violet-700">⭐ XP</span>
            </div>
            <div class="mt-3 overflow-x-auto">
                <svg :viewBox="`0 0 ${CHART_W} ${CHART_H + 16}`" class="w-full" preserveAspectRatio="none" style="min-width:240px; height:80px">
                    <!-- Area fill -->
                    <defs>
                        <linearGradient id="xpGrad" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#7c3aed" stop-opacity="0.3" />
                            <stop offset="100%" stop-color="#7c3aed" stop-opacity="0.03" />
                        </linearGradient>
                    </defs>
                    <polygon :points="xpArea" fill="url(#xpGrad)" />
                    <polyline :points="xpPolyline" fill="none" stroke="#7c3aed" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    <!-- Day labels -->
                    <text v-for="(d, i) in dayLabels" :key="i" :x="d.x" :y="CHART_H + 13" text-anchor="middle" font-size="7" fill="#94a3b8">{{ d.label }}</text>
                </svg>
            </div>
        </section>

        <!-- Chart Aulas (14 dias) -->
        <section class="mt-3 overflow-hidden rounded-2xl bg-white p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-800">Aulas concluídas — últimos 14 dias</h3>
                    <p class="text-xs text-slate-400">Total: {{ totalLessonsChart }} aulas no período</p>
                </div>
                <span class="rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-bold text-indigo-700">📚 Aulas</span>
            </div>
            <div class="mt-3 overflow-x-auto">
                <svg :viewBox="`0 0 ${CHART_W} ${CHART_H + 16}`" class="w-full" preserveAspectRatio="none" style="min-width:240px; height:80px">
                    <defs>
                        <linearGradient id="lessonsGrad" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#4f46e5" stop-opacity="0.3" />
                            <stop offset="100%" stop-color="#4f46e5" stop-opacity="0.03" />
                        </linearGradient>
                    </defs>
                    <polygon :points="lessonsArea" fill="url(#lessonsGrad)" />
                    <polyline :points="lessonsPolyline" fill="none" stroke="#4f46e5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    <text v-for="(d, i) in dayLabels" :key="i" :x="d.x" :y="CHART_H + 13" text-anchor="middle" font-size="7" fill="#94a3b8">{{ d.label }}</text>
                </svg>
            </div>
        </section>

        <!-- Ver conversas com tutor -->
        <Link
            :href="`/responsavel/${student.id}/conversas`"
            class="mt-4 flex items-center justify-between rounded-2xl border border-emerald-200 bg-gradient-to-r from-emerald-50 to-teal-50 px-4 py-3 transition hover:shadow-md"
        >
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100 text-lg">🤖</div>
                <div>
                    <p class="text-sm font-bold text-emerald-800">Conversas com o Tutor IA</p>
                    <p class="text-xs text-emerald-600">Ver histórico de dúvidas do aluno</p>
                </div>
            </div>
            <span class="text-sm text-emerald-500">→</span>
        </Link>

        <!-- Atividades recentes -->
        <section v-if="recent_activity.length > 0" class="mt-4">
            <h3 class="mb-2 text-sm font-semibold text-slate-700">Atividades recentes</h3>
            <div class="space-y-2">
                <div
                    v-for="activity in recent_activity"
                    :key="activity.id"
                    class="flex items-center justify-between rounded-2xl bg-white px-4 py-3 shadow-sm"
                >
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold text-slate-800">
                            {{ activity.lesson_title ?? 'Aula concluída' }}
                        </p>
                        <p class="text-xs text-slate-400">
                            {{ activity.path_title ?? 'Trilha' }} · {{ formatDate(activity.finished_at) }}
                        </p>
                    </div>
                    <div class="ml-3 flex shrink-0 items-center gap-2">
                        <span
                            class="rounded-full px-2 py-0.5 text-xs font-bold"
                            :class="scoreColor(activity.score ?? 0)"
                        >
                            {{ activity.score ?? 0 }}%
                        </span>
                        <span class="rounded-full bg-violet-100 px-2 py-0.5 text-xs font-semibold text-violet-700">
                            +{{ activity.xp_earned }} XP
                        </span>
                    </div>
                </div>
            </div>
        </section>

        <div v-else class="mt-4 rounded-2xl bg-white p-5 text-center shadow-sm">
            <p class="text-slate-400 text-sm">Nenhuma atividade registrada ainda.</p>
        </div>

    </AppLayout>
</template>
