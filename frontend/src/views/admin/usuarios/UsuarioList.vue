<script setup>
import { ref, onMounted, computed } from 'vue';
import usuarioServices from '@/services/usuario.services';
import ModalUsuarioForm from '@/components/usuarios/ModalUsuarioForm.vue';
import { useToast } from 'primevue/usetoast';
import { useConfirm } from 'primevue/useconfirm';
import { useAuthStore } from '@/stores/auth';

const toast = useToast();
const confirm = useConfirm();
const authStore = useAuthStore();

const usuarios = ref([]);
const loading = ref(false);

const filtroTexto = ref('');
const filtroRol = ref(null);
const filtroEstado = ref(null);

const modalVisible = ref(false);
const usuarioSeleccionado = ref(null);

const opcionesRoles = [
    { label: 'Administrador', value: 'Administrador' },
    { label: 'Técnico Comercial', value: 'Tecnico Comercial' },
    { label: 'Técnico UAF', value: 'Tecnico UAF' },
    { label: 'Técnico Legal', value: 'Tecnico Legal' }
];

const opcionesEstado = [
    { label: 'Activos', value: true },
    { label: 'Inactivos', value: false }
];

const usuariosFiltrados = computed(() => {
    return usuarios.value.filter((u) => {
        const q = filtroTexto.value.trim().toLowerCase();
        const matchTexto =
            !q ||
            u.name?.toLowerCase().includes(q) ||
            u.username?.toLowerCase().includes(q) ||
            u.email?.toLowerCase().includes(q) ||
            u.alias?.toLowerCase().includes(q);

        const matchRol = !filtroRol.value || (Array.isArray(u.roles) && u.roles.includes(filtroRol.value));
        const matchEstado = filtroEstado.value === null || Boolean(u.activo) === filtroEstado.value;

        return matchTexto && matchRol && matchEstado;
    });
});

onMounted(() => {
    cargarUsuarios();
});

async function cargarUsuarios() {
    loading.value = true;
    try {
        const { data } = await usuarioServices.listar();
        usuarios.value = data || [];
    } catch (e) {
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'No se pudo cargar el listado de usuarios.',
            life: 3000
        });
    } finally {
        loading.value = false;
    }
}

function abrirCrear() {
    usuarioSeleccionado.value = null;
    modalVisible.value = true;
}

function abrirEditar(usuario) {
    usuarioSeleccionado.value = usuario;
    modalVisible.value = true;
}

function confirmarToggleActivo(u) {
    const accion = u.activo ? 'desactivar' : 'activar';
    confirm.require({
        message: `¿Desea ${accion} el acceso de "${u.name}"?`,
        header: 'Confirmar estado de cuenta',
        icon: 'pi pi-exclamation-triangle',
        rejectProps: {
            label: 'Cancelar',
            severity: 'secondary',
            outlined: true
        },
        acceptProps: {
            label: u.activo ? 'Desactivar' : 'Activar',
            severity: u.activo ? 'danger' : 'success'
        },
        accept: async () => {
            try {
                await usuarioServices.toggleActivo(u.id);
                toast.add({
                    severity: 'info',
                    summary: 'Estado actualizado',
                    detail: `Cuenta de ${u.name} actualizada.`,
                    life: 2500
                });
                cargarUsuarios();
            } catch (e) {
                toast.add({
                    severity: 'error',
                    summary: 'Error',
                    detail: e.response?.data?.message || 'No se pudo cambiar el estado.',
                    life: 3500
                });
            }
        }
    });
}

function confirmarEliminar(u) {
    confirm.require({
        message: `¿Eliminar al usuario "${u.name}"? Sus cotizaciones y registros históricos se conservarán.`,
        header: 'Confirmar eliminación',
        icon: 'pi pi-exclamation-triangle',
        rejectProps: {
            label: 'Cancelar',
            severity: 'secondary',
            outlined: true
        },
        acceptProps: {
            label: 'Eliminar',
            severity: 'danger'
        },
        accept: async () => {
            try {
                await usuarioServices.eliminar(u.id);
                toast.add({
                    severity: 'success',
                    summary: 'Eliminado',
                    detail: `Usuario ${u.name} eliminado.`,
                    life: 2500
                });
                cargarUsuarios();
            } catch (e) {
                toast.add({
                    severity: 'error',
                    summary: 'Error',
                    detail: e.response?.data?.message || 'No se pudo eliminar el usuario.',
                    life: 3500
                });
            }
        }
    });
}

function getRolSeverity(rol) {
    switch (rol) {
        case 'Administrador':
            return 'contrast';
        case 'Tecnico Comercial':
            return 'info';
        case 'Tecnico UAF':
            return 'warn';
        case 'Tecnico Legal':
            return 'secondary';
        default:
            return 'secondary';
    }
}
</script>

