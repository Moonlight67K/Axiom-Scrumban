import { defineStore } from 'pinia';
import api from '../api/axios';

export const useSprintStore = defineStore('sprint', {
    state: () => ({
        activeSprint: null,
        sprints: []
    }),

    actions: {
        async fetchSprints(boardId) {
            const response = await api.get(`/boards/${boardId}/sprints`);
            this.sprints = response.data;
            this.activeSprint = this.sprints.find(s => s.status === 'active') || null;
        }
    }
});
