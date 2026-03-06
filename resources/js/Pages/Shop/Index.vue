<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useUiSfx } from '@/Composables/useUiSfx';

const props = defineProps({
    gems_balance: { type: Number, required: true },
    lives_current: { type: Number, required: true },
    lives_max: { type: Number, required: true },
    life_cost: { type: Number, required: true },
    items: { type: Array, default: () => [] },
});

const page = usePage();
const shopError = computed(() => page.props.errors?.shop ?? null);
const { combo, purchase, error } = useUiSfx();
const heroStyle = computed(() => ({
    background: 'linear-gradient(135deg, var(--color-game-hero-start), var(--color-game-hero-mid), var(--color-game-hero-end))',
}));
const canBuyLife = computed(() => props.lives_current < props.lives_max && props.gems_balance >= props.life_cost);
const ownedCount = computed(() => props.items.filter(item => item.is_owned).length);
const equippedCount = computed(() => props.items.filter(item => item.is_equipped).length);

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
</script>

<template>
    <Head title="Loja" />

    <AppLayout title="Loja de Itens">
        <section class="relative overflow-hidden rounded-2xl p-5 text-white shadow-xl" :style="heroStyle">
            <div class="game-shimmer pointer-events-none absolute inset-0 opacity-25" />
            <p class="text-xs font-semibold uppercase tracking-wide text-white/80">Loja do aluno</p>
            <p class="mt-1 text-3xl font-bold">{{ gems_balance }} Neurons</p>
            <p class="mt-1 text-xs text-white/80">Compre avatares, molduras, temas e vidas para continuar avançando.</p>

            <div class="mt-3 grid grid-cols-3 gap-2">
                <div class="rounded-xl bg-white/20 px-2 py-2 text-center">
                    <p class="text-[10px] uppercase tracking-wide text-white/75">Itens</p>
                    <p class="text-sm font-bold">{{ items.length }}</p>
                </div>
                <div class="rounded-xl bg-white/20 px-2 py-2 text-center">
                    <p class="text-[10px] uppercase tracking-wide text-white/75">Possuídos</p>
                    <p class="text-sm font-bold">{{ ownedCount }}</p>
                </div>
                <div class="rounded-xl bg-white/20 px-2 py-2 text-center">
                    <p class="text-[10px] uppercase tracking-wide text-white/75">Equipados</p>
                    <p class="text-sm font-bold">{{ equippedCount }}</p>
                </div>
            </div>
        </section>

        <section class="mt-3 rounded-2xl border border-rose-200 bg-gradient-to-br from-rose-50 to-pink-100 p-4">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-rose-500">Vidas</p>
                    <p class="mt-0.5 text-lg font-black text-rose-600">❤ {{ lives_current }}/{{ lives_max }}</p>
                    <p class="text-xs text-slate-500">Recupera +1 por hora.</p>
                </div>
                <Link
                    href="/loja/vidas/comprar"
                    method="post"
                    as="button"
                    class="rounded-xl px-3 py-2 text-xs font-bold text-white transition active:scale-[0.99]"
                    :class="canBuyLife ? 'bg-rose-600 hover:bg-rose-700' : 'bg-slate-300 cursor-not-allowed'"
                    :disabled="!canBuyLife"
                    preserve-scroll
                    @click="purchase"
                >
                    +1 vida ({{ life_cost }} Neurons)
                </Link>
            </div>
            <p v-if="lives_current >= lives_max" class="mt-2 text-xs text-slate-500">
                Você já está com vidas máximas.
            </p>
        </section>

        <p v-if="shopError" class="mt-3 rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700" @mouseenter="error">
            {{ shopError }}
        </p>

        <section class="mt-4 space-y-3">
            <article
                v-for="item in items"
                :key="item.id"
                class="relative overflow-hidden rounded-2xl border bg-white p-4 shadow-sm transition active:scale-[0.995]"
                :class="item.is_equipped ? 'border-emerald-300 ring-2 ring-emerald-200 bg-emerald-50/40' : 'border-slate-200'"
            >
                <div class="absolute right-3 top-3 rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-600">
                    {{ typeLabel(item.type) }}
                </div>
                <div class="flex items-start justify-between gap-3">
                    <div class="flex min-w-0 gap-3">
                        <div
                            v-if="item.type === 'avatar'"
                            class="h-14 w-14 shrink-0 overflow-hidden rounded-full border border-slate-200 bg-slate-100"
                        >
                            <img v-if="item.image_url" :src="item.image_url" :alt="`Avatar ${item.name}`" class="h-full w-full object-cover">
                            <div v-else class="flex h-full w-full flex-col items-center justify-center bg-gradient-to-br from-slate-200 to-slate-300 text-slate-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 12a4 4 0 100-8 4 4 0 000 8zm0 2c-3.314 0-6 2.238-6 5h12c0-2.762-2.686-5-6-5z" />
                                </svg>
                                <span class="mt-0.5 text-[10px] font-semibold uppercase tracking-wide">Sem imagem</span>
                            </div>
                        </div>
                        <div class="min-w-0">
                            <h2 class="truncate pr-20 text-sm font-bold text-slate-800">{{ item.name }}</h2>
                            <p class="mt-1 line-clamp-2 text-xs text-slate-500">{{ item.description || 'Item da loja sem descrição.' }}</p>
                        </div>
                    </div>

                    <p class="shrink-0 text-sm font-black text-cyan-700">{{ item.gem_price }} N</p>
                </div>

                <div class="mt-3 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span
                            v-if="item.is_equipped"
                            class="rounded-full border border-emerald-300 bg-emerald-100 px-2 py-1 text-xs font-semibold text-emerald-700"
                        >
                            Equipado agora
                        </span>
                        <span
                            v-else-if="item.is_owned"
                            class="rounded-full bg-amber-100 px-2 py-1 text-xs font-semibold text-amber-700"
                        >
                            Possuído
                        </span>
                    </div>

                    <div class="flex gap-2">
                        <Link
                            v-if="!item.is_owned"
                            href="/loja/comprar"
                            method="post"
                            as="button"
                            :data="{ item_id: item.id }"
                            class="rounded-xl bg-cyan-600 px-3 py-1.5 text-xs font-bold text-white transition active:scale-[0.99] hover:bg-cyan-700"
                            preserve-scroll
                            @click="purchase"
                        >
                            Comprar ↗
                        </Link>

                        <Link
                            v-else-if="!item.is_equipped"
                            href="/loja/equipar"
                            method="post"
                            as="button"
                            :data="{ item_id: item.id }"
                            class="rounded-xl bg-indigo-600 px-3 py-1.5 text-xs font-bold text-white transition active:scale-[0.99] hover:bg-indigo-700"
                            preserve-scroll
                            @click="combo"
                        >
                            Equipar ↗
                        </Link>
                    </div>
                </div>
            </article>

            <div v-if="!items.length" class="game-surface rounded-xl p-5 text-center text-sm text-slate-500">
                Ainda não há itens disponíveis na loja.
            </div>
        </section>
    </AppLayout>
</template>
