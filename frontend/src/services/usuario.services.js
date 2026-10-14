import { Api } from './ApiService';

export default {
    listar: (params = {}) => {
        return Api().get('users', { params });
    },
    mostrar: (id) => {
        return Api().get(`users/${id}`);
    },
    crear: (datos) => {
        return Api().post('users', datos);
    },
    actualizar: (id, datos) => {
        return Api().put(`users/${id}`, datos);
    },
    toggleActivo: (id) => {
        return Api().patch(`users/${id}/toggle-activo`);
    },
    eliminar: (id) => {
        return Api().delete(`users/${id}`);
    },
    roles: () => {
        return Api().get('roles');
    }
};
