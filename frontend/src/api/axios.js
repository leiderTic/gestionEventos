import axios from 'axios';

const defaultUrl = typeof window !== 'undefined' && window.location ? `http://${window.location.hostname}:8081/api` : 'http://localhost:8081/api';

const api = axios.create({
    baseURL: import.meta.env.VITE_API_URL || defaultUrl,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    }
});

export default api;
