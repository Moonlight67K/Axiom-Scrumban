import axios from 'axios';

const api = axios.create({
    baseURL: '/api',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
    withCredentials: true, // For Sanctum cookie-based auth
});

// Attach token from localStorage if present
const token = localStorage.getItem('token');
if (token) {
    api.defaults.headers.common['Authorization'] = `Bearer ${token}`;
}

// Attach organization header if present
const orgId = localStorage.getItem('organization_id');
if (orgId) {
    api.defaults.headers.common['X-Organization-ID'] = orgId;
}

api.interceptors.response.use(
    response => response,
    error => {
        if (error.response?.status === 401) {
            // Let app handle auth; simple redirect for now
            window.location.href = '/login';
        }
        return Promise.reject(error);
    }
);

export default api;
