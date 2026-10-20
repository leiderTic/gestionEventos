<template>
    <Dialog :visible="visible" @update:visible="$emit('update:visible', $event)" modal
        :style="{ width: '56rem', maxWidth: '96vw' }" :breakpoints="{ '960px': '85vw', '640px': '98vw' }">
        <template #header>
            <div class="flex items-center gap-3">
                <div :class="[
                    'w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0',
                    esServiciosAdicionales
                        ? 'bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300'
                        : 'bg-sky-100 dark:bg-sky-900/40 text-sky-700 dark:text-sky-300'
                ]">
                    <i class="pi pi-file-edit text-lg"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-surface-900 dark:text-surface-0 m-0 leading-tight">
                        {{ tituloModal }}
                    </h3>
                    <p class="text-xs text-muted-color m-0 mt-0.5">
                        Generación de nota institucional oficial con formato Century Gothic, márgenes reglamentarios y
                        código QR
                    </p>
                </div>
            </div>
        </template>

        <div v-if="cargandoFormulario" class="py-12 flex flex-col items-center justify-center gap-3 text-muted-color">
            <i class="pi pi-spin pi-spinner text-2xl text-primary"></i>
            <span class="text-xs font-medium">Cargando datos preestablecidos de la cotización y plantilla...</span>
        </div>

        <div v-else class="space-y-5 pt-2">
            <!-- Banner informativo -->
            <div :class="[
                'flex items-start gap-3 p-3.5 rounded-xl border text-xs',
                esServiciosAdicionales
                    ? 'bg-indigo-50/70 dark:bg-indigo-950/20 border-indigo-200 dark:border-indigo-800 text-indigo-900 dark:text-indigo-200'
                    : 'bg-sky-50/70 dark:bg-sky-950/20 border-sky-200 dark:border-sky-800 text-sky-900 dark:text-sky-200'
            ]">
                <i :class="[
                    'pi pi-info-circle mt-0.5 text-base flex-shrink-0',
                    esServiciosAdicionales ? 'text-indigo-600 dark:text-indigo-400' : 'text-sky-600 dark:text-sky-400'
                ]"></i>
                <div class="leading-relaxed">
                    <span class="font-bold">{{ esServiciosAdicionales ? 'Nota de Servicios Adicionales:' : 'Emisión previa obligatoria:' }}</span>
                    {{ descripcionBanner }}
                </div>
            </div>

            <!-- SECCIÓN 1: DATOS DE LA NOTA (FORMULARIO PRINCIPAL) -->
            <div
                class="p-4 rounded-xl bg-surface-50 dark:bg-surface-900/50 border border-surface-200 dark:border-surface-700 space-y-4">
                <div class="flex items-center justify-between border-b border-surface-200 dark:border-surface-700 pb-2">
                    <span
                        class="text-xs font-bold text-surface-700 dark:text-surface-300 uppercase tracking-wider flex items-center gap-2">
                        <i class="pi pi-file text-primary"></i> Datos de la Nota Institucional
                    </span>
                    <span class="text-[11px] text-muted-color">Campos requeridos para la emisión</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Fecha de la Nota -->
                    <div>
                        <label
                            class="block text-xs font-bold text-surface-700 dark:text-surface-300 uppercase tracking-tight mb-1.5">
                            Fecha de la Nota <span class="text-red-500">*</span>
                        </label>
                        <DatePicker v-model="form.fecha" dateFormat="yy-mm-dd" showIcon fluid
                            placeholder="aaaa-mm-dd" />
                        <p class="text-[11px] text-muted-color mt-1">Fecha con la que se emitirá la nota oficial.</p>
                    </div>

                    <!-- CITE (Número) -->
                    <div>
                        <label
                            class="block text-xs font-bold text-surface-700 dark:text-surface-300 uppercase tracking-tight mb-1.5">
                            CITE / Número de Nota <span class="text-red-500">*</span>
                        </label>
                        <div
                            class="flex items-stretch rounded-lg overflow-hidden border border-surface-300 dark:border-surface-600 focus-within:ring-1 focus-within:ring-primary focus-within:border-primary">
                            <span
                                class="bg-surface-100 dark:bg-surface-800 text-surface-600 dark:text-surface-300 px-3 flex items-center text-xs font-semibold select-none border-r border-surface-300 dark:border-surface-600">
                                {{ form.prefijo_cite || 'MEFP/UCPP/ UCFCM/NE N°' }}
                            </span>
                            <InputText v-model="form.cite" class="!border-none !ring-0 flex-1 text-sm font-bold"
                                placeholder="001" />
                            <span
                                class="bg-surface-100 dark:bg-surface-800 text-surface-500 dark:text-surface-400 px-3 flex items-center text-xs font-semibold select-none border-l border-surface-300 dark:border-surface-600">
                                /{{ form.anio }}
                            </span>
                        </div>
                        <p class="text-[11px] text-muted-color mt-1">Número correlativo asignado a esta nota.</p>
                    </div>

                    <!-- Del (Fecha solicitud del cliente) -->
                    <div>
                        <label
                            class="block text-xs font-bold text-surface-700 dark:text-surface-300 uppercase tracking-tight mb-1.5">
                            En atención a solicitud de fecha (Del)
                        </label>
                        <DatePicker v-model="form.del" dateFormat="yy-mm-dd" showIcon fluid
                            placeholder="Opcional: aaaa-mm-dd" />
                        <p class="text-[11px] text-muted-color mt-1">Fecha de la nota o solicitud presentada por el
                            cliente.</p>
                    </div>

                    <!-- Plazo de confirmación / pago -->
                    <div>
                        <label
                            class="block text-xs font-bold text-surface-700 dark:text-surface-300 uppercase tracking-tight mb-1.5">
                            Plazo de Formalización <span class="text-red-500">*</span>
                        </label>
                        <InputText v-model="form.plazo" fluid placeholder="tres (3) días hábiles" />
                        <p class="text-[11px] text-muted-color mt-1">Tiempo otorgado para confirmar y depositar.</p>
                    </div>

                    <!-- Teléfono -->
                    <div>
                        <label
                            class="block text-xs font-bold text-surface-700 dark:text-surface-300 uppercase tracking-tight mb-1.5">
                            Teléfono de Contacto
                        </label>
                        <InputText v-model="form.telefono" fluid placeholder="77240445" />
                        <p class="text-[11px] text-muted-color mt-1">Número de coordinación de la UCFCM.</p>
                    </div>

                    <!-- Correo electrónico -->
                    <div>
                        <label
                            class="block text-xs font-bold text-surface-700 dark:text-surface-300 uppercase tracking-tight mb-1.5">
                            Correo Electrónico
                        </label>
                        <InputText v-model="form.correo_electronico" fluid
                            placeholder="correo@economiayfinanzas.gob.bo" />
                        <p class="text-[11px] text-muted-color mt-1">Correo institucional de referencia.</p>
                    </div>

                    <!-- Hoja de Ruta -->
                    <div>
                        <label
                            class="block text-xs font-bold text-surface-700 dark:text-surface-300 uppercase tracking-tight mb-1.5">
                            Hoja de Ruta (HR)
                        </label>
                        <InputText v-model="form.hoja_ruta" fluid placeholder="Ej: 2026-43414-R" />
                        <p class="text-[11px] text-muted-color mt-1">Hoja de ruta institucional (pie de página).</p>
                    </div>

                    <!-- Moscas / Iniciales -->
                    <div>
                        <label
                            class="block text-xs font-bold text-surface-700 dark:text-surface-300 uppercase tracking-tight mb-1.5">
                            Moscas / Iniciales
                        </label>
                        <InputText v-model="form.moscas" fluid placeholder="RMRR/JLJC/HABB" />
                        <p class="text-[11px] text-muted-color mt-1">Iniciales del personal que elaboró y revisó la
                            nota.</p>
                    </div>
                </div>
            </div>

            <!-- SECCIÓN 2: DATOS DEL DESTINATARIO Y EVENTO (CALCULADOS / EDITABLES) -->
            <div
                class="border border-surface-200 dark:border-surface-700 rounded-xl overflow-hidden bg-white dark:bg-surface-900 shadow-xs">
                <button type="button" @click="seccionEventoColapsada = !seccionEventoColapsada"
                    class="w-full p-3.5 flex items-center justify-between text-left hover:bg-surface-50 dark:hover:bg-surface-800/60 transition-colors">
                    <div class="flex items-center gap-2.5">
                        <i class="pi pi-user-edit text-primary text-sm"></i>
                        <div>
                            <span
                                class="text-xs font-bold text-surface-800 dark:text-surface-200 uppercase tracking-wide">
                                Destinatario y Datos del Evento
                            </span>
                            <span
                                class="ml-2 text-[10px] bg-primary-50 dark:bg-primary-950/40 text-primary dark:text-primary-300 font-semibold px-2 py-0.5 rounded-full">
                                Autocompletado del sistema
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-1.5 text-xs text-muted-color">
                        <span>{{ seccionEventoColapsada ? 'Mostrar y editar' : 'Ocultar' }}</span>
                        <i
                            :class="['pi text-xs transition-transform', seccionEventoColapsada ? 'pi-chevron-down' : 'pi-chevron-up']"></i>
                    </div>
                </button>

                <div v-show="!seccionEventoColapsada"
                    class="p-4 border-t border-surface-200 dark:border-surface-700 bg-surface-50/50 dark:bg-surface-900/30 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label
                                class="block text-xs font-bold text-surface-700 dark:text-surface-300 uppercase tracking-tight mb-1">
                                Destinatario (Señor/a)
                            </label>
                            <InputText v-model="form.destinatario_nombre" fluid
                                placeholder="Nombre del contacto o cliente" />
                        </div>
                        <div>
                            <label
                                class="block text-xs font-bold text-surface-700 dark:text-surface-300 uppercase tracking-tight mb-1">
                                Cargo e Institución
                            </label>
                            <InputText v-model="form.destinatario_cargo_institucion" fluid
                                placeholder="ENCARGADO - NOMBRE INSTITUCION" />
                        </div>
                    </div>

                    <div>
                        <label
                            class="block text-xs font-bold text-surface-700 dark:text-surface-300 uppercase tracking-tight mb-1">
                            Nombre del Evento (Glosa)
                        </label>
                        <InputText v-model="form.evento_nombre" fluid placeholder="Denominación del evento" />
                    </div>

                    <div :class="['grid grid-cols-1 gap-3', esServiciosAdicionales ? 'md:grid-cols-1' : 'md:grid-cols-3']">
                        <div>
                            <label
                                class="block text-xs font-bold text-surface-700 dark:text-surface-300 uppercase tracking-tight mb-1">
                                Fechas Solicitadas
                            </label>
                            <InputText v-model="form.fechas_evento_texto" fluid
                                placeholder="del ... al ... de ... de ..." />
                        </div>
                        <div v-if="!esServiciosAdicionales">
                            <label
                                class="block text-xs font-bold text-surface-700 dark:text-surface-300 uppercase tracking-tight mb-1">
                                Fecha de Montaje (1 día antes)
                            </label>
                            <InputText v-model="form.fecha_montaje_texto" fluid placeholder="... de ... de ..." />
                        </div>
                        <div v-if="!esServiciosAdicionales">
                            <label
                                class="block text-xs font-bold text-surface-700 dark:text-surface-300 uppercase tracking-tight mb-1">
                                Fecha de Desmontaje (1 día después)
                            </label>
                            <InputText v-model="form.fecha_desmontaje_texto" fluid placeholder="... de ... de ..." />
                        </div>
                    </div>

                    <div>
                        <label
                            class="block text-xs font-bold text-surface-700 dark:text-surface-300 uppercase tracking-tight mb-1">
                            {{ esServiciosAdicionales ? 'Servicios Adicionales / Espacio Alquilado' : 'Espacios Arrendados' }}
                        </label>
                        <InputText v-model="form.espacios_texto" fluid
                            :placeholder="esServiciosAdicionales ? 'Ej: Depósito 4' : 'Auditorio Illimani, Sala VIP, etc.'" />
                    </div>
                </div>
            </div>
        </div>

        <template #footer>
            <div
                class="flex flex-col sm:flex-row justify-between items-center gap-3 pt-4 border-t border-surface-200 dark:border-surface-700 w-full">
                <div>
                    <span v-if="docEmitido" class="text-xs text-muted-color flex items-center gap-1.5">
                        <i class="pi pi-history text-primary"></i>
                        Última emisión registrada: <b class="text-surface-800 dark:text-surface-200">{{ docEmitido.cite
                            }}</b>
                    </span>
                </div>
                <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                    <Button label="Cancelar" icon="pi pi-times" text severity="secondary"
                        @click="$emit('update:visible', false)" />

                    <Button label="Vista Previa PDF" icon="pi pi-eye" severity="info" outlined
                        :disabled="cargandoFormulario" :loading="cargandoPreview" @click="generarVistaPrevia" />

                    <Button label="Guardar y Emitir PDF" icon="pi pi-check" severity="primary"
                        :disabled="cargandoFormulario" :loading="guardando" @click="guardarDocumento" />
                </div>
            </div>
        </template>
    </Dialog>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue';
