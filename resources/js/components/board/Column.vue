<template>
    <div class="column">
        <div class="column-header">
            <h3>{{ column.name }}</h3>
            <span class="wip-limit">Limit: {{ column.wip_limit }}</span>
        </div>
        <div class="task-list">
            <Card 
                v-for="task in tasks" 
                :key="task.id" 
                :task="task" 
            />
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { useBoardStore } from '../../stores/board';
import Card from './Card.vue';

const props = defineProps(['column']);
const boardStore = useBoardStore();

const tasks = computed(() => boardStore.getTasksByColumn(props.column.id));
</script>

<style scoped>
.column {
    background: #f3f4f6;
    min-width: 300px;
    border-radius: 8px;
    display: flex;
    flex-direction: column;
    max-height: 100%;
}
.column-header {
    padding: 1rem;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    font-weight: bold;
}
.task-list {
    flex: 1;
    overflow-y: auto;
    padding: 0.5rem;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}
</style>
