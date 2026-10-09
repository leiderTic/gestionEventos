import { Api } from './ApiService';

export default {
    registrarPago: (cotizacionId, formData) => {
        return Api().post(`cotizaciones/${cotizacionId}/registrar-pago`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
    },
    
    listarPendientes: () => {
        return Api().get('administrativo/pagos-pendientes');
    },
    
    verificar: (pagoId, datos) => {
        return Api().put(`pagos/${pagoId}/verificar`, datos);
    }
};
