import { ref } from 'vue';

const STORAGE_KEY = 'aprendegame:sfx-enabled';
const STORAGE_VOLUME_KEY = 'aprendegame:sfx-volume';
const STORAGE_PACK_KEY = 'aprendegame:sfx-pack';

const isEnabled = ref(typeof window !== 'undefined' ? localStorage.getItem(STORAGE_KEY) !== 'off' : true);
const volume = ref(typeof window !== 'undefined' ? Number(localStorage.getItem(STORAGE_VOLUME_KEY) ?? 70) : 70);
const pack = ref(typeof window !== 'undefined' ? localStorage.getItem(STORAGE_PACK_KEY) ?? 'arcade' : 'arcade');
const prefersReducedMotion = ref(false);

if (typeof window !== 'undefined' && window.matchMedia) {
    const mediaQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
    prefersReducedMotion.value = mediaQuery.matches;

    const updatePreference = (event) => {
        prefersReducedMotion.value = event.matches;
    };

    if (mediaQuery.addEventListener) {
        mediaQuery.addEventListener('change', updatePreference);
    } else if (mediaQuery.addListener) {
        mediaQuery.addListener(updatePreference);
    }
}

let audioContext = null;

function context() {
    if (typeof window === 'undefined') {
        return null;
    }

    if (!audioContext) {
        const Ctx = window.AudioContext || window.webkitAudioContext;
        audioContext = Ctx ? new Ctx() : null;
    }

    return audioContext;
}

function tone(frequency, durationMs, type = 'sine', gainValue = 0.04) {
    if (!isEnabled.value || prefersReducedMotion.value) {
        return;
    }

    const ctx = context();

    if (!ctx) {
        return;
    }

    if (ctx.state === 'suspended') {
        ctx.resume().catch(() => {});
    }

    const now = ctx.currentTime;
    const osc = ctx.createOscillator();
    const gain = ctx.createGain();

    osc.type = type;
    osc.frequency.setValueAtTime(frequency, now);

    gain.gain.setValueAtTime(0.0001, now);
    gain.gain.exponentialRampToValueAtTime(gainValue * (Math.max(0, Math.min(100, volume.value)) / 100), now + 0.01);
    gain.gain.exponentialRampToValueAtTime(0.0001, now + durationMs / 1000);

    osc.connect(gain);
    gain.connect(ctx.destination);

    osc.start(now);
    osc.stop(now + durationMs / 1000 + 0.02);
}

function sequence(steps) {
    steps.forEach((step, index) => {
        const delay = steps.slice(0, index).reduce((carry, item) => carry + item.duration + (item.gap ?? 0), 0);
        setTimeout(() => tone(step.frequency, step.duration, step.type, step.gain), delay);
    });
}

