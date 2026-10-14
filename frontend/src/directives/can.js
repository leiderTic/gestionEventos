import { useAuthStore } from '@/stores/auth';

export const canDirective = {
    mounted(el, binding) {
        checkPermission(el, binding);
    },
    updated(el, binding) {
        checkPermission(el, binding);
    }
};

function checkPermission(el, binding) {
    const authStore = useAuthStore();
    const permission = binding.value;

    if (!permission) return;

    const hasPermission = authStore.can(permission);

    if (!hasPermission) {
        el.style.display = 'none';
        // Alternativamente, se puede remover del DOM o deshabilitar
    } else {
        el.style.display = '';
    }
}

export default canDirective;
