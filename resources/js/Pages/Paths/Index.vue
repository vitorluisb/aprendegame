<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    paths: { type: Array, required: true },
});

const regularPaths = computed(() => props.paths.filter((p) => p.path_type === 'regular'));
const enemPaths = computed(() => props.paths.filter((p) => p.path_type === 'enem'));

function stageBadge(stage) {
    const map = {
        fundamental_1: { label: 'EF I', color: 'bg-cyan-100 text-cyan-700 ring-cyan-200' },
        fundamental_2: { label: 'EF II', color: 'bg-indigo-100 text-indigo-700 ring-indigo-200' },
        medio: { label: 'EM', color: 'bg-fuchsia-100 text-fuchsia-700 ring-fuchsia-200' },
    };

    return map[stage] ?? { label: stage, color: 'bg-slate-100 text-slate-700 ring-slate-200' };
}

function pathTypeCountLabel(count) {
    return `${count} ${count === 1 ? 'trilha' : 'trilhas'}`;
}
</script>

<template>
    <Head title="Trilhas" />
    <AppLayout title="Trilhas de estudo">
        <div class="space-y-5">
            <section class="relative overflow-hidden rounded-3xl border border-slate-200 bg-gradient-to-br from-[#08203e] via-[#103968] to-[#1f4f88] p-5 text-white shadow-sm sm:p-6">
                <div class="pointer-events-none absolute -right-12 -top-14 h-44 w-44 rounded-full bg-cyan-300/20 blur-2xl" />
                <div class="pointer-events-none absolute -bottom-10 left-6 h-36 w-36 rounded-full bg-indigo-300/20 blur-2xl" />
                <div class="relative">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-cyan-100/80">Mapa de Jornada</p>
                    <h2 class="mt-1 text-2xl font-black sm:text-3xl">Escolha sua próxima trilha</h2>
                    <p class="mt-2 max-w-xl text-sm text-white/80">Toque em uma trilha para entrar no mapa e iniciar a próxima parada.</p>
                    <div class="mt-4 grid grid-cols-2 gap-2 text-center text-xs font-bold sm:max-w-md">
                        <div class="rounded-xl bg-white/10 px-2 py-2">
                            <p class="text-lg text-cyan-200">{{ regularPaths.length }}</p>
                            <p class="text-white/80">Regular</p>
                        </div>
                        <div class="rounded-xl bg-white/10 px-2 py-2">
                            <p class="text-lg text-cyan-200">{{ enemPaths.length }}</p>
                            <p class="text-white/80">ENEM</p>
                        </div>
                    </div>
                </div>
            </section>

            <div v-if="paths.length === 0" class="rounded-2xl border border-slate-200 bg-white p-8 text-center shadow-sm">
                <p class="text-slate-500">Nenhuma trilha disponível no momento.</p>
            </div>

            <template v-else>
                <section v-if="regularPaths.length > 0" class="space-y-3">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-black uppercase tracking-wider text-slate-600">Ensino Regular</h3>
                        <span class="text-xs font-semibold text-slate-400">{{ pathTypeCountLabel(regularPaths.length) }}</span>
                    </div>

                    <div class="grid gap-3">
                        <Link
                            v-for="path in regularPaths"
                            :key="path.id"
                            :href="`/trilhas/${path.id}`"
                            class="trail-card group relative overflow-hidden rounded-2xl border border-slate-200 bg-white px-4 py-3.5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md active:scale-[0.99]"
                        >
                            <div class="absolute inset-y-0 left-0 w-1.5" :style="{ backgroundColor: path.subject.color }" />
                            <div class="ml-2 flex items-center gap-3">
                                <div
                                    class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl text-lg font-black text-white"
                                    :style="{ backgroundColor: path.subject.color }"
                                >
                                    {{ path.subject.name[0] }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-black text-slate-800 sm:text-base">{{ path.title }}</p>
                                    <div class="mt-1 flex items-center gap-2">
                                        <span class="rounded-full px-2 py-0.5 text-[11px] font-bold ring-1" :class="stageBadge(path.grade.stage).color">
                                            {{ stageBadge(path.grade.stage).label }}
                                        </span>
                                        <span class="text-xs font-semibold text-slate-500">{{ path.node_count }} missões</span>
                                    </div>
                                    <p class="mt-1 text-xs font-semibold text-sky-600">Toque para abrir o mapa →</p>
                                </div>
                                <div class="rounded-full bg-slate-100 px-2 py-1 text-xs font-black text-slate-500 transition group-hover:bg-slate-900 group-hover:text-white">IR</div>
                            </div>
                        </Link>
                    </div>
                </section>

                <section v-if="enemPaths.length > 0" class="space-y-3">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-black uppercase tracking-wider text-slate-600">ENEM</h3>
                        <span class="text-xs font-semibold text-slate-400">{{ pathTypeCountLabel(enemPaths.length) }}</span>
                    </div>

                    <div class="grid gap-3">
                        <Link
                            v-for="path in enemPaths"
                            :key="path.id"
                            :href="`/trilhas/${path.id}`"
                            class="trail-card group relative overflow-hidden rounded-2xl border border-violet-200 bg-gradient-to-br from-violet-50 to-fuchsia-50 px-4 py-3.5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md active:scale-[0.99]"
                        >
                            <div class="flex items-center gap-3">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-violet-600 text-lg font-black text-white">E</div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-black text-slate-800 sm:text-base">{{ path.title }}</p>
                                    <p class="mt-1 text-xs font-semibold text-violet-600">{{ path.node_count }} missões · Toque para abrir →</p>
                                </div>
                                <div class="rounded-full bg-violet-600 px-2 py-1 text-xs font-black text-white">ENEM</div>
                            </div>
                        </Link>
                    </div>
                </section>

            </template>
        </div>
    </AppLayout>
</template>

<style scoped>
.trail-card {
    animation: trail-reveal 360ms ease both;
}

.trail-card:nth-child(2) {
    animation-delay: 40ms;
}

.trail-card:nth-child(3) {
    animation-delay: 80ms;
}

.trail-card:nth-child(4) {
    animation-delay: 120ms;
}

@keyframes trail-reveal {
    from {
        opacity: 0;
        transform: translateY(8px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
