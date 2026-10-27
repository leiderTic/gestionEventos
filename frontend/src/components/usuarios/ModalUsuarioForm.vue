<script setup>
import { ref, watch, computed } from 'vue';
import usuarioServices from '@/services/usuario.services';
import { useToast } from 'primevue/usetoast';

const props = defineProps({
    visible: {
        type: Boolean,
        default: false
    },
    usuario: {
        type: Object,
        default: null
    }
});

const emit = defineEmits(['update:visible', 'saved']);

const toast = useToast();
const saving = ref(false);
const errors = ref({});

const esEdicion = computed(() => !!(props.usuario && props.usuario.id));

const form = ref({
    id: null,
    name: '',
    alias: '',
    username: '',
    email: '',
    password: '',
    activo: true,
    roles: []
});

const catalogoRoles = [
    {
        name: 'Administrador',
        desc: 'Control general del sistema, usuarios y catálogos'
    },
    {
        name: 'Tecnico Comercial',
        desc: 'Cotizaciones, documentos de respuesta, reserva y pagos'
    },
    {
        name: 'Tecnico UAF',
        desc: 'Verificación de depósitos, C21 y facturación'
    },
    {
        name: 'Tecnico Legal',
        desc: 'Revisión jurídica y gestión de contratos'
    }
];

function inicializar() {
    errors.value = {};
    if (props.usuario && props.usuario.id) {
        form.value = {
            id: props.usuario.id,
            name: props.usuario.name || '',
            alias: props.usuario.alias || '',
            username: props.usuario.username || '',
            email: props.usuario.email || '',
            password: '',
            activo: Boolean(props.usuario.activo),
            roles: [...(props.usuario.roles || [])]
        };
    } else {
        form.value = {
            id: null,
            name: '',
            alias: '',
            username: '',
            email: '',
            password: '',
            activo: true,
            roles: ['Tecnico Comercial']
        };
    }
}

watch(
    () => props.visible,
    (val) => {
        if (val) {
            inicializar();
        }
    }
);

function onNombreChange() {
    if (!esEdicion.value && form.value.name) {
        const parts = form.value.name.trim().split(/\s+/);
        form.value.alias = (parts[0][0] + (parts[1] ? parts[1][0] : '')).toUpperCase();
        if (!form.value.username) {
            form.value.username = form.value.name
                .toLowerCase()
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .replace(/\s+/g, '.');
            form.value.email = `${form.value.username}@it.com`;
        }
    }
}

function toggleRol(nombreRol) {
    const idx = form.value.roles.indexOf(nombreRol);
    if (idx === -1) {
        form.value.roles.push(nombreRol);
    } else {
        form.value.roles.splice(idx, 1);
    }
}

function cerrar() {
    emit('update:visible', false);
}

async function guardar() {
    errors.value = {};
    if (form.value.roles.length === 0) {
        errors.value.roles = ['Seleccione al menos un rol para el usuario.'];
        return;
    }

    saving.value = true;
    try {
        if (esEdicion.value) {
            await usuarioServices.actualizar(form.value.id, form.value);
            toast.add({
                severity: 'success',
                summary: 'Actualizado',
                detail: `Se guardaron los cambios de ${form.value.name}.`,
                life: 3000
            });
        } else {
            await usuarioServices.crear(form.value);
            toast.add({
                severity: 'success',
                summary: 'Registrado',
                detail: `Usuario ${form.value.name} creado.`,
                life: 3000
            });
        }
        emit('saved');
        cerrar();
    } catch (e) {
        const res = e.response?.data;
        if (res?.errors) {
            errors.value = res.errors;
        } else {
            toast.add({
                severity: 'error',
                summary: 'Error',
                detail: res?.message || 'No se pudo guardar el usuario.',
                life: 3500
            });
        }
    } finally {
        saving.value = false;
    }
}
</script>

