import { Api } from "./ApiService";

export default {
    listar: () => {
        return Api().get('servicios');
    },
    guardar: (servicio) => {
        return Api().post('servicios', servicio);
    },
    mostrar: (id) => {
        return Api().get(`servicios/${id}`);
    },
    actualizar: (id, servicio) => {
        return Api().put(`servicios/${id}`, servicio);
    },
    eliminar: (id) => {
        return Api().delete(`servicios/${id}`);
    }
}