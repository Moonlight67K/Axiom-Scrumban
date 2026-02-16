import { defineStore } from 'pinia';
import api from '../api/axios';

export const useBoardStore = defineStore('board', {
    state: () => ({
        currentBoard: null,
        columns: [],
        tasks: [], // Flat list of tasks? Or nested in columns? 
        // Normalized state is better for optimistic updates
    }),

    getters: {
        getTasksByColumn: (state) => (columnId) => {
            return state.tasks
                .filter(task => task.column_id === columnId)
                .sort((a, b) => a.position - b.position);
        }
    },

    actions: {
        async fetchBoard(boardId) {
            const response = await api.get(`/boards/${boardId}`);
            this.currentBoard = response.data;
            this.columns = response.data.columns || [];
            // Extract tasks from columns if nested, or fetch separately
            // For now assuming tasks come nested in columns
            this.tasks = this.columns.flatMap(col => col.tasks || []);
        },

        async moveTask(taskId, targetColumnId, newPosition) {
            // 1. Find task
            const taskIndex = this.tasks.findIndex(t => t.id === taskId);
            if (taskIndex === -1) return;
            const task = this.tasks[taskIndex];

            // 2. Snapshot for revert
            const originalState = { ...task };

            // 3. Optimistic Update
            task.column_id = targetColumnId;
            task.position = newPosition;

            // 4. API Call
            try {
                await api.put(`/tasks/${taskId}`, {
                    column_id: targetColumnId,
                    position: newPosition
                });
            } catch (error) {
                // 5. Revert on failure
                console.error('Move failed, reverting...', error);
                this.tasks[taskIndex] = originalState;
                // Trigger notification?
            }
        },

        handleWebSocketTaskMoved(event) {
            // Update state from external event
            const taskIndex = this.tasks.findIndex(t => t.id === event.task.id);
            if (taskIndex !== -1) {
                this.tasks[taskIndex] = event.task;
            }
        }
    }
});
