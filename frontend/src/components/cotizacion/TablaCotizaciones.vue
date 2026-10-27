<script setup>
import { ref } from 'vue';
import { FilterMatchMode } from '@primevue/core/api';
import {
    obtenerEstadoPaso,
    formatMoneda,
    formatFechaCorta,
    calcularDiasEvento,
    obtenerNombreClientePrincipal,
    obtenerNombreContactoSecundario,
    obtenerNombresEspacios
} from '@/utils/cotizacionProcesos';

defineProps({
    cotizaciones: {
        type: Array,
        default: () => []
    },
    loading: {
        type: Boolean,
        default: false
    },
    titulo: {
        type: String,
        default: 'Cotizaciones'
    },
    subtitulo: {
        type: String,
        default: 'Gestión y seguimiento de solicitudes de arrendamiento.'
    },
    mostrarBotonNueva: {
        type: Boolean,
        default: false
    },
    mostrarEjecutivo: {
        type: Boolean,
        default: true
    },
    emptyMessage: {
        type: String,
        default: 'No se encontraron cotizaciones registradas.'
    }
});

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS }
});

const getEspaciosTexto = (data) => {
    const lista = obtenerNombresEspacios(data);
    return lista.length > 0 ? lista.join(', ') : '-';
};
</script>

<template>
    <div class="card p-0 overflow-hidden border border-surface-200 dark:border-surface-800 rounded-xl shadow-none">
        <!-- Cabecera de la tabla -->
        <div
            class="px-6 py-5 bg-surface-0 dark:bg-surface-900 border-b border-surface-200 dark:border-surface-800 flex flex-col md:flex-row justify-between items-start md:items-center gap-4"
        >
            <div>
                <h1 class="text-xl font-bold text-surface-900 dark:text-surface-0 m-0">
                    {{ titulo }}
                </h1>
                <p class="text-sm text-surface-500 mt-1 mb-0">
                    {{ subtitulo }}
                </p>
            </div>

            <div class="flex items-center gap-2.5 w-full md:w-auto">
                <IconField class="w-full md:w-64">
                    <InputIcon class="pi pi-search" />
                    <InputText
                        v-model="filters['global'].value"
                        placeholder="Buscar por código, cliente..."
                        class="w-full text-sm"
                    />
                </IconField>

                <router-link v-if="mostrarBotonNueva" :to="{ name: 'cotizacionNueva' }">
                    <Button label="Nueva Cotización" icon="pi pi-plus" size="small" class="whitespace-nowrap" />
                </router-link>
            </div>
        </div>

        <!-- Tabla de Cotizaciones -->
        <DataTable
            :value="cotizaciones"
            :paginator="true"
            :rows="10"
            dataKey="id"
            :filters="filters"
            :loading="loading"
            :rowHover="true"
            responsiveLayout="scroll"
            class="text-sm"
        >
            <template #empty>
                <div class="py-10 text-center text-surface-500 text-sm">
                    {{ emptyMessage }}
                </div>
            </template>

            <!-- N° / Fecha -->
            <Column field="codigo" header="CÓDIGO / EMISIÓN" style="min-width: 11rem">
                <template #body="{ data }">
                    <div class="flex flex-col">
                        <span class="font-semibold text-surface-900 dark:text-surface-0 font-mono text-xs">
                            {{ data.codigo }}
                        </span>
                        <span class="text-xs text-surface-500 mt-0.5">
                            {{ formatFechaCorta(data.created_at) }}
                        </span>
                    </div>
                </template>
            </Column>

            <!-- Cliente -->
            <Column header="SOLICITANTE" style="min-width: 14rem">
                <template #body="{ data }">
                    <div class="flex flex-col">
                        <span class="font-semibold text-surface-900 dark:text-surface-0 leading-snug">
                            {{ obtenerNombreClientePrincipal(data) }}
                        </span>
                        <span v-if="obtenerNombreContactoSecundario(data)" class="text-xs text-surface-500 mt-0.5">
                            {{ obtenerNombreContactoSecundario(data) }}
                        </span>
                    </div>
                </template>
            </Column>

            <!-- Tipo de Evento -->
            <Column field="evento.descripcion" header="TIPO DE EVENTO" style="min-width: 11rem">
                <template #body="{ data }">
                    <span
                        class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-surface-100 dark:bg-surface-800 text-surface-700 dark:text-surface-300 border border-surface-200 dark:border-surface-700"
                    >
                        {{ data.evento?.descripcion || 'Evento General' }}
                    </span>
                </template>
            </Column>

            <!-- Espacios -->
            <Column header="ESPACIOS" style="min-width: 13rem">
                <template #body="{ data }">
                    <div
                        class="text-xs text-surface-700 dark:text-surface-300 truncate max-w-[14rem]"
                        :title="getEspaciosTexto(data)"
                    >
                        {{ getEspaciosTexto(data) }}
                    </div>
                </template>
            </Column>

            <!-- Fecha Evento -->
            <Column header="FECHAS DEL EVENTO" style="min-width: 12rem">
                <template #body="{ data }">
                    <div class="flex flex-col">
                        <span class="text-xs font-medium text-surface-800 dark:text-surface-200">
                            {{ formatFechaCorta(data.fecha_ini) }} – {{ formatFechaCorta(data.fecha_fin) }}
                        </span>
                        <span class="text-xs text-surface-500 mt-0.5">
                            {{ calcularDiasEvento(data.fecha_ini, data.fecha_fin) }} día(s)
                        </span>
                    </div>
                </template>
            </Column>

            <!-- Ejecutivo -->
            <Column v-if="mostrarEjecutivo" field="user.name" header="EJECUTIVO" style="min-width: 11rem">
                <template #body="{ data }">
                    <span class="text-xs text-surface-700 dark:text-surface-300">
                        {{ data.user?.name || 'Sistema' }}
                    </span>
                </template>
            </Column>

            <!-- Monto -->
            <Column header="MONTO TOTAL" style="min-width: 9rem">
                <template #body="{ data }">
                    <span class="font-semibold text-surface-900 dark:text-surface-0">
                        Bs {{ formatMoneda(data.monto_total) }}
                    </span>
                </template>
            </Column>

            <!-- Etapa / Proceso -->
            <Column header="ETAPA / ESTADO" style="min-width: 12rem">
                <template #body="{ data }">
                    <div class="flex flex-col items-start gap-0.5">
                        <Tag
                            :severity="obtenerEstadoPaso(data).severity"
                            :value="obtenerEstadoPaso(data).label"
                            class="!text-xs"
                        />
                        <span class="text-[11px] text-surface-500 pl-0.5">
                            {{ obtenerEstadoPaso(data).area }}
                        </span>
                    </div>
                </template>
            </Column>

            <!-- Acción -->
            <Column header="ACCIÓN" style="width: 6rem; text-align: right">
                <template #body="{ data }">
                    <router-link :to="{ name: 'cotizacionDetalle', params: { id: data.id } }">
                        <Button
                            label="Abrir"
                            icon="pi pi-arrow-right"
                            iconPos="right"
                            size="small"
                            outlined
                            severity="secondary"
                            class="!text-xs"
                        />
                    </router-link>
                </template>
            </Column>
        </DataTable>
    </div>
</template>
