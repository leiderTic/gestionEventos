<script setup>
import { computed } from 'vue';
import {
    obtenerEstadoPaso,
    obtenerNombreClientePrincipal,
    obtenerNombresEspacios
} from '@/utils/cotizacionProcesos';

const props = defineProps({
    cotizaciones: {
        type: Array,
        default: () => []
    }
});

const proximosEventos = computed(() => {
    const hoy = new Date();
    hoy.setHours(0, 0, 0, 0);

    return props.cotizaciones
        .filter((c) => !c.vencido && c.fecha_fin)
        .filter((c) => {
            const fin = new Date(c.fecha_fin);
            return fin >= hoy;
        })
        .sort((a, b) => new Date(a.fecha_ini) - new Date(b.fecha_ini))
        .slice(0, 6);
});

function obtenerDiaMes(fechaStr) {
    if (!fechaStr) return { dia: '--', mes: '---' };
    const date = new Date(fechaStr);
    date.setMinutes(date.getMinutes() + date.getTimezoneOffset());
    const dia = date.toLocaleDateString('es-ES', { day: '2-digit' });
    const mes = date.toLocaleDateString('es-ES', { month: 'short' }).replace('.', '').toUpperCase();
    return { dia, mes };
}
</script>

<template>
    <div class="bg-white dark:bg-surface-900 rounded-xl shadow-sm border border-surface-200 dark:border-surface-800 overflow-hidden">
        <div class="px-5 py-4 border-b border-surface-200 dark:border-surface-800">
            <h3 class="text-base font-bold text-surface-900 dark:text-surface-0 m-0">
                Próximos Eventos
            </h3>
            <p class="text-xs text-surface-500 dark:text-surface-400 m-0 mt-0.5">
                Agenda ordenada por fecha de inicio
            </p>
        </div>

        <div v-if="proximosEventos.length > 0" class="divide-y divide-surface-200 dark:divide-surface-800">
            <router-link
                v-for="ev in proximosEventos"
                :key="ev.id"
                :to="{ name: 'cotizacionDetalle', params: { id: ev.id } }"
                class="flex items-start gap-3.5 p-4 hover:bg-surface-50 dark:hover:bg-surface-800/50 transition-colors no-underline"
            >
                <div class="w-12 h-12 rounded-lg bg-surface-100 dark:bg-surface-800 border border-surface-200 dark:border-surface-700 flex flex-col items-center justify-center shrink-0">
                    <span class="text-[10px] font-bold text-primary uppercase leading-none">
                        {{ obtenerDiaMes(ev.fecha_ini).mes }}
                    </span>
                    <span class="text-base font-extrabold text-surface-900 dark:text-surface-0 leading-tight mt-0.5">
                        {{ obtenerDiaMes(ev.fecha_ini).dia }}
                    </span>
                </div>

                <div class="flex flex-col min-w-0 flex-1">
                    <span class="text-xs font-bold text-surface-900 dark:text-surface-0 truncate">
                        {{ obtenerNombreClientePrincipal(ev) }}
                    </span>
                    <span class="text-xs text-surface-500 dark:text-surface-400 truncate mt-0.5">
                        {{ obtenerNombresEspacios(ev).join(', ') || 'Sin espacio asignado' }}
                    </span>
                    <span class="text-[11px] text-surface-400 mt-1">
                        {{ ev.codigo }} · {{ obtenerEstadoPaso(ev).label }}
                    </span>
                </div>
            </router-link>
        </div>

        <div v-else class="p-8 text-center">
            <i class="pi pi-calendar text-2xl text-surface-400 mb-2 block"></i>
            <span class="text-sm text-surface-500 dark:text-surface-400 block">
                Sin eventos próximos programados.
            </span>
        </div>
    </div>
</template>
