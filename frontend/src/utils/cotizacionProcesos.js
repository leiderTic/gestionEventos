/**
 * Definición centralizada de las etapas y procesos reales del sistema CFCHM (gestionEventos).
 * Alta cohesión: un único punto de verdad para estados, etapas por área responsable y formateadores.
 */

export const ETAPAS_PROCESO = {
    TODAS: 'todas',
    COMERCIAL: 'comercial',   // Área Comercial: Solicitud y Cotización
    FINANZAS: 'finanzas',     // Tesorería y Finanzas: Verificación Financiera
    JURIDICA: 'juridica',     // Asesoría Jurídica: Formalización Contractual
    CONCLUIDOS: 'concluidos'  // Archivo Institucional: Eventos Concluidos
};

export const DEFINICION_ETAPAS = [
    {
        id: ETAPAS_PROCESO.COMERCIAL,
        area: 'Área Comercial',
        titulo: 'Solicitud y Cotización',
        detalle: 'Emisión de propuesta y notas de respuesta',
        icono: 'pi pi-file-edit'
    },
    {
        id: ETAPAS_PROCESO.FINANZAS,
        area: 'Tesorería y Finanzas',
        titulo: 'Verificación Financiera',
        detalle: 'Validación de depósito, C21 y facturación',
        icono: 'pi pi-receipt'
    },
    {
        id: ETAPAS_PROCESO.JURIDICA,
        area: 'Asesoría Jurídica',
        titulo: 'Formalización Contractual',
        detalle: 'Revisión legal, contrato y desarrollo del evento',
        icono: 'pi pi-briefcase'
    },
    {
        id: ETAPAS_PROCESO.CONCLUIDOS,
        area: 'Archivo Institucional',
        titulo: 'Eventos Concluidos',
        detalle: 'Cierre de expediente y devolución de garantía',
        icono: 'pi pi-check-square'
    }
];

export const CATALOGO_PASOS = {
    1: { label: 'Solicitud y cotización', area: 'Área Comercial', severity: 'info', etapa: ETAPAS_PROCESO.COMERCIAL },
    2: { label: 'Verificación financiera', area: 'Tesorería y Finanzas', severity: 'warn', etapa: ETAPAS_PROCESO.FINANZAS },
    3: { label: 'Coordinación operativa', area: 'Tesorería y Finanzas', severity: 'warn', etapa: ETAPAS_PROCESO.FINANZAS },
    4: { label: 'Liquidación y garantía', area: 'Tesorería y Finanzas', severity: 'warn', etapa: ETAPAS_PROCESO.FINANZAS },
    5: { label: 'Habilitación de inicio', area: 'Tesorería y Finanzas', severity: 'warn', etapa: ETAPAS_PROCESO.FINANZAS },
    6: { label: 'Revisión jurídica', area: 'Asesoría Jurídica', severity: 'secondary', etapa: ETAPAS_PROCESO.JURIDICA },
    7: { label: 'Contrato suscrito', area: 'Asesoría Jurídica', severity: 'success', etapa: ETAPAS_PROCESO.JURIDICA },
    8: { label: 'Premontaje autorizado', area: 'Asesoría Jurídica', severity: 'success', etapa: ETAPAS_PROCESO.JURIDICA },
    9: { label: 'Entrega de ambientes', area: 'Asesoría Jurídica', severity: 'success', etapa: ETAPAS_PROCESO.JURIDICA },
    10: { label: 'Evento en desarrollo', area: 'Asesoría Jurídica', severity: 'success', etapa: ETAPAS_PROCESO.JURIDICA },
    11: { label: 'Recepción de ambientes', area: 'Asesoría Jurídica', severity: 'info', etapa: ETAPAS_PROCESO.JURIDICA },
    12: { label: 'Evaluación técnica final', area: 'Asesoría Jurídica', severity: 'info', etapa: ETAPAS_PROCESO.JURIDICA },
    13: { label: 'Liberación de garantía', area: 'Asesoría Jurídica', severity: 'info', etapa: ETAPAS_PROCESO.JURIDICA },
    14: { label: 'Evento concluido', area: 'Archivo Institucional', severity: 'secondary', etapa: ETAPAS_PROCESO.CONCLUIDOS }
};

