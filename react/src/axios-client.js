import axios from "axios";

const axiosClient = axios.create({
    baseURL: `${import.meta.env.VITE_USER_API_BASE_URL}/api`
});

// Перехватчик запросов — добавляем токен в заголовки
axiosClient.interceptors.request.use((config) => {
    const token = localStorage.getItem('ACCESS_TOKEN');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

// Перехватчик ответов — обрабатываем 401
axiosClient.interceptors.response.use(
    (response) => {
        // Если сервер вернул новый токен (например, в заголовке), обновим его
        const newToken = response.headers['x-renewed-token'];
        if (newToken) {
            localStorage.setItem('ACCESS_TOKEN', newToken);
        }
        return response;
    },
    (error) => {
        const { response } = error;
        if (response?.status === 401) {
            console.warn("Токен недействителен или истёк — редирект на вход");
            localStorage.removeItem('ACCESS_TOKEN');
            localStorage.removeItem('USER_DATA');
            if (window.location.pathname !== '/login') {
                window.location.href = '/login';
            }
        }
        return Promise.reject(error);
    }
);

export default axiosClient;
