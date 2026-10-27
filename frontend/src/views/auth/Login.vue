<template>
    <div class="min-h-screen w-full flex flex-col justify-center items-center px-4 py-10 bg-surface-100 dark:bg-surface-950 relative overflow-hidden">
        <!-- Retícula arquitectónica sutil de fondo -->
        <div
            class="absolute inset-0 pointer-events-none opacity-[0.35] dark:opacity-[0.12]"
            style="background-image: radial-gradient(#94a3b8 1px, transparent 1px); background-size: 24px 24px;"
        ></div>

        <FloatingConfigurator />

        <div class="w-full max-w-[420px] relative z-10">
            <!-- Contenedor exterior tipo marco (Bento Frame 2026) -->
            <div class="p-1.5 rounded-2xl bg-surface-200/70 dark:bg-surface-800/70 border border-surface-300/80 dark:border-surface-700/80 shadow-xl backdrop-blur-sm">
                <!-- Tarjeta interior -->
                <div class="bg-white dark:bg-surface-900 rounded-[14px] p-8 sm:p-9 border border-surface-100 dark:border-surface-800">
                    <!-- Logo y título centrado -->
                    <div class="flex flex-col items-center text-center mb-7">
                        <div class="bg-white rounded-xl px-3.5 py-2 mb-5">
                            <img
                                :src="logoCfchm"
                                alt="Campo Ferial Chuquiago Marka"
                                class="h-16 w-auto object-contain"
                            />
                        </div>

                        <h1 class="text-2xl font-bold tracking-tight text-surface-900 dark:text-surface-0 m-0">
                            Iniciar sesión
                        </h1>
                        <p class="text-sm text-surface-500 dark:text-surface-400 mt-1.5 mb-0">
                            Sistema de Gestión de Eventos y Reservas
                        </p>
                    </div>

                    <Message v-if="errors.general" severity="error" :closable="false" class="mb-5 text-sm">
                        {{ errors.general }}
                    </Message>

                    <form @submit.prevent="funIngresar" class="flex flex-col gap-4" novalidate>
                        <div class="flex flex-col gap-1.5">
                            <label for="username" class="text-sm font-medium text-surface-700 dark:text-surface-200">
                                Usuario
                            </label>
                            <IconField>
                                <InputIcon class="pi pi-user text-surface-400" />
                                <InputText
                                    id="username"
                                    v-model="usuario.username"
                                    type="text"
                                    placeholder="nombre.apellido"
                                    autocomplete="username"
                                    class="w-full !py-2.5"
                                    :invalid="!!errors.username"
                                />
                            </IconField>
                            <small v-if="errors.username" class="text-red-500 text-xs">{{ errors.username[0] }}</small>
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label for="password" class="text-sm font-medium text-surface-700 dark:text-surface-200">
                                Contraseña
                            </label>
                            <IconField>
                                <InputIcon class="pi pi-lock text-surface-400 z-10" />
                                <Password
                                    id="password"
                                    v-model="usuario.password"
                                    placeholder="••••••••"
                                    :toggleMask="true"
                                    :feedback="false"
                                    fluid
                                    inputClass="w-full !py-2.5 !pl-10"
                                    :invalid="!!errors.password"
                                />
                            </IconField>
                            <small v-if="errors.password" class="text-red-500 text-xs">{{ errors.password[0] }}</small>
                        </div>

                        <div class="flex items-center justify-between pt-1">
                            <div class="flex items-center gap-2">
                                <Checkbox v-model="checked" inputId="rememberme" binary />
                                <label for="rememberme" class="text-sm text-surface-600 dark:text-surface-300 cursor-pointer select-none">
                                    Mantener sesión activa
                                </label>
                            </div>
                        </div>

                        <Button
                            type="submit"
                            label="Continuar"
                            icon="pi pi-arrow-right"
                            iconPos="right"
                            class="w-full !py-2.5 font-semibold mt-2 shadow-sm"
                            :loading="loading"
                        />
                    </form>
                </div>

                <!-- Pie integrado en el marco exterior -->
                <div class="py-2.5 px-4 flex items-center justify-between text-[11px] font-medium text-surface-500 dark:text-surface-400">
                    <span>Chuquiago Marka · UCPP</span>
                    <span>Gestión {{ new Date().getFullYear() }}</span>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import FloatingConfigurator from '@/components/FloatingConfigurator.vue';
import logoCfchm from '@/assets/logo-cfchm.png';
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const usuario = ref({ username: '', password: '' });
const errors = ref({});
const router = useRouter();
const authStore = useAuthStore();
const checked = ref(false);
const loading = ref(false);

async function funIngresar() {
    errors.value = {};
    loading.value = true;

    try {
        await authStore.login(usuario.value);
        router.push('/admin/dashboard');
    } catch (error) {
        const respuesta = error.response?.data;
        if (respuesta?.errors) {
            errors.value = respuesta.errors;
        } else if (respuesta?.message) {
            errors.value = { general: respuesta.message };
        } else {
            errors.value = { general: 'No se pudo conectar con el servidor. Verifique su conexión.' };
        }
    } finally {
        loading.value = false;
    }
}
</script>