export function perteneceAEtapa(cotizacion, etapaId) {
    if (!cotizacion) return false;
    if (etapaId === ETAPAS_PROCESO.TODAS) return true;

    const paso = Number(cotizacion.paso || 1);
    const infoPaso = CATALOGO_PASOS[paso];
    return infoPaso ? infoPaso.etapa === etapaId : false;
}

export function filtrarPorEtapa(cotizaciones = [], etapaId = ETAPAS_PROCESO.TODAS) {
    if (etapaId === ETAPAS_PROCESO.TODAS) return cotizaciones;
    return cotizaciones.filter((c) => perteneceAEtapa(c, etapaId));
}

export function sumarMonto(cotizaciones = []) {
    return cotizaciones.reduce((acc, c) => acc + Number(c.monto_total || 0), 0);
}

export function obtenerEstadoPaso(cotizacion) {
    const paso = Number(cotizacion?.paso || 1);
    return (
        CATALOGO_PASOS[paso] || {
            label: 'Solicitud y cotización',
            area: 'Área Comercial',
            severity: 'info',
            etapa: ETAPAS_PROCESO.COMERCIAL
        }
    );
}

export function obtenerTituloEtapa(etapaId) {
    if (etapaId === ETAPAS_PROCESO.TODAS) return 'Todas las áreas';
    const encontrada = DEFINICION_ETAPAS.find((e) => e.id === etapaId);
    return encontrada ? `${encontrada.area} · ${encontrada.titulo}` : 'Todas las áreas';
}

export function formatMoneda(valor, decimales = 2) {
    return Number(valor || 0).toLocaleString('es-BO', {
        minimumFractionDigits: decimales,
        maximumFractionDigits: decimales
    });
}

export function formatMonedaCompacta(valor) {
    const num = Number(valor || 0);
    return num.toLocaleString('es-BO', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2
    });
}

export function formatFechaCorta(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    date.setMinutes(date.getMinutes() + date.getTimezoneOffset());
    return date
        .toLocaleDateString('es-ES', { day: '2-digit', month: 'short', year: 'numeric' })
        .replace('.', '');
}

export function calcularDiasEvento(ini, fin) {
    if (!ini || !fin) return 0;
    const start = new Date(ini);
    const end = new Date(fin);
    const diffTime = Math.abs(end - start);
    return Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
}

export function obtenerNombreClientePrincipal(cotizacion) {
    if (!cotizacion?.clientes || cotizacion.clientes.length === 0) {
        return cotizacion?.descripcion || 'Sin cliente asignado';
    }
    const inst = cotizacion.clientes.find((c) => c.tipo_cliente_id === 1);
    const perso = cotizacion.clientes.find((c) => c.tipo_cliente_id === 2);
    return inst ? inst.nombre : perso ? perso.nombre : 'S/N';
}

export function obtenerNombreContactoSecundario(cotizacion) {
    if (!cotizacion?.clientes || cotizacion.clientes.length === 0) return null;
    const inst = cotizacion.clientes.find((c) => c.tipo_cliente_id === 1);
    const perso = cotizacion.clientes.find((c) => c.tipo_cliente_id === 2);
    if (inst && perso) return perso.nombre;
    if (!inst && perso && perso.ci_nit) return `CI/NIT ${perso.ci_nit}`;
    return null;
}

export function obtenerNombresEspacios(cotizacion) {
    if (!cotizacion?.tarifas || cotizacion.tarifas.length === 0) return [];
    return [...new Set(cotizacion.tarifas.map((t) => t.espacio?.nombre).filter(Boolean))];
}
