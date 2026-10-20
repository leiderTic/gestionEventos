<template>
    <div
        class="mb-6 p-4 rounded-xl bg-surface-50 dark:bg-surface-900/60 border border-surface-200 dark:border-surface-800 space-y-3">
        <div class="flex items-center justify-between">
            <div
                class="flex items-center gap-2 text-xs font-bold text-surface-700 dark:text-surface-300 uppercase tracking-wider">
                <i class="pi pi-paperclip text-primary"></i>
                <span>Documentos Base de la Cotización</span>
            </div>
            <Button label="Adjuntar / Actualizar" icon="pi pi-upload" text size="small"
                class="!text-xs !py-1 !px-2 font-bold text-primary" @click="$emit('abrir-documentos-base')" />
        </div>

        <div v-if="cotizacion?.solicitud_arrendamiento_path || cotizacion?.verificacion_habilitacion_path"
            class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <!-- Solicitud de Arrendamiento -->
            <div v-if="cotizacion.solicitud_arrendamiento_path"
                class="flex items-center justify-between p-3 rounded-lg bg-surface-0 dark:bg-surface-800 border border-surface-200 dark:border-surface-700 shadow-xs">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div
                        class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 flex items-center justify-center flex-shrink-0">
                        <i class="pi pi-file-edit text-sm"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-bold text-surface-900 dark:text-surface-0 truncate m-0">Solicitud de
                            Arrendamiento</p>
                        <p class="text-[10px] text-muted-color m-0">Borrador y requerimientos</p>
                    </div>
                </div>
                <Button label="Ver PDF" icon="pi pi-eye" size="small"
                    class="!text-xs !py-1.5 !px-3 font-bold !bg-primary-50 hover:!bg-primary-100 !text-primary dark:!bg-primary-950/40 dark:!text-primary-300 !border-none flex items-center gap-1.5 shadow-none"
                    @click="$emit('ver-pdf', { titulo: 'Solicitud de Arrendamiento', subtitulo: 'Formulario del cliente con requerimientos y borrador del evento', path: cotizacion.solicitud_arrendamiento_path })" />
            </div>
            <div v-else
                class="flex items-center gap-2 p-3 rounded-lg bg-surface-0 dark:bg-surface-800 border border-dashed border-surface-200 dark:border-surface-700 text-muted-color text-xs">
                <i class="pi pi-info-circle text-orange-400"></i>
                <span>Solicitud de Arrendamiento no adjunta</span>
            </div>

            <!-- Verificación de Habilitación -->
            <div v-if="cotizacion.verificacion_habilitacion_path"
                class="flex items-center justify-between p-3 rounded-lg bg-surface-0 dark:bg-surface-800 border border-surface-200 dark:border-surface-700 shadow-xs">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div
                        class="w-8 h-8 rounded-lg bg-teal-50 dark:bg-teal-950/40 text-teal-600 dark:text-teal-400 flex items-center justify-center flex-shrink-0">
                        <i class="pi pi-shield text-sm"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-bold text-surface-900 dark:text-surface-0 truncate m-0">Verificación de
                            Habilitación</p>
                        <p class="text-[10px] text-muted-color m-0">Certificación administrativa</p>
                    </div>
                </div>
                <Button label="Ver PDF" icon="pi pi-eye" size="small"
                    class="!text-xs !py-1.5 !px-3 font-bold !bg-teal-50 hover:!bg-teal-100 !text-teal-700 dark:!bg-teal-950/40 dark:!text-teal-300 !border-none flex items-center gap-1.5 shadow-none"
                    @click="$emit('ver-pdf', { titulo: 'Verificación de Habilitación', subtitulo: 'Certificación administrativa de habilitación y buen expediente', path: cotizacion.verificacion_habilitacion_path })" />
            </div>
            <div v-else
                class="flex items-center gap-2 p-3 rounded-lg bg-surface-0 dark:bg-surface-800 border border-dashed border-surface-200 dark:border-surface-700 text-muted-color text-xs">
                <i class="pi pi-info-circle text-orange-400"></i>
                <span>Verificación de Habilitación no adjunta</span>
            </div>
        </div>

        <div v-else class="flex items-center gap-2 text-xs text-muted-color py-1">
            <i class="pi pi-info-circle text-surface-400"></i>
            <span>No se han adjuntado la Solicitud de Arrendamiento ni la Verificación de Habilitación aún.</span>
        </div>

        <!-- NOTAS INSTITUCIONALES DE RESPUESTA (ARRENDAMIENTO Y SERVICIOS ADICIONALES) -->
        <div class="pt-2 border-t border-surface-200 dark:border-surface-700 space-y-2.5">
            <!-- 1. RESPUESTA A SOLICITUD DE COTIZACIÓN POR ARRENDAMIENTO -->
            <div v-if="docRespuestaArrendamiento"
                class="flex flex-col sm:flex-row items-start sm:items-center justify-between p-3 rounded-lg bg-surface-0 dark:bg-surface-800 border border-sky-200 dark:border-sky-800/60 shadow-xs gap-3">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div :class="[
                        'w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0',
                        esFirmado(docRespuestaArrendamiento)
                            ? 'bg-emerald-100 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300'
                            : 'bg-sky-50 dark:bg-sky-950/40 text-sky-700 dark:text-sky-300'
                    ]">
                        <i :class="['text-sm', esFirmado(docRespuestaArrendamiento) ? 'pi pi-lock' : 'pi pi-file-pdf']"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-1.5">
                            <p class="text-xs font-bold text-surface-900 dark:text-surface-0 truncate m-0">Respuesta
                                Cotización Arrendamiento</p>
                            <span
                                class="bg-sky-100 dark:bg-sky-950/50 text-sky-800 dark:text-sky-300 text-[10px] font-bold px-1.5 py-0.2 rounded">
                                CITE: {{ docRespuestaArrendamiento.cite }}
                            </span>
                            <span v-if="esFirmado(docRespuestaArrendamiento)"
                                class="bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 text-[10px] font-bold px-2 py-0.2 rounded-full flex items-center gap-1">
                                <i class="pi pi-check-circle text-[9px]"></i> Firmado y Bloqueado
                            </span>
                            <span v-else
                                class="bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 text-[10px] font-bold px-2 py-0.2 rounded-full">
                                Pendiente subir firmado
                            </span>
                        </div>
                        <p class="text-[10px] text-muted-color m-0 mt-0.5">
                            Emitido: {{ formatDateShort(docRespuestaArrendamiento.fecha) }} · Destinatario: {{
                                docRespuestaArrendamiento.snapshot_datos?.destinatario_nombre || 'Cliente' }}
                        </p>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-1.5 self-end sm:self-center">
                    <!-- Si ya está firmado: mostrar Ver PDF Firmado y Ver Original (generado al vuelo) -->
                    <template v-if="esFirmado(docRespuestaArrendamiento)">
                        <Button label="Ver PDF Firmado" icon="pi pi-verified" size="small"
                            class="!text-xs !py-1.5 !px-3 font-bold !bg-emerald-600 hover:!bg-emerald-700 !text-white !border-none flex items-center gap-1.5 shadow-xs"
                            @click="$emit('ver-pdf', { titulo: 'Respuesta Arrendamiento (FIRMADO)', subtitulo: 'CITE: ' + (docRespuestaArrendamiento.cite || 'S/N'), path: docRespuestaArrendamiento.pdf_firmado_path })" />
                        <Button label="Original" icon="pi pi-eye" size="small" text
                            :loading="cargandoPdfId === docRespuestaArrendamiento.id"
                            class="!text-xs !py-1.5 !px-2 font-semibold text-surface-500 hover:text-primary"
                            @click="verPdfSinFirmar(docRespuestaArrendamiento, 'Respuesta Arrendamiento (Original Sin Firmar)')" />
                    </template>

                    <!-- Si aún NO está firmado: permitir Imprimir/Ver (al vuelo), Subir Firmado y Editar -->
                    <template v-else>
                        <Button label="Ver / Imprimir" icon="pi pi-print" size="small"
                            :loading="cargandoPdfId === docRespuestaArrendamiento.id"
                            class="!text-xs !py-1.5 !px-2.5 font-bold !bg-sky-50 hover:!bg-sky-100 !text-sky-800 dark:!bg-sky-950/40 dark:!text-sky-300 !border-none flex items-center gap-1 shadow-none"
                            @click="verPdfSinFirmar(docRespuestaArrendamiento, 'Respuesta a Solicitud de Cotización por Arrendamiento')" />
                        <Button label="Subir Firmado" icon="pi pi-upload" size="small"
                            class="!text-xs !py-1.5 !px-2.5 font-bold !bg-emerald-50 hover:!bg-emerald-100 !text-emerald-700 dark:!bg-emerald-950/40 dark:!text-emerald-300 !border !border-emerald-200 flex items-center gap-1 shadow-none"
                            @click="$emit('abrir-subir-firmado', { tipo: 'respuesta-arrendamiento', doc: docRespuestaArrendamiento })" />
                        <Button label="Editar" icon="pi pi-pencil" size="small" text
                            class="!text-xs !py-1.5 !px-2 font-bold text-surface-600 hover:text-primary"
                            @click="$emit('abrir-respuesta-arrendamiento')" />
                    </template>
                </div>
            </div>

            <div v-else
                class="flex flex-col sm:flex-row items-start sm:items-center justify-between p-3 rounded-lg bg-surface-0 dark:bg-surface-800 border border-dashed border-surface-300 dark:border-surface-700 gap-3">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div
                        class="w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 flex items-center justify-center flex-shrink-0">
                        <i class="pi pi-exclamation-circle text-sm"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-bold text-surface-900 dark:text-surface-0 truncate m-0">1. Respuesta a
                            Solicitud por Arrendamiento</p>
                        <p class="text-[10px] text-muted-color m-0">Nota oficial CITE pendiente de emisión y firma
                            (obligatoria para confirmar reserva)</p>
                    </div>
                </div>
                <Button label="Generar Documento" icon="pi pi-file-edit" size="small"
                    class="!text-xs !py-1.5 !px-3 font-bold !bg-primary-50 hover:!bg-primary-100 !text-primary dark:!bg-primary-950/40 dark:!text-primary-300 !border-none flex items-center gap-1.5 shadow-none self-end sm:self-center"
                    @click="$emit('abrir-respuesta-arrendamiento')" />
            </div>

            <!-- 2. RESPUESTA A SOLICITUD DE COTIZACIÓN SERVICIOS ADICIONALES -->
            <div v-if="docRespuestaServicios"
                class="flex flex-col sm:flex-row items-start sm:items-center justify-between p-3 rounded-lg bg-surface-0 dark:bg-surface-800 border border-indigo-200 dark:border-indigo-800/60 shadow-xs gap-3">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div :class="[
                        'w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0',
                        esFirmado(docRespuestaServicios)
                            ? 'bg-emerald-100 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300'
                            : 'bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300'
                    ]">
                        <i :class="['text-sm', esFirmado(docRespuestaServicios) ? 'pi pi-lock' : 'pi pi-file-pdf']"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-1.5">
                            <p class="text-xs font-bold text-surface-900 dark:text-surface-0 truncate m-0">Respuesta
                                Servicios Adicionales</p>
                            <span
                                class="bg-indigo-100 dark:bg-indigo-950/50 text-indigo-800 dark:text-indigo-300 text-[10px] font-bold px-1.5 py-0.2 rounded">
                                CITE: {{ docRespuestaServicios.cite }}
                            </span>
                            <span v-if="esFirmado(docRespuestaServicios)"
                                class="bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 text-[10px] font-bold px-2 py-0.2 rounded-full flex items-center gap-1">
                                <i class="pi pi-check-circle text-[9px]"></i> Firmado y Bloqueado
                            </span>
                            <span v-else
                                class="bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 text-[10px] font-bold px-2 py-0.2 rounded-full">
                                Pendiente subir firmado
                            </span>
                        </div>
                        <p class="text-[10px] text-muted-color m-0 mt-0.5">
                            Emitido: {{ formatDateShort(docRespuestaServicios.fecha) }} · Destinatario: {{
                                docRespuestaServicios.snapshot_datos?.destinatario_nombre || 'Cliente' }}
                        </p>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-1.5 self-end sm:self-center">
                    <!-- Si ya está firmado: mostrar Ver PDF Firmado y Ver Original (generado al vuelo) -->
                    <template v-if="esFirmado(docRespuestaServicios)">
                        <Button label="Ver PDF Firmado" icon="pi pi-verified" size="small"
                            class="!text-xs !py-1.5 !px-3 font-bold !bg-emerald-600 hover:!bg-emerald-700 !text-white !border-none flex items-center gap-1.5 shadow-xs"
                            @click="$emit('ver-pdf', { titulo: 'Respuesta Servicios Adicionales (FIRMADO)', subtitulo: 'CITE: ' + (docRespuestaServicios.cite || 'S/N'), path: docRespuestaServicios.pdf_firmado_path })" />
                        <Button label="Original" icon="pi pi-eye" size="small" text
                            :loading="cargandoPdfId === docRespuestaServicios.id"
                            class="!text-xs !py-1.5 !px-2 font-semibold text-surface-500 hover:text-primary"
                            @click="verPdfSinFirmar(docRespuestaServicios, 'Respuesta Servicios Adicionales (Original Sin Firmar)')" />
                    </template>

                    <!-- Si aún NO está firmado: permitir Imprimir/Ver (al vuelo), Subir Firmado y Editar -->
                    <template v-else>
                        <Button label="Ver / Imprimir" icon="pi pi-print" size="small"
                            :loading="cargandoPdfId === docRespuestaServicios.id"
                            class="!text-xs !py-1.5 !px-2.5 font-bold !bg-indigo-50 hover:!bg-indigo-100 !text-indigo-800 dark:!bg-indigo-950/40 dark:!text-indigo-300 !border-none flex items-center gap-1 shadow-none"
                            @click="verPdfSinFirmar(docRespuestaServicios, 'Respuesta a Solicitud de Cotización Servicios Adicionales')" />
                        <Button label="Subir Firmado" icon="pi pi-upload" size="small"
                            class="!text-xs !py-1.5 !px-2.5 font-bold !bg-emerald-50 hover:!bg-emerald-100 !text-emerald-700 dark:!bg-emerald-950/40 dark:!text-emerald-300 !border !border-emerald-200 flex items-center gap-1 shadow-none"
                            @click="$emit('abrir-subir-firmado', { tipo: 'respuesta-servicios-adicionales', doc: docRespuestaServicios })" />
                        <Button label="Editar" icon="pi pi-pencil" size="small" text
                            class="!text-xs !py-1.5 !px-2 font-bold text-surface-600 hover:text-primary"
                            @click="$emit('abrir-respuesta-servicios')" />
                    </template>
                </div>
            </div>

            <div v-else
                class="flex flex-col sm:flex-row items-start sm:items-center justify-between p-3 rounded-lg bg-surface-0 dark:bg-surface-800 border border-dashed border-surface-300 dark:border-surface-700 gap-3">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div
                        class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center flex-shrink-0">
                        <i class="pi pi-file-plus text-sm"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-bold text-surface-900 dark:text-surface-0 truncate m-0">2. Respuesta a
                            Solicitud de Servicios Adicionales</p>
                        <p class="text-[10px] text-muted-color m-0">Nota oficial CITE pendiente de emisión y firma
                            (obligatoria para confirmar reserva)</p>
                    </div>
                </div>
                <Button label="Generar Documento" icon="pi pi-file-edit" size="small"
                    class="!text-xs !py-1.5 !px-3 font-bold !bg-indigo-50 hover:!bg-indigo-100 !text-indigo-700 dark:!bg-indigo-950/40 dark:!text-indigo-300 !border-none flex items-center gap-1.5 shadow-none self-end sm:self-center"
                    @click="$emit('abrir-respuesta-servicios')" />
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useToast } from 'primevue/usetoast';
import documentoServices from '@/services/documento.services';

