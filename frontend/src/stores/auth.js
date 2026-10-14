import { defineStore } from 'pinia';
import authService from '@/services/auth.service';
import router from '@/router';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        token: localStorage.getItem('access_token') || null,
        user: JSON.parse(localStorage.getItem('user_data') || 'null'),
        roles: JSON.parse(localStorage.getItem('user_roles') || '[]'),
        permissions: JSON.parse(localStorage.getItem('user_permissions') || '[]'),
        isLoaded: false
    }),

    getters: {
        isAuthenticated: (state) => !!state.token,
        isAdmin: (state) => state.roles.includes('Administrador'),
        userDisplayName: (state) => state.user?.name || 'Usuario',
        userRoleSummary: (state) => state.roles.length > 0 ? state.roles.join(', ') : 'Sin Rol'
    },

    actions: {
        setAuth(data) {
            this.token = data.access_token || this.token;
            if (data.access_token) {
                localStorage.setItem('access_token', data.access_token);
            }

            if (data.user) {
                this.user = {
                    id: data.user.id,
                    name: data.user.name,
                    alias: data.user.alias,
                    username: data.user.username,
                    email: data.user.email,
                    activo: data.user.activo
                };
                this.roles = Array.isArray(data.user.roles) ? data.user.roles : [];
                this.permissions = Array.isArray(data.user.permissions) ? data.user.permissions : [];

                localStorage.setItem('user_data', JSON.stringify(this.user));
                localStorage.setItem('user_roles', JSON.stringify(this.roles));
                localStorage.setItem('user_permissions', JSON.stringify(this.permissions));
            }
            this.isLoaded = true;
        },

        async login(credenciales) {
            const { data } = await authService.login(credenciales);
            this.setAuth(data);
            return data;
        },

        async fetchMe() {
            if (!this.token) return null;
            try {
                const { data } = await authService.perfil();
                this.setAuth({ user: data });
                return data;
            } catch (error) {
                if (error.response?.status === 401) {
                    this.logout();
                }
                throw error;
            }
        },

        logout() {
            try {
                if (this.token) {
                    authService.salir().catch(() => {});
                }
            } finally {
                this.token = null;
                this.user = null;
                this.roles = [];
                this.permissions = [];
                this.isLoaded = false;

                localStorage.removeItem('access_token');
                localStorage.removeItem('user_data');
                localStorage.removeItem('user_roles');
                localStorage.removeItem('user_permissions');

                router.push('/login');
            }
        },

        can(permission) {
            if (this.isAdmin) return true;
            if (!permission) return true;
            if (Array.isArray(permission)) {
                return permission.some((p) => this.permissions.includes(p));
            }
            return this.permissions.includes(permission);
        },

        hasRole(role) {
            if (this.isAdmin) return true;
            if (!role) return true;
            if (Array.isArray(role)) {
                return role.some((r) => this.roles.includes(r));
            }
            return this.roles.includes(role);
        }
    }
});
