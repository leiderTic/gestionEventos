import { Api } from './ApiService';

export default {
    listarPorCotizacion: (cotizacionId) => {
        return Api().get(`cotizaciones/${cotizacionId}/reuniones`);
    },
    guardar: (cotizacionId, data) => {
        return Api().post(`cotizaciones/${cotizacionId}/reuniones`, data);
    },
    subirActa: (reunionId, formData) => {
        return Api().post(`reuniones/${reunionId}/acta`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
    }
};
