<template>
    <div class="app-layout">
        <header>
            <div class="logo">Axiom Scrumban</div>
            <nav>
                <router-link :to="{ name: 'Dashboard' }" active-class="active">Dashboard</router-link>
                <!-- For now, Board link might need an ID, or we just link to a default/last one. 
                     Since we don't have a specific board ID context globally yet, 
                     we might omit a direct 'Board' link in the top nav unless it's 'Boards' (Dashboard). 
                     But per requirements, adding 'Board' placeholder. -->
                <!-- <router-link :to="{ name: 'Board' }" active-class="active">Board</router-link> -->
                <router-link :to="{ name: 'Analytics' }" active-class="active">Analytics</router-link>
                <button @click="logout" class="logout-btn">Logout</button>
            </nav>
        </header>
        <main>
            <router-view></router-view>
        </main>
    </div>
</template>

<script setup>
import { useAuthStore } from '../stores/auth';
import { useRouter } from 'vue-router';

const authStore = useAuthStore();
const router = useRouter();

const logout = async () => {
    await authStore.logout();
    router.push({ name: 'Login' });
};
</script>

<style scoped>
.app-layout {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}
header {
    background: #fff;
    padding: 1rem 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #e5e7eb;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}
.logo {
    font-weight: 700;
    font-size: 1.25rem;
    color: #4f46e5;
}
nav {
    display: flex;
    gap: 1.5rem;
    align-items: center;
}
nav a {
    text-decoration: none;
    color: #4b5563;
    font-weight: 500;
}
nav a.active {
    color: #4f46e5;
}
nav a:hover {
    color: #1f2937;
}
.logout-btn {
    padding: 0.5rem 1rem;
    background-color: #ef4444;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-weight: 500;
}
.logout-btn:hover {
    background-color: #dc2626;
}
main {
    flex: 1;
    padding: 2rem;
    max-width: 1200px;
    margin: 0 auto;
    width: 100%;
}
</style>
