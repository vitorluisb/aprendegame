<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    path: { type: Object, required: true },
    nodes: { type: Array, required: true },
});

const subjectGradients = {
    matematica: ['#0f1b35', '#1a3a6b'],
    portugues: ['#2d0a0a', '#5c1a1a'],
    ciencias: ['#0a2d1a', '#1a5c35'],
    historia: ['#2d1a0a', '#5c3a1a'],
    geografia: ['#2d1f0a', '#5c3f1a'],
    fisica: ['#0f1520', '#1a2535'],
    quimica: ['#1a0a2d', '#35155c'],
    biologia: ['#0f200f', '#1f401f'],
    filosofia: ['#201510', '#3d2a1f'],
    sociologia: ['#251008', '#4a2010'],
};

const subjectIcons = {
    matematica: '🔢',
    portugues: '📚',
    ciencias: '🧪',
    historia: '🏛️',
    geografia: '🗺️',
    fisica: '⚛️',
    quimica: '🧫',
    biologia: '🌿',
    filosofia: '💭',
    sociologia: '👥',
};

const progressPercent = computed(() => {
    if (!props.path.total_xp) {
        return 0;
    }

    return Math.round((props.path.earned_xp / props.path.total_xp) * 100);
});

const backgroundStyle = computed(() => {
    const gradient = subjectGradients[props.path.subject_slug] ?? ['#0f1b35', '#1a3a6b'];

    return {
        background: `linear-gradient(180deg, ${gradient[0]} 0%, ${gradient[1]} 100%)`,
    };
});

function resolveNodeType(node, index) {
    if (node.is_boss) {
        return {
            label: 'Boss',
            icon: '🏆',
            color: '#FFD700',
            nodeClass: 'boss-glow',
        };
    }

    if (node.node_type === 'review') {
        return {
            label: 'Revisão',
            icon: '🔁',
            color: '#60A5FA',
            nodeClass: '',
        };
    }

    if (index === 0) {
        return {
            label: 'Início',
            icon: '🌱',
            color: '#86EFAC',
            nodeClass: '',
        };
    }

    return {
        label: 'Desafio',
        icon: '⚔️',
        color: '#FB923C',
        nodeClass: '',
    };
}

function nodeStateLabel(status) {
    const labels = {
        completed: 'Concluído',
        unlocked: 'Disponível',
        locked: 'Bloqueada',
    };

    return labels[status] ?? 'Disponível';
}

function isCurrentNode(node) {
    return node.status === 'unlocked' && node.order === props.path.current_node_order;
}

function nodeCircleClass(node) {
    if (node.status === 'locked') {
        return 'bg-[#3a3a3a] border-white/25 text-white/70';
    }

    if (node.status === 'completed') {
        return 'bg-[#4CAF50] border-emerald-200 text-white';
    }

    if (isCurrentNode(node)) {
        return 'pulse-ring bg-yellow-300 border-yellow-100 text-slate-900';
    }

    return 'bg-white/20 border-white/80 text-white';
}

function canOpenNode(node) {
    return node.status !== 'locked' && Boolean(node.primary_lesson_id);
}

function lessonHref(node) {
    if (!canOpenNode(node)) {
        return '#';
    }

    return `/aulas/${node.primary_lesson_id}/jogar`;
}

function handleNodeClick(event, node) {
    if (!canOpenNode(node)) {
        event.preventDefault();
    }
}

function displayMissionTitle(title) {
    return String(title ?? '').replace(/^m[oó]dulo/iu, 'Missão');
}
</script>

