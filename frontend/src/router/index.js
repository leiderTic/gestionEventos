import AppLayout from '../layout/AppLayout.vue'
import { createRouter, createWebHistory } from 'vue-router';
import Login from '../views/auth/Login.vue'
import Inicio from '../views/web/Inicio.vue'
import Dashboard from '../views/admin/dashboard/Dashboard.vue'
import NotFound from '../views/errors/NotFound.vue'
import CotizacionNuevo from '@/views/admin/cotizacion/CotizacionNuevo.vue';
import Cotizacion from '@/views/admin/cotizacion/Cotizacion.vue';
import CotizacionDetalle from '@/views/admin/cotizacion/CotizacionDetalle.vue';

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
                component: CotizacionNuevo
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
    let token = localStorage.getItem('access_token')

    if (to.meta.requiresAuth) {
        if (token) {
            return true
        } else {
            return { name: 'login' }
        }
    }

    if (to.meta.requiresIfAuth && token) {
        return { name: 'usuario' }
    }

    return true
})

export default router
