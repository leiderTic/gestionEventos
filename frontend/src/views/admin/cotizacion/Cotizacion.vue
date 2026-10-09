<template>
    <div class="card p-0 overflow-hidden shadow-lg border-none rounded-xl">
        <!-- Header Section -->
        <div
            class="p-6 bg-white dark:bg-surface-900 border-b border-surface-200 dark:border-surface-700 flex flex-col md:flex-row justify-between items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold text-surface-900 dark:text-surface-0 m-0">Cotizaciones</h1>
                <p class="text-surface-500 mt-1">Gestión y seguimiento de solicitudes de arrendamiento.</p>
            </div>

            <div class="flex gap-3 w-full md:w-auto overflow-x-auto pb-2 md:pb-0 hide-scrollbar">
                <span class="p-input-icon-left w-full md:w-auto">
                    <i class="pi pi-search" />
                    <InputText v-model="filters['global'].value" placeholder="Buscar..."
                        class="w-full md:w-64 rounded-full bg-surface-50" />
                </span>

                <router-link :to="{ name: 'cotizacionNueva' }">
                    <Button label="Nueva Cotización" icon="pi pi-plus" class="rounded-full !px-5" />
                </router-link>
            </div>
        </div>

        <!-- DataTable -->
        <DataTable :value="cotizaciones" :paginator="true" :rows="10" dataKey="id" :filters="filters"
            filterDisplay="menu" :loading="loading" class="p-datatable-lg custom-header" :rowHover="true"
            responsiveLayout="scroll" emptyMessage="No se encontraron cotizaciones.">

            <!-- N° / Fecha -->
            <Column field="id" header="N° / FECHA" style="min-width: 12rem">
                <template #body="{ data }">
                    <div class="flex flex-col">
                        <span class="font-bold text-surface-900 dark:text-surface-0">{{ data.codigo }}</span>
                        <span class="text-xs text-surface-500">{{ formatDateShort(data.created_at) }}</span>
                    </div>
                </template>
            </Column>

            <!-- Cliente -->
            <Column header="CLIENTE" style="min-width: 14rem">
                <template #body="{ data }">
                    <div class="flex flex-col">
                        <span class="font-bold text-surface-900 dark:text-surface-0">{{ getInstitucionNombre(data)
                        }}</span>
                        <span class="text-xs text-surface-500">{{ getContactoNombre(data) }}</span>
                    </div>
                </template>
            </Column>

            <!-- Tipo -->
            <Column field="evento.descripcion" header="TIPO" style="min-width: 10rem">
                <template #body="{ data }">
                    <Tag :severity="getEventoSeverity(data.evento?.descripcion)"
                        class="rounded-xl px-3 font-semibold !text-xs whitespace-nowrap">
                        <div class="flex items-center gap-2">
                            <i :class="getEventoIcon(data.evento?.descripcion)"></i>
                            <span>{{ data.evento?.descripcion || 'Evento' }}</span>
                        </div>
                    </Tag>
                </template>
            </Column>

            <!-- Espacios -->
            <Column header="ESPACIOS" style="min-width: 14rem">
                <template #body="{ data }">
                    <div class="text-sm text-surface-700 dark:text-surface-300 truncate max-w-[14rem]"
                        :title="getEspaciosList(data)">
                        {{ getEspaciosList(data) || '-' }}
                    </div>
                </template>
            </Column>

            <!-- Fecha Evento -->
            <Column header="FECHA EVENTO" style="min-width: 14rem">
                <template #body="{ data }">
                    <div class="flex flex-col">
                        <span class="text-sm font-medium">{{ formatDateShort(data.fecha_ini) }} - {{
                            formatDateShort(data.fecha_fin) }}</span>
                        <span class="text-xs text-surface-500">{{ calcularDias(data.fecha_ini, data.fecha_fin) }}
                            día(s)</span>
                    </div>
                </template>
            </Column>

            <!-- Ejecutivo -->
            <Column header="EJECUTIVO" style="min-width: 12rem">
                <template #body="{ data }">
                    <span class="text-sm text-surface-700 dark:text-surface-300">{{ data.user?.name || 'Sistema'
                    }}</span>
                </template>
            </Column>

            <!-- Monto -->
            <Column header="MONTO" style="min-width: 10rem" alignFrozen="right">
                <template #body="{ data }">
                    <span class="font-bold text-surface-900 dark:text-surface-0">Bs {{
                        formatCurrency(data.monto_total) }}</span>
                </template>
            </Column>

            <!-- Estado -->
            <Column header="ESTADO" style="min-width: 10rem" alignFrozen="right">
                <template #body="{ data }">
                    <Tag :severity="getEstadoSeverity(data.paso)" :value="getEstadoLabel(data.paso)"
                        class="rounded-full px-3" />
                </template>
            </Column>

            <!-- Action -->
            <Column style="width: 5rem; text-align: center" alignFrozen="right" :frozen="true">
                <template #body="{ data }">
                    <router-link :to="{ name: 'cotizacionDetalle', params: { id: data.id } }">
                        <Button label="Ver" icon="pi pi-arrow-right" iconPos="right" size="small"
                            class="bg-surface-900 border-surface-900 hover:bg-surface-700 text-white !text-xs rounded-full shadow-md" />
                    </router-link>
                </template>
            </Column>

        </DataTable>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { FilterMatchMode } from '@primevue/core/api';
