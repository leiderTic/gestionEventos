import { Api } from "./ApiService";

export default {
    listar: () => {
        return Api().get('eventos');
    },
    guardar: (evento) => {
        return Api().post('eventos', evento);
    },
    mostrar: (id) => {
        return Api().get(`eventos/${id}`);
    },
    actualizar: (id, evento) => {
        return Api().put(`eventos/${id}`, evento);
    },
    eliminar: (id) => {
        return Api().delete(`eventos/${id}`);
    }
}