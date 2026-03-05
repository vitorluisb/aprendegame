<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const recovery = ref(false);

const form = useForm({
    code: '',
    recovery_code: '',
});

const submit = () => form.post('/two-factor-challenge');

const toggleRecovery = () => {
    recovery.value = !recovery.value;
    form.reset();
};
</script>

<template>
    <Head title="Verificação em dois fatores" />
    <GuestLayout>
        <h2 class="text-xl font-bold text-slate-900 mb-1">Verificação em dois fatores</h2>
        <p class="text-slate-500 text-sm mb-6">
            <template v-if="!recovery">
                Digite o código de 6 dígitos gerado pelo seu aplicativo autenticador.
            </template>
            <template v-else>
                Digite um dos seus códigos de recuperação de emergência.
            </template>
        </p>

        <form class="space-y-4" @submit.prevent="submit">
            <div v-if="!recovery">
                <label class="block text-sm font-medium text-slate-700 mb-1">Código de autenticação</label>
                <input
                    v-model="form.code"
                    type="text"
                    inputmode="numeric"
                    autocomplete="one-time-code"
                    placeholder="000000"
                    maxlength="6"
                    autofocus
                    :class="['w-full rounded-lg border px-3 py-2.5 text-sm tracking-widest text-center transition focus:outline-none focus:ring-2 focus:ring-blue-500',
                             form.errors.code ? 'border-red-400 bg-red-50' : 'border-slate-300 bg-white']"
                />
                <p v-if="form.errors.code" class="mt-1 text-xs text-red-600">{{ form.errors.code }}</p>
            </div>

            <div v-else>
                <label class="block text-sm font-medium text-slate-700 mb-1">Código de recuperação</label>
                <input
                    v-model="form.recovery_code"
                    type="text"
                    autocomplete="one-time-code"
                    placeholder="xxxx-xxxx-xxxx"
                    :class="['w-full rounded-lg border px-3 py-2.5 text-sm font-mono transition focus:outline-none focus:ring-2 focus:ring-blue-500',
                             form.errors.recovery_code ? 'border-red-400 bg-red-50' : 'border-slate-300 bg-white']"
                />
                <p v-if="form.errors.recovery_code" class="mt-1 text-xs text-red-600">{{ form.errors.recovery_code }}</p>
            </div>

            <button
                type="submit"
                :disabled="form.processing"
                class="w-full rounded-lg bg-blue-600 hover:bg-blue-700 disabled:opacity-60 px-4 py-2.5 text-sm font-semibold text-white transition"
            >
                <span v-if="form.processing">Verificando...</span>
                <span v-else>Verificar</span>
            </button>
        </form>

        <button
            type="button"
            class="mt-4 w-full text-center text-sm text-blue-600 hover:underline"
            @click="toggleRecovery"
        >
            <template v-if="!recovery">Usar código de recuperação</template>
            <template v-else>Usar código do aplicativo</template>
        </button>
    </GuestLayout>
</template>