function profile() {
    const sets = {
        arcade: {
            pop: [{ frequency: 520, duration: 60, type: 'triangle', gain: 0.03 }],
            success: [
                { frequency: 520, duration: 70, type: 'triangle', gain: 0.035, gap: 25 },
                { frequency: 700, duration: 100, type: 'triangle', gain: 0.035 },
            ],
            error: [{ frequency: 240, duration: 130, type: 'sawtooth', gain: 0.03 }],
            combo: [
                { frequency: 560, duration: 60, type: 'triangle', gain: 0.03, gap: 15 },
                { frequency: 680, duration: 70, type: 'triangle', gain: 0.033, gap: 15 },
                { frequency: 820, duration: 90, type: 'triangle', gain: 0.036 },
            ],
            purchase: [
                { frequency: 420, duration: 55, type: 'square', gain: 0.028, gap: 10 },
                { frequency: 530, duration: 55, type: 'square', gain: 0.028, gap: 10 },
                { frequency: 650, duration: 90, type: 'triangle', gain: 0.032 },
            ],
            victory: [
                { frequency: 520, duration: 60, type: 'triangle', gain: 0.03, gap: 10 },
                { frequency: 660, duration: 70, type: 'triangle', gain: 0.032, gap: 10 },
                { frequency: 820, duration: 80, type: 'triangle', gain: 0.034, gap: 10 },
                { frequency: 990, duration: 120, type: 'triangle', gain: 0.036 },
            ],
        },
        neon: {
            pop: [{ frequency: 610, duration: 40, type: 'sine', gain: 0.026 }],
            success: [
                { frequency: 730, duration: 65, type: 'sine', gain: 0.03, gap: 12 },
                { frequency: 910, duration: 100, type: 'sine', gain: 0.031 },
            ],
            error: [{ frequency: 180, duration: 120, type: 'triangle', gain: 0.026 }],
            combo: [
                { frequency: 650, duration: 45, type: 'sine', gain: 0.028, gap: 10 },
                { frequency: 760, duration: 45, type: 'sine', gain: 0.028, gap: 10 },
                { frequency: 920, duration: 85, type: 'sine', gain: 0.03 },
            ],
            purchase: [
                { frequency: 500, duration: 45, type: 'triangle', gain: 0.026, gap: 10 },
                { frequency: 600, duration: 45, type: 'triangle', gain: 0.026, gap: 10 },
                { frequency: 770, duration: 90, type: 'sine', gain: 0.03 },
            ],
            victory: [
                { frequency: 620, duration: 50, type: 'sine', gain: 0.028, gap: 10 },
                { frequency: 760, duration: 60, type: 'sine', gain: 0.03, gap: 10 },
                { frequency: 910, duration: 70, type: 'sine', gain: 0.03, gap: 10 },
                { frequency: 1080, duration: 110, type: 'sine', gain: 0.032 },
            ],
        },
        chiptune: {
            pop: [{ frequency: 480, duration: 55, type: 'square', gain: 0.025 }],
            success: [
                { frequency: 510, duration: 45, type: 'square', gain: 0.028, gap: 10 },
                { frequency: 640, duration: 60, type: 'square', gain: 0.03 },
            ],
            error: [{ frequency: 210, duration: 120, type: 'square', gain: 0.025 }],
            combo: [
                { frequency: 520, duration: 40, type: 'square', gain: 0.027, gap: 8 },
                { frequency: 650, duration: 45, type: 'square', gain: 0.029, gap: 8 },
                { frequency: 780, duration: 70, type: 'square', gain: 0.03 },
            ],
            purchase: [
                { frequency: 430, duration: 40, type: 'square', gain: 0.026, gap: 8 },
                { frequency: 520, duration: 45, type: 'square', gain: 0.027, gap: 8 },
                { frequency: 690, duration: 80, type: 'square', gain: 0.03 },
            ],
            victory: [
                { frequency: 520, duration: 45, type: 'square', gain: 0.027, gap: 8 },
                { frequency: 650, duration: 50, type: 'square', gain: 0.028, gap: 8 },
                { frequency: 780, duration: 55, type: 'square', gain: 0.03, gap: 8 },
                { frequency: 930, duration: 100, type: 'square', gain: 0.032 },
            ],
        },
    };

    return sets[pack.value] ?? sets.arcade;
}

function pop() {
    sequence(profile().pop);
}

function success() {
    sequence(profile().success);
}

function error() {
    sequence(profile().error);
}

function combo() {
    sequence(profile().combo);
}

function purchase() {
    sequence(profile().purchase);
}

function victory() {
    sequence(profile().victory);
}

function toggle() {
    isEnabled.value = !isEnabled.value;
    localStorage.setItem(STORAGE_KEY, isEnabled.value ? 'on' : 'off');

    if (isEnabled.value) {
        pop();
    }
}

function setVolume(nextValue) {
    volume.value = Math.max(0, Math.min(100, Number(nextValue) || 0));
    localStorage.setItem(STORAGE_VOLUME_KEY, String(volume.value));
}

function setPack(nextPack) {
    pack.value = ['arcade', 'neon', 'chiptune'].includes(nextPack) ? nextPack : 'arcade';
    localStorage.setItem(STORAGE_PACK_KEY, pack.value);
    pop();
}

export function useUiSfx() {
    return {
        isEnabled,
        volume,
        pack,
        prefersReducedMotion,
        pop,
        success,
        error,
        combo,
        purchase,
        victory,
        toggle,
        setVolume,
        setPack,
    };
}
