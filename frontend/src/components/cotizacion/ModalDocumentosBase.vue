<template>
    <Dialog :visible="visible" @update:visible="$emit('update:visible', $event)"
        header="Documentos Base de la Cotización" modal :style="{ width: '38rem' }"
        :breakpoints="{ '960px': '75vw', '640px': '95vw' }">
        <div class="space-y-4 pt-2">
            <p class="text-xs text-surface-600 dark:text-surface-400 m-0">
                Adjunte o actualice la Solicitud de Arrendamiento y la Verificación de Habilitación en formato PDF.
            </p>

            <!-- Campo 1: Solicitud de Arrendamiento -->
            <div
                class="border border-surface-200 dark:border-surface-700 rounded-xl p-3 bg-surface-50 dark:bg-surface-900/40">
                <label class="block text-xs font-bold text-surface-900 dark:text-surface-0 uppercase tracking-wider mb-1">
                    Solicitud de Arrendamiento (PDF)
                </label>
                <p class="text-[11px] text-muted-color mb-2">Borrador de fechas, espacios y servicios firmado por el
                    cliente.</p>
                <div v-if="cotizacion?.solicitud_arrendamiento_path"
                    class="mb-2 text-xs text-emerald-600 flex items-center gap-1 font-medium">
                    <i class="pi pi-check-circle"></i>
                    <span>Archivo actual registrado en el expediente.</span>
                </div>
                <input type="file" accept="application/pdf,.pdf" @change="onSolicitudChange"
                    class="w-full text-xs text-surface-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer" />
            </div>

            <!-- Campo 2: Verificación de Habilitación -->
            <div
                class="border border-surface-200 dark:border-surface-700 rounded-xl p-3 bg-surface-50 dark:bg-surface-900/40">
                <label class="block text-xs font-bold text-surface-900 dark:text-surface-0 uppercase tracking-wider mb-1">
                    Verificación de Habilitación (PDF)
                </label>
                <p class="text-[11px] text-muted-color mb-2">Certificación administrativa de habilitación y buen
                    expediente.</p>
                <div v-if="cotizacion?.verificacion_habilitacion_path"
                    class="mb-2 text-xs text-teal-600 flex items-center gap-1 font-medium">
                    <i class="pi pi-check-circle"></i>
                    <span>Archivo actual registrado en el expediente.</span>
                </div>
                <input type="file" accept="application/pdf,.pdf" @change="onHabilitacionChange"
                    class="w-full text-xs text-surface-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100 cursor-pointer" />
            </div>
        </div>

        <template #footer>
            <div class="flex justify-end gap-2 pt-3">
                <Button label="Cancelar" icon="pi pi-times" text severity="secondary"
                    @click="$emit('update:visible', false)" />
                <Button label="Guardar Documentos" icon="pi pi-check" :loading="guardando"
                    :disabled="!archivoSolicitud && !archivoHabilitacion" @click="guardarDocumentos" />
            </div>
        </template>
    </Dialog>
</template>

<script setup>
import { ref, watch } from 'vue';
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
    }
});

const emit = defineEmits(['update:visible', 'saved']);
const toast = useToast();

const guardando = ref(false);
const archivoSolicitud = ref(null);
const archivoHabilitacion = ref(null);

watch(
    () => props.visible,
    (newVal) => {
        if (newVal) {
            archivoSolicitud.value = null;
            archivoHabilitacion.value = null;
        }
    }
);

const validarArchivoPdf = (file, event) => {
    if (!file) return null;
    if (file.type !== 'application/pdf' && !file.name.toLowerCase().endsWith('.pdf')) {
        toast.add({ severity: 'error', summary: 'Archivo no válido', detail: 'Debe ser un archivo PDF.', life: 4000 });
        event.target.value = '';
        return null;
    }
    if (file.size > 10 * 1024 * 1024) {
        toast.add({ severity: 'error', summary: 'Archivo muy grande', detail: 'El archivo excede el límite de 10 MB.', life: 4000 });
        event.target.value = '';
        return null;
    }
    return file;
};

const onSolicitudChange = (event) => {
    archivoSolicitud.value = validarArchivoPdf(event.target.files?.[0], event);
};

const onHabilitacionChange = (event) => {
    archivoHabilitacion.value = validarArchivoPdf(event.target.files?.[0], event);
};

const guardarDocumentos = async () => {
    if (!props.cotizacion?.id || (!archivoSolicitud.value && !archivoHabilitacion.value)) return;
    try {
        guardando.value = true;
        const formData = new FormData();
        if (archivoSolicitud.value) {
            formData.append('solicitud_arrendamiento', archivoSolicitud.value);
        }
        if (archivoHabilitacion.value) {
            formData.append('verificacion_habilitacion', archivoHabilitacion.value);
        }

        const res = await documentoServices.subirDocumentosBase(props.cotizacion.id, formData);

        toast.add({
            severity: 'success',
            summary: 'Documentos guardados',
            detail: res.data?.message || 'Los documentos base fueron actualizados correctamente.',
            life: 5000
        });

        emit('saved', res.data);
        emit('update:visible', false);
    } catch (e) {
        console.error('Error al guardar documentos base', e);
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: e.response?.data?.message || 'No se pudieron guardar los documentos.',
            life: 5000
        });
    } finally {
        guardando.value = false;
    }
};
</script>
