<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useUiSfx } from '@/Composables/useUiSfx';

const props = defineProps({
    is_student: { type: Boolean, default: true },
    student: { type: Object, default: null },
    grade_options: { type: Array, default: () => [] },
});
const page = usePage();

const gradeForm = useForm({
    grade_id: props.student?.grade_id ?? '',
});
const avatarForm = useForm({
    avatar: null,
});
const { isEnabled, volume, pack, prefersReducedMotion, pop, success, error, combo, purchase, victory, setVolume, setPack, toggle } = useUiSfx();
const selectedPack = ref(pack.value);
const packOptions = [
    { value: 'arcade', label: 'Arcade' },
    { value: 'neon', label: 'Neon' },
    { value: 'chiptune', label: 'Chiptune' },
];

const xpPercent = computed(() => {
    if (!props.student) {
        return 0;
    }

    return props.student.xp_in_level;
});
const equippedFrameStyle = computed(() => page.props.gameplay_customization?.frame?.style ?? null);
const equippedFrameClass = computed(() => {
    const slug = page.props.gameplay_customization?.frame?.slug;

    if (slug === 'borda-fogo') {
        return 'game-avatar-frame--fire';
    }

    if (slug === 'borda-arco-iris') {
        return 'game-avatar-frame--rainbow';
    }

    if (slug) {
        return 'game-avatar-frame--gold';
    }

    return '';
});
const heroStyle = computed(() => ({
    background: 'linear-gradient(135deg, var(--color-game-hero-start), var(--color-game-hero-mid), var(--color-game-hero-end))',
}));

function typeLabel(type) {
    const map = {
        avatar: 'Avatar',
        frame: 'Moldura',
        border: 'Moldura',
        theme: 'Tema',
        power_up: 'Power-up',
        powerup: 'Power-up',
    };

    return map[type] ?? type;
}

function onVolumeChange(event) {
    setVolume(Number(event.target.value));
}

function onPackChange(event) {
    selectedPack.value = event.target.value;
    setPack(selectedPack.value);
}

function onAvatarSelected(event) {
    const file = event.target.files?.[0];

    if (!file) {
        return;
    }

    avatarForm.avatar = file;
    avatarForm.post('/perfil/avatar', {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => success(),
        onError: () => error(),
        onFinish: () => {
            event.target.value = '';
            avatarForm.reset('avatar');
        },
    });
}
</script>

