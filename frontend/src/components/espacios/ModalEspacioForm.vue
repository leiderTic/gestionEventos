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
    },
    eventos: {
        type: Array,
        default: () => []
    },
    temporadas: {
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

// Matriz de precios indexada por `${evento_id}_${temporada_id}` -> precio_dia
const matrizPrecios = ref({});

// Identificar temporada baja (id=1 o que contenga 'baja') y temporada alta (id=2 o que contenga 'alta')
const temporadaBaja = computed(() => {
    return (
        props.temporadas.find((t) => t.descripcion?.toLowerCase().includes('baja')) ||
        props.temporadas[0] || { id: 1, descripcion: 'Temporada baja' }
    );
});

const temporadaAlta = computed(() => {
    return (
        props.temporadas.find((t) => t.descripcion?.toLowerCase().includes('alta')) ||
        props.temporadas[1] || { id: 2, descripcion: 'Temporada alta' }
    );
});

function inicializarFormulario() {
    errors.value = {};
    const nuevaMatriz = {};

    // Inicializar todas las combinaciones evento x temporada en 0
    props.eventos.forEach((ev) => {
        props.temporadas.forEach((temp) => {
            nuevaMatriz[`${ev.id}_${temp.id}`] = 0;
        });
    });

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

        // Poblar tarifas existentes
        if (Array.isArray(props.espacio.tarifas)) {
            props.espacio.tarifas.forEach((t) => {
                const key = `${t.evento_id}_${t.temporada_id}`;
                nuevaMatriz[key] = Number(t.precio_dia || 0);
            });
        }
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

    matrizPrecios.value = nuevaMatriz;
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

function copiarBajaEnAlta() {
    const idBaja = temporadaBaja.value.id;
    const idAlta = temporadaAlta.value.id;
    props.eventos.forEach((ev) => {
        const valorBaja = matrizPrecios.value[`${ev.id}_${idBaja}`] || 0;
        matrizPrecios.value[`${ev.id}_${idAlta}`] = valorBaja;
    });
}

async function guardar() {
    errors.value = {};

    if (!form.value.nombre.trim()) {
        errors.value.nombre = ['El nombre del espacio es obligatorio.'];
        return;
    }
    if (!form.value.tipo_espacio_id) {
        errors.value.tipo_espacio_id = ['Seleccione el tipo de espacio.'];
        return;
    }
    if (!form.value.bloque_id) {
        errors.value.bloque_id = ['Seleccione el bloque al que pertenece.'];
        return;
    }
    if (!form.value.descripcion.trim()) {
        errors.value.descripcion = ['Detalle qué incluye el espacio (descripción).'];
        return;
    }

    // Construir arreglo plano de tarifas igual que el seeder
    const tarifasPayload = [];
    props.eventos.forEach((ev) => {
        props.temporadas.forEach((temp) => {
            const key = `${ev.id}_${temp.id}`;
            const precio = Number(matrizPrecios.value[key] ?? 0);
            tarifasPayload.push({
                evento_id: ev.id,
                temporada_id: temp.id,
                precio_dia: precio >= 0 ? precio : 0
            });
        });
    });

    const payload = {
        nombre: form.value.nombre.trim(),
        descripcion: form.value.descripcion.trim(),
        dias_max: form.value.dias_max || null,
        area_m2: Number(form.value.area_m2 || 0),
        aforo: Number(form.value.aforo || 0),
        tipo_espacio_id: form.value.tipo_espacio_id,
        bloque_id: form.value.bloque_id,
        tarifas: tarifasPayload
    };

    saving.value = true;
    try {
        let resp;
        if (esEdicion.value) {
            resp = await espacioServices.actualizar(form.value.id, payload);
            toast.add({
                severity: 'success',
                summary: 'Espacio actualizado',
                detail: `Se actualizó "${payload.nombre}" y su tarifario correctamente.`,
                life: 3500
            });
        } else {
            resp = await espacioServices.guardar(payload);
            toast.add({
                severity: 'success',
                summary: 'Espacio creado',
                detail: `Se registró "${payload.nombre}" con sus tarifas iniciales.`,
                life: 3500
            });
        }
        emit('saved', resp.data);
        cerrar();
    } catch (e) {
        if (e.response?.status === 422 && e.response.data?.errors) {
            errors.value = e.response.data.errors;
        } else {
            toast.add({
                severity: 'error',
                summary: 'Error al guardar',
                detail: e.response?.data?.message || 'Ocurrió un problema al guardar el espacio.',
                life: 4000
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
        :header="esEdicion ? 'Editar Espacio y Tarifario' : 'Nuevo Espacio y Tarifario Inicial'"
        :style="{ width: '780px' }"
        :breakpoints="{ '960px': '92vw', '640px': '98vw' }"
    >
        <div class="flex flex-col gap-5 pt-2">
            <!-- SECCIÓN 1: DATOS FÍSICOS Y CLASIFICACIÓN DEL ESPACIO -->
            <div class="bg-surface-50 dark:bg-surface-800/50 border border-surface-200 dark:border-surface-700 rounded-xl p-4">
                <div class="flex items-center gap-2 mb-3">
                    <i class="pi pi-building text-primary"></i>
                    <span class="font-bold text-sm uppercase tracking-wider text-surface-700 dark:text-surface-200">
                        1. Datos Generales del Espacio
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                    <!-- Nombre -->
                    <div class="md:col-span-6">
                        <label class="block text-xs font-bold text-surface-600 dark:text-surface-300 mb-1">
                            Nombre del Espacio *
                        </label>
                        <InputText
                            v-model="form.nombre"
                            placeholder="Ej: Salón Illimani, Plaza Bicentenario..."
                            class="w-full"
                            :invalid="!!errors.nombre"
                        />
                        <small v-if="errors.nombre" class="text-red-500 text-xs">{{ errors.nombre[0] }}</small>
                    </div>

                    <!-- Tipo de Espacio -->
                    <div class="md:col-span-3">
                        <label class="block text-xs font-bold text-surface-600 dark:text-surface-300 mb-1">
                            Tipo de Espacio *
                        </label>
                        <Select
                            v-model="form.tipo_espacio_id"
                            :options="tiposEspacio"
                            optionLabel="nombre"
                            optionValue="id"
                            placeholder="Seleccione..."
                            class="w-full"
                            :invalid="!!errors.tipo_espacio_id"
                        />
                        <small v-if="errors.tipo_espacio_id" class="text-red-500 text-xs">{{ errors.tipo_espacio_id[0] }}</small>
                    </div>

                    <!-- Bloque -->
                    <div class="md:col-span-3">
                        <label class="block text-xs font-bold text-surface-600 dark:text-surface-300 mb-1">
                            Bloque *
                        </label>
                        <Select
                            v-model="form.bloque_id"
                            :options="bloques"
                            optionLabel="descripcion"
                            optionValue="id"
                            placeholder="Bloque..."
                            class="w-full"
                            :invalid="!!errors.bloque_id"
                        >
                            <template #option="slotProps">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="w-3 h-3 rounded-full inline-block border border-black/10"
                                        :style="{ backgroundColor: slotProps.option.color || '#64748b' }"
                                    ></span>
                                    <span>{{ slotProps.option.descripcion }}</span>
                                </div>
                            </template>
                        </Select>
                        <small v-if="errors.bloque_id" class="text-red-500 text-xs">{{ errors.bloque_id[0] }}</small>
                    </div>

                    <!-- Área m2 -->
                    <div class="md:col-span-4">
                        <label class="block text-xs font-bold text-surface-600 dark:text-surface-300 mb-1">
                            Área Superficie (m²) *
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

                    <!-- Aforo -->
                    <div class="md:col-span-4">
                        <label class="block text-xs font-bold text-surface-600 dark:text-surface-300 mb-1">
                            Aforo Máximo (Personas) *
                        </label>
                        <InputNumber
                            v-model="form.aforo"
                            suffix=" pers."
                            :min="0"
                            :useGrouping="true"
                            class="w-full"
                            inputClass="w-full"
                        />
                    </div>

                    <!-- Días Máximos -->
                    <div class="md:col-span-4">
                        <label class="block text-xs font-bold text-surface-600 dark:text-surface-300 mb-1">
                            Días Máx. Alquiler (Opcional)
                        </label>
                        <InputNumber
                            v-model="form.dias_max"
                            placeholder="Sin límite"
                            suffix=" días"
                            :min="1"
                            showClear
                            class="w-full"
                            inputClass="w-full"
                        />
                    </div>

                    <!-- Descripción / Incluye -->
                    <div class="md:col-span-12">
                        <label class="block text-xs font-bold text-surface-600 dark:text-surface-300 mb-1">
                            ¿Qué incluye el espacio? (Descripción para Cotización y PDF) *
                        </label>
                        <Textarea
                            v-model="form.descripcion"
                            rows="2"
                            placeholder="Ej: Incluye escenario, camerinos, iluminación básica, limpieza y seguridad en áreas comunes..."
                            class="w-full"
                            :invalid="!!errors.descripcion"
                        />
                        <small v-if="errors.descripcion" class="text-red-500 text-xs">{{ errors.descripcion[0] }}</small>
                    </div>
                </div>
            </div>

            <!-- SECCIÓN 2: MATRIZ DE TARIFAS (TEMPORADA BAJA Y ALTA POR EVENTO) -->
            <div class="bg-surface-50 dark:bg-surface-800/50 border border-surface-200 dark:border-surface-700 rounded-xl p-4">
                <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                    <div class="flex items-center gap-2">
                        <i class="pi pi-dollar text-emerald-600"></i>
                        <div>
                            <span class="font-bold text-sm uppercase tracking-wider text-surface-700 dark:text-surface-200 block">
                                2. Matriz de Tarifas por Tipo de Evento (Bs / Día)
                            </span>
                            <span class="text-xs text-surface-500">
                                Define el precio diario en Temporada Baja y Temporada Alta para cada categoría de evento.
                            </span>
                        </div>
                    </div>
                    <Button
                        type="button"
                        label="Copiar Baja → Alta"
                        icon="pi pi-copy"
                        size="small"
                        severity="secondary"
                        outlined
                        @click="copiarBajaEnAlta"
                        v-tooltip.top="'Copia los montos de Temporada Baja hacia Temporada Alta'"
                    />
                </div>

                <div class="overflow-x-auto border border-surface-200 dark:border-surface-700 rounded-lg bg-white dark:bg-surface-900">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-surface-100 dark:bg-surface-800 text-surface-700 dark:text-surface-200 text-xs uppercase font-bold">
                            <tr>
                                <th class="py-2.5 px-3 border-b border-surface-200 dark:border-surface-700">
                                    Tipo de Evento
                                </th>
                                <th class="py-2.5 px-3 border-b border-surface-200 dark:border-surface-700 w-48">
                                    <div class="flex items-center gap-1.5 text-sky-700 dark:text-sky-400">
                                        <i class="pi pi-sun"></i>
                                        <span>{{ temporadaBaja.descripcion }} (Bs/día)</span>
                                    </div>
                                </th>
                                <th class="py-2.5 px-3 border-b border-surface-200 dark:border-surface-700 w-48">
                                    <div class="flex items-center gap-1.5 text-amber-700 dark:text-amber-400">
                                        <i class="pi pi-bolt"></i>
                                        <span>{{ temporadaAlta.descripcion }} (Bs/día)</span>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-surface-200 dark:divide-surface-700">
                            <tr
                                v-for="ev in eventos"
                                :key="ev.id"
                                class="hover:bg-surface-50 dark:hover:bg-surface-800/40 transition-colors"
                            >
                                <td class="py-2.5 px-3 font-semibold text-surface-800 dark:text-surface-100">
                                    {{ ev.descripcion }}
                                </td>
                                <td class="py-2 px-3">
                                    <InputNumber
                                        v-model="matrizPrecios[`${ev.id}_${temporadaBaja.id}`]"
                                        mode="currency"
                                        currency="BOB"
                                        locale="es-BO"
                                        :min="0"
                                        class="w-full"
                                        inputClass="w-full text-right font-semibold"
                                    />
                                </td>
                                <td class="py-2 px-3">
                                    <InputNumber
                                        v-model="matrizPrecios[`${ev.id}_${temporadaAlta.id}`]"
                                        mode="currency"
                                        currency="BOB"
                                        locale="es-BO"
                                        :min="0"
                                        class="w-full"
                                        inputClass="w-full text-right font-semibold"
                                    />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <template #footer>
            <div class="flex justify-end gap-2 pt-2">
                <Button
                    label="Cancelar"
                    icon="pi pi-times"
                    severity="secondary"
                    text
                    :disabled="saving"
                    @click="cerrar"
                />
                <Button
                    :label="esEdicion ? 'Guardar Cambios' : 'Crear Espacio y Tarifas'"
                    icon="pi pi-check"
                    :loading="saving"
                    @click="guardar"
                />
            </div>
        </template>
    </Dialog>
</template>
