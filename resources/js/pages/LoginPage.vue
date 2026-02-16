<template>
    <div class="login-container">
        <h1>Axiom Scrumban</h1>
        <form @submit.prevent="handleLogin">
            <input v-model="email" @input="onInput" type="email" placeholder="Email" required />
            <input v-model="password" @input="onInput" type="password" placeholder="Password" required />
            <button type="submit">Login</button>
        </form>
        <p v-if="error" class="error">
            {{ error }}
            <button class="dismiss" @click="dismiss">×</button>
        </p>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useAuthStore } from '../stores/auth';
import { useRouter } from 'vue-router';

const email = ref('');
const password = ref('');
const error = ref('');
const authStore = useAuthStore();
const router = useRouter();

const handleLogin = async () => {
    const res = await authStore.login({ email: email.value, password: password.value });
    if (res.success) {
        // persist organization id for api header usage
        if (authStore.organization) {
            localStorage.setItem('organization_id', authStore.organization.id);
        }
        router.push({ name: 'Dashboard' });
    } else {
        error.value = res.errors?.message || Object.values(res.errors || {}).flat().join('\n') || 'Invalid credentials';
    }
};

const onInput = () => {
    // Do not clear error immediately on any small change; only clear when user starts typing to correct
    // We'll keep the message until user types, so this clears the error on first input action
    if (error.value) error.value = '';
};

const dismiss = () => {
    error.value = '';
};
</script>

<style scoped>
.login-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100vh;
}
</style>
