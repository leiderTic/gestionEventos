<script setup>
import { ref, watch, computed } from 'vue';
import espacioServices from '@/services/espacio.services';
import { useToast } from 'primevue/usetoast';

const props = defineProps({
    visible: {
        type: Boolean,
        default: false
    },
    espacio: {
        type: Object,
        default: null
    },
    bloques: {
        type: Array,
        default: () => []
    },
    tiposEspacio: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['update:visible', 'saved']);

const toast = useToast();
const saving = ref(false);
const errors = ref({});

const esEdicion = computed(() => !!(props.espacio && props.espacio.id));

const form = ref({
    id: null,
    nombre: '',
    descripcion: '',
    dias_max: null,
    area_m2: 0,
    aforo: 0,
    tipo_espacio_id: null,
    bloque_id: null
});

function inicializarFormulario() {
    errors.value = {};

    if (props.espacio && props.espacio.id) {
        form.value = {
            id: props.espacio.id,
            nombre: props.espacio.nombre || '',
            descripcion: props.espacio.descripcion || '',
            dias_max: props.espacio.dias_max !== null ? Number(props.espacio.dias_max) : null,
            area_m2: Number(props.espacio.area_m2 || 0),
            aforo: Number(props.espacio.aforo || 0),
            tipo_espacio_id: props.espacio.tipo_espacio_id || props.espacio.tipo_espacio?.id || null,
            bloque_id: props.espacio.bloque_id || props.espacio.bloque?.id || null
        };
    } else {
        form.value = {
            id: null,
            nombre: '',
            descripcion: '',
            dias_max: null,
            area_m2: 0,
            aforo: 0,
            tipo_espacio_id: props.tiposEspacio[0]?.id || null,
            bloque_id: props.bloques[0]?.id || null
        };
    }
}

watch(
    () => props.visible,
    (val) => {
        if (val) {
            inicializarFormulario();
        }
    }
);

function cerrar() {
    emit('update:visible', false);
}

async function guardar() {
    errors.value = {};

    if (!form.value.nombre.trim()) {
        errors.value.nombre = ['Ingrese el nombre del espacio.'];
        return;
    }
    if (!form.value.tipo_espacio_id) {
        errors.value.tipo_espacio_id = ['Seleccione un tipo.'];
        return;
    }
    if (!form.value.bloque_id) {
        errors.value.bloque_id = ['Seleccione un bloque.'];
        return;
    }
    if (!form.value.descripcion.trim()) {
        errors.value.descripcion = ['Indique qué incluye el espacio.'];
        return;
    }

    const payload = {
        nombre: form.value.nombre.trim(),
        descripcion: form.value.descripcion.trim(),
        dias_max: Number(form.value.dias_max || 0),
        area_m2: Number(form.value.area_m2 || 0),
        aforo: Number(form.value.aforo || 0),
        tipo_espacio_id: form.value.tipo_espacio_id,
        bloque_id: form.value.bloque_id
    };

    saving.value = true;
    try {
        let resp;
        if (esEdicion.value) {
            resp = await espacioServices.actualizar(form.value.id, payload);
            toast.add({
                severity: 'success',
                summary: 'Actualizado',
                detail: `Se guardaron los datos de ${payload.nombre}.`,
                life: 3000
            });
        } else {
            resp = await espacioServices.guardar(payload);
            toast.add({
                severity: 'success',
                summary: 'Registrado',
                detail: `Espacio ${payload.nombre} creado.`,
                life: 3000
            });
        }
        emit('saved', { espacio: resp.data, esNuevo: !esEdicion.value });
        cerrar();
    } catch (e) {
        if (e.response?.status === 422 && e.response.data?.errors) {
            errors.value = e.response.data.errors;
        } else {
            toast.add({
                severity: 'error',
                summary: 'Error',
                detail: e.response?.data?.message || 'No se pudo guardar el espacio.',
                life: 3500
            });
        }
    } finally {
        saving.value = false;
    }
}
</script>

<template>
    <Dialog
        :visible="visible"
        @update:visible="(val) => emit('update:visible', val)"
        modal
        :header="esEdicion ? 'Editar espacio' : 'Registrar espacio'"
        :style="{ width: '540px' }"
        :breakpoints="{ '640px': '95vw' }"
    >
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 pt-1">
            <div class="md:col-span-12">
                <label class="block text-sm font-medium text-surface-700 dark:text-surface-200 mb-1">
                    Nombre del espacio *
                </label>
                <InputText
                    v-model="form.nombre"
                    placeholder="Ej. Salón Illimani"
                    class="w-full"
                    :invalid="!!errors.nombre"
                />
                <small v-if="errors.nombre" class="text-red-500 text-xs">{{ errors.nombre[0] }}</small>
            </div>

            <div class="md:col-span-6">
                <label class="block text-sm font-medium text-surface-700 dark:text-surface-200 mb-1">
                    Tipo de espacio *
                </label>
                <Select
                    v-model="form.tipo_espacio_id"
                    :options="tiposEspacio"
                    optionLabel="nombre"
                    optionValue="id"
                    placeholder="Seleccionar"
                    class="w-full"
                    :invalid="!!errors.tipo_espacio_id"
                />
                <small v-if="errors.tipo_espacio_id" class="text-red-500 text-xs">{{ errors.tipo_espacio_id[0] }}</small>
            </div>

            <div class="md:col-span-6">
                <label class="block text-sm font-medium text-surface-700 dark:text-surface-200 mb-1">
                    Bloque *
                </label>
                <Select
                    v-model="form.bloque_id"
                    :options="bloques"
                    optionLabel="descripcion"
                    optionValue="id"
                    placeholder="Seleccionar"
                    class="w-full"
                    :invalid="!!errors.bloque_id"
                >
                    <template #option="slotProps">
                        <div class="flex items-center gap-2">
                            <span
                                class="w-2.5 h-2.5 rounded-full inline-block"
                                :style="{ backgroundColor: slotProps.option.color || '#64748b' }"
                            ></span>
                            <span>{{ slotProps.option.descripcion }}</span>
                        </div>
                    </template>
                </Select>
                <small v-if="errors.bloque_id" class="text-red-500 text-xs">{{ errors.bloque_id[0] }}</small>
            </div>

            <div class="md:col-span-4">
                <label class="block text-sm font-medium text-surface-700 dark:text-surface-200 mb-1">
                    Superficie (m²) *
                </label>
                <InputNumber
                    v-model="form.area_m2"
                    suffix=" m²"
                    :min="0"
                    :maxFractionDigits="2"
                    class="w-full"
                    inputClass="w-full"
                />
            </div>

            <div class="md:col-span-4">
                <label class="block text-sm font-medium text-surface-700 dark:text-surface-200 mb-1">
                    Aforo (personas) *
                </label>
                <InputNumber
                    v-model="form.aforo"
                    :min="0"
                    :useGrouping="true"
                    class="w-full"
                    inputClass="w-full"
                />
            </div>

            <div class="md:col-span-4">
                <label class="block text-sm font-medium text-surface-700 dark:text-surface-200 mb-1">
                    Días máx.
                </label>
                <InputNumber
                    v-model="form.dias_max"
                    placeholder="Opcional"
                    :min="1"
                    showClear
                    class="w-full"
                    inputClass="w-full"
                />
            </div>

            <div class="md:col-span-12">
                <label class="block text-sm font-medium text-surface-700 dark:text-surface-200 mb-1">
                    Descripción / Incluye *
                </label>
                <Textarea
                    v-model="form.descripcion"
                    rows="3"
                    placeholder="Detalle qué ambientes o equipamiento incluye este espacio..."
                    class="w-full"
                    :invalid="!!errors.descripcion"
                />
                <small v-if="errors.descripcion" class="text-red-500 text-xs">{{ errors.descripcion[0] }}</small>
            </div>
        </div>

        <template #footer>
            <div class="flex justify-end gap-2">
                <Button
                    label="Cancelar"
                    severity="secondary"
                    text
                    :disabled="saving"
                    @click="cerrar"
                />
                <Button
                    :label="esEdicion ? 'Guardar cambios' : 'Guardar'"
                    icon="pi pi-check"
                    :loading="saving"
                    @click="guardar"
                />
            </div>
        </template>
    </Dialog>
</template>
