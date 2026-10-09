<template>
    <div class="min-h-[100vh] bg-surface-50 dark:bg-surface-950 flex flex-col relative overflow-hidden">
        <!-- Floating shapes for background -->
        <div class="absolute top-[-10%] left-[-10%] w-[40rem] h-[40rem] bg-primary-400/20 rounded-full blur-3xl opacity-40 z-0"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[30rem] h-[30rem] bg-teal-400/20 rounded-full blur-3xl opacity-40 z-0"></div>

        <!-- Header navbar -->
        <header class="w-full z-10 px-8 py-6 flex justify-between items-center max-w-7xl mx-auto">
            <div class="flex items-center gap-2">
                <i class="pi pi-calendar text-primary text-3xl font-black"></i>
                <span class="text-2xl font-black tracking-tight text-surface-900 dark:text-surface-0">Eventos<span class="text-primary">Pro</span></span>
            </div>
            <div class="flex gap-4">
                <Button label="Ver Servicios" icon="pi pi-compass" text severity="secondary" @click="$router.push('/servicios')" class="font-bold hidden sm:flex" />
                <Button label="Iniciar Sesión" icon="pi pi-user" @click="$router.push('/login')" class="font-bold rounded-full !px-6" />
            </div>
        </header>

        <!-- Main Hero Section -->
        <main class="flex-1 flex flex-col items-center justify-center z-10 px-6 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-surface-200 dark:border-surface-700 bg-surface-0/50 dark:bg-surface-900/50 backdrop-blur-sm mb-8 text-sm font-medium text-surface-600 dark:text-surface-300 shadow-sm animate-fade-in">
                <span class="bg-primary text-white text-xs font-bold px-2 py-0.5 rounded shadow-sm">PORTAL OFICIAL</span>
                Sistema Institucional de Reservas
            </div>
            
            <h1 class="text-5xl md:text-7xl font-black mb-6 text-surface-900 dark:text-surface-0 tracking-tight leading-tight max-w-4xl">
                Gestión avanzada para tus <br class="hidden md:block"/>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary to-teal-400">Espacios y Eventos</span>
            </h1>
            
            <p class="text-lg md:text-xl text-surface-600 dark:text-surface-300 max-w-2xl mb-10 leading-relaxed">
                Centraliza las cotizaciones, administra los ambientes de la institución y coordina los servicios opcionales desde una sola plataforma profesional.
            </p>
            
            <div class="flex flex-col sm:flex-row gap-4">
                <Button label="Acceso Administrativo" icon="pi pi-sign-in" iconPos="right" size="large" class="rounded-full !px-8 font-bold shadow-lg h-14" @click="$router.push('/login')" />
                <Button label="Calendario Público" icon="pi pi-calendar" outlined size="large" severity="secondary" class="rounded-full !px-8 font-bold bg-surface-0/50 dark:bg-surface-900/50 backdrop-blur-sm h-14" @click="$router.push('/servicios')" />
            </div>

            <!-- Features Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-8 mt-20 max-w-5xl text-left bg-surface-0/60 dark:bg-surface-900/60 p-8 rounded-3xl backdrop-blur-md border border-surface-200 dark:border-surface-800 shadow-xl">
                <div class="flex flex-col gap-3">
                    <div class="w-12 h-12 rounded-xl bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary">
                        <i class="pi pi-calculator text-xl"></i>
                    </div>
                    <h3 class="font-bold text-surface-900 dark:text-surface-0 text-lg">Cotizaciones Ágiles</h3>
                    <p class="text-sm text-surface-500 dark:text-surface-400">Genera presupuestos de alquiler con cálculos automatizados entre temporada alta y baja.</p>
                </div>
                <div class="flex flex-col gap-3">
                    <div class="w-12 h-12 rounded-xl bg-teal-100 dark:bg-teal-900/30 flex items-center justify-center text-teal-600 dark:text-teal-400">
                        <i class="pi pi-map text-xl"></i>
                    </div>
                    <h3 class="font-bold text-surface-900 dark:text-surface-0 text-lg">Disponibilidad 360</h3>
                    <p class="text-sm text-surface-500 dark:text-surface-400">Catálogo interactivo de bloques, plazas y salones con aforos estandarizados.</p>
                </div>
                <div class="flex flex-col gap-3">
                    <div class="w-12 h-12 rounded-xl bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center text-orange-600 dark:text-orange-400">
                        <i class="pi pi-chart-pie text-xl"></i>
                    </div>
                    <h3 class="font-bold text-surface-900 dark:text-surface-0 text-lg">Análisis Visual</h3>
                    <p class="text-sm text-surface-500 dark:text-surface-400">Tableros de mando operativos con información fidedigna de ingresos institucionales.</p>
                </div>
            </div>
            <div class="mt-auto pt-10 pb-4 text-sm text-surface-500 dark:text-surface-400">
                &copy; {{ new Date().getFullYear() }} EventosPro Institucional. Todos los derechos reservados.
            </div>
        </main>
    </div>
</template>
