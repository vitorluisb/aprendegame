<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    paths: { type: Array, required: true },
});

const regularPaths = computed(() => props.paths.filter(p => p.path_type === 'regular'));
const enemPaths = computed(() => props.paths.filter(p => p.path_type === 'enem'));
const vestibularPaths = computed(() => props.paths.filter(p => p.path_type.startsWith('vestibular')));

function typeLabel(type) {
    const labels = {
        regular: 'Ensino Regular',
        enem: 'ENEM',
        vestibular_fuvest: 'FUVEST',
        vestibular_unicamp: 'UNICAMP',
    };
    return labels[type] ?? type;
}

function stageBadge(stage) {
    const map = {
        fundamental_1: { label: 'EF I', color: 'bg-blue-100 text-blue-700' },
        fundamental_2: { label: 'EF II', color: 'bg-indigo-100 text-indigo-700' },
        medio: { label: 'EM', color: 'bg-purple-100 text-purple-700' },
    };
    return map[stage] ?? { label: stage, color: 'bg-slate-100 text-slate-600' };
}
</script>

<template>
    <Head title="Trilhas" />
    <AppLayout title="Trilhas de estudo">

        <div v-if="paths.length === 0" class="rounded-2xl bg-white p-8 text-center shadow">
            <p class="text-slate-500">Nenhuma trilha disponível no momento.</p>
        </div>

        <template v-else>
            <!-- Trilhas regulares -->
            <section v-if="regularPaths.length > 0" class="mb-6">
                <h2 class="mb-3 text-sm font-bold uppercase tracking-wider text-slate-400">Ensino Regular</h2>
                <div class="space-y-2">
                    <Link
                        v-for="path in regularPaths"
                        :key="path.id"
                        :href="`/trilhas/${path.id}`"
                        class="flex items-center gap-4 rounded-2xl bg-white px-4 py-3.5 shadow-sm transition-all hover:shadow-md active:scale-[0.98]"
                    >
                        <div
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-white text-lg font-bold"
                            :style="{ backgroundColor: path.subject.color }"
                        >
                            {{ path.subject.name[0] }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate font-semibold text-slate-800">{{ path.title }}</p>
                            <div class="mt-0.5 flex items-center gap-2">
                                <span
                                    class="rounded-full px-2 py-0.5 text-xs font-medium"
                                    :class="stageBadge(path.grade.stage).color"
                                >
                                    {{ stageBadge(path.grade.stage).label }}
                                </span>
                                <span class="text-xs text-slate-400">{{ path.node_count }} nós</span>
                            </div>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </Link>
                </div>
            </section>

            <!-- Trilhas ENEM -->
            <section v-if="enemPaths.length > 0" class="mb-6">
                <h2 class="mb-3 text-sm font-bold uppercase tracking-wider text-slate-400">ENEM</h2>
                <div class="space-y-2">
                    <Link
                        v-for="path in enemPaths"
                        :key="path.id"
                        :href="`/trilhas/${path.id}`"
                        class="flex items-center gap-4 rounded-2xl bg-gradient-to-r from-violet-50 to-purple-50 px-4 py-3.5 shadow-sm ring-1 ring-violet-100 transition-all hover:shadow-md active:scale-[0.98]"
                    >
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-violet-600 text-white text-lg font-bold">
                            E
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate font-semibold text-slate-800">{{ path.title }}</p>
                            <p class="text-xs text-violet-500 font-medium">{{ path.node_count }} nós</p>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </Link>
                </div>
            </section>

            <!-- Trilhas Vestibular -->
            <section v-if="vestibularPaths.length > 0">
                <h2 class="mb-3 text-sm font-bold uppercase tracking-wider text-slate-400">Vestibulares</h2>
                <div class="space-y-2">
                    <Link
                        v-for="path in vestibularPaths"
                        :key="path.id"
                        :href="`/trilhas/${path.id}`"
                        class="flex items-center gap-4 rounded-2xl bg-gradient-to-r from-amber-50 to-yellow-50 px-4 py-3.5 shadow-sm ring-1 ring-amber-100 transition-all hover:shadow-md active:scale-[0.98]"
                    >
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-500 text-white text-xs font-bold">
                            {{ typeLabel(path.path_type) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate font-semibold text-slate-800">{{ path.title }}</p>
                            <p class="text-xs text-amber-600 font-medium">{{ path.node_count }} nós</p>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </Link>
                </div>
            </section>
        </template>

    </AppLayout>
</template>
