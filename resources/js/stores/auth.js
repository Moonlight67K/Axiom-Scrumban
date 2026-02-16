import { defineStore } from 'pinia';
import api from '../api/axios';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
        token: localStorage.getItem('token') || null,
        organization: null,
        errors: null,
    }),

    actions: {
        setAuth(token, user) {
            this.token = token;
            this.user = user;
            localStorage.setItem('token', token);
            api.defaults.headers.common['Authorization'] = `Bearer ${token}`;
        },

        clearAuth() {
            this.user = null;
            this.token = null;
            this.organization = null;
            this.errors = null;
            localStorage.removeItem('token');
            delete api.defaults.headers.common['Authorization'];
        },

        async login(payload) {
            this.errors = null;
            try {
                const res = await api.post('/api/login', payload);
                const { token, user, organization } = res.data;
                this.setAuth(token, user);
                this.organization = organization || null;
                if (this.organization) {
                    localStorage.setItem('organization_id', this.organization.id);
                    api.defaults.headers.common['X-Organization-ID'] = this.organization.id;
                }
                return { success: true };
            } catch (err) {
                this.errors = err.response?.data?.errors || { message: err.response?.data?.message };
                return { success: false, errors: this.errors };
            }
        },

        async register(payload) {
            this.errors = null;
            try {
                const res = await api.post('/api/register', payload);
                const { token, user, organization } = res.data;
                this.setAuth(token, user);
                this.organization = organization || null;
                if (this.organization) {
                    localStorage.setItem('organization_id', this.organization.id);
                    api.defaults.headers.common['X-Organization-ID'] = this.organization.id;
                }
                return { success: true };
            } catch (err) {
                this.errors = err.response?.data?.errors || { message: err.response?.data?.message };
                return { success: false, errors: this.errors };
            }
        },

        async fetchUser() {
            if (!this.token) return null;
            try {
                api.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
                const res = await api.get('/api/user');
                this.user = res.data.user || res.data;
                this.organization = res.data.organization || this.organization;
                if (this.organization) {
                    localStorage.setItem('organization_id', this.organization.id);
                    api.defaults.headers.common['X-Organization-ID'] = this.organization.id;
                }
                return this.user;
            } catch (err) {
                this.clearAuth();
                return null;
            }
        },

        logout() {
            this.clearAuth();
        },
    },
});
