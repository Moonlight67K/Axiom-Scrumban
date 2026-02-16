<template>
    <div class="register-container">
        <h1>Create Organization</h1>
        <form @submit.prevent="handleRegister">
            <input v-model="organization_name" type="text" placeholder="Organization name" required />
            <input v-model="name" type="text" placeholder="Your name" required />
            <input v-model="email" type="email" placeholder="Email" required />
            <input v-model="password" type="password" placeholder="Password" required />
            <input v-model="password_confirmation" type="password" placeholder="Confirm password" required />
            <button type="submit">Register</button>
        </form>
        <p v-if="error" class="error">{{ error }}</p>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useAuthStore } from '../stores/auth';
import { useRouter } from 'vue-router';

const organization_name = ref('');
const name = ref('');
const email = ref('');
const password = ref('');
const password_confirmation = ref('');
const error = ref('');

const authStore = useAuthStore();
const router = useRouter();

const handleRegister = async () => {
    error.value = '';
    const res = await authStore.register({
        organization_name: organization_name.value,
        name: name.value,
        email: email.value,
        password: password.value,
        password_confirmation: password_confirmation.value,
    });

    if (res.success) {
        if (authStore.organization) {
            localStorage.setItem('organization_id', authStore.organization.id);
        }
        router.push({ name: 'Dashboard' });
    } else {
        error.value = res.errors?.message || Object.values(res.errors || {}).flat().join('\n') || 'Registration failed';
    }
};
</script>

<style scoped>
.register-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100vh;
}
</style>
