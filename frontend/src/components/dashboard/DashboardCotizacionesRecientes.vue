<script setup>
import { computed } from 'vue';
import {
    ETAPAS_PROCESO,
    obtenerEstadoPaso,
    obtenerTituloEtapa,
    formatMoneda,
    formatFechaCorta,
    calcularDiasEvento,
    obtenerNombreClientePrincipal,
    obtenerNombreContactoSecundario,
    obtenerNombresEspacios
} from '@/utils/cotizacionProcesos';

const props = defineProps({
    cotizaciones: {
        type: Array,
        default: () => []
    },
    loading: {
        type: Boolean,
        default: false
    },
    esAdministrador: {
        type: Boolean,
        default: false
    },
    etapaActiva: {
        type: String,
        default: ETAPAS_PROCESO.TODAS
    },
    puedeCrear: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['resetFiltro']);

const etiquetaEtapaActiva = computed(() => obtenerTituloEtapa(props.etapaActiva));
</script>

<template>
    <div class="bg-white dark:bg-surface-900 rounded-xl shadow-sm border border-surface-200 dark:border-surface-800 overflow-hidden">
        <!-- Cabecera -->
        <div class="px-6 py-4 flex flex-wrap items-center justify-between gap-3 border-b border-surface-200 dark:border-surface-800">
            <div class="flex flex-wrap items-center gap-2.5">
                <h2 class="text-base font-bold text-surface-900 dark:text-surface-0 m-0">
                    {{ esAdministrador ? 'Expedientes de Cotización' : 'Mis Expedientes' }}
                </h2>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-surface-100 dark:bg-surface-800 text-surface-700 dark:text-surface-200">
                    {{ etiquetaEtapaActiva }} ({{ cotizaciones.length }})
                </span>
                <button
                    v-if="etapaActiva !== ETAPAS_PROCESO.TODAS"
                    type="button"
                    @click="emit('resetFiltro')"
                    class="text-xs text-primary hover:underline font-semibold cursor-pointer bg-transparent border-0 p-0"
                >
                    Mostrar todos
                </button>
            </div>

            <div class="flex items-center gap-3">
                <router-link
                    :to="{ name: 'cotizacion' }"
                    class="text-xs font-semibold text-surface-600 dark:text-surface-300 hover:text-primary no-underline flex items-center gap-1"
                >
                    <span>Bandeja completa</span>
                    <i class="pi pi-arrow-right !text-[10px]"></i>
                </router-link>
                <router-link v-if="puedeCrear" :to="{ name: 'cotizacionNueva' }">
                    <Button label="Nueva cotización" icon="pi pi-plus" size="small" />
                </router-link>
            </div>
        </div>

        <!-- Estado de carga -->
        <div v-if="loading" class="p-8 flex justify-center items-center text-sm text-surface-500">
            <i class="pi pi-spin pi-spinner mr-2"></i> Cargando expedientes...
        </div>

        <!-- Listado de filas de expediente -->
        <div v-else-if="cotizaciones.length > 0" class="divide-y divide-surface-200 dark:divide-surface-800">
            <router-link
                v-for="item in cotizaciones.slice(0, 10)"
                :key="item.id"
                :to="{ name: 'cotizacionDetalle', params: { id: item.id } }"
                class="block px-6 py-4 hover:bg-surface-50 dark:hover:bg-surface-800/40 transition-colors no-underline text-inherit"
            >
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                    <!-- Columna 1: Cliente, Código y Ejecutivo -->
                    <div class="flex flex-col gap-1 min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="font-bold text-surface-900 dark:text-surface-0 text-sm">
                                {{ obtenerNombreClientePrincipal(item) }}
                            </span>
                            <span
                                v-if="obtenerNombreContactoSecundario(item)"
                                class="text-xs text-surface-500 dark:text-surface-400"
                            >
                                · {{ obtenerNombreContactoSecundario(item) }}
                            </span>
                        </div>

                        <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-surface-500 dark:text-surface-400">
                            <span class="font-mono font-semibold text-surface-700 dark:text-surface-300">
                                {{ item.codigo }}
                            </span>
                            <span v-if="item.evento?.descripcion">
                                {{ item.evento.descripcion }}
                            </span>
                            <span v-if="esAdministrador && item.user?.name" class="inline-flex items-center gap-1">
                                <i class="pi pi-user !text-[10px]"></i>
                                {{ item.user.name }}
                            </span>
                        </div>
                    </div>

                    <!-- Columna 2: Espacios y Fechas -->
                    <div class="flex flex-col md:items-end gap-1 min-w-0 md:w-64 shrink-0">
                        <div class="flex flex-wrap md:justify-end gap-1">
                            <template v-if="obtenerNombresEspacios(item).length > 0">
                                <span
                                    v-for="esp in obtenerNombresEspacios(item).slice(0, 2)"
                                    :key="esp"
                                    class="px-2 py-0.5 rounded bg-surface-100 dark:bg-surface-800 text-surface-700 dark:text-surface-200 text-xs font-medium truncate max-w-[10rem]"
                                >
                                    {{ esp }}
                                </span>
                                <span
                                    v-if="obtenerNombresEspacios(item).length > 2"
                                    class="px-1.5 py-0.5 rounded bg-surface-100 dark:bg-surface-800 text-surface-500 text-xs font-semibold"
                                >
                                    +{{ obtenerNombresEspacios(item).length - 2 }}
                                </span>
                            </template>
                            <span v-else class="text-xs text-surface-400 italic">Sin espacio asignado</span>
                        </div>

                        <div class="text-xs text-surface-500 dark:text-surface-400 flex items-center gap-1.5">
                            <i class="pi pi-calendar !text-[11px]"></i>
                            <span>{{ formatFechaCorta(item.fecha_ini) }} al {{ formatFechaCorta(item.fecha_fin) }}</span>
                            <span class="text-surface-400">({{ calcularDiasEvento(item.fecha_ini, item.fecha_fin) }}d)</span>
                        </div>
                    </div>

                    <!-- Columna 3: Monto y Proceso Actual -->
                    <div class="flex items-center justify-between md:justify-end gap-4 md:w-60 shrink-0 pt-2 md:pt-0 border-t md:border-t-0 border-surface-100 dark:border-surface-800">
                        <div class="flex flex-col md:items-end">
                            <span class="font-bold text-surface-900 dark:text-surface-0 text-sm">
                                Bs {{ formatMoneda(item.monto_total) }}
                            </span>
                            <Tag
                                :severity="obtenerEstadoPaso(item).severity"
                                :value="obtenerEstadoPaso(item).label"
                                class="!text-[11px] !px-2 !py-0.5 mt-0.5"
                            />
                        </div>
                        <i class="pi pi-chevron-right text-surface-400 text-xs"></i>
                    </div>
                </div>
            </router-link>
        </div>

        <!-- Estado vacío cuando una etapa no tiene expedientes -->
        <div v-else class="p-10 text-center">
            <p class="text-sm text-surface-500 dark:text-surface-400 m-0">
                No existen expedientes en <strong>{{ etiquetaEtapaActiva }}</strong>.
            </p>
            <Button
                v-if="etapaActiva !== ETAPAS_PROCESO.TODAS"
                label="Ver todas las etapas"
                size="small"
                text
                class="mt-2"
                @click="emit('resetFiltro')"
            />
        </div>
    </div>
</template>
