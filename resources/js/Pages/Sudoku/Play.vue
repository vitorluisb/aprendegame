<script setup>
import SudokuBoard from '@/Components/Sudoku/SudokuBoard.vue';
import SudokuModeHero from '@/Components/Sudoku/SudokuModeHero.vue';
import SudokuNumberPad from '@/Components/Sudoku/SudokuNumberPad.vue';
import SudokuStatsBar from '@/Components/Sudoku/SudokuStatsBar.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import axios from 'axios';
import { Head, router } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

const props = defineProps({
    session: { type: Object, required: true },
    puzzle: { type: Object, required: true },
});

function cloneMatrix(matrix) {
    return (matrix ?? []).map((row) => [...row]);
}

const currentSession = ref({ ...props.session });
const board = ref(cloneMatrix(props.puzzle.filled_cells));
const fixedPositions = ref(cloneMatrix(props.puzzle.fixed_positions));
const selectedCell = ref(null);
const wrongCell = ref(null);
const loading = ref(false);
const localElapsed = ref(currentSession.value.elapsed_seconds ?? 0);
let elapsedTimer = null;

const elapsedLabel = computed(() => {
    const total = localElapsed.value;
    const minutes = Math.floor(total / 60).toString().padStart(2, '0');
    const seconds = (total % 60).toString().padStart(2, '0');

    return `${minutes}:${seconds}`;
});

function selectCell(cell) {
    if (fixedPositions.value[cell.row]?.[cell.col]) {
        selectedCell.value = null;

        return;
    }

    selectedCell.value = cell;
}

async function sendMove(value = null) {
    if (!selectedCell.value || loading.value) {
        return;
    }

    loading.value = true;

    try {
        const response = await axios.post(`/jogos/sudoku/sessoes/${currentSession.value.id}/movimentos`, {
            row_index: selectedCell.value.row,
            col_index: selectedCell.value.col,
            value,
        }, {
            headers: { Accept: 'application/json' },
        });

        const payload = response.data.result;

        board.value = payload.puzzle.filled_cells;
        currentSession.value = payload.session;
        localElapsed.value = payload.session.elapsed_seconds;

        if (!payload.correct) {
            wrongCell.value = { ...selectedCell.value };
            setTimeout(() => {
                wrongCell.value = null;
            }, 600);
        }

        if (payload.completed && response.data.redirect) {
            setTimeout(() => {
                window.location.href = response.data.redirect;
            }, 450);
        }
    } catch (error) {
        const message = error?.response?.data?.message ?? 'Não foi possível registrar a jogada.';
        window.alert(message);
    } finally {
        loading.value = false;
    }
}

function handleNumber(number) {
    void sendMove(number);
}

function handleClear() {
    void sendMove(null);
}

function exitSudoku() {
    router.visit('/jogos/sudoku');
}

onMounted(() => {
    elapsedTimer = window.setInterval(() => {
        localElapsed.value += 1;
    }, 1000);
});

onBeforeUnmount(() => {
    if (elapsedTimer) {
        window.clearInterval(elapsedTimer);
    }
});
</script>

<template>
    <Head title="Sudoku • Jogar" />
    <AppLayout title="Sudoku">
        <section class="space-y-3">
            <SudokuModeHero
                title="Partida em andamento"
                subtitle="Preencha o tabuleiro completo sem perder o ritmo."
            />

            <div class="flex items-center justify-between gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-xs text-slate-600 shadow-sm">
                <p>Selecione uma célula vazia e toque em um número.</p>
                <button type="button" class="rounded-lg border border-slate-300 px-3 py-1 font-semibold text-slate-600" @click="exitSudoku">Sair</button>
            </div>

            <SudokuStatsBar
                :difficulty="currentSession.difficulty"
                :elapsed-label="elapsedLabel"
                :mistakes="currentSession.mistakes_count"
                :reward-xp="currentSession.reward_xp"
                :reward-gems="currentSession.reward_gems"
            />

            <SudokuBoard
                class="-mx-1 sm:mx-0"
                :board="board"
                :fixed-positions="fixedPositions"
                :selected-cell="selectedCell"
                :wrong-cell="wrongCell"
                @select-cell="selectCell"
            />

            <SudokuNumberPad :disabled="loading || !selectedCell" @select-number="handleNumber" @clear-cell="handleClear" />
        </section>
    </AppLayout>
</template>