<template>
    <Head title="Perfil" />

    <AppLayout title="Perfil">
        <template v-if="student">
            <section class="relative overflow-hidden rounded-2xl p-5 text-white shadow-xl" :style="heroStyle">
                <div class="game-shimmer pointer-events-none absolute inset-0 opacity-25" />
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-white/80">Perfil do aluno</p>
                        <h2 class="mt-1 text-2xl font-bold">{{ student.name }}</h2>
                    </div>
                    <div class="rounded-full bg-white/20 px-3 py-1 text-xs font-semibold">
                        Nível {{ student.level }}
                    </div>
                </div>

                <div class="mt-4">
                    <div class="flex items-end justify-between text-xs">
                        <span class="font-semibold">Progresso no nível</span>
                        <span>{{ student.xp_in_level }}/100 XP</span>
                    </div>
                    <div class="mt-1.5 h-2.5 overflow-hidden rounded-full bg-white/30">
                        <div class="h-full rounded-full bg-white transition-all duration-700" :style="{ width: xpPercent + '%' }" />
                    </div>
                    <p class="mt-1.5 text-xs text-white/80">{{ student.total_xp }} XP no total</p>
                </div>
            </section>

            <section class="game-surface mt-4 p-4">
                <h3 class="text-sm font-semibold text-slate-700">Avatar do perfil</h3>
                <p class="mt-1 text-xs text-slate-500">Avatar da loja equipado aparece no perfil/ranking. Sem avatar equipado, usamos sua foto pessoal.</p>

                <div class="mt-3 grid grid-cols-2 gap-3">
                    <article class="rounded-xl border border-slate-200 bg-white p-3 text-center">
                        <p class="text-[11px] font-semibold text-slate-500">Avatar ativo</p>
                        <div class="mx-auto mt-2 h-16 w-16 overflow-hidden rounded-full border border-slate-200 bg-slate-100" :class="equippedFrameClass" :style="equippedFrameStyle">
                            <img v-if="student.avatar_url" :src="student.avatar_url" alt="Avatar ativo" class="h-full w-full object-cover">
                            <div v-else class="flex h-full w-full items-center justify-center text-lg font-bold text-slate-500">
                                {{ student.name?.[0] ?? '?' }}
                            </div>
                        </div>
                    </article>

                    <article class="rounded-xl border border-slate-200 bg-white p-3 text-center">
                        <p class="text-[11px] font-semibold text-slate-500">Foto pessoal</p>
                        <div class="mx-auto mt-2 h-16 w-16 overflow-hidden rounded-full border border-slate-200 bg-slate-100">
                            <img v-if="student.avatar_personal_url" :src="student.avatar_personal_url" alt="Foto pessoal" class="h-full w-full object-cover">
                            <div v-else class="flex h-full w-full items-center justify-center text-lg font-bold text-slate-500">
                                {{ student.name?.[0] ?? '?' }}
                            </div>
                        </div>
                    </article>
                </div>

                <label class="mt-3 inline-flex cursor-pointer items-center gap-2 rounded-xl bg-[var(--color-game-accent)] px-3 py-2 text-xs font-semibold text-white transition hover:brightness-95">
                    <input type="file" accept="image/png,image/jpeg,image/webp,image/gif,image/avif,image/heic,image/heif" class="hidden" :disabled="avatarForm.processing" @change="onAvatarSelected">
                    {{ avatarForm.processing ? 'Enviando foto...' : 'Enviar foto pessoal' }}
                </label>
                <p v-if="avatarForm.errors.avatar" class="mt-2 text-xs text-red-600">{{ avatarForm.errors.avatar }}</p>
            </section>

            <section class="mt-4 grid grid-cols-2 gap-3">
                <article class="game-surface p-4">
                    <p class="text-xs font-medium text-slate-500">Sequência atual</p>
                    <p class="mt-1 text-3xl font-bold text-orange-500">{{ student.streak_current }}</p>
                    <p class="text-xs text-slate-400">dias</p>
                    <p class="mt-1 text-xs text-slate-400">Melhor: {{ student.streak_best }}</p>
                </article>

                <article class="game-surface p-4">
                    <p class="text-xs font-medium text-slate-500">Saldo de Neurons</p>
                    <p class="mt-1 text-3xl font-bold text-cyan-600">{{ student.total_gems }}</p>
                    <p class="text-xs text-slate-400">para gastar na loja</p>
                    <p class="mt-1 text-xs text-slate-400">Itens: {{ student.inventory_count }}</p>
                </article>
            </section>

            <section class="game-surface mt-4 p-4">
                <h3 class="text-sm font-semibold text-slate-700">Série atual</h3>
                <p class="mt-1 text-xs text-slate-500">Isso ajuda a filtrar trilhas e conteúdos recomendados.</p>

                <form
                    class="mt-3 flex flex-col gap-2 sm:flex-row"
                    @submit.prevent="gradeForm.post('/perfil/serie', { preserveScroll: true, onSuccess: () => success() })"
                >
                    <select
                        v-model="gradeForm.grade_id"
                        class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 outline-none transition focus:border-indigo-400"
                    >
                        <option value="">Não informar agora</option>
                        <option v-for="grade in grade_options" :key="grade.id" :value="grade.id">
                            {{ grade.name }}
                        </option>
                    </select>

                    <button
                        type="submit"
                        class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white disabled:cursor-not-allowed disabled:opacity-60"
                        :disabled="gradeForm.processing"
                    >
                        Salvar série
                    </button>
                </form>
            </section>

            <section class="game-surface mt-4 p-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-700">Sons do jogo</h3>
                        <p class="mt-1 text-xs text-slate-500">Escolha pack e volume dos efeitos sonoros.</p>
                    </div>
                    <button
                        type="button"
                        class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50"
                        @click="toggle"
                    >
                        {{ isEnabled ? 'Som: ligado' : 'Som: desligado' }}
                    </button>
                </div>

                <div class="mt-3 grid grid-cols-1 gap-3">
                    <p
                        v-if="prefersReducedMotion"
                        class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-medium text-amber-700"
                    >
                        Seu sistema está com “reduzir movimento” ativo. SFX ficam no mudo automaticamente.
                    </p>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Pack</label>
                        <select
                            :value="selectedPack"
                            class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 outline-none transition focus:border-indigo-400"
                            @change="onPackChange"
                        >
                            <option v-for="option in packOptions" :key="option.value" :value="option.value">
                                {{ option.label }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Volume: {{ volume }}%</label>
                        <input
                            type="range"
                            min="0"
                            max="100"
                            :value="volume"
                            class="h-2 w-full cursor-pointer appearance-none rounded-lg bg-slate-200 accent-indigo-500"
                            @input="onVolumeChange"
                        >
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <button type="button" class="rounded-lg bg-slate-100 px-2.5 py-1.5 text-xs font-semibold text-slate-700" @click="pop">Tap</button>
                        <button type="button" class="rounded-lg bg-emerald-100 px-2.5 py-1.5 text-xs font-semibold text-emerald-700" @click="success">Sucesso</button>
                        <button type="button" class="rounded-lg bg-cyan-100 px-2.5 py-1.5 text-xs font-semibold text-cyan-700" @click="purchase">Compra</button>
                        <button type="button" class="rounded-lg bg-indigo-100 px-2.5 py-1.5 text-xs font-semibold text-indigo-700" @click="combo">Combo</button>
                        <button type="button" class="rounded-lg bg-amber-100 px-2.5 py-1.5 text-xs font-semibold text-amber-700" @click="victory">Vitória</button>
                        <button type="button" class="rounded-lg bg-red-100 px-2.5 py-1.5 text-xs font-semibold text-red-700" @click="error">Erro</button>
                    </div>
                </div>
            </section>

            <section class="mt-5">
                <div class="mb-2 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-slate-700">Conquistas</h3>
                    <span class="text-xs text-slate-500">{{ student.badges_count }}</span>
                </div>

                <div v-if="student.badges.length" class="grid grid-cols-2 gap-2">
                    <article
                        v-for="entry in student.badges"
                        :key="entry.id"
                        class="game-surface rounded-xl p-3"
                    >
                        <div class="flex items-center gap-2">
                            <span class="text-lg">{{ entry.badge.icon || '🏅' }}</span>
                            <p class="truncate text-sm font-semibold text-slate-800">{{ entry.badge.name }}</p>
                        </div>
                        <p class="mt-1 line-clamp-2 text-xs text-slate-500">{{ entry.badge.description || 'Sem descrição' }}</p>
                    </article>
                </div>

                <div v-else class="game-surface rounded-xl p-4 text-center text-sm text-slate-500">
                    Você ainda não desbloqueou conquistas.
                </div>
            </section>

            <section class="mt-5">
                <div class="mb-2 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-slate-700">Itens da loja</h3>
                    <span class="text-xs text-slate-500">{{ student.inventory_count }}</span>
                </div>

                <div v-if="student.inventory.length" class="space-y-2">
                    <article
                        v-for="entry in student.inventory"
                        :key="entry.id"
                        class="game-surface flex items-center justify-between rounded-xl px-4 py-3"
                    >
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-slate-800">{{ entry.item.name }}</p>
                            <p class="text-xs text-slate-500">{{ typeLabel(entry.item.type) }}</p>
                        </div>

                        <span
                            v-if="entry.equipped"
                            class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-700"
                        >
                            Equipado
                        </span>
                    </article>
                </div>

                <div v-else class="game-surface rounded-xl p-4 text-center text-sm text-slate-500">
                    Você ainda não possui itens da loja.
                </div>
            </section>

            <div class="mt-6 grid grid-cols-2 gap-3">
                <Link
                    href="/trilhas"
                    class="game-cta bg-indigo-600 px-4 py-3 text-center text-sm"
                    @click="pop"
                >
                    Voltar às trilhas
                </Link>
                <Link
                    href="/loja"
                    class="rounded-xl border border-cyan-200 bg-cyan-50 px-4 py-3 text-center text-sm font-semibold text-cyan-700 transition hover:bg-cyan-100"
                    @click="pop"
                >
                    Abrir loja
                </Link>
            </div>
        </template>

        <template v-else>
            <section class="rounded-2xl bg-white p-6 text-center shadow-sm">
                <template v-if="is_student">
                    <p class="text-slate-600">Seu perfil de aluno ainda não foi configurado.</p>
                    <p class="mt-1 text-sm text-slate-400">Entre em contato com sua escola.</p>
                </template>
                <template v-else>
                    <p class="text-slate-600">Este perfil gamificado está disponível para contas de aluno.</p>
                    <p class="mt-1 text-sm text-slate-400">Sua conta atual não tem trilhas e progressão de estudante.</p>
                </template>
            </section>
        </template>
    </AppLayout>
</template>
