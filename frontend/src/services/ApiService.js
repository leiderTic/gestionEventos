import axios from "axios";

const url_base = import.meta.env.VITE_API_URL || 'http://localhost:8081/api';
export function Api() {
    let token = localStorage.getItem('access_token');
    const api = axios.create({
        baseURL: url_base,
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': `Bearer ${token}`
        }
    });
    //interceptor(errores 401, 403)
    api.interceptors.response.use(
        (response) => {
            return response;
        },
        (error) => {
            if (error.response && error.response.status == 401) {
                localStorage.removeItem("access_token");
                //router.push('/login');
                window.location.href = '/login';
            }
            return Promise.reject(error);
        }
    );

    return api;
}
