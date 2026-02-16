<template>
    <div class="board-view">
        <Column 
            v-for="column in boardStore.columns" 
            :key="column.id" 
            :column="column" 
        />
    </div>
</template>

<script setup>
import { useBoardStore } from '../../stores/board';
import Column from './Column.vue';

const boardStore = useBoardStore();
</script>

<style scoped>
.board-view {
    display: flex;
    gap: 1rem;
    overflow-x: auto;
    height: calc(100vh - 200px);
    padding-bottom: 1rem;
}
</style>
