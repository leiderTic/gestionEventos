<script setup>
import { ref, onMounted, computed } from 'vue';
import espacioServices from '@/services/espacio.services';
import bloqueServices from '@/services/bloque.services';
import tipoEspacioServices from '@/services/tipoespacio.services';
import eventoServices from '@/services/evento.services';
import temporadaServices from '@/services/temporada.services';
import ModalEspacioForm from '@/components/espacios/ModalEspacioForm.vue';
import { useToast } from 'primevue/usetoast';
import { useConfirm } from 'primevue/useconfirm';

const toast = useToast();
const confirm = useConfirm();

const espacios = ref([]);
const bloques = ref([]);
const tiposEspacio = ref([]);
const eventos = ref([]);
const temporadas = ref([]);

const loading = ref(false);
const filtroTexto = ref('');
const filtroBloque = ref(null);
const filtroTipo = ref(null);

// Estado del modal modular
const modalVisible = ref(false);
const espacioSeleccionado = ref(null);

const temporadaBaja = computed(() => {
    return (
        temporadas.value.find((t) => t.descripcion?.toLowerCase().includes('baja')) ||
        temporadas.value[0] || { id: 1, descripcion: 'Temporada baja' }
    );
});

const temporadaAlta = computed(() => {
    return (
        temporadas.value.find((t) => t.descripcion?.toLowerCase().includes('alta')) ||
        temporadas.value[1] || { id: 2, descripcion: 'Temporada alta' }
    );
});

const espaciosFiltrados = computed(() => {
    return espacios.value.filter((esp) => {
        const q = filtroTexto.value.trim().toLowerCase();
        const matchTexto =
            !q ||
            esp.nombre?.toLowerCase().includes(q) ||
            esp.descripcion?.toLowerCase().includes(q) ||
            esp.bloque?.descripcion?.toLowerCase().includes(q) ||
            esp.tipo_espacio?.nombre?.toLowerCase().includes(q);

        const matchBloque = !filtroBloque.value || esp.bloque_id === filtroBloque.value;
        const matchTipo = !filtroTipo.value || esp.tipo_espacio_id === filtroTipo.value;

        return matchTexto && matchBloque && matchTipo;
    });
});

onMounted(() => {
    cargarTodo();
});

async function cargarTodo() {
    loading.value = true;
    try {
        const [resEspacios, resBloques, resTipos, resEventos, resTemporadas] = await Promise.all([
            espacioServices.listar(),
            bloqueServices.listar(),
            tipoEspacioServices.listar(),
            eventoServices.listar(),
            temporadaServices.listar()
        ]);
        espacios.value = resEspacios.data || [];
        bloques.value = resBloques.data || [];
        tiposEspacio.value = resTipos.data || [];
        eventos.value = resEventos.data || [];
        temporadas.value = resTemporadas.data || [];
    } catch (e) {
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'No se pudieron cargar los catálogos de espacios.',
            life: 3500
        });
    } finally {
        loading.value = false;
    }
}

function abrirCrear() {
    espacioSeleccionado.value = null;
    modalVisible.value = true;
}

function abrirEditar(espacio) {
    espacioSeleccionado.value = espacio;
    modalVisible.value = true;
}

function onEspacioGuardado() {
    cargarTodo();
}

function confirmarEliminar(espacio) {
    confirm.require({
        message: `¿Está seguro de eliminar el espacio "${espacio.nombre}"?`,
        header: 'Confirmar Eliminación',
        icon: 'pi pi-exclamation-triangle',
        rejectProps: {
            label: 'Cancelar',
            severity: 'secondary',
            outlined: true
        },
        acceptProps: {
            label: 'Sí, Eliminar',
            severity: 'danger'
        },
        accept: async () => {
            try {
                await espacioServices.eliminar(espacio.id);
                toast.add({
                    severity: 'success',
                    summary: 'Eliminado',
                    detail: `El espacio "${espacio.nombre}" fue eliminado.`,
                    life: 3000
                });
                cargarTodo();
            } catch (e) {
                toast.add({
                    severity: 'error',
                    summary: 'No se pudo eliminar',
                    detail: e.response?.data?.message || 'Error al eliminar el espacio.',
                    life: 4000
                });
            }
        }
    });
}

