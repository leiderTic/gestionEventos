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
router.beforeEach((to, from) => {
    const authStore = useAuthStore();
    const token = authStore.token || localStorage.getItem('access_token');

    if (to.meta.requiresAuth) {
        if (!token) {
            return { name: 'login' };
        }

        // Validación de permisos
        if (to.meta.permission && !authStore.can(to.meta.permission)) {
            return { name: 'dashboard' };
        }

        return true;
    }

    if (to.meta.requiresIfAuth && token) {
        return { name: 'dashboard' };
    }

    return true;
})

export default router
