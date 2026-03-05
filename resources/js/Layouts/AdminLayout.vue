<script setup>
defineProps({ title: { type: String, required: true } });
</script>

<template>
    <div class="min-h-screen bg-white">
        <header class="bg-slate-900 px-5 py-4 text-white">
            <h1 class="text-lg font-semibold">{{ title }}</h1>
        </header>

        <main class="mx-auto max-w-6xl px-5 py-6">
            <slot />
        </main>
    </div>
</template>
