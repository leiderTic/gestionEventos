import { Api } from './ApiService';

export default {
    listar: () => {
        return Api().get('cotizaciones');
    },
    mostrar: (id) => {
        return Api().get(`cotizaciones/${id}`);
    },
    guardar: (datos) => {
        if (datos instanceof FormData) {
            return Api().post('cotizaciones', datos, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });
        }
        return Api().post('cotizaciones', datos);
    },
    actualizar: (id, datos) => {
        return Api().put(`cotizaciones/${id}`, datos);
    },
    eliminar: (id) => {
        return Api().delete(`cotizaciones/${id}`);
    },
    preview: (datos) => {
        return Api().post('cotizaciones/preview', datos, { responseType: 'blob' });
    },
    imprimir: (id) => {
        return Api().get(`cotizaciones/${id}/imprimir`, { responseType: 'blob' });
    },
    subirRespaldo: (id, formData) => {
        return Api().post(`cotizaciones/${id}/subir-respaldo`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
    },
    getRespaldos: (id) => {
        return Api().get(`cotizaciones/${id}/respaldos`);
    },
    getFichaReservaPdf: (id) => {
        return Api().get(`cotizaciones/${id}/ficha-reserva-pdf`, { responseType: 'blob' });
    },
    confirmarReserva: (id, datos = {}) => {
        return Api().post(`cotizaciones/${id}/confirmar-reserva`, datos);
    },
    subirDocumentosBase: (id, formData) => {
        return Api().post(`cotizaciones/${id}/documentos-base`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
    }
};
