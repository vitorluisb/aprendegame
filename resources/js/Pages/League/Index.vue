<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';

const props = defineProps({
    scope: { type: String, required: true },
    grade_filter_name: { type: String, default: null },
    week: { type: Number, required: true },
    year: { type: Number, required: true },
    entries: { type: Array, default: () => [] },
    my_position: { type: Object, required: true },
});
const page = usePage();
const equippedFrameStyle = page.props.gameplay_customization?.frame?.style ?? null;
const equippedFrameSlug = page.props.gameplay_customization?.frame?.slug ?? null;
const heroStyle = {
    background: 'linear-gradient(135deg, var(--color-game-hero-start), var(--color-game-hero-mid), var(--color-game-hero-end))',
};

function leagueLabel(league) {
    const map = {
        bronze: 'Bronze',
        silver: 'Prata',
        gold: 'Ouro',
        platinum: 'Platina',
    };

    return map[league] ?? league;
}

function leagueClass(league) {
    const map = {
        bronze: 'bg-amber-100 text-amber-800',
        silver: 'bg-slate-200 text-slate-700',
        gold: 'bg-yellow-100 text-yellow-800',
        platinum: 'bg-cyan-100 text-cyan-800',
    };

    return map[league] ?? 'bg-slate-100 text-slate-700';
}

function initialFromName(name) {
    return name?.[0]?.toUpperCase?.() ?? '?';
}

function frameClass(entry) {
    if (!entry?.is_me || !equippedFrameSlug) {
        return '';
    }

    if (equippedFrameSlug === 'borda-fogo') {
        return 'game-avatar-frame--fire';
    }

    if (equippedFrameSlug === 'borda-arco-iris') {
        return 'game-avatar-frame--rainbow';
    }

    return 'game-avatar-frame--gold';
}

function rankBadge(rank) {
    if (rank === 1) return '🥇';
    if (rank === 2) return '🥈';
    if (rank === 3) return '🥉';

    return `#${rank}`;
}
</script>

<template>
    <Head title="Ranking" />

    <AppLayout title="Ranking Semanal">
        <section class="relative overflow-hidden rounded-2xl p-5 text-white shadow-xl" :style="heroStyle">
            <div class="game-shimmer pointer-events-none absolute inset-0 opacity-25" />
            <p class="text-xs font-semibold uppercase tracking-wide text-white/80">Liga da Semana {{ week }}/{{ year }}</p>
            <div class="mt-3 grid grid-cols-3 gap-3 text-center">
                <article class="rounded-xl bg-white/15 px-2 py-3">
                    <p class="text-[11px] text-white/80">Seu rank</p>
                    <p class="mt-1 text-2xl font-bold">#{{ my_position.rank }}</p>
                </article>
                <article class="rounded-xl bg-white/15 px-2 py-3">
                    <p class="text-[11px] text-white/80">XP semanal</p>
                    <p class="mt-1 text-2xl font-bold">{{ my_position.weekly_xp }}</p>
                </article>
                <article class="rounded-xl bg-white/15 px-2 py-3">
                    <p class="text-[11px] text-white/80">Sua liga</p>
                    <p class="mt-1 text-lg font-bold">{{ leagueLabel(my_position.league) }}</p>
                </article>
            </div>
            <p class="mt-3 text-xs text-white/80">
                {{ scope === 'school' ? 'Ranking da sua escola' : 'Ranking global (alunos sem escola)' }}
                <template v-if="grade_filter_name"> · Série: {{ grade_filter_name }}</template>
            </p>
        </section>

        <section class="mt-4 grid grid-cols-2 gap-3">
            <article class="rounded-2xl border border-violet-200 bg-gradient-to-br from-violet-50 to-indigo-100 p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-violet-500">Sua posição</p>
                <p class="mt-1 text-2xl font-black text-slate-800">{{ rankBadge(my_position.rank) }}</p>
                <p class="text-xs text-slate-500">na liga semanal</p>
            </article>
            <article class="rounded-2xl border border-cyan-200 bg-gradient-to-br from-cyan-50 to-sky-100 p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-cyan-600">Seu resultado</p>
                <p class="mt-1 text-2xl font-black text-slate-800">{{ my_position.weekly_xp }} XP</p>
                <p class="text-xs text-slate-500">{{ leagueLabel(my_position.league) }}</p>
            </article>
        </section>

        <section class="mt-4">
            <h2 class="mb-2 text-sm font-semibold text-slate-700">Classificação da Semana</h2>

            <div v-if="entries.length" class="space-y-2">
                <article
                    v-for="entry in entries"
                    :key="entry.student.id"
                    class="relative overflow-hidden rounded-2xl border bg-white px-4 py-3 shadow-sm"
                    :class="entry.is_me ? 'border-indigo-300 ring-2 ring-indigo-200 bg-indigo-50/40' : 'border-slate-200'"
                >
                    <div class="flex min-w-0 items-center gap-3">
                        <p class="w-10 text-center text-sm font-black text-slate-600">{{ rankBadge(entry.rank) }}</p>
                        <div class="h-10 w-10 overflow-hidden rounded-full border border-slate-200 bg-slate-100" :class="frameClass(entry)" :style="entry.is_me ? equippedFrameStyle : null">
                            <img v-if="entry.student.avatar_url" :src="entry.student.avatar_url" :alt="`Avatar de ${entry.student.name}`" class="h-full w-full object-cover">
                            <div v-else class="flex h-full w-full items-center justify-center text-xs font-bold text-slate-600">
                                {{ initialFromName(entry.student.name) }}
                            </div>
                        </div>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-slate-800">{{ entry.student.name }}</p>
                            <p v-if="entry.is_me" class="text-xs font-bold text-indigo-600">Você</p>
                        </div>
                    </div>

                    <p class="text-sm font-black text-slate-700">{{ entry.weekly_xp }} XP</p>
                </article>
            </div>

            <div v-else class="game-surface rounded-xl p-5 text-center text-sm text-slate-500">
                Ainda não há pontuação nesta semana.
            </div>
        </section>

        <section class="game-surface mt-4 p-4">
            <h3 class="text-sm font-semibold text-slate-700">Como funcionam as ligas</h3>
            <div class="mt-2 flex flex-wrap gap-2">
                <span class="rounded-full px-2 py-1 text-xs font-semibold" :class="leagueClass('bronze')">Bronze</span>
                <span class="rounded-full px-2 py-1 text-xs font-semibold" :class="leagueClass('silver')">Prata</span>
                <span class="rounded-full px-2 py-1 text-xs font-semibold" :class="leagueClass('gold')">Ouro</span>
                <span class="rounded-full px-2 py-1 text-xs font-semibold" :class="leagueClass('platinum')">Platina</span>
            </div>
            <p class="mt-2 text-xs text-slate-500">A classificação é atualizada com o XP da semana e reinicia no fim do ciclo semanal.</p>
        </section>
    </AppLayout>
</template>