import cotizacionServices from '@/services/cotizacion.services';
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const cotizaciones = ref([]);
const loading = ref(true);

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

onMounted(async () => {
    await loadCotizaciones();
});

const loadCotizaciones = async () => {
    try {
        loading.value = true;
        const res = await cotizacionServices.listar();
        cotizaciones.value = res.data;
    } catch (e) {
        toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudieron cargar las cotizaciones.', life: 3000 });
    } finally {
        loading.value = false;
    }
};

// --- Helpers de Formateo ---

const formatDateShort = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    // Add timezone offset correction to avoid day shifting
    date.setMinutes(date.getMinutes() + date.getTimezoneOffset());
    return date.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: '2-digit' }).replace(/\//g, '-');
};

const calcularDias = (ini, fin) => {
    const start = new Date(ini);
    const end = new Date(fin);
    const diffTime = Math.abs(end - start);
    return Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
};

const getInstitucionNombre = (data) => {
    if (!data.clientes || data.clientes.length === 0) return 'Sin Cliente';
    const inst = data.clientes.find(c => c.tipo_cliente_id === 1);
    const perso = data.clientes.find(c => c.tipo_cliente_id === 2);
    // Si tiene institución, mostrar, sino mostrar nombre de persona
    return inst ? inst.nombre : (perso ? perso.nombre : 'S/N');
};

const getContactoNombre = (data) => {
    if (!data.clientes || data.clientes.length === 0) return '-';
    // Si la persona de contacto es la misma, se repite o se omite
    const inst = data.clientes.find(c => c.tipo_cliente_id === 1);
    const perso = data.clientes.find(c => c.tipo_cliente_id === 2);

    if (inst && perso) return perso.nombre;
    if (inst && !perso) return '-';
    // Solo persona natural
    if (!inst && perso) return perso.ci_nit ? `CI/NIT: ${perso.ci_nit}` : '-';
    return '-';
};

const getEspaciosList = (data) => {
    if (!data.tarifas || data.tarifas.length === 0) return '';
    // Extract unique space names
    const spaces = [...new Set(data.tarifas.map(t => {
        let name = t.espacio?.nombre || '';
        // Simplify 'Salón Potosí' -> 'S. Potosí' just for listing visually if preferred,
        // or just keep original.
        return name;
    }))];
    return spaces.join(', ');
};

const formatCurrency = (value) => {
    return Number(value || 0).toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

// --- Tags / UI Visual Helpers ---

const getEventoSeverity = (tipo) => {
    const l = (tipo || '').toLowerCase();
    if (l.includes('social') || l.includes('fiesta')) return 'warn';
    if (l.includes('corporativo') || l.includes('conferencia')) return 'info';
    if (l.includes('feria') || l.includes('exposicion')) return 'success';
    return 'secondary';
};

const getEventoIcon = (tipo) => {
    const l = (tipo || '').toLowerCase();
    if (l.includes('social')) return 'pi pi-heart-fill text-pink-500';
    if (l.includes('corporativo')) return 'pi pi-briefcase text-blue-500';
    if (l.includes('feria')) return 'pi pi-th-large text-green-500';
    return 'pi pi-star';
};

const getEstadoLabel = (paso) => {
    const estados = {
        1: 'En gestión',
        2: 'Confirma Reserva',
        3: 'Reunión Operativa',
        4: 'Pago Total',
        5: 'Informe Inicio',
        6: 'Revisión Legal',
        7: 'Contrato',
        8: 'Premontaje',
        9: 'Acta Entrega',
        10: 'En Curso',
        11: 'Devolución',
        12: 'Informe Cierre',
        13: 'Análisis Garantía',
        14: 'Cerrado'
    };
    return estados[paso] || 'Desconocido';
};

const getEstadoSeverity = (paso) => {
    if (paso === 1) return 'info'; // En gestión, light blue tag
    if (paso > 1 && paso < 10) return 'warn';
    if (paso === 10) return 'success'; // En Curso
    if (paso >= 14) return 'secondary'; // Cerrado
    return 'info';
};
</script>

<style scoped>
:deep(.custom-header .p-datatable-thead > tr > th) {
    background-color: #f8fafc;
    color: #64748b;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding-top: 1rem;
    padding-bottom: 1rem;
}

.dark :deep(.custom-header .p-datatable-thead > tr > th) {
    background-color: #0f172a;
    color: #94a3b8;
}

/* Esconder barra de scroll en botones de accion top */
.hide-scrollbar::-webkit-scrollbar {
    display: none;
}

.hide-scrollbar {
    -ms-overflow-style: none;
    /* IE and Edge */
    scrollbar-width: none;
    /* Firefox */
}
</style>