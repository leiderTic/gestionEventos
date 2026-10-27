<script setup>
import { computed } from 'vue';
import {
    ETAPAS_PROCESO,
    DEFINICION_ETAPAS,
    filtrarPorEtapa,
    sumarMonto,
    formatMonedaCompacta
} from '@/utils/cotizacionProcesos';

const props = defineProps({
    cotizaciones: {
        type: Array,
        default: () => []
    },
    etapaActiva: {
        type: String,
        default: ETAPAS_PROCESO.TODAS
    }
});

const emit = defineEmits(['update:etapaActiva']);

const tarjetasProceso = computed(() => {
    return DEFINICION_ETAPAS.map((etapa) => {
        const lista = filtrarPorEtapa(props.cotizaciones, etapa.id);
        return {
            ...etapa,
            cantidad: lista.length,
            monto: `Bs ${formatMonedaCompacta(sumarMonto(lista))}`
        };
    });
});

function alternarEtapa(id) {
    if (props.etapaActiva === id) {
        emit('update:etapaActiva', ETAPAS_PROCESO.TODAS);
    } else {
        emit('update:etapaActiva', id);
    }
}
</script>

<template>
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <button
            v-for="card in tarjetasProceso"
            :key="card.id"
            type="button"
            @click="alternarEtapa(card.id)"
            class="text-left rounded-xl p-5 transition-all duration-150 cursor-pointer border flex flex-col justify-between gap-4"
            :class="[
                etapaActiva === card.id
                    ? 'bg-surface-900 dark:bg-surface-0 text-white dark:text-surface-900 border-surface-900 dark:border-surface-0 shadow-md'
                    : 'bg-white dark:bg-surface-900 text-surface-900 dark:text-surface-0 border-surface-200 dark:border-surface-800 hover:border-surface-400 dark:hover:border-surface-600 shadow-sm'
            ]"
        >
            <div class="flex items-start justify-between gap-2 w-full">
                <div>
                    <span
                        class="inline-block text-[11px] font-bold uppercase tracking-wider px-2 py-0.5 rounded mb-1.5"
                        :class="
                            etapaActiva === card.id
                                ? 'bg-white/15 text-white dark:bg-surface-900/15 dark:text-surface-900'
                                : 'bg-surface-100 dark:bg-surface-800 text-surface-600 dark:text-surface-300'
                        "
                    >
                        {{ card.area }}
                    </span>
                    <div class="font-bold text-sm leading-snug">
                        {{ card.titulo }}
                    </div>
                    <div
                        class="text-xs mt-0.5"
                        :class="etapaActiva === card.id ? 'text-surface-300 dark:text-surface-600' : 'text-surface-500 dark:text-surface-400'"
                    >
                        {{ card.detalle }}
                    </div>
                </div>

                <div
                    class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0"
                    :class="
                        etapaActiva === card.id
                            ? 'bg-white/10 text-white dark:bg-surface-900/10 dark:text-surface-900'
                            : 'bg-surface-100 dark:bg-surface-800 text-surface-600 dark:text-surface-300'
                    "
                >
                    <i :class="[card.icono, 'text-sm']"></i>
                </div>
            </div>

            <div
                class="flex items-baseline justify-between pt-3 border-t"
                :class="etapaActiva === card.id ? 'border-white/15 dark:border-surface-900/15' : 'border-surface-100 dark:border-surface-800'"
            >
                <div class="flex items-baseline gap-1.5">
                    <span class="text-2xl font-extrabold leading-none">
                        {{ card.cantidad }}
                    </span>
                    <span
                        class="text-xs"
                        :class="etapaActiva === card.id ? 'text-surface-300 dark:text-surface-600' : 'text-surface-500 dark:text-surface-400'"
                    >
                        {{ card.cantidad === 1 ? 'expediente' : 'expedientes' }}
                    </span>
                </div>
                <span
                    class="text-xs font-semibold"
                    :class="etapaActiva === card.id ? 'text-surface-200 dark:text-surface-700' : 'text-surface-700 dark:text-surface-200'"
                >
                    {{ card.monto }}
                </span>
            </div>
        </button>
    </div>
</template>