const props = defineProps({
    cotizacion: {
        type: Object,
        default: null
    },
    docRespuestaArrendamiento: {
        type: Object,
        default: null
    },
    docRespuestaServicios: {
        type: Object,
        default: null
    }
});

const emit = defineEmits([
    'abrir-documentos-base',
    'abrir-respuesta-arrendamiento',
    'abrir-respuesta-servicios',
    'abrir-subir-firmado',
    'ver-pdf'
]);

const toast = useToast();
const cargandoPdfId = ref(null);

const esFirmado = (doc) => {
    return !!(doc && (doc.bloqueado || doc.pdf_firmado_path));
};

const verPdfSinFirmar = async (doc, titulo) => {
    if (!props.cotizacion?.id || !doc?.id) return;
    try {
        cargandoPdfId.value = doc.id;
        const res = await documentoServices.getDocumentoPdf(props.cotizacion.id, doc.id);
        const blob = new Blob([res.data], { type: 'application/pdf' });
        const blobUrl = URL.createObjectURL(blob);
        emit('ver-pdf', {
            titulo,
            subtitulo: `CITE: ${doc.cite || 'S/N'} (Generado dinámicamente sin firmar)`,
            path: blobUrl
        });
    } catch (e) {
        console.error('Error al generar PDF sin firmar', e);
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'No se pudo generar el PDF del documento.',
            life: 4000
        });
    } finally {
        cargandoPdfId.value = null;
    }
};

const formatDateShort = (dateStr) => {
    if (!dateStr) return '-';
    const d = new Date(dateStr);
    if (isNaN(d)) return dateStr;
    return d.toLocaleDateString('es-BO', { day: '2-digit', month: 'short', year: 'numeric' });
};
</script>



