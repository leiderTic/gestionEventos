<template>
    <Dialog :visible="visible" @update:visible="$emit('update:visible', $event)" modal :showHeader="false"
        class="!p-0 !border-none !overflow-hidden max-w-[1200px]" :style="{ width: '92vw' }"
        :contentStyle="{ padding: '0' }">
        <template #default>
            <div
                class="bg-[#06352c] dark:bg-surface-900 text-white px-6 py-3.5 flex flex-col sm:flex-row justify-between items-center gap-3 border-b border-white/10 dark:border-surface-700">
                <div class="flex items-center gap-3">
                    <div class="bg-red-500/90 text-white p-2 rounded-xl flex items-center justify-center shadow-md">
                        <i class="pi pi-file-pdf text-xl"></i>
                    </div>
                    <div class="flex flex-col">
                        <span class="font-bold text-lg leading-tight">{{ titulo }}</span>
                        <span class="text-xs text-white/60 mt-0.5">{{ subtitulo }}</span>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a :href="url" download target="_blank"
                        class="p-button p-button-sm !bg-white/15 hover:!bg-white/25 !text-white !border-none !py-1.5 !px-3.5 text-xs font-bold flex items-center gap-1.5 rounded-lg no-underline transition-all">
                        <i class="pi pi-download text-xs"></i>
                        <span>Descargar</span>
                    </a>
                    <a :href="url" target="_blank"
                        class="p-button p-button-sm !bg-white/15 hover:!bg-white/25 !text-white !border-none !py-1.5 !px-2.5 text-xs font-bold flex items-center rounded-lg no-underline transition-all"
                        title="Abrir en pestaña externa">
                        <i class="pi pi-external-link text-xs"></i>
                    </a>
                    <div class="w-px h-6 bg-white/20 mx-1"></div>
                    <Button icon="pi pi-times" text rounded @click="$emit('update:visible', false)"
                        class="!text-white hover:!bg-white/15 w-8 h-8" />
                </div>
            </div>

            <!-- Visor Iframe -->
            <div class="w-full h-[82vh] bg-surface-100 dark:bg-surface-950 flex flex-col">
                <iframe v-if="url" :src="url" class="w-full h-full border-none bg-white"></iframe>
            </div>
        </template>
    </Dialog>
</template>

<script setup>
defineProps({
    visible: {
        type: Boolean,
        default: false
    },
    titulo: {
        type: String,
        default: 'Documento PDF'
    },
    subtitulo: {
        type: String,
        default: ''
    },
    url: {
        type: String,
        default: ''
    }
});

defineEmits(['update:visible']);
</script>