<template>
    <Dialog
        :visible="visible"
        @update:visible="(val) => emit('update:visible', val)"
        modal
        :header="esEdicion ? 'Editar usuario' : 'Nuevo usuario'"
        :style="{ width: '540px' }"
        :breakpoints="{ '640px': '95vw' }"
    >
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 pt-1">
            <!-- Nombre y Sigla -->
            <div class="md:col-span-9">
                <label class="block text-sm font-medium text-surface-700 dark:text-surface-200 mb-1">
                    Nombre completo *
                </label>
                <InputText
                    v-model="form.name"
                    @input="onNombreChange"
                    placeholder="Ej. Juan Pérez Ramos"
                    class="w-full"
                    :invalid="!!errors.name"
                />
                <small v-if="errors.name" class="text-red-500 text-xs">{{ errors.name[0] }}</small>
            </div>

            <div class="md:col-span-3">
                <label class="block text-sm font-medium text-surface-700 dark:text-surface-200 mb-1">
                    Sigla / Alias
                </label>
                <InputText
                    v-model="form.alias"
                    placeholder="JP"
                    maxlength="6"
                    class="w-full font-mono uppercase"
                />
            </div>

            <!-- Usuario y Correo -->
            <div class="md:col-span-6">
                <label class="block text-sm font-medium text-surface-700 dark:text-surface-200 mb-1">
                    Usuario *
                </label>
                <InputText
                    v-model="form.username"
                    placeholder="juan.perez"
                    class="w-full"
                    :invalid="!!errors.username"
                />
                <small v-if="errors.username" class="text-red-500 text-xs">{{ errors.username[0] }}</small>
            </div>

            <div class="md:col-span-6">
                <label class="block text-sm font-medium text-surface-700 dark:text-surface-200 mb-1">
                    Correo electrónico *
                </label>
                <InputText
                    v-model="form.email"
                    placeholder="juan.perez@it.com"
                    class="w-full"
                    :invalid="!!errors.email"
                />
                <small v-if="errors.email" class="text-red-500 text-xs">{{ errors.email[0] }}</small>
            </div>

            <!-- Contraseña -->
            <div class="md:col-span-12">
                <label class="block text-sm font-medium text-surface-700 dark:text-surface-200 mb-1">
                    {{ esEdicion ? 'Nueva contraseña (opcional)' : 'Contraseña *' }}
                </label>
                <Password
                    v-model="form.password"
                    :placeholder="esEdicion ? 'Dejar vacío para conservar la actual' : 'Mínimo 8 caracteres'"
                    :toggleMask="true"
                    :feedback="false"
                    fluid
                    inputClass="w-full"
                    :invalid="!!errors.password"
                />
                <small v-if="errors.password" class="text-red-500 text-xs">{{ errors.password[0] }}</small>
            </div>

            <!-- Roles -->
            <div class="md:col-span-12 pt-2 border-t border-surface-200 dark:border-surface-700">
                <label class="block text-sm font-medium text-surface-700 dark:text-surface-200 mb-2">
                    Roles asignados *
                </label>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                    <div
                        v-for="r in catalogoRoles"
                        :key="r.name"
                        @click="toggleRol(r.name)"
                        class="p-3 rounded-lg border cursor-pointer transition-colors flex items-start gap-2.5"
                        :class="
                            form.roles.includes(r.name)
                                ? 'border-surface-900 dark:border-surface-0 bg-surface-50 dark:bg-surface-800'
                                : 'border-surface-200 dark:border-surface-700 hover:border-surface-300'
                        "
                    >
                        <Checkbox
                            :modelValue="form.roles.includes(r.name)"
                            binary
                            class="mt-0.5"
                            @click.stop
                            @change="toggleRol(r.name)"
                        />
                        <div class="flex flex-col min-w-0">
                            <span class="text-xs font-bold text-surface-900 dark:text-surface-0">
                                {{ r.name }}
                            </span>
                            <span class="text-[11px] text-surface-500 dark:text-surface-400 leading-snug mt-0.5">
                                {{ r.desc }}
                            </span>
                        </div>
                    </div>
                </div>
                <small v-if="errors.roles" class="text-red-500 text-xs block mt-1.5">{{ errors.roles[0] }}</small>
            </div>
        </div>

        <template #footer>
            <div class="flex justify-end gap-2">
                <Button label="Cancelar" severity="secondary" text :disabled="saving" @click="cerrar" />
                <Button
                    :label="esEdicion ? 'Guardar cambios' : 'Guardar'"
                    icon="pi pi-check"
                    :loading="saving"
                    @click="guardar"
                />
            </div>
        </template>
    </Dialog>
</template>