function obtenerTarifa(espacio, eventoId, temporadaId) {
    if (!Array.isArray(espacio.tarifas)) return 0;
    const encontrada = espacio.tarifas.find(
        (t) => Number(t.evento_id) === Number(eventoId) && Number(t.temporada_id) === Number(temporadaId)
    );
    return encontrada ? Number(encontrada.precio_dia) : 0;
}

function formatMoneda(valor) {
    const num = Number(valor || 0);
    return num.toLocaleString('es-BO', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
}

function limpiarFiltros() {
    filtroTexto.value = '';
    filtroBloque.value = null;
    filtroTipo.value = null;
}
</script>

<template>
    <div class="flex flex-col gap-6">
        <ConfirmDialog />

        <!-- ENCABEZADO Y MÉTRICAS RÁPIDAS -->
        <div class="card !mb-0">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2.5">
                        <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                            <i class="pi pi-building text-xl"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-surface-900 dark:text-surface-0 m-0">
                                Gestión de Espacios y Tarifarios
                            </h2>
                            <p class="text-sm text-surface-500 m-0">
                                Administra los salones, plazas, teatros, capacidades y matriz de precios por temporada y tipo de evento.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <Button
                        label="Actualizar"
                        icon="pi pi-refresh"
                        severity="secondary"
                        outlined
                        :loading="loading"
                        @click="cargarTodo"
                    />
                    <Button
                        label="Nuevo Espacio"
                        icon="pi pi-plus"
                        @click="abrirCrear"
                    />
                </div>
            </div>

            <!-- FILTROS -->
            <div class="grid grid-cols-1 md:grid-cols-12 gap-3 mt-5 pt-4 border-t border-surface-200 dark:border-surface-700">
                <div class="md:col-span-5">
                    <IconField>
                        <InputIcon class="pi pi-search" />
                        <InputText
                            v-model="filtroTexto"
                            placeholder="Buscar por nombre, descripción o bloque..."
                            class="w-full"
                        />
                    </IconField>
                </div>

                <div class="md:col-span-3">
                    <Select
                        v-model="filtroBloque"
                        :options="bloques"
                        optionLabel="descripcion"
                        optionValue="id"
                        placeholder="Todos los Bloques"
                        showClear
                        class="w-full"
                    >
                        <template #option="slotProps">
                            <div class="flex items-center gap-2">
                                <span
                                    class="w-3 h-3 rounded-full inline-block"
                                    :style="{ backgroundColor: slotProps.option.color || '#64748b' }"
                                ></span>
                                <span>Bloque {{ slotProps.option.descripcion }}</span>
                            </div>
                        </template>
                    </Select>
                </div>

                <div class="md:col-span-3">
                    <Select
                        v-model="filtroTipo"
                        :options="tiposEspacio"
                        optionLabel="nombre"
                        optionValue="id"
                        placeholder="Todos los Tipos"
                        showClear
                        class="w-full"
                    />
                </div>

                <div class="md:col-span-1 flex justify-end">
                    <Button
                        icon="pi pi-filter-slash"
                        severity="secondary"
                        outlined
                        class="w-full"
                        v-tooltip.top="'Limpiar filtros'"
                        @click="limpiarFiltros"
                    />
                </div>
            </div>
        </div>

        <!-- TABLA DE ESPACIOS -->
        <div class="card !mb-0">
            <DataTable
                :value="espaciosFiltrados"
                :loading="loading"
                dataKey="id"
                paginator
                :rows="10"
                :rowsPerPageOptions="[10, 20, 50]"
                stripedRows
                responsiveLayout="scroll"
                emptyMessage="No se encontraron espacios registrados."
            >
                <!-- Espacio + Incluye -->
                <Column field="nombre" header="Espacio / Incluye" sortable style="min-width: 260px">
                    <template #body="{ data }">
                        <div class="flex flex-col gap-1">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-surface-900 dark:text-surface-0 text-base">
                                    {{ data.nombre }}
                                </span>
                                <Tag
                                    v-if="data.tipo_espacio"
                                    :value="data.tipo_espacio.nombre"
                                    severity="secondary"
                                    class="!text-xs"
                                />
                            </div>
                            <p class="text-xs text-surface-500 dark:text-surface-400 line-clamp-2 m-0">
                                {{ data.descripcion }}
                            </p>
                        </div>
                    </template>
                </Column>

                <!-- Bloque -->
                <Column field="bloque.descripcion" header="Bloque" sortable style="min-width: 130px">
                    <template #body="{ data }">
                        <div v-if="data.bloque" class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full text-xs font-bold border border-surface-200 dark:border-surface-700 bg-surface-50 dark:bg-surface-800">
                            <span
                                class="w-2.5 h-2.5 rounded-full"
                                :style="{ backgroundColor: data.bloque.color || '#64748b' }"
                            ></span>
                            <span>{{ data.bloque.descripcion }}</span>
                        </div>
                        <span v-else class="text-xs text-surface-400">Sin bloque</span>
                    </template>
                </Column>

                <!-- Dimensiones y Capacidad -->
                <Column header="Capacidad / Área" style="min-width: 150px">
                    <template #body="{ data }">
                        <div class="flex flex-col gap-1 text-xs">
                            <div class="flex items-center gap-1.5 text-surface-700 dark:text-surface-200 font-semibold">
                                <i class="pi pi-users text-primary text-xs"></i>
                                <span>Aforo: {{ Number(data.aforo || 0).toLocaleString('es-BO') }} pers.</span>
                            </div>
                            <div class="flex items-center gap-1.5 text-surface-500">
                                <i class="pi pi-stop text-xs"></i>
                                <span>Área: {{ data.area_m2 }} m²</span>
                            </div>
                            <div v-if="data.dias_max" class="flex items-center gap-1.5 text-amber-600 dark:text-amber-400">
                                <i class="pi pi-calendar text-xs"></i>
                                <span>Máx: {{ data.dias_max }} días</span>
                            </div>
                        </div>
                    </template>
                </Column>

                <!-- Matriz resumida de Tarifas (Temporada Baja / Alta) -->
                <Column header="Tarifario Diario (Baja / Alta)" style="min-width: 340px">
                    <template #body="{ data }">
                        <div class="grid grid-cols-1 gap-1 text-xs">
                            <div
                                v-for="ev in eventos"
                                :key="ev.id"
                                class="flex items-center justify-between py-1 px-2 rounded bg-surface-50 dark:bg-surface-800/60 border border-surface-200/70 dark:border-surface-700/70"
                            >
                                <span class="text-surface-600 dark:text-surface-300 font-medium truncate pr-2">
                                    {{ ev.descripcion }}:
                                </span>
                                <div class="flex items-center gap-2 shrink-0 font-semibold">
                                    <span class="text-sky-700 dark:text-sky-400" v-tooltip.top="temporadaBaja.descripcion">
                                        Bs {{ formatMoneda(obtenerTarifa(data, ev.id, temporadaBaja.id)) }}
                                    </span>
                                    <span class="text-surface-300">/</span>
                                    <span class="text-amber-700 dark:text-amber-400" v-tooltip.top="temporadaAlta.descripcion">
                                        Bs {{ formatMoneda(obtenerTarifa(data, ev.id, temporadaAlta.id)) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </template>
                </Column>

                <!-- Acciones -->
                <Column header="Acciones" alignFrozen="right" style="min-width: 110px">
                    <template #body="{ data }">
                        <div class="flex items-center gap-1">
                            <Button
                                icon="pi pi-pencil"
                                severity="info"
                                text
                                rounded
                                v-tooltip.top="'Editar espacio y tarifas'"
                                @click="abrirEditar(data)"
                            />
                            <Button
                                icon="pi pi-trash"
                                severity="danger"
                                text
                                rounded
                                v-tooltip.top="'Eliminar espacio'"
                                @click="confirmarEliminar(data)"
                            />
                        </div>
                    </template>
                </Column>
            </DataTable>
        </div>

        <!-- COMPONENTE MODULAR DE CREACIÓN / EDICIÓN -->
        <ModalEspacioForm
            v-model:visible="modalVisible"
            :espacio="espacioSeleccionado"
            :bloques="bloques"
            :tiposEspacio="tiposEspacio"
            :eventos="eventos"
            :temporadas="temporadas"
            @saved="onEspacioGuardado"
        />
    </div>
</template>
