import { Api } from "./ApiService";

export default {
    listar: () => {
        return Api().get('temporadas');
    },
    guardar: (temporada) => {
        return Api().post('temporadas', temporada);
    },
    mostrar: (id) => {
        return Api().get(`temporadas/${id}`);
    },
    actualizar: (id, evento) => {
        return Api().put(`temporadas/${id}`, evento);
    },
    eliminar: (id) => {
        return Api().delete(`temporadas/${id}`);
    }
}