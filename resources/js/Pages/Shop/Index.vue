<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useUiSfx } from '@/Composables/useUiSfx';

const props = defineProps({
    gems_balance: { type: Number, required: true },
    items: { type: Array, default: () => [] },
});

const page = usePage();
const shopError = computed(() => page.props.errors?.shop ?? null);
const { combo, purchase, error } = useUiSfx();

function typeLabel(type) {
    const map = {
        avatar: 'Avatar',
        frame: 'Moldura',
        theme: 'Tema',
        power_up: 'Power-up',
    };

    return map[type] ?? type;
}
</script>

<template>
    <Head title="Loja" />

    <AppLayout title="Loja de Itens">
        <section class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-cyan-500 via-sky-500 to-indigo-600 p-5 text-white shadow-xl">
            <div class="game-shimmer pointer-events-none absolute inset-0 opacity-25" />
            <p class="text-xs font-semibold uppercase tracking-wide text-white/80">Saldo atual</p>
            <p class="mt-1 text-3xl font-bold">{{ gems_balance }} gems</p>
            <p class="mt-1 text-xs text-white/80">Compre avatares, molduras e temas para personalizar seu perfil.</p>
        </section>

        <p v-if="shopError" class="mt-3 rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700" @mouseenter="error">
            {{ shopError }}
        </p>

        <section class="mt-4 space-y-3">
            <article
                v-for="item in items"
                :key="item.id"
                class="game-surface p-4"
            >
                <div class="flex items-start justify-between gap-3">
                    <div class="flex min-w-0 gap-3">
                        <div
                            v-if="item.type === 'avatar'"
                            class="h-12 w-12 shrink-0 overflow-hidden rounded-full border border-slate-200 bg-slate-100"
                        >
                            <img v-if="item.image_url" :src="item.image_url" :alt="`Avatar ${item.name}`" class="h-full w-full object-cover">
                            <div v-else class="flex h-full w-full items-center justify-center text-sm font-bold text-slate-600">
                                {{ item.name?.[0] ?? '?' }}
                            </div>
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <h2 class="truncate text-sm font-semibold text-slate-800">{{ item.name }}</h2>
                                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-600">
                                    {{ typeLabel(item.type) }}
                                </span>
                            </div>
                            <p class="mt-1 line-clamp-2 text-xs text-slate-500">{{ item.description || 'Item da loja sem descrição.' }}</p>
                        </div>
                    </div>

                    <p class="shrink-0 text-sm font-bold text-cyan-700">{{ item.gem_price }} gems</p>
                </div>

                <div class="mt-3 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span
                            v-if="item.is_equipped"
                            class="rounded-full bg-emerald-100 px-2 py-1 text-xs font-semibold text-emerald-700"
                        >
                            Equipado
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
                            class="rounded-lg bg-cyan-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-cyan-700"
                            preserve-scroll
                            @click="purchase"
                        >
                            Comprar
                        </Link>

                        <Link
                            v-else-if="!item.is_equipped"
                            href="/loja/equipar"
                            method="post"
                            as="button"
                            :data="{ item_id: item.id }"
                            class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-indigo-700"
                            preserve-scroll
                            @click="combo"
                        >
                            Equipar
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
