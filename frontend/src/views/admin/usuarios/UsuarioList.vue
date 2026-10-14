<script setup>
import { ref, onMounted, computed } from 'vue';
import usuarioServices from '@/services/usuario.services';
import { useToast } from 'primevue/usetoast';
import { useConfirm } from 'primevue/useconfirm';
import { useAuthStore } from '@/stores/auth';

const toast = useToast();
const confirm = useConfirm();
const authStore = useAuthStore();

const usuarios = ref([]);
const rolesDisponibles = ref([]);
const loading = ref(false);
const saving = ref(false);

const filtroTexto = ref('');
const filtroRol = ref(null);

// Modal state
const modalUsuario = ref(false);
const esEdicion = ref(false);
const formUsuario = ref({
    id: null,
    name: '',
    alias: '',
    username: '',
    email: '',
    password: '',
    activo: true,
    roles: []
});
const errors = ref({});

const rolesFijos = [
    { name: 'Administrador', desc: 'Control total del sistema y gestión de usuarios', color: 'danger', icon: 'pi pi-shield' },
    { name: 'Tecnico Comercial', desc: 'Cotizaciones, reserva (20%), bouchers y pagos', color: 'success', icon: 'pi pi-briefcase' },
    { name: 'Tecnico UAF', desc: 'Recepción de pagos con boucher, C21 y facturación', color: 'warn', icon: 'pi pi-receipt' },
    { name: 'Tecnico Legal', desc: 'Revisión legal y contratos (eventos 100% pagados)', color: 'info', icon: 'pi pi-book' }
];

const usuariosFiltrados = computed(() => {
    return usuarios.value.filter((u) => {
        const matchesTexto =
            !filtroTexto.value ||
            u.name.toLowerCase().includes(filtroTexto.value.toLowerCase()) ||
            u.username.toLowerCase().includes(filtroTexto.value.toLowerCase()) ||
            u.email.toLowerCase().includes(filtroTexto.value.toLowerCase());

        const matchesRol = !filtroRol.value || (u.roles && u.roles.includes(filtroRol.value));

        return matchesTexto && matchesRol;
    });
});

onMounted(() => {
    cargarUsuarios();
    cargarRoles();
});

