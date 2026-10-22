import AppLayout from '../layout/AppLayout.vue'
import { createRouter, createWebHistory } from 'vue-router';
import Login from '../views/auth/Login.vue'
import Inicio from '../views/web/Inicio.vue'
import Dashboard from '../views/admin/dashboard/Dashboard.vue'
import NotFound from '../views/errors/NotFound.vue'
import CotizacionNuevo from '@/views/admin/cotizacion/CotizacionNuevo.vue';
import Cotizacion from '@/views/admin/cotizacion/Cotizacion.vue';
import CotizacionDetalle from '@/views/admin/cotizacion/CotizacionDetalle.vue';
import UsuarioList from '@/views/admin/usuarios/UsuarioList.vue';
import EspacioList from '@/views/admin/espacios/EspacioList.vue';
import { useAuthStore } from '@/stores/auth';

const routes = [
    {
        path: '/',
        name: 'inicio',
        component: Inicio,
        meta: {
            requiresIfAuth: true
        }
    },
    {
        path: '/login',
        name: 'login',
        component: Login,
        meta: {
            requiresIfAuth: true
        }
    },
    {
        path: '/admin',
        component: AppLayout,
        meta: {
            requiresAuth: true
        },
        children: [
            {
                path: 'dashboard',
                name: 'dashboard',
                component: Dashboard
            },
            {
                path: 'cotizacion/nueva',
                name: 'cotizacionNueva',
                component: CotizacionNuevo,
                meta: {
                    permission: 'cotizaciones.crear'
                }
            },
            {
                path: 'cotizacion',
                name: 'cotizacion',
                component: Cotizacion
            },
            {
                path: 'cotizacion/:id',
                name: 'cotizacionDetalle',
                component: CotizacionDetalle
            },
            {
                path: 'espacios',
                name: 'espacios',
                component: EspacioList,
                meta: {
                    permission: 'catalogos.gestionar'
                }
            },
            {
                path: 'usuarios',
                name: 'usuarios',
                component: UsuarioList,
                meta: {
                    permission: 'usuarios.gestionar'
                }
            },
            {
                path: 'roles',
                redirect: { name: 'usuarios' }
            }
        ]
    },
    {
        path: '/:pathMatch(.*)*',
        name: 'not-found',
        component: NotFound
    }
]

const router = createRouter({
    history: createWebHistory(),
    routes
})

// guardia de rutas
router.beforeEach(async (to, from) => {
    const authStore = useAuthStore();
    const token = authStore.token || localStorage.getItem('access_token');

    const requiresAuth = to.matched.some(record => record.meta.requiresAuth);
    const requiresIfAuth = to.matched.some(record => record.meta.requiresIfAuth);

    if (requiresAuth) {
        if (!token) {
            return { name: 'login' };
        }

        // Si hay token pero falta el perfil en memoria, sincronizar con backend
        if (!authStore.user) {
            try {
                await authStore.fetchMe();
            } catch (e) {
                authStore.logout();
                return { name: 'login' };
            }
        }

        // Validación de permisos en la ruta actual o ancestros
        const matchedPermission = to.matched.find(record => record.meta.permission)?.meta.permission;
        if (matchedPermission && !authStore.can(matchedPermission)) {
            return { name: 'dashboard' };
        }

        return true;
    }

    if (requiresIfAuth && token) {
        return { name: 'dashboard' };
    }

    return true;
})

export default router
