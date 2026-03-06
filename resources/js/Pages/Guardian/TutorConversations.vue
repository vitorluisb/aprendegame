<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    student: { type: Object, required: true },
    messages: { type: Array, default: () => [] },
});

const search = ref('');

const filteredMessages = computed(() => {
    if (!search.value.trim()) return props.messages;
    const q = search.value.toLowerCase();
    return props.messages.filter((m) => m.content.toLowerCase().includes(q));
});

// Group messages by date
const groupedMessages = computed(() => {
    const groups = {};
    filteredMessages.value.forEach((m) => {
        const date = m.created_at?.split(' ')?.[0] ?? 'Sem data';
        if (!groups[date]) groups[date] = [];
        groups[date].push(m);
    });
    return Object.entries(groups).map(([date, msgs]) => ({ date, messages: msgs }));
});

function formatDateLabel(dateStr) {
    try {
        const [d, m, y] = dateStr.split('/');
        const date = new Date(`${y}-${m}-${d}`);
        return new Intl.DateTimeFormat('pt-BR', { weekday: 'long', day: 'numeric', month: 'long' }).format(date);
    } catch {
        return dateStr;
    }
}
</script>

<template>
    <Head :title="`Conversas - ${student.name}`" />
    <AppLayout :title="`Conversas · ${student.name}`">

        <!-- Voltar -->
        <Link :href="`/responsavel/${student.id}`" class="mb-3 inline-flex items-center gap-1 text-sm text-slate-500 hover:text-slate-700">
            ← Voltar para {{ student.name }}
        </Link>

        <!-- Header -->
        <section class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-500 via-teal-500 to-cyan-600 p-5 text-white shadow-xl">
            <div class="pointer-events-none absolute inset-0 opacity-20 [background:radial-gradient(ellipse_at_top_right,_#ffffff55_0%,_transparent_70%)]" />
            <div class="flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/20 text-2xl">🤖</div>
                <div>
                    <p class="text-sm font-medium opacity-80">Histórico de conversas</p>
                    <h2 class="text-xl font-black">{{ student.name }} · Tutor IA</h2>
                    <p class="mt-0.5 text-xs opacity-70">
                        {{ messages.length }} mensagem{{ messages.length !== 1 ? 's' : '' }} · Apagadas automaticamente após 15 dias
                    </p>
                </div>
            </div>
        </section>

        <!-- Aviso somente leitura -->
        <div class="mt-3 flex items-start gap-2 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2.5">
            <span class="text-lg">🔒</span>
            <p class="text-xs text-amber-700">
                <strong>Modo visualização.</strong> Você está vendo as conversas do seu filho com o Tutor IA. As mensagens são apagadas automaticamente após 15 dias.
            </p>
        </div>

        <!-- Sem mensagens -->
        <div v-if="messages.length === 0" class="mt-6 rounded-2xl bg-white p-8 text-center shadow-sm">
            <p class="text-4xl">💬</p>
            <p class="mt-3 font-semibold text-slate-700">Sem conversas ainda</p>
            <p class="mt-1 text-sm text-slate-400">Seu filho ainda não iniciou conversas com o Tutor IA.</p>
        </div>

        <template v-else>
            <!-- Busca -->
            <div class="mt-3">
                <input
                    v-model="search"
                    type="search"
                    placeholder="Buscar nas conversas..."
                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm placeholder-slate-400 outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100"
                >
                <p v-if="search && filteredMessages.length === 0" class="mt-2 text-center text-sm text-slate-400">
                    Nenhuma mensagem encontrada.
                </p>
            </div>

            <!-- Mensagens agrupadas por data -->
            <div class="mt-4 space-y-6">
                <div v-for="group in groupedMessages" :key="group.date">
                    <!-- Separador de data -->
                    <div class="flex items-center gap-2">
                        <div class="h-px flex-1 bg-slate-200" />
                        <span class="rounded-full bg-slate-100 px-3 py-0.5 text-xs font-medium text-slate-500 capitalize">
                            {{ formatDateLabel(group.date) }}
                        </span>
                        <div class="h-px flex-1 bg-slate-200" />
                    </div>

                    <!-- Mensagens do grupo -->
                    <div class="space-y-2">
                        <div
                            v-for="msg in group.messages"
                            :key="msg.id"
                            class="flex"
                            :class="msg.role === 'student' ? 'justify-end' : 'justify-start'"
                        >
                            <!-- Avatar do tutor -->
                            <div v-if="msg.role === 'tutor'" class="mr-2 flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-sm self-end">
                                🤖
                            </div>

                            <div
                                class="max-w-[80%] rounded-2xl px-3.5 py-2.5 text-sm shadow-sm"
                                :class="[
                                    msg.role === 'student'
                                        ? 'rounded-br-sm bg-violet-600 text-white'
                                        : 'rounded-bl-sm bg-white text-slate-800',
                                    msg.blocked ? 'opacity-60' : '',
                                ]"
                            >
                                <!-- Mensagem bloqueada -->
                                <div v-if="msg.blocked" class="mb-1.5 flex items-center gap-1.5 rounded-lg bg-red-100 px-2 py-1 text-xs text-red-700">
                                    <span>🚫</span>
                                    <span>Mensagem bloqueada pela moderação</span>
                                </div>

                                <p class="leading-relaxed whitespace-pre-wrap">{{ msg.content }}</p>

                                <p class="mt-1 text-right text-[10px] opacity-60">
                                    {{ msg.created_at?.split(' ')?.[1] ?? '' }}
                                </p>
                            </div>

                            <!-- Avatar do aluno -->
                            <div v-if="msg.role === 'student'" class="ml-2 flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-violet-100 text-sm self-end overflow-hidden">
                                <img v-if="student.avatar_url" :src="student.avatar_url" :alt="student.name" class="h-full w-full object-cover">
                                <span v-else class="text-xs font-bold text-violet-600">{{ student.name?.[0] ?? '?' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>

    </AppLayout>
</template>
