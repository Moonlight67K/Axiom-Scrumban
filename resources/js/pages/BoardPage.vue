<template>
    <div class="board-page">
        <div v-if="loading">Loading Board...</div>
        <div v-else-if="error">{{ error }}</div>
        <div v-else>
            <h1>{{ boardStore.currentBoard?.name }}</h1>
            <BoardView />
        </div>
    </div>
</template>

<script setup>
import { onMounted, computed, ref } from 'vue';
import { useBoardStore } from '../stores/board';
import { useWebSocketStore } from '../stores/websocket';
import BoardView from '../components/board/BoardView.vue';

const props = defineProps(['id']);
const boardStore = useBoardStore();
const wsStore = useWebSocketStore();
const loading = ref(true);
const error = ref(null);

onMounted(async () => {
    try {
        await boardStore.fetchBoard(props.id);
        
        // Initialize WebSocket
        const channel = wsStore.subscribeToBoard(props.id);
        
        channel.listen('.TaskMoved', (e) => {
            boardStore.handleWebSocketTaskMoved(e);
        });
        
    } catch (e) {
        error.value = 'Failed to load board';
    } finally {
        loading.value = false;
    }
});
</script>
