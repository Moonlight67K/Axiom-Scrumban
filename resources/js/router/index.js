import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';

// Layouts
import PublicLayout from '../layouts/PublicLayout.vue';
import AppLayout from '../layouts/AppLayout.vue';

// Pages
import LoginPage from '../pages/LoginPage.vue';
import RegisterPage from '../pages/RegisterPage.vue';
import DashboardPage from '../pages/DashboardPage.vue';
import BoardPage from '../pages/BoardPage.vue';
import AnalyticsPage from '../pages/AnalyticsPage.vue';

const routes = [
    {
        path: '/login',
        component: PublicLayout,
        children: [
            {
                path: '',
                name: 'Login',
                component: LoginPage,
                meta: { guest: true }
            }
            ,{
                path: 'register',
                name: 'Register',
                component: RegisterPage,
                meta: { guest: true }
            }
        ]
    },
    {
        path: '/',
        component: AppLayout,
        meta: { requiresAuth: true },
        children: [
            {
                path: 'dashboard',
                alias: '', // Map root to dashboard
                name: 'Dashboard',
                component: DashboardPage,
            },
            {
                path: 'board/:id?', // Optional ID for direct access or default
                name: 'Board',
                component: BoardPage,
                props: true
            },
            {
                path: 'analytics',
                name: 'Analytics',
                component: AnalyticsPage,
            }
        ]
    },
    // Fallback for 404 (optional, redirects to dashboard for now)
    {
        path: '/:pathMatch(.*)*',
        redirect: { name: 'Dashboard' }
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach(async (to, from, next) => {
    const authStore = useAuthStore();

    // Check authentication status
    // In a real app, might validate token with backend here or relying on axios interceptor
    const isAuthenticated = !!authStore.token;

    if (to.meta.requiresAuth && !isAuthenticated) {
        next({ name: 'Login' });
    } else if (to.meta.guest && isAuthenticated) {
        next({ name: 'Dashboard' });
    } else {
        next();
    }
});

export default router;
