<script setup>
import { useLayout } from '@/layout/composables/layout';
import AppConfigurator from './AppConfigurator.vue';
import logoCfchm from '@/assets/logo-cfchm.png';
import { ref } from 'vue';
import { useAuthStore } from '@/stores/auth';

const { toggleMenu, toggleDarkMode, isDarkTheme } = useLayout();
const authStore = useAuthStore();
const op = ref();

const toggleProfile = (event) => {
    op.value.toggle(event);
};

const onLogout = () => {
    authStore.logout();
};
</script>

<template>
    <div class="layout-topbar">
        <div class="layout-topbar-logo-container">
            <button class="layout-menu-button layout-topbar-action" @click="toggleMenu">
                <i class="pi pi-bars"></i>
            </button>
            <router-link to="/admin/dashboard" class="layout-topbar-logo flex items-center gap-2">
                <img :src="logoCfchm" alt="Campo Ferial Chuquiago Marka" class="h-12 w-auto object-contain bg-white rounded px-2 py-0.5" />
            </router-link>
        </div>

        <div class="layout-topbar-actions">
            <div class="layout-config-menu">
                <button type="button" class="layout-topbar-action" @click="toggleDarkMode">
                    <i :class="['pi', { 'pi-moon': isDarkTheme, 'pi-sun': !isDarkTheme }]"></i>
                </button>
                <div class="relative">
                    <button
                        v-styleclass="{ selector: '@next', enterFromClass: 'hidden', enterActiveClass: 'p-anchored-overlay-enter-active', leaveToClass: 'hidden', leaveActiveClass: 'p-anchored-overlay-leave-active', hideOnOutsideClick: true }"
                        type="button"
                        class="layout-topbar-action layout-topbar-action-highlight"
                    >
                        <i class="pi pi-palette"></i>
                    </button>
                    <AppConfigurator />
                </div>
            </div>

            <button
                class="layout-topbar-menu-button layout-topbar-action"
                v-styleclass="{ selector: '@next', enterFromClass: 'hidden', enterActiveClass: 'p-anchored-overlay-enter-active', leaveToClass: 'hidden', leaveActiveClass: 'p-anchored-overlay-leave-active', hideOnOutsideClick: true }"
            >
                <i class="pi pi-ellipsis-v"></i>
            </button>

            <div class="layout-topbar-menu hidden lg:block">
                <div class="layout-topbar-menu-content">
                    <button type="button" class="layout-topbar-action" @click="toggleProfile">
                        <i class="pi pi-user"></i>
                        <span>Profile</span>
                    </button>
                </div>
            </div>

            <Popover ref="op">
                <div class="flex flex-col gap-4 w-[18rem]">
                    <div>
                        <span class="font-medium block mb-2 text-color-secondary text-sm uppercase">Usuario</span>
                        <div class="flex items-center gap-4 p-3 bg-surface-100 dark:bg-surface-800 rounded-xl">
                            <Avatar :label="authStore.user?.alias || (authStore.user?.name ? authStore.user.name.substring(0, 2).toUpperCase() : 'U')" size="large" shape="circle" class="bg-primary text-primary-contrast font-bold" />
                            <div class="flex flex-col min-w-0">
                                <span class="font-bold text-base truncate">{{ authStore.user?.name || 'Usuario' }}</span>
                                <span class="text-xs text-muted-color truncate">{{ authStore.user?.email || '@' + authStore.user?.username }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col gap-3">
                        <div class="text-xs font-bold text-muted-color uppercase px-1">Roles asignados</div>
                        <div class="flex flex-wrap gap-1 px-1">
                            <span v-for="rol in authStore.roles" :key="rol" class="font-bold bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 px-2 py-0.5 rounded text-[11px]">
                                {{ rol }}
                            </span>
                            <span v-if="!authStore.roles || authStore.roles.length === 0" class="text-xs text-muted-color italic">Sin rol</span>
                        </div>
                        <div class="flex flex-col gap-2 text-sm px-1 pt-1 border-t border-surface-200 dark:border-surface-700">
                            <div class="flex justify-between items-center">
                                <span class="text-muted-color text-xs">Estado:</span>
                                <span class="text-green-500 flex items-center gap-1 font-medium text-xs">
                                    <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                                    Activo
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="flex border-t border-surface pt-4 gap-3">
                        <Button label="Perfil" icon="pi pi-user-edit" severity="secondary" outlined class="flex-1 text-sm" />
                        <Button label="Salir" icon="pi pi-sign-out" severity="danger" class="flex-1 text-sm" @click="onLogout" />
                    </div>
                </div>
            </Popover>
        </div>
    </div>
</template>