<template>
    <div class="card p-0 overflow-hidden shadow-sm border border-surface-200 dark:border-surface-700 rounded-xl bg-white dark:bg-surface-900">
        <ConfirmDialog />

        <!-- Cabecera y Filtros (misma línea visual que Espacios y Cotizaciones) -->
        <div class="p-5 border-b border-surface-200 dark:border-surface-700 flex flex-col gap-4">
            <div class="flex flex-col md:flex-row justify-between md:items-center gap-3">
                <div>
                    <h1 class="text-xl font-bold text-surface-900 dark:text-surface-0 m-0">
                        Usuarios y Roles
                    </h1>
                    <p class="text-sm text-surface-500 mt-0.5 m-0">
                        Cuentas de acceso institucional, siglas para códigos CITE/COT y permisos por área.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <Button
                        label="Nuevo usuario"
                        icon="pi pi-plus"
                        @click="abrirCrear"
                    />
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                <div class="md:col-span-6">
                    <IconField>
                        <InputIcon class="pi pi-search" />
                        <InputText
                            v-model="filtroTexto"
                            placeholder="Buscar por nombre, usuario, correo o sigla..."
                            class="w-full"
                        />
                    </IconField>
                </div>

                <div class="md:col-span-3">
                    <Select
                        v-model="filtroRol"
                        :options="opcionesRoles"
                        optionLabel="label"
                        optionValue="value"
                        placeholder="Todos los roles"
                        showClear
                        class="w-full"
                    />
                </div>

                <div class="md:col-span-3">
                    <Select
                        v-model="filtroEstado"
                        :options="opcionesEstado"
                        optionLabel="label"
                        optionValue="value"
                        placeholder="Todos los estados"
                        showClear
                        class="w-full"
                    />
                </div>
            </div>
        </div>

        <!-- Tabla de Usuarios con columnas bien distribuidas -->
        <DataTable
            :value="usuariosFiltrados"
            :loading="loading"
            dataKey="id"
            paginator
            :rows="10"
            :rowsPerPageOptions="[10, 20, 50]"
            rowHover
            responsiveLayout="scroll"
            emptyMessage="No se encontraron usuarios."
        >
            <!-- Nombre y Sigla -->
            <Column field="name" header="FUNCIONARIO" sortable style="min-width: 220px">
                <template #body="{ data }">
                    <div class="flex items-center gap-3">
                        <span class="w-8 h-8 rounded-lg bg-surface-100 dark:bg-surface-800 border border-surface-200 dark:border-surface-700 text-surface-700 dark:text-surface-200 text-xs font-mono font-bold flex items-center justify-center shrink-0">
                            {{ data.alias || data.name?.substring(0, 2).toUpperCase() }}
                        </span>
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="font-semibold text-surface-900 dark:text-surface-0 text-sm truncate">
                                {{ data.name }}
                            </span>
                            <span
                                v-if="data.id === authStore.user?.id"
                                class="text-[10px] font-semibold bg-surface-100 dark:bg-surface-800 text-surface-600 dark:text-surface-300 px-1.5 py-0.5 rounded border border-surface-200 dark:border-surface-700"
                            >
                                Actual
                            </span>
                        </div>
                    </div>
                </template>
            </Column>

            <!-- Usuario (Login) -->
            <Column field="username" header="USUARIO" sortable style="min-width: 150px">
                <template #body="{ data }">
                    <span class="font-mono text-xs text-surface-700 dark:text-surface-300">
                        {{ data.username }}
                    </span>
                </template>
            </Column>

            <!-- Correo -->
            <Column field="email" header="CORREO" sortable style="min-width: 190px">
                <template #body="{ data }">
                    <span class="text-sm text-surface-600 dark:text-surface-400">
                        {{ data.email }}
                    </span>
                </template>
            </Column>

            <!-- Roles -->
            <Column header="ROLES ASIGNADOS" style="min-width: 180px">
                <template #body="{ data }">
                    <div class="flex flex-wrap gap-1.5">
                        <Tag
                            v-for="r in data.roles"
                            :key="r"
                            :value="r"
                            :severity="getRolSeverity(r)"
                            class="!text-xs"
                        />
                        <span v-if="!data.roles || data.roles.length === 0" class="text-xs text-surface-400">
                            Sin asignar
                        </span>
                    </div>
                </template>
            </Column>

            <!-- Estado -->
            <Column field="activo" header="ESTADO" sortable style="min-width: 120px">
                <template #body="{ data }">
                    <button
                        type="button"
                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded text-xs font-medium transition-colors cursor-pointer border bg-transparent"
                        :class="
                            data.activo
                                ? 'text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800 hover:bg-emerald-50 dark:hover:bg-emerald-950/30'
                                : 'text-red-700 dark:text-red-400 border-red-200 dark:border-red-800 hover:bg-red-50 dark:hover:bg-red-950/30'
                        "
                        :disabled="data.id === authStore.user?.id"
                        @click="confirmarToggleActivo(data)"
                        v-tooltip.top="data.id === authStore.user?.id ? 'Sesión actual' : 'Cambiar estado de acceso'"
                    >
                        <span class="w-1.5 h-1.5 rounded-full" :class="data.activo ? 'bg-emerald-600' : 'bg-red-600'"></span>
                        {{ data.activo ? 'Activo' : 'Inactivo' }}
                    </button>
                </template>
            </Column>

            <!-- Acciones -->
            <Column header="ACCIONES" alignFrozen="right" style="width: 110px">
                <template #body="{ data }">
                    <div class="flex items-center gap-1">
                        <Button
                            icon="pi pi-pencil"
                            severity="secondary"
                            text
                            rounded
                            v-tooltip.top="'Editar usuario'"
                            @click="abrirEditar(data)"
                        />
                        <Button
                            icon="pi pi-trash"
                            severity="danger"
                            text
                            rounded
                            :disabled="data.id === authStore.user?.id"
                            v-tooltip.top="'Eliminar usuario'"
                            @click="confirmarEliminar(data)"
                        />
                    </div>
                </template>
            </Column>
        </DataTable>

        <!-- Modal Modular de Creación / Edición -->
        <ModalUsuarioForm
            v-model:visible="modalVisible"
            :usuario="usuarioSeleccionado"
            @saved="cargarUsuarios"
        />
    </div>
</template>
