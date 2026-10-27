<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useToast } from 'primevue/usetoast';
import { useAuthStore } from '@/stores/auth';
import cotizacionServices from '@/services/cotizacion.services';
import TablaCotizaciones from '@/components/cotizacion/TablaCotizaciones.vue';

const route = useRoute();
const toast = useToast();
const authStore = useAuthStore();

const cotizaciones = ref([]);
const loading = ref(true);

const tieneBandejaInstitucional = computed(() =>
    authStore.can(['cotizaciones.ver_todas', 'uaf.ver_con_boucher', 'legal.ver_pagadas'])
);

// Determina el alcance real según la ruta activa y los permisos del usuario
const scopeActivo = computed(() => {
    if (route.meta?.scope === 'propias') {
        return 'propias';
    }
    if (tieneBandejaInstitucional.value) {
        return 'bandeja';
    }
    return 'propias';
});

const esVistaPropias = computed(() => scopeActivo.value === 'propias');

const configuracionVista = computed(() => {
    if (esVistaPropias.value) {
        return {
            titulo: 'Mis Cotizaciones',
            subtitulo: 'Cartera personal de cotizaciones emitidas y solicitudes bajo tu gestión comercial.',
            mostrarBotonNueva: authStore.can('cotizaciones.crear'),
            mostrarEjecutivo: false,
            emptyMessage: 'Aún no has emitido cotizaciones con tu usuario.'
        };
    }

    return {
        titulo: 'Control de Cotizaciones',
        subtitulo: 'Bandeja institucional de verificación financiera, formalización jurídica y seguimiento general.',
        mostrarBotonNueva: false,
        mostrarEjecutivo: true,
        emptyMessage: 'No existen cotizaciones habilitadas para revisión en tu bandeja.'
    };
});

const loadCotizaciones = async () => {
    try {
        loading.value = true;
        const res = await cotizacionServices.listar({ scope: scopeActivo.value });
        cotizaciones.value = res.data;
    } catch (e) {
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'No se pudieron cargar las cotizaciones.',
            life: 3000
        });
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    loadCotizaciones();
});

watch(
    () => route.fullPath,
    () => {
        loadCotizaciones();
    }
);
</script>

<template>
    <div>
        <Toast />
        <TablaCotizaciones
            :cotizaciones="cotizaciones"
            :loading="loading"
            :titulo="configuracionVista.titulo"
            :subtitulo="configuracionVista.subtitulo"
            :mostrarBotonNueva="configuracionVista.mostrarBotonNueva"
            :mostrarEjecutivo="configuracionVista.mostrarEjecutivo"
            :emptyMessage="configuracionVista.emptyMessage"
        />
    </div>
</template>