<script setup>
import SudokuCell from '@/Components/Sudoku/SudokuCell.vue';

const props = defineProps({
    board: { type: Array, required: true },
    fixedPositions: { type: Array, required: true },
    selectedCell: { type: Object, default: null },
    wrongCell: { type: Object, default: null },
});

const emit = defineEmits(['select-cell']);

function isHighlighted(row, col) {
    if (!props.selectedCell) {
        return false;
    }

    const sameRow = props.selectedCell.row === row;
    const sameCol = props.selectedCell.col === col;
    const sameBlock = Math.floor(props.selectedCell.row / 3) === Math.floor(row / 3)
        && Math.floor(props.selectedCell.col / 3) === Math.floor(col / 3);

    return sameRow || sameCol || sameBlock;
}

function isWrongCell(row, col) {
    return props.wrongCell?.row === row && props.wrongCell?.col === col;
}

function borderClass(row, col) {
    const thickRight = col === 2 || col === 5;
    const thickBottom = row === 2 || row === 5;

    return {
        'border-r-2 border-r-slate-500': thickRight,
        'border-b-2 border-b-slate-500': thickBottom,
        'border-r border-r-slate-300': !thickRight,
        'border-b border-b-slate-300': !thickBottom,
        'border-l border-l-slate-300': col === 0,
        'border-t border-t-slate-300': row === 0,
    };
}
</script>

<template>
    <section class="rounded-2xl border border-slate-200 bg-white p-1.5 shadow-sm sm:p-3">
        <div class="grid grid-cols-9 overflow-hidden rounded-xl border-2 border-slate-500">
            <template v-for="(rowValues, rowIndex) in board" :key="`row-${rowIndex}`">
                <SudokuCell
                    v-for="(value, colIndex) in rowValues"
                    :key="`cell-${rowIndex}-${colIndex}`"
                    :value="value"
                    :fixed="fixedPositions[rowIndex]?.[colIndex]"
                    :selected="selectedCell?.row === rowIndex && selectedCell?.col === colIndex"
                    :highlighted="isHighlighted(rowIndex, colIndex)"
                    :wrong="isWrongCell(rowIndex, colIndex)"
                    :class="borderClass(rowIndex, colIndex)"
                    @click="emit('select-cell', { row: rowIndex, col: colIndex })"
                />
            </template>
        </div>
    </section>
</template>