<template>
    <Head :title="path.title" />
    <AppLayout :title="path.title">
        <div class="relative overflow-hidden rounded-3xl p-4 text-white sm:p-6" :style="backgroundStyle">
            <div class="pointer-events-none absolute inset-0 opacity-30" style="background: radial-gradient(circle at 20% 0%, rgba(255,255,255,0.25), transparent 30%), radial-gradient(circle at 90% 40%, rgba(255,255,255,0.18), transparent 35%);" />

            <div class="relative z-10 flex items-start justify-between gap-3">
                <Link href="/trilhas" class="rounded-xl border border-white/30 bg-white/10 px-3 py-2 text-sm font-semibold transition hover:bg-white/20">
                    ← Voltar
                </Link>
                <div class="text-center">
                    <p class="text-xs uppercase tracking-wider text-white/70">{{ path.grade }} · {{ path.subject }}</p>
                    <h2 class="mt-1 text-xl font-black sm:text-2xl">{{ path.title }}</h2>
                    <p class="mt-1 text-sm text-white/80">{{ subjectIcons[path.subject_slug] ?? '🎯' }} Parada {{ path.current_node_order }} de {{ nodes.length }}</p>
                </div>
                <div class="rounded-xl border border-white/30 bg-white/10 px-3 py-2 text-xs font-semibold">
                    🔥 Trilha
                </div>
            </div>

            <div class="relative z-10 mt-4 rounded-2xl border border-white/20 bg-black/20 p-3">
                <div class="mb-2 flex items-center justify-between text-xs font-semibold text-white/80">
                    <span>Progresso XP</span>
                    <span>{{ path.earned_xp }} / {{ path.total_xp }}</span>
                </div>
                <div class="h-3 overflow-hidden rounded-full bg-white/20">
                    <div class="h-full rounded-full bg-gradient-to-r from-cyan-300 via-sky-300 to-indigo-300 transition-all duration-500" :style="{ width: `${progressPercent}%` }" />
                </div>
            </div>

            <div v-if="nodes.length === 0" class="relative z-10 mt-4 rounded-2xl border border-white/20 bg-black/20 p-6 text-center text-white/80">
                Esta trilha ainda não possui conteúdo publicado.
            </div>

            <div v-else class="relative z-10 mt-8 pb-12">
                <div class="relative flex flex-col items-center gap-0">
                    <div
                        v-for="(node, index) in nodes"
                        :key="node.id"
                        class="relative flex w-full max-w-sm flex-col items-center pb-10 last:pb-0"
                    >
                        <div
                            v-if="index < nodes.length - 1"
                            class="absolute left-1/2 top-24 z-0 h-[calc(100%-3.5rem)] w-[5px] -translate-x-1/2 rounded-full"
                            :class="node.status === 'completed' ? 'bg-emerald-300/80' : 'bg-white/35'"
                        />

                        <Link
                            :href="lessonHref(node)"
                            class="group relative z-10 flex items-center justify-center rounded-full border-[3px] font-black shadow-xl backdrop-blur transition active:scale-95"
                            :class="[
                                nodeCircleClass(node),
                                canOpenNode(node) ? 'cursor-pointer hover:scale-105' : 'cursor-not-allowed',
                                node.is_boss ? 'h-24 w-24 text-3xl boss-glow' : 'h-20 w-20 text-2xl',
                                isCurrentNode(node) ? 'ring-8 ring-yellow-200/30' : '',
                            ]"
                            @click="handleNodeClick($event, node)"
                        >
                            <span
                                v-if="isCurrentNode(node)"
                                class="absolute -top-3 rounded-full border border-yellow-200/70 bg-yellow-300 px-2 py-0.5 text-[10px] uppercase tracking-wide text-slate-900"
                            >
                                Atual
                            </span>

                            <span v-if="node.status === 'locked'">🔒</span>
                            <span v-else-if="node.status === 'completed'">✓</span>
                            <span v-else>{{ resolveNodeType(node, index).icon }}</span>
                        </Link>

                        <div class="relative z-10 mt-3 w-full max-w-[280px] rounded-2xl border border-white/20 bg-black/25 px-3 py-2 text-center backdrop-blur">
                            <p class="truncate text-sm font-black sm:text-base">{{ displayMissionTitle(node.title) }}</p>
                            <p class="mt-0.5 text-xs text-white/75">{{ resolveNodeType(node, index).label }} · {{ nodeStateLabel(node.status) }}</p>
                            <div class="mt-2 flex items-center justify-center gap-2 text-[11px] font-bold">
                                <span class="rounded-full border border-white/20 bg-white/10 px-2 py-0.5">{{ node.progress_questions }}/{{ node.question_target }} questões</span>
                                <span class="rounded-full border border-white/20 bg-white/10 px-2 py-0.5">+{{ node.xp_total }} XP</span>
                            </div>
                            <p v-if="node.status === 'completed'" class="mt-1 text-sm tracking-wide">⭐{{ '⭐'.repeat(Math.max(node.stars - 1, 0)) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.pulse-ring {
    animation: pulse-ring 2s infinite;
}

.boss-glow {
    animation: boss-glow 1.7s ease-in-out infinite;
}

@keyframes pulse-ring {
    0%,
    100% {
        transform: scale(1);
        box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.45);
    }
    50% {
        transform: scale(1.03);
        box-shadow: 0 0 0 10px rgba(255, 255, 255, 0);
    }
}

@keyframes boss-glow {
    0%,
    100% {
        box-shadow: 0 0 12px rgba(255, 215, 0, 0.4);
    }
    50% {
        box-shadow: 0 0 24px rgba(255, 215, 0, 0.8);
    }
}
</style>
