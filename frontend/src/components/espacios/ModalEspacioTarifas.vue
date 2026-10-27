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
const matrizPrecios = ref({});

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

function cargarMatriz() {
    const nuevaMatriz = {};

    props.eventos.forEach((ev) => {
        props.temporadas.forEach((temp) => {
            nuevaMatriz[`${ev.id}_${temp.id}`] = 0;
        });
    });

    if (props.espacio && Array.isArray(props.espacio.tarifas)) {
        props.espacio.tarifas.forEach((t) => {
            nuevaMatriz[`${t.evento_id}_${t.temporada_id}`] = Number(t.precio_dia || 0);
        });
    }

    matrizPrecios.value = nuevaMatriz;
}

watch(
    () => props.visible,
    (val) => {
        if (val) {
            cargarMatriz();
        }
    }
);

function cerrar() {
    emit('update:visible', false);
}

async function guardarTarifas() {
    if (!props.espacio?.id) return;

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

    saving.value = true;
    try {
        const { data } = await espacioServices.actualizarTarifas(props.espacio.id, tarifasPayload);
        toast.add({
            severity: 'success',
            summary: 'Costos guardados',
            detail: `Tarifario de ${props.espacio.nombre} actualizado.`,
            life: 3000
        });
        emit('saved', data);
        cerrar();
    } catch (e) {
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: e.response?.data?.message || 'No se pudieron guardar los costos.',
            life: 3500
        });
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
        header="Asignación de costos por día"
        :style="{ width: '640px' }"
        :breakpoints="{ '640px': '95vw' }"
    >
        <div v-if="espacio" class="flex flex-col gap-4 pt-1">
            <div class="flex items-center justify-between py-2.5 px-3.5 rounded-lg bg-surface-50 dark:bg-surface-800 border border-surface-200 dark:border-surface-700">
                <div>
                    <div class="font-bold text-surface-900 dark:text-surface-0">{{ espacio.nombre }}</div>
                    <div class="text-xs text-surface-500">
                        {{ espacio.tipo_espacio?.nombre || 'Espacio' }} · Aforo {{ Number(espacio.aforo || 0).toLocaleString('es-BO') }} pers. · {{ espacio.area_m2 }} m²
                    </div>
                </div>
                <span
                    v-if="espacio.bloque"
                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded text-xs font-semibold border border-surface-200 dark:border-surface-700 bg-white dark:bg-surface-900"
                >
                    <span
                        class="w-2 h-2 rounded-full"
                        :style="{ backgroundColor: espacio.bloque.color || '#64748b' }"
                    ></span>
                    Bloque {{ espacio.bloque.descripcion }}
                </span>
            </div>

            <div class="border border-surface-200 dark:border-surface-700 rounded-lg overflow-hidden">
                <table class="w-full text-sm text-left">
                    <thead class="bg-surface-100 dark:bg-surface-800 text-surface-700 dark:text-surface-200 text-xs font-semibold">
                        <tr>
                            <th class="py-2.5 px-3 border-b border-surface-200 dark:border-surface-700">
                                Tipo de evento
                            </th>
                            <th class="py-2.5 px-3 border-b border-surface-200 dark:border-surface-700 w-44">
                                {{ temporadaBaja.descripcion }} (Bs)
                            </th>
                            <th class="py-2.5 px-3 border-b border-surface-200 dark:border-surface-700 w-44">
                                {{ temporadaAlta.descripcion }} (Bs)
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-200 dark:divide-surface-700">
                        <tr v-for="ev in eventos" :key="ev.id">
                            <td class="py-2.5 px-3 font-medium text-surface-800 dark:text-surface-100">
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
                                    inputClass="w-full text-right"
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
                                    inputClass="w-full text-right"
                                />
                            </td>
                        </tr>
                    </tbody>
                </table>
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
                    label="Guardar costos"
                    icon="pi pi-check"
                    :loading="saving"
                    @click="guardarTarifas"
                />
            </div>
        </template>
    </Dialog>
</template>
