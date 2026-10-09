import { Api } from "./ApiService";

export default {
    listar: () => {
        return Api().get('espacios');
    },
    guardar: (espacio) => {
        return Api().post('espacios', espacio);
    },
    mostrar: (id) => {
        return Api().get(`espacios/${id}`);
    },
    actualizar: (id, espacio) => {
        return Api().put(`espacios/${id}`, espacio);
    },
    eliminar: (id) => {
        return Api().delete(`espacios/${id}`);
    }
}