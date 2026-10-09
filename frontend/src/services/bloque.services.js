import { Api } from "./ApiService";

export default {
    listar: () => {
        return Api().get('bloques');
    },
    guardar: (bloque) => {
        return Api().post('bloques', bloque);
    },
    mostrar: (id) => {
        return Api().get(`bloques/${id}`);
    },
    actualizar: (id, bloque) => {
        return Api().put(`bloques/${id}`, bloque);
    },
    eliminar: (id) => {
        return Api().delete(`bloques/${id}`);
    }
}