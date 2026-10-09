<template>
    <div class="flex min-h-[100vh] min-w-[100vw] overflow-hidden bg-surface-0 dark:bg-surface-950 font-sans">
        <!-- Columna Izquierda (Branding / Hero Imagen) -->
        <div class="hidden lg:flex flex-col justify-between w-1/2 bg-surface-900 border-r border-surface-200 dark:border-surface-800 relative p-12">
            <div class="absolute inset-0 z-0 overflow-hidden pointer-events-none">
                <div class="absolute top-[-20%] left-[-10%] w-[50rem] h-[50rem] bg-primary-500/20 rounded-full blur-[100px] opacity-60"></div>
                <div class="absolute bottom-[-10%] right-[-10%] w-[40rem] h-[40rem] bg-teal-500/10 rounded-full blur-[80px] opacity-50"></div>
                <!-- Patrón sutil (opcional) -->
                <div class="absolute inset-0 opacity-[0.03] bg-[radial-gradient(#fff_1px,transparent_1px)] [background-size:20px_20px]"></div>
            </div>
            
            <div class="relative z-10 flex items-center gap-3 cursor-pointer" @click="$router.push('/')">
                <i class="pi pi-calendar text-primary text-4xl font-black"></i>
                <span class="text-3xl font-black tracking-tight text-white">Eventos<span class="text-primary-400">Pro</span></span>
            </div>

            <div class="relative z-10 mt-auto text-white max-w-xl pb-10">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded bg-surface-800/80 backdrop-blur border border-surface-700 text-xs font-bold text-primary-300 mb-6 uppercase tracking-wider shadow-sm">
                    Sistema de Administración
                </div>
                <h1 class="text-5xl font-black tracking-tight leading-[1.1] mb-6 text-white text-balance">
                    Gestión Integral para<br/>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary-400 to-teal-300">Organización Institucional</span>
                </h1>
                <p class="text-surface-300 text-lg leading-relaxed max-w-lg">
                    Accede al portal administrativo para controlar reservas de espacios, realizar cotizaciones con tarifas vigentes y emitir reportes gerenciales.
                </p>
                
                <div class="flex items-center gap-4 mt-8 pt-6 border-t border-surface-700/50">
                    <div class="flex -space-x-3">
                        <div class="w-10 h-10 rounded-full border-2 border-surface-900 bg-surface-700 flex items-center justify-center text-xs font-bold shadow"><i class="pi pi-check text-green-400"></i></div>
                        <div class="w-10 h-10 rounded-full border-2 border-surface-900 bg-surface-700 flex items-center justify-center text-xs font-bold shadow"><i class="pi pi-bolt text-yellow-400"></i></div>
                        <div class="w-10 h-10 rounded-full border-2 border-surface-900 bg-surface-700 flex items-center justify-center text-xs font-bold shadow"><i class="pi pi-shield text-blue-400"></i></div>
                    </div>
                    <span class="text-sm font-medium text-surface-400">Plataforma segura y optimizada</span>
                </div>
            </div>
        </div>

        <!-- Columna Derecha (Formulario de Login) -->
        <div class="w-full lg:w-1/2 flex items-center justify-center p-6 sm:p-12 md:p-20 relative bg-surface-0 dark:bg-surface-950 z-10">
            <FloatingConfigurator />
            <div class="w-full max-w-[28rem] relative z-10 animate-fade-in-up">
                
                <!-- Navbar / Logo en Móvil -->
                <div class="lg:hidden flex justify-start items-center gap-2 mb-12 cursor-pointer" @click="$router.push('/')">
                    <i class="pi pi-calendar text-primary text-3xl font-black"></i>
                    <span class="text-2xl font-black tracking-tight text-surface-900 dark:text-surface-0">Eventos<span class="text-primary">Pro</span></span>
                </div>

                <div class="text-left mb-10">
                    <h2 class="text-surface-900 dark:text-surface-0 text-3xl sm:text-4xl font-bold mb-3 tracking-tight">Iniciar Sesión</h2>
                    <p class="text-surface-500 dark:text-surface-400 font-medium text-lg">Ingresa tus credenciales administrativas</p>
                </div>

                <!-- Banner de Error General -->
                <Message v-if="errors.general" severity="error" :closable="false" class="mb-6 shadow-sm">{{ errors.general }}</Message>

                <form @submit.prevent="funIngresar" class="flex flex-col gap-6" novalidate>
                    <div class="flex flex-col gap-2">
                        <label for="username" class="font-semibold text-surface-700 dark:text-surface-300 text-sm uppercase tracking-wider">Usuario</label>
                        <InputText id="username" type="text" placeholder="Ej. administrador" 
                            class="w-full p-4!" 
                            v-model="usuario.username" 
                            :class="{'p-invalid ring-1 ring-red-500': errors.username}" />
                        <small v-if="errors.username" class="text-red-500 font-medium mt-1">{{ errors.username[0] }}</small>
                    </div>

                    <div class="flex flex-col gap-2">
                        <label for="password" class="font-semibold text-surface-700 dark:text-surface-300 text-sm uppercase tracking-wider">Contraseña</label>
                        <Password id="password" v-model="usuario.password" placeholder="Tu contraseña" 
                            :toggleMask="true" 
                            fluid 
                            :feedback="false" 
                            inputClass="w-full p-4!" 
                            :class="{'p-invalid': errors.password}" />
                        <small v-if="errors.password" class="text-red-500 font-medium mt-1">{{ errors.password[0] }}</small>
                    </div>

                    <div class="flex items-center justify-between mt-2 mb-2">
                        <div class="flex items-center gap-2">
                            <Checkbox v-model="checked" id="rememberme" binary></Checkbox>
                            <label for="rememberme" class="text-sm font-medium text-surface-600 dark:text-surface-400 cursor-pointer select-none">Mantener sesión</label>
                        </div>
                        <a href="#" class="font-bold no-underline text-sm text-primary transition-colors hover:text-primary-600">¿Olvidaste tu clave?</a>
                    </div>

                    <Button type="submit" label="Ingresar al Sistema" icon="pi pi-arrow-right" iconPos="right" 
                        class="w-full p-4! text-lg font-bold shadow-md transition-transform hover:-translate-y-0.5 active:translate-y-0 mt-2" 
                        :loading="loading"></Button>
                </form>
                
                <div class="mt-12 pt-6 border-t border-surface-200 dark:border-surface-800 text-center">
                    <p class="text-surface-500 dark:text-surface-400 text-sm">¿No tienes cuenta institucional? <br class="sm:hidden"/> <a href="#" class="text-primary font-bold hover:underline transition-colors">Solicita acceso</a> al administrador</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import FloatingConfigurator from '@/components/FloatingConfigurator.vue';
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import authService from './../../services/auth.service';

const usuario = ref({ username: "", password: "" });
const errors = ref({});
const router = useRouter();
const checked = ref(false);
const loading = ref(false);

async function funIngresar() {
    errors.value = {};
    loading.value = true;

    try {
        const { data } = await authService.login(usuario.value);
        localStorage.setItem('access_token', data.access_token);
        router.push('/admin/dashboard');
    } catch (error) {
        const respuesta = error.response?.data;
        if (respuesta?.errors) {
            // Errores de validación campo por campo (422)
            errors.value = respuesta.errors;
        } else if (respuesta?.message) {
            // Error general (401 credenciales inválidas, etc.)
            errors.value = { general: respuesta.message };
        } else {
            errors.value = { general: 'Ocurrió un error inesperado al intentar iniciar sesión. Verifica tu conexión.' };
        }
    } finally {
        loading.value = false;
    }
}
</script>

<style scoped>
@keyframes fade-in-up {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in-up {
    animation: fade-in-up 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

:deep(.p-password input) {
    padding-block: 1rem;
}
</style>
