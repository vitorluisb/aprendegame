<script setup>
defineProps({
    disabled: { type: Boolean, default: false },
});

const emit = defineEmits(['select-number', 'clear-cell']);

const numbers = [1, 2, 3, 4, 5, 6, 7, 8, 9];
</script>

<template>
    <section class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm">
        <div class="grid grid-cols-3 gap-2">
            <button
                v-for="number in numbers"
                :key="number"
                type="button"
                class="h-12 rounded-xl bg-slate-100 px-3 text-base font-black text-slate-700 transition hover:bg-amber-100 disabled:cursor-not-allowed disabled:opacity-50 sm:h-11 sm:rounded-lg sm:text-sm"
                :disabled="disabled"
                @click="emit('select-number', number)"
            >
                {{ number }}
            </button>
        </div>
        <button
            type="button"
            class="mt-2 w-full rounded-xl bg-slate-900 px-3 py-3 text-base font-bold leading-none text-white transition hover:bg-slate-700 disabled:cursor-not-allowed disabled:opacity-50 sm:rounded-lg sm:py-2 sm:text-sm"
            :disabled="disabled"
            @click="emit('clear-cell')"
        >
            Limpar
        </button>
    </section>
</template>
