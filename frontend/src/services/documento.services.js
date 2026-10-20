import { Api } from './ApiService';

export default {
    /**
     * Sube o actualiza los documentos base en PDF (Solicitud de Arrendamiento y Verificación de Habilitación)
     */
    subirDocumentosBase: (cotizacionId, formData) => {
        return Api().post(`cotizaciones/${cotizacionId}/documentos-base`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
    },

    /**
     * Obtiene los datos precargados y valores por defecto para un tipo de documento ('respuesta-arrendamiento' | 'respuesta-servicios-adicionales')
     */
    getFormularioPorTipo: (cotizacionId, tipo) => {
        return Api().get(`cotizaciones/${cotizacionId}/documentos/${tipo}/formulario`);
    },

    /**
     * Genera el stream en memoria (Blob PDF) de la vista previa por tipo de documento
     */
    previewPorTipo: (cotizacionId, tipo, datos) => {
        return Api().post(`cotizaciones/${cotizacionId}/documentos/${tipo}/preview`, datos, {
            responseType: 'blob'
        });
    },

    /**
     * Guarda los datos y persiste el PDF oficial por tipo de documento
     */
    guardarPorTipo: (cotizacionId, tipo, datos) => {
        return Api().post(`cotizaciones/${cotizacionId}/documentos/${tipo}`, datos);
    },

    /**
     * Sube el PDF escaneado firmado de un documento emitido y bloquea su edición posterior
     */
    subirFirmadoPorTipo: (cotizacionId, tipo, formData) => {
        return Api().post(`cotizaciones/${cotizacionId}/documentos/${tipo}/subir-firmado`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
    },

    /**
     * Alias específicos para Respuesta de Arrendamiento
     */
    getFormularioRespuestaArrendamiento: (cotizacionId) => {
        return Api().get(`cotizaciones/${cotizacionId}/documentos/respuesta-arrendamiento/formulario`);
    },
    previewRespuestaArrendamiento: (cotizacionId, datos) => {
        return Api().post(`cotizaciones/${cotizacionId}/documentos/respuesta-arrendamiento/preview`, datos, {
            responseType: 'blob'
        });
    },
    guardarRespuestaArrendamiento: (cotizacionId, datos) => {
        return Api().post(`cotizaciones/${cotizacionId}/documentos/respuesta-arrendamiento`, datos);
    },

    /**
     * Alias específicos para Respuesta de Servicios Adicionales
     */
    getFormularioRespuestaServicios: (cotizacionId) => {
        return Api().get(`cotizaciones/${cotizacionId}/documentos/respuesta-servicios-adicionales/formulario`);
    },
    previewRespuestaServicios: (cotizacionId, datos) => {
        return Api().post(`cotizaciones/${cotizacionId}/documentos/respuesta-servicios-adicionales/preview`, datos, {
            responseType: 'blob'
        });
    },
    guardarRespuestaServicios: (cotizacionId, datos) => {
        return Api().post(`cotizaciones/${cotizacionId}/documentos/respuesta-servicios-adicionales`, datos);
    },

    /**
     * Obtiene el PDF almacenado de un documento emitido
     */
    getDocumentoPdf: (cotizacionId, documentoId) => {
        return Api().get(`cotizaciones/${cotizacionId}/documentos/${documentoId}/pdf`, {
            responseType: 'blob'
        });
    }
};