async function cargarUsuarios() {
    loading.value = true;
    try {
        const { data } = await usuarioServices.listar();
        usuarios.value = data;
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

async function cargarRoles() {
    try {
        const { data } = await usuarioServices.roles();
        rolesDisponibles.value = data;
    } catch (e) {
        console.error('Error cargando roles:', e);
    }
}

function abrirModalCrear() {
    esEdicion.value = false;
    errors.value = {};
    formUsuario.value = {
        id: null,
        name: '',
        alias: '',
        username: '',
        email: '',
        password: '',
        activo: true,
        roles: ['Tecnico Comercial']
    };
    modalUsuario.value = true;
}

function abrirModalEditar(u) {
    esEdicion.value = true;
    errors.value = {};
    formUsuario.value = {
        id: u.id,
        name: u.name,
        alias: u.alias,
        username: u.username,
        email: u.email,
        password: '',
        activo: u.activo,
        roles: [...(u.roles || [])]
    };
    modalUsuario.value = true;
}

function onNombreChange() {
    if (!esEdicion.value && formUsuario.value.name) {
        // Generar alias
        const parts = formUsuario.value.name.trim().split(/\s+/);
        formUsuario.value.alias = (parts[0][0] + (parts[1] ? parts[1][0] : '')).toUpperCase();
        // Generar username sugerido si está vacío
        if (!formUsuario.value.username) {
            formUsuario.value.username = formUsuario.value.name
                .toLowerCase()
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .replace(/\s+/g, '.');
            formUsuario.value.email = `${formUsuario.value.username}@it.com`;
        }
    }
}

function toggleRol(nombreRol) {
    const idx = formUsuario.value.roles.indexOf(nombreRol);
    if (idx === -1) {
        formUsuario.value.roles.push(nombreRol);
    } else {
        formUsuario.value.roles.splice(idx, 1);
    }
}

async function guardarUsuario() {
    errors.value = {};
    if (formUsuario.value.roles.length === 0) {
        errors.value.roles = ['Debe asignar al menos un rol al usuario.'];
        return;
    }

    saving.value = true;
    try {
        if (esEdicion.value) {
            await usuarioServices.actualizar(formUsuario.value.id, formUsuario.value);
            toast.add({
                severity: 'success',
                summary: 'Actualizado',
                detail: 'Usuario actualizado correctamente.',
                life: 3000
            });
        } else {
            await usuarioServices.crear(formUsuario.value);
            toast.add({
                severity: 'success',
                summary: 'Creado',
                detail: 'Usuario registrado exitosamente.',
                life: 3000
            });
        }
        modalUsuario.value = false;
        cargarUsuarios();
    } catch (e) {
        const res = e.response?.data;
        if (res?.errors) {
            errors.value = res.errors;
        } else if (res?.message) {
            toast.add({
                severity: 'error',
                summary: 'Atención',
                detail: res.message,
                life: 4000
            });
        }
    } finally {
        saving.value = false;
    }
}

function confirmarToggleActivo(u) {
    const accion = u.activo ? 'desactivar' : 'activar';
    confirm.require({
        message: `¿Está seguro de ${accion} la cuenta de "${u.name}"? ${
            u.activo ? 'El usuario perderá acceso al sistema de inmediato.' : 'Podrá volver a iniciar sesión.'
        }`,
        header: 'Confirmar cambio de estado',
        icon: 'pi pi-exclamation-triangle',
        acceptClass: u.activo ? 'p-button-danger' : 'p-button-success',
        acceptLabel: u.activo ? 'Desactivar' : 'Activar',
        rejectLabel: 'Cancelar',
        accept: async () => {
            try {
                await usuarioServices.toggleActivo(u.id);
                toast.add({
                    severity: 'info',
                    summary: 'Estado actualizado',
                    detail: `Usuario ${accion}do.`,
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
        message: `¿Desea eliminar al usuario "${u.name}"? Sus cotizaciones y registros históricos se conservarán intactos.`,
        header: 'Confirmar eliminación',
        icon: 'pi pi-trash',
        acceptClass: 'p-button-danger',
        acceptLabel: 'Eliminar',
        rejectLabel: 'Cancelar',
        accept: async () => {
            try {
                await usuarioServices.eliminar(u.id);
                toast.add({
                    severity: 'success',
                    summary: 'Eliminado',
                    detail: 'Usuario eliminado.',
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
            return 'danger';
        case 'Tecnico Comercial':
            return 'success';
        case 'Tecnico UAF':
            return 'warn';
        case 'Tecnico Legal':
            return 'info';
        default:
            return 'secondary';
    }
}
</script>

<template>
    <div class="card p-0 overflow-hidden shadow-lg border-none rounded-2xl bg-white dark:bg-surface-900">
        <!-- Header -->
        <div class="p-6 border-b border-surface-200 dark:border-surface-700 flex flex-col md:flex-row justify-between items-center gap-4 bg-gradient-to-r from-surface-50 to-white dark:from-surface-900 dark:to-surface-800">
            <div>
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-6 bg-primary rounded-full"></span>
                    <h1 class="text-2xl font-black text-surface-900 dark:text-surface-0 m-0 tracking-tight">
                        Gestión de Usuarios y Roles
                    </h1>
                </div>
                <p class="text-surface-500 dark:text-surface-400 text-sm mt-1 mb-0 pl-4">
                    Administración de cuentas institucionales, control de accesos y asignación de roles.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
                <span class="p-input-icon-left flex-1 md:flex-initial">
                    <i class="pi pi-search" />
                    <InputText v-model="filtroTexto" placeholder="Buscar usuario..." class="w-full md:w-60 rounded-xl" />
                </span>

                <Select
                    v-model="filtroRol"
                    :options="[
                        { label: 'Todos los roles', value: null },
                        { label: 'Administrador', value: 'Administrador' },
                        { label: 'Técnico Comercial', value: 'Tecnico Comercial' },
                        { label: 'Técnico UAF', value: 'Tecnico UAF' },
                        { label: 'Técnico Legal', value: 'Tecnico Legal' }
                    ]"
                    optionLabel="label"
                    optionValue="value"
                    placeholder="Filtrar por rol"
                    class="w-full md:w-48 rounded-xl"
                />

                <Button label="Nuevo Usuario" icon="pi pi-user-plus" class="rounded-xl font-bold shadow-md hover:scale-105 transition-transform" @click="abrirModalCrear" />
            </div>
        </div>

        <!-- DataTable -->
        <DataTable
            :value="usuariosFiltrados"
            :loading="loading"
            :paginator="true"
            :rows="10"
            dataKey="id"
            responsiveLayout="scroll"
            class="p-datatable-lg"
            emptyMessage="No se encontraron usuarios registrados."
        >
            <!-- Columna Usuario -->
            <Column header="USUARIO" style="min-width: 16rem">
                <template #body="{ data }">
                    <div class="flex items-center gap-3">
                        <Avatar
                            :label="data.alias || data.name.substring(0, 2).toUpperCase()"
                            shape="circle"
                            size="large"
                            class="font-black bg-primary/10 text-primary dark:bg-primary/20 shrink-0 border border-primary/20"
                        />
                        <div class="flex flex-col">
                            <span class="font-bold text-surface-900 dark:text-surface-0 flex items-center gap-2">
                                {{ data.name }}
                                <span v-if="data.id === authStore.user?.id" class="text-[10px] font-black uppercase tracking-wider bg-surface-200 dark:bg-surface-700 text-surface-700 dark:text-surface-200 px-1.5 py-0.5 rounded">Tú</span>
                            </span>
                            <span class="text-xs text-surface-500 dark:text-surface-400">@{{ data.username }} &bull; {{ data.email }}</span>
                        </div>
                    </div>
                </template>
            </Column>

            <!-- Columna Roles Asignados -->
            <Column header="ROLES ASIGNADOS" style="min-width: 16rem">
                <template #body="{ data }">
                    <div class="flex flex-wrap gap-1.5">
                        <Tag
                            v-for="r in data.roles"
                            :key="r"
                            :value="r"
                            :severity="getRolSeverity(r)"
                            class="!text-[11px] !font-bold px-2.5 py-0.5 rounded-lg shadow-xs"
                        />
                        <span v-if="!data.roles || data.roles.length === 0" class="text-xs italic text-surface-400">Sin roles</span>
                    </div>
                </template>
            </Column>

            <!-- Columna Estado -->
            <Column header="ESTADO" style="min-width: 8rem" class="text-center">
                <template #body="{ data }">
                    <button
                        type="button"
                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold transition-all cursor-pointer border"
                        :class="data.activo
                            ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800'
                            : 'bg-rose-50 text-rose-700 border-rose-200 hover:bg-rose-100 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800'"
                        :disabled="data.id === authStore.user?.id"
                        @click="confirmarToggleActivo(data)"
                        :title="data.id === authStore.user?.id ? 'No puede desactivarse a sí mismo' : 'Click para cambiar estado'"
                    >
                        <span class="w-2 h-2 rounded-full" :class="data.activo ? 'bg-emerald-500' : 'bg-rose-500'"></span>
                        {{ data.activo ? 'Activo' : 'Inactivo' }}
                    </button>
                </template>
            </Column>

            <!-- Columna Acciones -->
            <Column header="ACCIONES" style="min-width: 8rem" class="text-right">
                <template #body="{ data }">
                    <div class="flex justify-end items-center gap-1">
                        <Button
                            icon="pi pi-pencil"
                            text
                            rounded
                            severity="secondary"
                            class="!w-8 !h-8"
                            @click="abrirModalEditar(data)"
                            title="Editar usuario y roles"
                        />
                        <Button
                            icon="pi pi-trash"
                            text
                            rounded
                            severity="danger"
                            class="!w-8 !h-8"
                            :disabled="data.id === authStore.user?.id"
                            @click="confirmarEliminar(data)"
                            :title="data.id === authStore.user?.id ? 'No puede eliminarse a sí mismo' : 'Eliminar usuario'"
                        />
                    </div>
                </template>
            </Column>
        </DataTable>

        <!-- Diálogo Modal: Crear / Editar Usuario -->
        <Dialog
            v-model:visible="modalUsuario"
            modal
            :header="esEdicion ? 'Editar Usuario' : 'Nuevo Usuario'"
            :style="{ width: '560px' }"
            class="p-fluid rounded-2xl"
        >
            <div class="space-y-4 pt-2">
                <!-- Nombre Completo y Alias -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div class="md:col-span-2 flex flex-col gap-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-surface-600 dark:text-surface-300">
                            Nombre Completo <span class="text-red-500">*</span>
                        </label>
                        <InputText
                            v-model="formUsuario.name"
                            @input="onNombreChange"
                            placeholder="Ej. Juan Pérez Ramos"
                            :class="{ 'p-invalid': errors.name }"
                        />
                        <small v-if="errors.name" class="text-red-500 text-xs">{{ errors.name[0] }}</small>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-surface-600 dark:text-surface-300">
                            Alias / Sigla
                        </label>
                        <InputText v-model="formUsuario.alias" placeholder="Ej. JP" maxlength="6" />
                    </div>
                </div>

                <!-- Username y Email -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-surface-600 dark:text-surface-300">
                            Nombre de Usuario <span class="text-red-500">*</span>
                        </label>
                        <InputText
                            v-model="formUsuario.username"
                            placeholder="juan.perez"
                            :class="{ 'p-invalid': errors.username }"
                        />
                        <small v-if="errors.username" class="text-red-500 text-xs">{{ errors.username[0] }}</small>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-surface-600 dark:text-surface-300">
                            Correo Electrónico <span class="text-red-500">*</span>
                        </label>
                        <InputText
                            v-model="formUsuario.email"
                            placeholder="juan.perez@it.com"
                            :class="{ 'p-invalid': errors.email }"
                        />
                        <small v-if="errors.email" class="text-red-500 text-xs">{{ errors.email[0] }}</small>
                    </div>
                </div>

                <!-- Contraseña -->
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-bold uppercase tracking-wider text-surface-600 dark:text-surface-300">
                        {{ esEdicion ? 'Nueva Contraseña (opcional)' : 'Contraseña' }}
                        <span v-if="!esEdicion" class="text-red-500">*</span>
                    </label>
                    <Password
                        v-model="formUsuario.password"
                        :placeholder="esEdicion ? 'Dejar en blanco para mantener la actual' : 'Mínimo 8 caracteres'"
                        :toggleMask="true"
                        :feedback="false"
                        fluid
                        :class="{ 'p-invalid': errors.password }"
                    />
                    <small v-if="errors.password" class="text-red-500 text-xs">{{ errors.password[0] }}</small>
                </div>

                <!-- Selección de Roles Fijos -->
                <div class="pt-2 border-t border-surface-200 dark:border-surface-700">
                    <label class="text-xs font-black uppercase tracking-wider text-surface-700 dark:text-surface-200 block mb-2">
                        Roles del Usuario <span class="text-red-500">*</span>
                    </label>
                    <p class="text-xs text-surface-500 dark:text-surface-400 mb-3">
                        Un usuario puede tener múltiples roles para acceder a diferentes fases del flujo.
                    </p>

                    <div class="space-y-2">
                        <div
                            v-for="r in rolesFijos"
                            :key="r.name"
                            class="p-3 rounded-xl border transition-all cursor-pointer flex items-center justify-between"
                            :class="formUsuario.roles.includes(r.name)
                                ? 'border-primary bg-primary/5 dark:bg-primary/10 shadow-xs'
                                : 'border-surface-200 dark:border-surface-700 hover:border-surface-300'"
                            @click="toggleRol(r.name)"
                        >
                            <div class="flex items-center gap-3">
                                <Checkbox
                                    :modelValue="formUsuario.roles.includes(r.name)"
                                    binary
                                    @click.stop
                                    @change="toggleRol(r.name)"
                                />
                                <div class="flex flex-col">
                                    <div class="flex items-center gap-2">
                                        <i :class="r.icon" class="text-xs text-primary"></i>
                                        <span class="font-bold text-sm text-surface-900 dark:text-surface-0">{{ r.name }}</span>
                                    </div>
                                    <span class="text-xs text-surface-500 dark:text-surface-400 leading-tight mt-0.5">{{ r.desc }}</span>
                                </div>
                            </div>
                            <Tag :value="r.name" :severity="r.color" class="!text-[10px] hidden sm:inline-flex" />
                        </div>
                    </div>
                    <small v-if="errors.roles" class="text-red-500 text-xs block mt-2">{{ errors.roles[0] }}</small>
                </div>
            </div>

            <template #footer>
                <div class="flex justify-end gap-2 pt-3 border-t border-surface-200 dark:border-surface-700">
                    <Button label="Cancelar" icon="pi pi-times" text severity="secondary" @click="modalUsuario = false" />
                    <Button
                        :label="esEdicion ? 'Actualizar' : 'Crear Usuario'"
                        icon="pi pi-check"
                        :loading="saving"
                        @click="guardarUsuario"
                    />
                </div>
            </template>
        </Dialog>
    </div>
</template>
