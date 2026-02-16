import { defineStore } from 'pinia';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

export const useWebSocketStore = defineStore('websocket', {
    state: () => ({
        echo: null,
        isConnected: false,
    }),

    actions: {
        init() {
            if (this.echo) return;

            window.Pusher = Pusher;

            this.echo = new Echo({
                broadcaster: 'pusher',
                key: import.meta.env.VITE_PUSHER_APP_KEY,
                cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
                forceTLS: true
            });

            this.echo.connector.pusher.connection.bind('connected', () => {
                this.isConnected = true;
                console.log('WebSocket Connected');
            });

            this.echo.connector.pusher.connection.bind('disconnected', () => {
                this.isConnected = false;
                console.log('WebSocket Disconnected');
            });
        },

        subscribeToBoard(boardId) {
            if (!this.echo) this.init();

            return this.echo.private(`boards.${boardId}`);
        },

        leaveBoard(boardId) {
            if (this.echo) {
                this.echo.leave(`boards.${boardId}`);
            }
        }
    }
});
