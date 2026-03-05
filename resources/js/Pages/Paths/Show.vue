<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    path: { type: Object, required: true },
    nodes: { type: Array, required: true },
});

function nodeTypeLabel(type) {
    const labels = {
        lesson: 'Aulas',
        checkpoint: 'Ponto de verificação',
        review: 'Revisão',
        boss: 'Desafio final',
    };
    return labels[type] ?? 'Conteúdo';
}

function nodeTypeIcon(type) {
    const icons = {
        lesson: '📚',
        checkpoint: '🏁',
        review: '🔁',
        boss: '⚔️',
    };
    return icons[type] ?? '•';
}

function difficultyStars(difficulty) {
    return '★'.repeat(Math.min(difficulty, 5)) + '☆'.repeat(Math.max(0, 5 - difficulty));
}
</script>

<template>
    <Head :title="path.title" />
    <AppLayout :title="path.title">

        <!-- Cabeçalho da trilha -->
        <div
            class="mb-5 rounded-2xl p-5 text-white shadow"
            :style="{ backgroundColor: path.subject_color || '#6366f1' }"
        >
            <p class="text-xs font-medium opacity-80">{{ path.grade }} · {{ path.subject }}</p>
            <h2 class="mt-1 text-xl font-bold">{{ path.title }}</h2>
            <p class="mt-1 text-sm opacity-80">{{ nodes.length }} nós de conteúdo</p>
        </div>

        <!-- Sem nós -->
        <div v-if="nodes.length === 0" class="rounded-2xl bg-white p-6 text-center shadow">
            <p class="text-slate-500">Esta trilha ainda não possui conteúdo publicado.</p>
        </div>

        <!-- Lista de nós -->
        <div v-else class="relative">
            <!-- Linha vertical de conexão -->
            <div class="absolute left-6 top-6 bottom-6 w-0.5 bg-slate-200" />

            <div class="space-y-3">
                <div
                    v-for="(node, index) in nodes"
                    :key="node.id"
                    class="relative pl-14"
                >
                    <!-- Círculo do nó -->
                    <div
                        class="absolute left-3 top-3.5 flex h-6 w-6 items-center justify-center rounded-full text-xs font-bold"
                        :class="node.lesson_count > 0 ? 'bg-amber-500 text-white' : 'bg-slate-200 text-slate-500'"
                    >
                        {{ index + 1 }}
                    </div>

                    <!-- Card do nó -->
                    <div class="rounded-2xl bg-white p-4 shadow-sm">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0 flex-1">
                                <p class="font-semibold text-slate-800">{{ node.title }}</p>
                                <p class="mt-0.5 text-xs text-slate-400">
                                    {{ nodeTypeIcon(node.node_type) }} {{ nodeTypeLabel(node.node_type) }}
                                    · {{ node.lesson_count }} {{ node.lesson_count === 1 ? 'aula' : 'aulas' }}
                                </p>
                            </div>
                        </div>

                        <!-- Aulas dentro do nó -->
                        <div v-if="node.lessons.length > 0" class="mt-3 space-y-1.5">
                            <Link
                                v-for="lesson in node.lessons"
                                :key="lesson.id"
                                :href="`/aulas/${lesson.id}/jogar`"
                                class="flex items-center justify-between rounded-xl bg-slate-50 px-3 py-2.5 transition-colors hover:bg-amber-50"
                            >
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-slate-700 truncate">{{ lesson.title }}</p>
                                    <p class="text-xs text-slate-400">{{ difficultyStars(lesson.difficulty) }}</p>
                                </div>
                                <div class="ml-3 flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-amber-500 text-white">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M8 5v14l11-7z" />
                                    </svg>
                                </div>
                            </Link>
                        </div>
                        <p v-else class="mt-2 text-xs text-slate-400 italic">
                            Nenhuma aula disponível neste nó ainda.
                        </p>
                    </div>
                </div>
            </div>
        </div>

    </AppLayout>
</template>