import { useToast } from 'primevue/usetoast';
import documentoServices from '@/services/documento.services';

const props = defineProps({
    visible: {
        type: Boolean,
        default: false
    },
    cotizacion: {
        type: Object,
        default: null
    },
    docEmitido: {
        type: Object,
        default: null
    },
    tipoDocumento: {
        type: String,
        default: 'respuesta-arrendamiento' // 'respuesta-arrendamiento' | 'respuesta-servicios-adicionales'
    }
});

const emit = defineEmits(['update:visible', 'preview-pdf', 'saved']);
const toast = useToast();

const esServiciosAdicionales = computed(() => props.tipoDocumento === 'respuesta-servicios-adicionales');

const tituloModal = computed(() =>
    esServiciosAdicionales.value
        ? 'Respuesta a Solicitud de Cotización Servicios Adicionales'
        : 'Respuesta a Solicitud de Cotización por Arrendamiento'
);

const descripcionBanner = computed(() =>
    esServiciosAdicionales.value
        ? 'Este documento institucional responde formalmente a la solicitud de servicios adicionales, especificando el pago total en la cuenta de Recursos Propios y el 10% de garantía en la cuenta de Fondo Rotativo.'
        : 'Este documento institucional responde formalmente a la solicitud del cliente detallando las condiciones del arrendamiento, plazos de pago y cuentas bancarias oficiales antes de proceder a la confirmación de la reserva.'
);

