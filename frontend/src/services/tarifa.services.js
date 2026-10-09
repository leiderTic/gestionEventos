import { Api } from "./ApiService";

export default {
    listar: () => {
        return Api().get('tarifas');
    },
    guardar: (tarifa) => {
        return Api().post('tarifas', tarifa);
    },
    mostrar: (id) => {
        return Api().get(`tarifas/${id}`);
    },
    actualizar: (id, tarifa) => {
        return Api().put(`tarifas/${id}`, tarifa);
    },
    eliminar: (id) => {
        return Api().delete(`tarifas/${id}`);
    },
    obtenerPorEspacio: (espacio_id) => {
        return Api().get(`tarifas/espacio/${espacio_id}`);
    },
    /**
     * OBSOLETO: Se mantiene por compatibilidad pero prefiere usar filtrarPorEvento.
     */
    obtenerPorFilro: (espacio_id, temporada_id, tipo_tarifa_id) => {
        return Api().get(`tarifas/espacio/${espacio_id}/temporada/${temporada_id}/tipo/${tipo_tarifa_id}`);
    },
    /**
     * Filtro de tarifas por espacio, temporada y evento.
     * GET /api/tarifas/espacio/{espacioId}/temporada/{temporadaId}/evento/{eventoId}
     */
    filtrarPorEvento: (espacio_id, temporada_id, evento_id) => {
        return Api().get(`tarifas/espacio/${espacio_id}/temporada/${temporada_id}/evento/${evento_id}`);
    }
}