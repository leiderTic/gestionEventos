<script setup>
import { ref, onMounted, computed } from 'vue';
import espacioServices from '@/services/espacio.services';
import bloqueServices from '@/services/bloque.services';
import tipoEspacioServices from '@/services/tipoespacio.services';
import eventoServices from '@/services/evento.services';
import temporadaServices from '@/services/temporada.services';
import ModalEspacioForm from '@/components/espacios/ModalEspacioForm.vue';
import ModalEspacioTarifas from '@/components/espacios/ModalEspacioTarifas.vue';
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

const modalEspacioVisible = ref(false);
const modalTarifasVisible = ref(false);
const espacioSeleccionado = ref(null);

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
    cargarDatos();
});

async function cargarDatos() {
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
            detail: 'No se pudieron cargar los espacios.',
            life: 3000
        });
    } finally {
        loading.value = false;
    }
}

function abrirCrearEspacio() {
    espacioSeleccionado.value = null;
    modalEspacioVisible.value = true;
}

function abrirEditarEspacio(espacio) {
    espacioSeleccionado.value = espacio;
    modalEspacioVisible.value = true;
}

function abrirTarifas(espacio) {
    espacioSeleccionado.value = espacio;
    modalTarifasVisible.value = true;
}

async function onEspacioGuardado({ espacio, esNuevo }) {
    await cargarDatos();
    if (esNuevo && espacio?.id) {
        const recienCreado = espacios.value.find((e) => e.id === espacio.id) || espacio;
        abrirTarifas(recienCreado);
    }
}

function onTarifasGuardadas() {
    cargarDatos();
}

function tieneCostosAsignados(espacio) {
    if (!Array.isArray(espacio.tarifas) || espacio.tarifas.length === 0) return false;
    return espacio.tarifas.some((t) => Number(t.precio_dia) > 0);
}

