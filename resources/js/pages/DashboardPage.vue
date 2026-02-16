<template>
    <div class="dashboard">
        <h1>My Boards</h1>
        <div class="board-grid">
            <div v-for="board in boards" :key="board.id" class="board-card" @click="openBoard(board.id)">
                <h3>{{ board.name }}</h3>
                <p>{{ board.description || 'No description' }}</p>
                <div class="meta">
                    <span class="badg">{{ board.columns_count || 0 }} Columns</span>
                </div>
            </div>
            <!-- Create Board Placeholder -->
            <div class="board-card create-card" @click="createBoard">
                <h3>+ New Board</h3>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../api/axios';
import { useRouter } from 'vue-router';

const boards = ref([]);
const router = useRouter();

onMounted(async () => {
    try {
        const response = await api.get('/boards');
        boards.value = response.data;
    } catch (e) {
        console.error('Failed to fetch boards', e);
    }
});

const openBoard = (id) => {
    router.push({ name: 'Board', params: { id } });
};

const createBoard = async () => {
    // Placeholder for create logic
    const name = prompt('Enter board name:');
    if (name) {
        try {
            const orgId = '...'; // Needs context, for now simple post
            // In a real flow we need the specific org context
            // Assuming header/interceptor handles it or we prompt/select org
            await api.post('/boards', { name });
            // Refresh
            const response = await api.get('/boards');
            boards.value = response.data;
        } catch (e) {
            alert('Failed to create board');
        }
    }
};
</script>

<style scoped>
.dashboard {
    padding-top: 1rem;
}
.board-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 1.5rem;
    padding: 1rem 0;
}
.board-card {
    border: 1px solid #e5e7eb;
    padding: 1.5rem;
    cursor: pointer;
    border-radius: 8px;
    background: white;
    transition: transform 0.2s, box-shadow 0.2s;
}
.board-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}
.create-card {
    border: 2px dashed #9ca3af;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6b7280;
}
.create-card:hover {
    border-color: #4f46e5;
    color: #4f46e5;
}
</style>
