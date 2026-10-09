import { Api } from "./ApiService";

export default {
    listar: () => {
        return Api().get('tipo-espacios');
    },
    guardar: (tipoEspacio) => {
        return Api().post('tipo-espacios', tipoEspacio);
    },
    mostrar: (id) => {
        return Api().get(`tipo-espacios/${id}`);
    },
    actualizar: (id, tipoEspacio) => {
        return Api().put(`tipo-espacios/${id}`, tipoEspacio);
    },
    eliminar: (id) => {
        return Api().delete(`tipo-espacios/${id}`);
    }
}