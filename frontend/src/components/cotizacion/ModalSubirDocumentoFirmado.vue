<template>
    <Dialog :visible="visible" @update:visible="$emit('update:visible', $event)" modal
        :style="{ width: '36rem', maxWidth: '94vw' }">
        <template #header>
            <div class="flex items-center gap-3">
                <div
                    class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 flex items-center justify-center flex-shrink-0">
                    <i class="pi pi-verified text-lg"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-surface-900 dark:text-surface-0 m-0 leading-tight">
                        Subir Documento Firmado
                    </h3>
                    <p class="text-xs text-muted-color m-0 mt-0.5">
                        {{ tituloDocumento }} · CITE: <b class="text-surface-700 dark:text-surface-200">{{
                            documento?.cite || 'S/N' }}</b>
                    </p>
                </div>
            </div>
        </template>

        <div class="space-y-4 pt-2">
            <!-- Alerta de Bloqueo Inmutable -->
            <div
                class="flex items-start gap-3 p-3.5 rounded-xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800 text-xs text-amber-900 dark:text-amber-200">
                <i class="pi pi-lock text-amber-600 dark:text-amber-400 mt-0.5 text-base flex-shrink-0"></i>
                <div class="leading-relaxed">
                    <span class="font-bold">Acción definitiva e inmutable:</span> Una vez subido el PDF escaneado con
                    las firmas y sellos correspondientes, <b>este documento quedará bloqueado permanentemente</b> y ya no
                    se podrá editar su contenido ni su CITE.
                </div>
            </div>

            <!-- Selector de archivo PDF -->
            <div
                class="p-4 rounded-xl bg-surface-50 dark:bg-surface-900/60 border border-surface-200 dark:border-surface-700 space-y-3">
                <label class="block text-xs font-bold text-surface-700 dark:text-surface-300 uppercase tracking-wider">
                    Archivo PDF Escaneado con Firmas <span class="text-red-500">*</span>
                </label>

                <input ref="fileInput" type="file" accept="application/pdf,.pdf" @change="onFileSelect"
                    class="w-full text-xs text-surface-600 dark:text-surface-300 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer" />

                <div v-if="archivoSeleccionado"
                    class="flex items-center justify-between p-2.5 rounded-lg bg-emerald-50/60 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-800 text-xs">
                    <div class="flex items-center gap-2 min-w-0">
                        <i class="pi pi-file-pdf text-emerald-600"></i>
                        <span class="font-semibold text-surface-800 dark:text-surface-200 truncate">{{
                            archivoSeleccionado.name }}</span>
                    </div>
                    <span class="text-[11px] text-muted-color flex-shrink-0 ml-2">
                        {{ (archivoSeleccionado.size / 1024 / 1024).toFixed(2) }} MB
                    </span>
                </div>

                <p class="text-[11px] text-muted-color m-0">
                    Formato admitido: únicamente <b>PDF</b> (máximo 10 MB).
                </p>
            </div>
        </div>

        <template #footer>
            <div class="flex justify-end gap-2 pt-3 border-t border-surface-200 dark:border-surface-700 w-full">
                <Button label="Cancelar" icon="pi pi-times" text severity="secondary"
                    @click="$emit('update:visible', false)" />
                <Button label="Confirmar y Bloquear Documento" icon="pi pi-lock" severity="success"
                    :disabled="!archivoSeleccionado" :loading="subiendo" @click="subirFirmado" />
            </div>
        </template>
    </Dialog>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
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
    tipoDocumento: {
        type: String,
        default: 'respuesta-arrendamiento'
    },
    documento: {
        type: Object,
        default: null
    }
});

const emit = defineEmits(['update:visible', 'saved']);
const toast = useToast();

const archivoSeleccionado = ref(null);
const fileInput = ref(null);
const subiendo = ref(false);

const tituloDocumento = computed(() =>
    props.tipoDocumento === 'respuesta-servicios-adicionales'
        ? 'Respuesta a Solicitud de Servicios Adicionales'
        : 'Respuesta a Solicitud de Cotización por Arrendamiento'
);

watch(
    () => props.visible,
    (val) => {
        if (val) {
            archivoSeleccionado.value = null;
            if (fileInput.value) fileInput.value.value = '';
        }
    }
);

const onFileSelect = (event) => {
    const file = event.target.files?.[0];
    if (!file) {
        archivoSeleccionado.value = null;
        return;
    }
    if (file.type !== 'application/pdf' && !file.name.toLowerCase().endsWith('.pdf')) {
        toast.add({
            severity: 'warn',
            summary: 'Formato inválido',
            detail: 'Solo se permiten archivos en formato PDF.',
            life: 4000
        });
        event.target.value = '';
        archivoSeleccionado.value = null;
        return;
    }
    if (file.size > 10 * 1024 * 1024) {
        toast.add({
            severity: 'warn',
            summary: 'Archivo demasiado grande',
            detail: 'El archivo PDF no debe superar los 10 MB.',
            life: 4000
        });
        event.target.value = '';
        archivoSeleccionado.value = null;
        return;
    }
    archivoSeleccionado.value = file;
};

const subirFirmado = async () => {
    if (!archivoSeleccionado.value || !props.cotizacion?.id) return;

    try {
        subiendo.value = true;
        const formData = new FormData();
        formData.append('pdf_firmado', archivoSeleccionado.value);

        const res = await documentoServices.subirFirmadoPorTipo(
            props.cotizacion.id,
            props.tipoDocumento,
            formData
        );

        toast.add({
            severity: 'success',
            summary: 'Documento Firmado Registrado',
            detail: res.data?.message || 'El PDF firmado fue adjuntado y la edición del documento ha quedado bloqueada.',
            life: 5000
        });

        emit('saved', res.data);
        emit('update:visible', false);
    } catch (e) {
        console.error('Error al subir documento firmado', e);
        const msg = e.response?.data?.message || 'No se pudo adjuntar el documento firmado.';
        toast.add({ severity: 'error', summary: 'Error', detail: msg, life: 5000 });
    } finally {
        subiendo.value = false;
    }
};
</script>