const cargandoFormulario = ref(false);
const cargandoPreview = ref(false);
const guardando = ref(false);
const seccionEventoColapsada = ref(true);

const form = reactive({
    fecha: new Date(),
    cite: '',
    del: null,
    plazo: 'tres (3) días hábiles',
    telefono: '77240445',
    correo_electronico: 'heidy.burgoa@economiayfinanzas.gob.bo',
    hoja_ruta: '',
    moscas: 'RMRR/JLJC/HABB',
    destinatario_nombre: '',
    destinatario_cargo_institucion: '',
    evento_nombre: '',
    fechas_evento_texto: '',
    fecha_montaje_texto: '',
    fecha_desmontaje_texto: '',
    espacios_texto: '',
    prefijo_cite: 'MEFP/UCPP/ UCFCM/NE N°',
    anio: new Date().getFullYear(),
});

const formatLocalDate = (date) => {
    if (!date) return '';
    if (typeof date === 'string') {
        return date.includes('T') ? date.split('T')[0] : date;
    }
    if (date instanceof Date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }
    return '';
};

const cargarDatosFormulario = async () => {
    if (!props.cotizacion?.id) return;
    try {
        cargandoFormulario.value = true;
        const res = await documentoServices.getFormularioPorTipo(props.cotizacion.id, props.tipoDocumento);
        const data = res.data.valores_defecto;

        form.fecha = data.fecha ? new Date(data.fecha + 'T12:00:00') : new Date();
        form.cite = data.cite || '';
        form.del = data.del ? new Date(data.del + 'T12:00:00') : null;
        form.plazo = data.plazo || 'tres (3) días hábiles';
        form.telefono = data.telefono || '77240445';
        form.correo_electronico = data.correo_electronico || 'heidy.burgoa@economiayfinanzas.gob.bo';
        form.hoja_ruta = data.hoja_ruta || '';
        form.moscas = data.moscas || 'RMRR/JLJC/HABB';

        form.destinatario_nombre = data.destinatario_nombre || '';
        form.destinatario_cargo_institucion = data.destinatario_cargo_institucion || '';
        form.evento_nombre = data.evento_nombre || '';
        form.fechas_evento_texto = data.fechas_evento_texto || '';
        form.fecha_montaje_texto = data.fecha_montaje_texto || '';
        form.fecha_desmontaje_texto = data.fecha_desmontaje_texto || '';
        form.espacios_texto = data.espacios_texto || '';
        form.prefijo_cite = data.prefijo_cite || 'MEFP/UCPP/ UCFCM/NE N°';
        form.anio = form.fecha ? form.fecha.getFullYear() : new Date().getFullYear();
    } catch (e) {
        console.error('Error al cargar formulario de respuesta', e);
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'No se pudieron cargar los datos del formulario de respuesta.',
            life: 4000
        });
        emit('update:visible', false);
    } finally {
        cargandoFormulario.value = false;
    }
};

