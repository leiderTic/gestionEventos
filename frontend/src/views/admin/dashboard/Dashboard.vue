<script setup>
import { ref, computed, onMounted } from 'vue';
import cotizacionServices from '@/services/cotizacion.services';
import DashboardMetricas from '@/components/dashboard/DashboardMetricas.vue';
import DashboardCotizacionesRecientes from '@/components/dashboard/DashboardCotizacionesRecientes.vue';
import DashboardProximosEventos from '@/components/dashboard/DashboardProximosEventos.vue';
import { ETAPAS_PROCESO, filtrarPorEtapa } from '@/utils/cotizacionProcesos';
import { useAuthStore } from '@/stores/auth';
import { useToast } from 'primevue/usetoast';

const authStore = useAuthStore();
const toast = useToast();

const cotizaciones = ref([]);
const loading = ref(true);
const etapaActiva = ref(ETAPAS_PROCESO.TODAS);

const esAdministrador = computed(() => authStore.can('cotizaciones.ver_todas'));
const puedeCrearCotizacion = computed(() => authStore.can('cotizaciones.crear'));

const cotizacionesFiltradas = computed(() =>
    filtrarPorEtapa(cotizaciones.value, etapaActiva.value)
);

onMounted(async () => {
    await cargarDatos();
});

async function cargarDatos() {
    loading.value = true;
    try {
        const { data } = await cotizacionServices.listar();
        cotizaciones.value = data || [];
    } catch (e) {
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'No se pudieron cargar los datos del dashboard.',
            life: 3000
        });
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <div class="flex flex-col gap-5">
        <!-- Módulo 1: 4 Tarjetas de Etapas del Proceso Real CFCHM -->
        <DashboardMetricas
            :cotizaciones="cotizaciones"
            v-model:etapaActiva="etapaActiva"
        />

        <!-- Módulo 2 y 3: Lista de Expedientes Filtrada + Próximos Eventos -->
        <div class="grid grid-cols-1 xl:grid-cols-12 gap-5 items-start">
            <div class="xl:col-span-8">
                <DashboardCotizacionesRecientes
                    :cotizaciones="cotizacionesFiltradas"
                    :loading="loading"
                    :esAdministrador="esAdministrador"
                    :etapaActiva="etapaActiva"
                    :puedeCrear="puedeCrearCotizacion"
                    @resetFiltro="etapaActiva = ETAPAS_PROCESO.TODAS"
                />
            </div>

            <div class="xl:col-span-4">
                <DashboardProximosEventos :cotizaciones="cotizaciones" />
            </div>
        </div>
    </div>
</template>
