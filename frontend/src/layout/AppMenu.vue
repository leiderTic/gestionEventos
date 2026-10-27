<script setup>
import { computed } from 'vue';
import AppMenuItem from './AppMenuItem.vue';
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();

const model = computed(() => {
    const sections = [];

    // 1. Seguimiento y Control (Dashboard + Bandeja Institucional para Admin / UAF / Legal)
    const seguimientoItems = [
        { label: 'Dashboard', icon: 'pi pi-fw pi-chart-bar', to: '/admin/dashboard' }
    ];

    if (authStore.can(['cotizaciones.ver_todas', 'uaf.ver_con_boucher', 'legal.ver_pagadas'])) {
        seguimientoItems.push({
            label: 'Control de Cotizaciones',
            icon: 'pi pi-fw pi-inbox',
            to: '/admin/cotizacion'
        });
    }

    sections.push({
        label: 'SEGUIMIENTO Y CONTROL',
        items: seguimientoItems
    });

    // 2. Área Comercial (Cartera propia del Ejecutivo Comercial)
    const comercialItems = [];

    if (authStore.can('cotizaciones.crear')) {
        comercialItems.push({
            label: 'Nueva Cotización',
            icon: 'pi pi-fw pi-plus-circle',
            to: '/admin/cotizacion/nueva'
        });
    }

    if (authStore.can(['cotizaciones.ver_propias', 'cotizaciones.crear'])) {
        comercialItems.push({
            label: 'Mis Cotizaciones',
            icon: 'pi pi-fw pi-file',
            to: '/admin/cotizacion/mias'
        });
    }

    if (comercialItems.length > 0) {
        sections.push({
            label: 'ÁREA COMERCIAL',
            items: comercialItems
        });
    }

    // 3. Administración (Catálogos y Usuarios)
    const adminItems = [];
    if (authStore.can('catalogos.gestionar')) {
        adminItems.push({ label: 'Espacios y Tarifas', icon: 'pi pi-fw pi-building', to: '/admin/espacios' });
    }
    if (authStore.can('usuarios.gestionar')) {
        adminItems.push({ label: 'Usuarios y Roles', icon: 'pi pi-fw pi-users', to: '/admin/usuarios' });
    }

    if (adminItems.length > 0) {
        sections.push({
            label: 'ADMINISTRACIÓN',
            items: adminItems
        });
    }

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
