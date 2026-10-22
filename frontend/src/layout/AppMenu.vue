<script setup>
import { computed } from 'vue';
import AppMenuItem from './AppMenuItem.vue';
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();

const model = computed(() => {
    const sections = [];

    // Sección Comercial / Cotizaciones
    const comercialItems = [
        { label: 'Dashboard', icon: 'pi pi-fw pi-chart-bar', to: '/admin/dashboard' }
    ];

    if (authStore.can('cotizaciones.crear')) {
        comercialItems.push({ label: 'Nueva Cotización', icon: 'pi pi-fw pi-plus-circle', to: '/admin/cotizacion/nueva' });
    }

    if (authStore.can(['cotizaciones.ver_todas', 'cotizaciones.ver_propias', 'uaf.ver_con_boucher', 'legal.ver_pagadas'])) {
        comercialItems.push({ label: 'Cotizaciones', icon: 'pi pi-fw pi-file', to: '/admin/cotizacion' });
    }

    if (authStore.can('cotizaciones.editar')) {
        comercialItems.push({ label: 'CRM Comercial', icon: 'pi pi-fw pi-comments', to: '/admin/crm' });
    }

    if (comercialItems.length > 0) {
        sections.push({
            label: 'GESTIÓN COMERCIAL',
            items: comercialItems
        });
    }

    // Sección Operaciones
    sections.push({
        label: 'OPERACIONES',
        items: [
            { label: 'Calendario de Eventos', icon: 'pi pi-fw pi-calendar', to: '/admin/calendario' }
        ]
    });

    // Sección Administración & Análisis
    const adminItems = [];
    if (authStore.can('catalogos.gestionar')) {
        adminItems.push({ label: 'Espacios y Tarifas', icon: 'pi pi-fw pi-building', to: '/admin/espacios' });
    }
    if (authStore.can('usuarios.gestionar')) {
        adminItems.push({ label: 'Usuarios y Roles', icon: 'pi pi-fw pi-users', to: '/admin/usuarios' });
    }
    adminItems.push({ label: 'Reportes', icon: 'pi pi-fw pi-chart-line', to: '/admin/reportes' });
    adminItems.push({ label: 'Sanciones', icon: 'pi pi-fw pi-exclamation-triangle', to: '/admin/sanciones' });

    sections.push({
        label: 'ADMINISTRACIÓN Y ANÁLISIS',
        items: adminItems
    });

    return sections;
});
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