watch(
    () => props.visible,
    (newVal) => {
        if (newVal) {
            cargarDatosFormulario();
        }
    }
);

const prepararPayload = () => {
    return {
        fecha: formatLocalDate(form.fecha),
        cite: form.cite ? String(form.cite).trim() : '',
        del: form.del ? formatLocalDate(form.del) : null,
        plazo: form.plazo ? String(form.plazo).trim() : 'tres (3) días hábiles',
        telefono: form.telefono,
        correo_electronico: form.correo_electronico,
        hoja_ruta: form.hoja_ruta,
        moscas: form.moscas,
        destinatario_nombre: form.destinatario_nombre,
        destinatario_cargo_institucion: form.destinatario_cargo_institucion,
        evento_nombre: form.evento_nombre,
        fechas_evento_texto: form.fechas_evento_texto,
        fecha_montaje_texto: form.fecha_montaje_texto,
        fecha_desmontaje_texto: form.fecha_desmontaje_texto,
        espacios_texto: form.espacios_texto,
    };
};

const generarVistaPrevia = async () => {
    if (!form.cite) {
        toast.add({
            severity: 'warn',
            summary: 'CITE requerido',
            detail: 'Por favor ingrese el número correlativo de CITE para generar la vista previa.',
            life: 4000
        });
        return;
    }
    try {
        cargandoPreview.value = true;
        const payload = prepararPayload();
        const response = await documentoServices.previewPorTipo(props.cotizacion.id, props.tipoDocumento, payload);
        const blob = new Blob([response.data], { type: 'application/pdf' });
        const blobUrl = URL.createObjectURL(blob);

        emit('preview-pdf', {
            titulo: `Vista Previa: ${tituloModal.value}`,
            subtitulo: `CITE: ${form.prefijo_cite || 'MEFP/UCPP/ UCFCM/NE N°'} ${form.cite}/${form.anio}`,
            url: blobUrl
        });
    } catch (e) {
        console.error('Error al generar vista previa', e);
        const msg = e.response?.data?.message || 'No se pudo generar la vista previa del documento.';
        toast.add({ severity: 'error', summary: 'Error', detail: msg, life: 5000 });
    } finally {
        cargandoPreview.value = false;
    }
};

const guardarDocumento = async () => {
    if (!form.cite) {
        toast.add({
            severity: 'warn',
            summary: 'CITE requerido',
            detail: 'Por favor ingrese el número correlativo de CITE antes de guardar.',
            life: 4000
        });
        return;
    }
    if (!form.fecha) {
        toast.add({
            severity: 'warn',
            summary: 'Fecha requerida',
            detail: 'Por favor seleccione la fecha de la nota.',
            life: 4000
        });
        return;
    }
    try {
        guardando.value = true;
        const payload = prepararPayload();
        const res = await documentoServices.guardarPorTipo(props.cotizacion.id, props.tipoDocumento, payload);
        toast.add({
            severity: 'success',
            summary: 'Documento Emitido',
            detail: res.data?.message || 'La nota institucional fue generada y guardada con éxito.',
            life: 5000
        });
        emit('saved', res.data);
        emit('update:visible', false);
    } catch (e) {
        console.error('Error al guardar documento de respuesta', e);
        const msg = e.response?.data?.message || 'Error al guardar y emitir el documento.';
        toast.add({ severity: 'error', summary: 'Error', detail: msg, life: 5000 });
    } finally {
        guardando.value = false;
    }
};
</script>

