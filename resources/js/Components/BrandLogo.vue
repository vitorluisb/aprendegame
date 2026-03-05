<script setup>
const props = defineProps({
    compact: { type: Boolean, default: false },
    light: { type: Boolean, default: false },
    showTagline: { type: Boolean, default: true },
    iconOnly: { type: Boolean, default: false },
});
</script>

<template>
    <div class="inline-flex items-center gap-3" :class="props.compact ? 'gap-2' : 'gap-3'">
        <svg
            :class="props.compact ? 'h-9 w-9' : 'h-12 w-12'"
            viewBox="0 0 100 100"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
            aria-hidden="true"
        >
            <defs>
                <linearGradient id="brandLogoG1" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#FF6B6B" />
                    <stop offset="100%" stop-color="#FFB347" />
                </linearGradient>
                <linearGradient id="brandLogoG2" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#FFD93D" />
                    <stop offset="100%" stop-color="#FF6B6B" />
                </linearGradient>
            </defs>

            <path
                d="M 10 38 Q 10 22 28 22 L 72 22 Q 90 22 90 38 L 90 58 Q 90 66 82 70 L 68 78 Q 60 82 52 82 L 48 82 Q 40 82 32 78 L 18 70 Q 10 66 10 58 Z"
                fill="none"
                stroke="url(#brandLogoG1)"
                stroke-width="3.5"
                stroke-linejoin="round"
            />
            <rect x="18" y="41" width="24" height="8" rx="3.5" fill="url(#brandLogoG1)" />
            <rect x="26" y="33" width="8" height="24" rx="3.5" fill="url(#brandLogoG1)" />
            <circle cx="70" cy="36" r="5" fill="url(#brandLogoG2)" />
            <circle cx="80" cy="46" r="5" fill="url(#brandLogoG1)" />
            <circle cx="70" cy="56" r="5" fill="url(#brandLogoG1)" opacity="0.6" />
            <rect
                x="45"
                y="44"
                width="10"
                height="6"
                rx="3"
                :fill="props.light ? 'rgba(255,255,255,0.28)' : 'rgba(0,0,0,0.16)'"
            />
            <polygon
                points="50,6 52.8,14.4 61.8,14.4 54.9,19.6 57.7,28 50,22.8 42.3,28 45.1,19.6 38.2,14.4 47.2,14.4"
                fill="url(#brandLogoG2)"
            />
        </svg>

        <div v-if="!props.iconOnly" class="leading-none">
            <div :class="props.compact ? 'text-xl' : 'text-3xl'" class="font-black tracking-tight">
                <span :class="props.light ? 'text-white' : 'text-[var(--color-game-deep)]'">Aprende</span><span class="bg-gradient-to-br from-[var(--color-game-accent)] to-[var(--color-game-accent-2)] bg-clip-text text-transparent">Game</span>
            </div>
            <p
                v-if="props.showTagline"
                :class="props.compact ? 'mt-0.5 text-[9px] tracking-[0.2em]' : 'mt-1 text-[10px] tracking-[0.22em]'"
                class="font-semibold uppercase"
                :style="{ color: props.light ? 'rgba(126, 200, 200, 0.95)' : '#3A8A8A' }"
            >
                Aprenda · Evolua · Vença
            </p>
        </div>
    </div>
</template>
