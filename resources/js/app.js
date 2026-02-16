import './bootstrap';
import { createApp } from 'vue';
import { createPinia } from 'pinia';
import router from './router';
import App from './App.vue';

const app = createApp(App);

app.use(createPinia());
app.use(router);

// Initialize auth from token if present
import { useAuthStore } from './stores/auth';

const pinia = createPinia();
app.use(pinia);

const authStore = useAuthStore(pinia);
if (authStore.token) {
	// attempt to load user; if fails, token will be cleared
	authStore.fetchUser();
}

app.mount('#app');