function resumenRangoCostos(espacio) {
    if (!tieneCostosAsignados(espacio)) return null;
    const precios = espacio.tarifas.map((t) => Number(t.precio_dia)).filter((p) => p > 0);
    if (precios.length === 0) return null;
    const min = Math.min(...precios);
    const max = Math.max(...precios);
    const fmt = (n) => n.toLocaleString('es-BO', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
    return min === max ? `Bs ${fmt(min)}` : `Bs ${fmt(min)} - ${fmt(max)}`;
}

function confirmarEliminar(espacio) {
    confirm.require({
        message: `¿Eliminar el espacio "${espacio.nombre}"?`,
        header: 'Confirmar eliminación',
        icon: 'pi pi-exclamation-triangle',
        rejectProps: {
            label: 'Cancelar',
            severity: 'secondary',
            outlined: true
        },
        acceptProps: {
            label: 'Eliminar',
            severity: 'danger'
        },
        accept: async () => {
            try {
                await espacioServices.eliminar(espacio.id);
                toast.add({
                    severity: 'success',
                    summary: 'Eliminado',
                    detail: `Espacio ${espacio.nombre} eliminado.`,
                    life: 3000
                });
                cargarDatos();
            } catch (e) {
                toast.add({
                    severity: 'error',
                    summary: 'Error',
                    detail: e.response?.data?.message || 'No se pudo eliminar el espacio.',
                    life: 3500
                });
            }
        }
    });
}
</script>

<template>
    <div class="card p-0 overflow-hidden shadow-sm border border-surface-200 dark:border-surface-700 rounded-xl">
        <ConfirmDialog />

        <!-- Encabezado y barra de búsqueda -->
        <div class="p-5 bg-white dark:bg-surface-900 border-b border-surface-200 dark:border-surface-700 flex flex-col gap-4">
            <div class="flex flex-col md:flex-row justify-between md:items-center gap-3">
                <div>
                    <h1 class="text-xl font-bold text-surface-900 dark:text-surface-0 m-0">Espacios</h1>
                    <p class="text-sm text-surface-500 mt-0.5 m-0">
                        Catálogo de salones, plazas, teatros y asignación de costos diarios.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <Button
                        label="Nuevo espacio"
                        icon="pi pi-plus"
                        @click="abrirCrearEspacio"
                    />
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                <div class="md:col-span-6">
                    <IconField>
                        <InputIcon class="pi pi-search" />
                        <InputText
                            v-model="filtroTexto"
                            placeholder="Buscar por nombre o descripción..."
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
                        placeholder="Filtrar por bloque"
                        showClear
                        class="w-full"
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
                </div>

                <div class="md:col-span-3">
                    <Select
                        v-model="filtroTipo"
                        :options="tiposEspacio"
                        optionLabel="nombre"
                        optionValue="id"
                        placeholder="Filtrar por tipo"
                        showClear
                        class="w-full"
                    />
                </div>
            </div>
        </div>

        <!-- Listado de Espacios -->
        <DataTable
            :value="espaciosFiltrados"
            :loading="loading"
            dataKey="id"
            paginator
            :rows="10"
            :rowsPerPageOptions="[10, 20, 50]"
            rowHover
            responsiveLayout="scroll"
            emptyMessage="No hay espacios registrados."
        >
            <Column field="nombre" header="ESPACIO" sortable style="min-width: 240px">
                <template #body="{ data }">
                    <div class="flex flex-col">
                        <span class="font-semibold text-surface-900 dark:text-surface-0">{{ data.nombre }}</span>
                        <span class="text-xs text-surface-500 line-clamp-1" :title="data.descripcion">
                            {{ data.descripcion }}
                        </span>
                    </div>
                </template>
            </Column>

            <Column field="tipo_espacio.nombre" header="TIPO" sortable style="min-width: 110px">
                <template #body="{ data }">
                    <span class="text-sm text-surface-700 dark:text-surface-300">
                        {{ data.tipo_espacio?.nombre || '-' }}
                    </span>
                </template>
            </Column>

            <Column field="bloque.descripcion" header="BLOQUE" sortable style="min-width: 130px">
                <template #body="{ data }">
                    <div v-if="data.bloque" class="inline-flex items-center gap-2 text-sm font-medium">
                        <span
                            class="w-2.5 h-2.5 rounded-full"
                            :style="{ backgroundColor: data.bloque.color || '#64748b' }"
                        ></span>
                        <span>{{ data.bloque.descripcion }}</span>
                    </div>
                    <span v-else class="text-xs text-surface-400">-</span>
                </template>
            </Column>

            <Column field="area_m2" header="SUPERFICIE" sortable style="min-width: 120px">
                <template #body="{ data }">
                    <span class="text-sm">{{ data.area_m2 }} m²</span>
                </template>
            </Column>

            <Column field="aforo" header="AFORO" sortable style="min-width: 120px">
                <template #body="{ data }">
                    <span class="text-sm">{{ Number(data.aforo || 0).toLocaleString('es-BO') }} pers.</span>
                </template>
            </Column>

            <Column header="COSTOS / DÍA" style="min-width: 170px">
                <template #body="{ data }">
                    <div v-if="tieneCostosAsignados(data)" class="flex items-center gap-2">
                        <span class="text-sm font-semibold text-surface-800 dark:text-surface-100">
                            {{ resumenRangoCostos(data) }}
                        </span>
                    </div>
                    <Tag v-else value="Sin costos" severity="warn" />
                </template>
            </Column>

            <Column header="ACCIONES" alignFrozen="right" style="min-width: 180px">
                <template #body="{ data }">
                    <div class="flex items-center gap-1">
                        <Button
                            label="Costos"
                            icon="pi pi-dollar"
                            size="small"
                            severity="secondary"
                            outlined
                            @click="abrirTarifas(data)"
                        />
                        <Button
                            icon="pi pi-pencil"
                            severity="secondary"
                            text
                            rounded
                            v-tooltip.top="'Editar datos del espacio'"
                            @click="abrirEditarEspacio(data)"
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

        <!-- Ventana 1: CRUD de Datos del Espacio -->
        <ModalEspacioForm
            v-model:visible="modalEspacioVisible"
            :espacio="espacioSeleccionado"
            :bloques="bloques"
            :tiposEspacio="tiposEspacio"
            @saved="onEspacioGuardado"
        />

        <!-- Ventana 2: Asignación de Costos del Espacio -->
        <ModalEspacioTarifas
            v-model:visible="modalTarifasVisible"
            :espacio="espacioSeleccionado"
            :eventos="eventos"
            :temporadas="temporadas"
            @saved="onTarifasGuardadas"
        />
    </div>
</template>
