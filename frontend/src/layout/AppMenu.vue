<script setup>
import { ref } from 'vue';
import AppMenuItem from './AppMenuItem.vue';

const model = ref([
    {
        label: 'COMERCIAL',
        items: [
            { label: 'Dashboard', icon: 'pi pi-fw pi-chart-bar', to: '/admin/dashboard' },
            { label: 'CRM Comercial', icon: 'pi pi-fw pi-comments', to: '/admin/crm' },
            { label: 'Nueva Cotización', icon: 'pi pi-fw pi-plus-circle', to: '/admin/cotizacion/nueva' },
            { label: 'Cotizaciones', icon: 'pi pi-fw pi-file', to: '/admin/cotizacion' }
        ]
    },
    {
        label: 'OPERACIONES',
        items: [
            { label: 'Calendario de Eventos', icon: 'pi pi-fw pi-calendar', to: '/admin/calendario' }
        ]
    },
    {
        label: 'ANÁLISIS',
        items: [
            { label: 'Reportes', icon: 'pi pi-fw pi-chart-line', to: '/admin/reportes' },
            { label: 'Gestión de Roles', icon: 'pi pi-fw pi-users', to: '/admin/roles' },
            { label: 'Sanciones', icon: 'pi pi-fw pi-exclamation-triangle', to: '/admin/sanciones' }
        ]
    }
]);
</script>

<template>
    <ul class="layout-menu">
        <template v-for="(item, i) in model" :key="item">
            <app-menu-item v-if="!item.separator" :item="item" :index="i"></app-menu-item>
            <li v-if="item.separator" class="menu-separator"></li>
        </template>
    </ul>
</template>

<style lang="scss" scoped></style>
