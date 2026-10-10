<template>
    <div class="px-2 md:px-4 pb-10" v-if="!loading && cotizacion">
        <!-- HEADER VOLVER -->
        <div class="flex items-center justify-between mb-4 mt-2">
            <router-link :to="{ name: 'cotizacion' }">
                <Button label="Volver a la lista" icon="pi pi-arrow-left" text
                    class="!text-surface-500 font-bold !p-0 hover:!bg-transparent hover:!text-primary" />
            </router-link>
            <!-- <div class="font-bold text-lg text-surface-700">Detalle</div> -->
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- COLUMNA IZQUIERDA: Tarjeta Principal -->
            <div class="lg:col-span-9 space-y-6">
                <!-- TARJETA PRINCIPAL -->
                <div
                    class="bg-white dark:bg-surface-900 rounded-2xl border border-surface-200 dark:border-surface-700 shadow-sm p-6 lg:p-8 relative">

                    <!-- ENCABEZADO -->
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <p
                                class="text-surface-500 dark:text-surface-400 text-xs font-bold uppercase tracking-wider !m-0 !p-0 leading-none mb-1">
                                {{ cotizacion.codigo }}</p>
                            <h2
                                class="text-2xl lg:text-3xl font-black text-surface-900 dark:text-surface-0 !m-0 !p-0 leading-none">
                                {{
                                    getInstitucionNombre() }}</h2>
                            <p class="text-surface-500 dark:text-surface-400 text-sm !m-0 !p-0 leading-none mt-1">{{
                                getContactoNombre() }} -
                                {{ getContactoTelefono() }}</p>
                        </div>
                        <div class="text-right flex flex-col items-end">
                            <span
                                class="border border-blue-200 text-blue-600 bg-blue-50 px-3 py-1 rounded-full text-xs font-bold shadow-sm">
                                {{ getEstadoLabel(cotizacion.paso) }}
                            </span>
                            <p class="text-xs text-surface-400 mt-2">Por {{ cotizacion.user?.name || 'Sistema' }}</p>
                        </div>
                    </div>

                    <!-- STEPPER HORIZONTAL -->
                    <div class="flex items-center gap-2 overflow-x-auto pb-4 hide-scrollbar mb-6">
                        <template v-for="(step, index) in stepsBase" :key="index">
                            <div class="flex items-center flex-shrink-0">
                                <div @click="step.id === cotizacion.paso ? drawerVisible = true : null" :class="[
                                    'px-4 py-1.5 rounded text-xs border whitespace-nowrap text-center transition-all',
                                    step.id === cotizacion.paso
                                        ? 'bg-red-500 text-white border-red-500 font-bold cursor-pointer shadow-md hover:bg-red-600 hover:shadow-lg'
                                        : step.id < cotizacion.paso
                                            ? 'bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 border-green-300 dark:border-green-800/50 font-medium cursor-default'
                                            : 'bg-surface-50 dark:bg-surface-800 text-surface-400 dark:text-surface-500 border-surface-200 dark:border-surface-700 opacity-50 cursor-default'
                                ]" :title="step.id === cotizacion.paso ? 'Ver detalles del paso actual' : step.label">
                                    <template v-if="step.id < cotizacion.paso">
                                        <i
                                            class="pi pi-check-circle text-green-600 dark:text-green-400 mr-1 text-[10px]"></i>
                                    </template>
                                    {{ step.short || step.label }}
                                    <i v-if="step.id === cotizacion.paso"
                                        class="pi pi-caret-right text-[10px] ml-1"></i>
                                </div>
                            </div>
                            <div v-if="index < stepsBase.length - 1"
                                class="w-4 border-t border-dashed border-surface-300 dark:border-surface-700 flex-shrink-0">
                            </div>
                        </template>
                    </div>

                    <!-- DATOS DE LA COTIZACION -->
                    <div
                        class="grid grid-cols-2 gap-y-4 gap-x-8 text-sm mb-8 border-b border-surface-200 dark:border-surface-800 pb-8">
                        <div>
                            <p
                                class="text-xs font-bold text-surface-500 dark:text-surface-400 uppercase tracking-wider !m-0 !p-0 leading-none mb-1">
                                Tipo de
                                Evento</p>
                            <p class="font-bold text-surface-900 dark:text-surface-0 !m-0 !p-0 leading-tight">{{
                                cotizacion.evento?.descripcion || 'No definido'
                                }}</p>
                        </div>
                        <div>
                            <p
                                class="text-xs font-bold text-surface-500 dark:text-surface-400 uppercase tracking-wider !m-0 !p-0 leading-none mb-1">
                                Fechas</p>
                            <p class="font-bold text-surface-900 dark:text-surface-0 !m-0 !p-0 leading-tight">{{
                                formatDateShort(cotizacion.fecha_ini) }} — {{
                                    formatDateShort(cotizacion.fecha_fin) }} ({{ totalDias }} días)</p>
                        </div>
                        <div>
                            <p
                                class="text-xs font-bold text-surface-500 dark:text-surface-400 uppercase tracking-wider !m-0 !p-0 leading-none mb-1">
                                Espacios</p>
                            <p class="text-surface-800 dark:text-surface-200 !m-0 !p-0 leading-tight">{{
                                getEspaciosList() || '-' }}</p>
                        </div>
                        <div>
                            <p
                                class="text-xs font-bold text-surface-500 dark:text-surface-400 uppercase tracking-wider !m-0 !p-0 leading-none mb-1">
                                Descripción
                            </p>
                            <p class="text-surface-800 dark:text-surface-200 !m-0 !p-0 leading-tight">{{
                                cotizacion.descripcion || '-' }}
                            </p>
                        </div>
                    </div>

                    <!-- LISTA DE ITEMS (CARRO) -->
                    <div class="mb-6 space-y-1">
                        <template v-if="cotizacion.tarifas && cotizacion.tarifas.length > 0">
                            <div v-for="(tarifa, index) in cotizacion.tarifas" :key="'t-' + index"
                                class="flex justify-between py-2 border-b border-surface-100 dark:border-surface-800 items-center">
                                <div>
                                    <p
                                        class="font-bold text-surface-900 dark:text-surface-0 text-sm !m-0 !p-0 leading-tight">
                                        {{
                                            tarifa.espacio?.nombre || 'Espacio' }}</p>
                                    <p
                                        class="text-xs text-surface-500 dark:text-surface-400 !m-0 !p-0 leading-tight mt-0.5">
                                        {{
                                            tarifa.pivot?.dias || 0 }}d × Bs {{ formatCurrency(tarifa.pivot?.precio_aplicado
                                            || 0) }} ({{ tarifa.evento?.descripcion || tarifa.tipo_tarifa?.nombre ||
                                            'Tarifa' }})</p>
                                </div>
                                <div class="font-black text-surface-900 dark:text-surface-0 text-base">
                                    Bs {{ formatCurrency(Number(tarifa.pivot?.dias || 0) *
                                        Number(tarifa.pivot?.precio_aplicado || 0)) }}
                                </div>
                            </div>
                        </template>

                        <template v-if="cotizacion.servicios && cotizacion.servicios.length > 0">
                            <div v-for="(servicio, index) in cotizacion.servicios" :key="'s-' + index"
                                class="flex justify-between py-2 border-b border-surface-100 dark:border-surface-800 items-center">
                                <div>
                                    <p
                                        class="font-bold text-surface-900 dark:text-surface-0 text-sm !m-0 !p-0 leading-tight">
                                        {{
                                            servicio.nombre }}</p>
                                    <p
                                        class="text-xs text-surface-500 dark:text-surface-400 !m-0 !p-0 leading-tight mt-0.5">
                                        {{ servicio.pivot?.cantidad || 1 }}u × Bs {{
                                            formatCurrency(servicio.pivot?.precio_aplicado || 0) }} <span
                                            v-if="servicio.pivot?.dias">× {{ servicio.pivot.dias }} día(s)</span>
                                    </p>
                                </div>
                                <div class="font-black text-surface-900 dark:text-surface-0 text-base">
                                    Bs {{ formatCurrency(Number(servicio.pivot?.cantidad || 1) *
                                        Number(servicio.pivot?.precio_aplicado || 0) * Number(servicio.pivot?.dias || 1)) }}
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- BANNER TOTAL -->
                    <div
                        class="bg-primary-900 dark:bg-primary-800 text-primary-contrast py-4 px-6 flex justify-between items-center rounded-lg mt-8 shadow-lg">
                        <span class="text-sm font-bold uppercase tracking-wider">TOTAL COTIZACIÓN</span>
                        <span class="text-xl font-black">Bs {{ formatCurrency(cotizacion.monto_total) }}</span>
                    </div>

                    <!-- BOTONERAS DE ACCIÓN -->
                    <div class="mt-6 flex flex-col gap-3">
                        <Button label="Reimprimir" icon="pi pi-print" severity="secondary" outlined
                            class="w-full font-bold shadow-sm" @click="reimprimirCotizacion" :loading="printing" />
                        <Button label="Gestionar Cotización" icon="pi pi-pencil" severity="warning"
                            class="w-full font-bold shadow-sm" @click="abrirModalEdicion" />
                        <Button :label="'Confirmar Reserva / Registrar Anticipo'" icon="pi pi-check-square"
                            severity="success" class="w-full font-bold shadow-sm" @click="abrirModalReserva" />
                    </div>
                </div>

                <!-- HISTORIAL CRM -->
                <div
                    class="bg-white dark:bg-surface-900 rounded-2xl border border-surface-200 dark:border-surface-700 shadow-sm relative pt-0">
                    <!-- CRM HEADER -->
                    <div
                        class="flex items-center justify-between p-6 border-b border-surface-100 dark:border-surface-800">
                        <div class="flex items-center gap-3">
                            <span
                                class="bg-yellow-100 dark:bg-yellow-900/30 text-yellow-600 dark:text-yellow-400 rounded-lg p-2 text-sm leading-none shadow-sm">🤝</span>
                            <h3 class="text-lg font-black text-surface-900 dark:text-surface-0 m-0">Historial CRM</h3>
                            <span
                                class="text-xs bg-surface-100 dark:bg-surface-800 text-surface-600 dark:text-surface-400 px-3 py-1 rounded-full font-bold ml-2">0
                                gestiones</span>
                        </div>
                        <Button label="+ Nueva gestión" size="small" severity="primary" class="rounded-full px-4" />
                    </div>

                    <!-- FORMULARIO NUEVA GESTION -->
                    <div class="p-6">
                        <div
                            class="bg-surface-50 dark:bg-surface-800 border border-surface-200 dark:border-surface-700 p-6 rounded-xl text-left shadow-inner">
                            <h4
                                class="text-sm font-bold text-primary dark:text-primary-400 mb-4 m-0 uppercase tracking-widest">
                                +
                                Registrar nueva gestión</h4>

                            <div class="grid grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label
                                        class="block text-xs font-bold text-surface-500 dark:text-surface-400 mb-2 uppercase tracking-tighter">TIPO</label>
                                    <Dropdown :options="['Llamada', 'Reunión', 'Correo', 'WhatsApp']"
                                        placeholder="Llamada" class="w-full" />
                                </div>
                                <div>
                                    <label
                                        class="block text-xs font-bold text-surface-500 dark:text-surface-400 mb-2 uppercase tracking-tighter">FECHA</label>
                                    <Calendar placeholder="06/05/2026" class="w-full" />
                                </div>
                            </div>

                            <div class="mb-4">
                                <label
                                    class="block text-xs font-bold text-surface-500 dark:text-surface-400 mb-2 uppercase tracking-tighter">DESCRIPCIÓN</label>
                                <Textarea rows="3"
                                    class="w-full border-surface-300 dark:border-surface-700 dark:bg-surface-900" />
                            </div>

                            <div class="mb-5">
                                <label
                                    class="block text-xs font-bold text-surface-500 dark:text-surface-400 mb-2 uppercase tracking-tighter">RESULTADO
                                    / SIGUIENTE PASO</label>
                                <InputText class="w-full dark:border-surface-700 dark:bg-surface-900" />
                            </div>

                            <Button label="Guardar gestión" severity="primary" class="rounded-full px-6" />
                        </div>

                        <p class="text-surface-400 text-sm text-center mt-8 mb-4">Sin gestiones registradas aún.</p>
                    </div>
                </div>

                <!-- HISTORIAL DE AUDITORÍA (Cambios en la cotización) -->
                <div v-if="cotizacion.historial && cotizacion.historial.length > 0"
                    class="bg-white dark:bg-surface-900 rounded-2xl border border-surface-200 dark:border-surface-700 shadow-sm relative pt-0 mt-6">
                    <div
                        class="flex items-center justify-between p-6 border-b border-surface-100 dark:border-surface-800">
                        <div class="flex items-center gap-3">
                            <i
                                class="pi pi-history text-primary dark:text-primary-400 bg-primary-50 dark:bg-primary-900/20 p-2 rounded-lg text-sm"></i>
                            <h3 class="text-lg font-black text-surface-900 dark:text-surface-0 m-0">Auditoría de Cambios
                            </h3>
                            <span
                                class="text-xs bg-surface-100 dark:bg-surface-800 text-surface-600 dark:text-surface-400 px-3 py-1 rounded-full font-bold ml-2">
                                {{ cotizacion.historial.length }} cambios
                            </span>
                        </div>
                    </div>
                    <div class="p-6 pt-0 space-y-4">
                        <div v-for="(item, index) in cotizacion.historial" :key="index"
                            class="bg-surface-50 dark:bg-surface-800 p-4 rounded-xl border border-surface-200 dark:border-surface-700 relative hover:border-primary/30 transition-all">
                            <div class="flex justify-between items-start mb-2">
                                <div class="flex items-center gap-2">
                                    <div
                                        class="w-8 h-8 rounded-full bg-primary/10 dark:bg-primary-900/40 flex items-center justify-center text-primary dark:text-primary-400">
                                        <i class="pi pi-user text-xs"></i>
                                    </div>
                                    <div class="flex flex-col">
                                        <span
                                            class="font-bold text-surface-900 dark:text-surface-0 text-sm leading-none">{{
                                                item.user?.name || 'Usuario' }}</span>
                                        <span
                                            class="text-[10px] text-surface-400 dark:text-surface-500 font-medium mt-1 uppercase tracking-tighter">
                                            {{ formatDateTime(item.created_at) }}
                                        </span>
                                    </div>
                                </div>
                                <div
                                    class="bg-primary/5 dark:bg-primary-900/20 text-primary dark:text-primary-400 text-[10px] font-bold px-2 py-0.5 rounded uppercase">
                                    Edición
                                </div>
                            </div>
                            <div class="pl-10">
                                <p
                                    class="text-sm text-surface-600 dark:text-surface-300 m-0 leading-relaxed italic border-l-2 border-surface-200 dark:border-surface-700 pl-3 py-1">
                                    "{{ item.motivo }}"
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- COLUMNA DERECHA: Flujo de Aprobación -->
            <div class="lg:col-span-3">
                <div
                    class="bg-white dark:bg-surface-900 rounded-2xl shadow-sm border border-surface-200 dark:border-surface-700 p-5 sticky top-24">
                    <h3
                        class="text-lg font-black text-surface-800 dark:text-surface-0 mb-4 flex items-center gap-2 m-0 tracking-tight leading-none uppercase">
                        <i class="pi pi-sort-alt text-surface-600 dark:text-surface-400"></i> FLUJO DE APROBACIÓN
                    </h3>

                    <div class="space-y-2">
                        <div v-for="step in approvalSteps" :key="step.id" :class="[
                            'rounded-xl border p-4 flex gap-4 items-center relative transition-all',
                            step.status === 'completed' ? 'bg-white dark:bg-surface-900 border-surface-200 dark:border-surface-800 shadow-sm' : (step.status === 'current' ? 'bg-surface-0 dark:bg-surface-800 border-yellow-500 shadow-md' : 'bg-white dark:bg-surface-950 border-surface-100 dark:border-surface-800 opacity-50')
                        ]">

                            <!-- Icono Izquierdo Base -->
                            <div class="w-10 h-10 rounded-full flex-shrink-0 flex items-center justify-center border transition-colors"
                                :class="[
                                    step.status === 'current' ? 'bg-white dark:bg-surface-900 text-blue-600 dark:text-blue-400 border-surface-200 dark:border-surface-700 shadow-sm' : (step.status === 'completed' ? 'bg-surface-50 dark:bg-surface-800 text-surface-400 border-surface-200 dark:border-surface-800' : 'bg-surface-50 dark:bg-surface-900 text-surface-300 border-surface-100 dark:border-surface-800')
                                ]">
                                <i
                                    :class="[step.status === 'completed' ? 'pi pi-check text-green-600 dark:text-green-400 font-bold' : step.icon, 'text-sm']"></i>
                            </div>

                            <!-- Contenido Central -->
                            <div class="flex-grow min-w-0" style="line-height: 1">
                                <div class="flex items-center gap-2 !m-0 !p-0">
                                    <p
                                        class="font-bold text-surface-900 dark:text-surface-0 text-sm !m-0 !p-0 truncate leading-tight">
                                        {{
                                            step.label }}
                                    </p>
                                    <span
                                        class="text-[10px] text-surface-400 dark:text-surface-500 hover:text-primary cursor-pointer whitespace-nowrap">▶
                                        Ver</span>
                                </div>

                                <p v-if="step.status === 'current' && step.id > 1"
                                    class="text-xs text-surface-500 dark:text-surface-400 !m-0 !p-0 leading-none mt-0.5">
                                    Pendiente
                                </p>
                                <p v-if="step.id === 1"
                                    class="text-[11px] text-surface-700 dark:text-surface-300 font-bold !m-0 !p-0 truncate leading-none mt-0.5">
                                    {{
                                        cotizacion.user?.name || 'Sistema' }}</p>

                                <p
                                    class="text-[11px] text-surface-500 dark:text-surface-400 !m-0 !p-0 truncate leading-none mt-0.5">
                                    {{
                                        step.desc }}</p>
                            </div>

                            <!-- Icono Derecho -->
                            <div class="flex-shrink-0 flex justify-center items-center w-6">
                                <i v-if="step.status === 'current'"
                                    class="pi pi-hourglass text-yellow-500 text-sm animate-pulse"></i>
                                <div v-else-if="step.status === 'pending'"
                                    class="w-3 h-3 rounded-full border-2 border-surface-300 dark:border-surface-700">
                                </div>
                                <div v-else-if="step.status === 'completed'"
                                    class="w-3 h-3 rounded-full border-2 border-surface-200"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Drawer Detalles -->
        <Drawer v-model:visible="drawerVisible" position="right" style="width: 20vw"
            class="p-0 border-l border-surface-200 dark:border-surface-800"
            :pt="{ header: { class: 'bg-[#06352c] dark:bg-surface-950 text-white border-b border-white/10 dark:border-surface-800' }, closeButton: { class: 'text-white hover:bg-white/10', style: 'right: 1.5rem; position: absolute;' } }">
            <template #header>
                <div class="flex items-center gap-3">
                    <div
                        class="bg-primary-700/50 dark:bg-primary-500/20 p-2.5 rounded-full flex items-center justify-center">
                        <i class="pi pi-user text-primary-100 dark:text-primary-400 text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold m-0 leading-tight text-white dark:text-surface-0">Cotización
                            Comercial</h2>
                        <span class="text-xs opacity-90 truncate text-primary-100 dark:text-surface-400 font-medium">{{
                            getInstitucionNombre() }} • {{
                                cotizacion.codigo }}</span>
                    </div>
                </div>
            </template>

            <div class="p-6 text-sm dark:bg-surface-900 h-full">
                <h4
                    class="font-bold text-surface-500 dark:text-surface-400 text-[10px] mt-2 mb-4 uppercase tracking-widest border-b border-surface-200 dark:border-surface-800 pb-2">
                    DATOS DE LA COTIZACIÓN
                </h4>
                <div class="grid grid-cols-12 border-b border-surface-100 dark:border-surface-800 py-3 items-center">
                    <span class="col-span-4 text-surface-500 dark:text-surface-400">ID Cotización</span>
                    <span class="col-span-8 font-bold text-right text-surface-900 dark:text-surface-0">{{
                        cotizacion.codigo
                        }}</span>
                </div>
                <div class="grid grid-cols-12 border-b border-surface-100 dark:border-surface-800 py-3 items-center">
                    <span class="col-span-4 text-surface-500 dark:text-surface-400">Cliente</span>
                    <span class="col-span-8 font-bold text-right text-surface-900 dark:text-surface-0">{{
                        getInstitucionNombre()
                    }}</span>
                </div>
                <div class="grid grid-cols-12 border-b border-surface-100 dark:border-surface-800 py-3 items-center">
                    <span class="col-span-4 text-surface-500 dark:text-surface-400">NIT</span>
                    <span class="col-span-8 font-bold text-right text-surface-900 dark:text-surface-0">{{
                        getInstitucionNit()
                    }}</span>
                </div>
                <div class="grid grid-cols-12 border-b border-surface-100 dark:border-surface-800 py-3 items-center">
                    <span class="col-span-4 text-surface-500 dark:text-surface-400">Contacto</span>
                    <span class="col-span-8 font-bold text-right text-surface-900 dark:text-surface-0">{{
                        getContactoNombre() }}
                        - {{
                            getContactoTelefono() }}</span>
                </div>
                <div class="grid grid-cols-12 border-b border-surface-100 dark:border-surface-800 py-3 items-center">
                    <span class="col-span-4 text-surface-500 dark:text-surface-400">Correo</span>
                    <span class="col-span-8 font-bold text-right text-surface-900 dark:text-surface-0">{{
                        getContactoEmail()
                    }}</span>
                </div>
                <div class="grid grid-cols-12 border-b border-surface-100 dark:border-surface-800 py-3 items-center">
                    <span class="col-span-4 text-surface-500 dark:text-surface-400">Tipo de Evento</span>
                    <span class="col-span-8 font-bold text-right text-surface-900 dark:text-surface-0">{{
                        cotizacion.evento?.descripcion
                    }}</span>
                </div>
                <div class="grid grid-cols-12 border-b border-surface-100 dark:border-surface-800 py-3 items-center">
                    <span class="col-span-4 text-surface-500 dark:text-surface-400">Fecha Inicio</span>
                    <span class="col-span-8 font-bold text-right text-surface-900 dark:text-surface-0">{{
                        formatDateShort(cotizacion.fecha_ini)
                        }}</span>
                </div>
                <div class="grid grid-cols-12 border-b border-surface-100 dark:border-surface-800 py-3 items-center">
                    <span class="col-span-4 text-surface-500 dark:text-surface-400">Fecha Fin</span>
                    <span class="col-span-8 font-bold text-right text-surface-900 dark:text-surface-0">{{
                        formatDateShort(cotizacion.fecha_fin)
                        }}</span>
                </div>
                <div class="grid grid-cols-12 border-b border-surface-100 dark:border-surface-800 py-3 items-start">
                    <span class="col-span-4 text-surface-500 dark:text-surface-400 pt-0.5">Descripción</span>
                    <span class="col-span-8 font-bold text-right text-surface-900 dark:text-surface-0">{{
                        cotizacion.descripcion
                        || '-'
                    }}</span>
                </div>

                <h4
                    class="font-bold text-surface-500 dark:text-surface-400 text-[10px] mt-8 mb-4 uppercase tracking-widest border-b border-surface-200 dark:border-surface-800 pb-2">
                    ESPACIOS COTIZADOS
                </h4>
                <div v-for="t in cotizacion.tarifas" :key="t.id"
                    class="grid grid-cols-12 border-b border-surface-100 dark:border-surface-800 py-3 items-center">
                    <span class="col-span-8 font-bold text-surface-900 dark:text-surface-0 truncate">{{
                        t.espacio?.nombre
                    }}</span>
                    <span
                        class="col-span-4 text-surface-400 dark:text-surface-500 text-right uppercase text-[10px] truncate">{{
                            t.evento?.descripcion
                            || t.tipo_tarifa?.nombre || 'Tarifa' }}</span>
                </div>

                <h4
                    class="font-bold text-surface-500 dark:text-surface-400 text-[10px] mt-8 mb-4 uppercase tracking-widest border-b border-surface-200 dark:border-surface-800 pb-2">
                    EJECUTIVO ASIGNADO
                </h4>
                <div class="grid grid-cols-12 border-b border-surface-100 dark:border-surface-800 py-3 items-center">
                    <span class="col-span-4 text-surface-500 dark:text-surface-400">Nombre</span>
                    <span class="col-span-8 font-bold text-right text-surface-900 dark:text-surface-0">{{
                        cotizacion.user?.name
                        || 'Sistema'
                    }}</span>
                </div>
                <div class="grid grid-cols-12 border-b border-surface-100 dark:border-surface-800 py-3 items-center">
                    <span class="col-span-4 text-surface-500 dark:text-surface-400">Cargo</span>
                    <span
                        class="col-span-8 font-bold text-right text-surface-900 dark:text-surface-0 truncate">Responsable
                        Planificación...</span>
                </div>
                <div class="grid grid-cols-12 border-b border-surface-100 dark:border-surface-800 py-3 items-center">
                    <span class="col-span-4 text-surface-500 dark:text-surface-400">Monto Cotizado</span>
                    <span class="col-span-8 font-bold text-right text-surface-900 dark:text-surface-0">Bs {{
                        formatCurrency(cotizacion.monto_total)
                    }}</span>
                </div>
            </div>
        </Drawer>
    </div>

    <!-- Loader -->
    <div v-else class="flex flex-col items-center justify-center py-20 h-[60vh]">
        <i class="pi pi-spinner pi-spin text-primary text-4xl mb-4"></i>
        <p class="text-surface-500 text-sm font-medium">Cargando...</p>
    </div>

    <!-- ================================================================ -->
    <!-- MODAL: Gestionar Cotización (Edición Completa)                  -->
    <!-- ================================================================ -->
    <Dialog v-model:visible="showModalEdicion" :style="{ width: '50vw', maxWidth: '750px' }"
        :breakpoints="{ '1199px': '70vw', '575px': '95vw' }" modal :closable="false" :dismissableMask="false"
        class="p-0 overflow-hidden shadow-2xl"
        :pt="{ mask: { style: 'backdrop-filter: blur(6px); background: rgba(0,0,0,0.55)' } }">

        <!-- HEADER DEL MODAL -->
        <template #header>
            <div class="flex flex-col w-full">
                <div
                    class="flex items-center justify-between w-full bg-[#06352c] text-white p-3 pl-5 border-b border-[#0d4a3e]">
                    <div class="flex items-center gap-2.5">
                        <div class="border-2 border-white/30 p-1.5 rounded-lg flex items-center justify-center">
                            <i class="pi pi-pencil text-base text-white"></i>
                        </div>
                        <div class="flex flex-col">
                            <span class="font-bold text-sm leading-none">GESTIONAR COTIZACIÓN</span>
                            <span class="text-xs text-white/60 uppercase tracking-widest mt-0.5">
                                {{ formEdit.codigo || cotizacion?.codigo || 'COT-000' }} •
                                <span class="text-white font-bold">{{ getEstadoLabel(cotizacion?.paso) }}</span>
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <Button icon="pi pi-times" text rounded @click="cerrarModalEdicion"
                            class="!text-white hover:bg-white/10 w-8 h-8" />
                    </div>
                </div>
                <!-- Banner Alerta -->
                <div
                    class="px-5 py-2.5 bg-orange-50 dark:bg-orange-900/20 border-b border-orange-200 dark:border-orange-800/30">
                    <div class="flex items-start gap-2 text-orange-700 dark:text-orange-300">
                        <i class="pi pi-exclamation-triangle mt-0.5 text-sm flex-shrink-0"></i>
                        <div class="text-sm font-medium">
                            <strong>Solo editable en estado "En gestión".</strong> Los cambios se aplican al guardar.
                            Una vez confirmada, deberás crear una nueva versión.
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <!-- BODY DEL MODAL -->
        <div class="p-5 space-y-6 bg-surface-50/50 dark:bg-surface-900">

            <!-- SECCIÓN 1: DATOS DEL EVENTO -->
            <div>
                <div class="flex items-center gap-2 mb-4 pl-3 border-l-4 border-[#06352c] dark:border-primary-400 py-1">
                    <i class="pi pi-calendar text-[#06352c] dark:text-primary-400 text-base"></i>
                    <span class="font-bold text-base text-[#06352c] dark:text-primary-400">Datos del Evento</span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex flex-col gap-2">
                        <label class="font-semibold text-sm text-surface-900 dark:text-surface-0">Tipo de Evento</label>
                        <Dropdown v-model="formEdit.tipo_evento_id" :options="tiposEvento" optionLabel="descripcion"
                            optionValue="id" placeholder="Seleccionar tipo de evento" class="w-full"
                            :disabled="editandoTipoEvento" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="font-semibold text-sm text-surface-900 dark:text-surface-0">Descripción</label>
                        <InputText v-model="formEdit.descripcion" placeholder="Descripción del evento" class="w-full" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="font-semibold text-sm text-surface-900 dark:text-surface-0">Fecha Inicio</label>
                        <DatePicker v-model="formEdit.fecha_ini" showIcon fluid placeholder="dd/mm/aaaa" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="font-semibold text-sm text-surface-900 dark:text-surface-0">Fecha Fin</label>
                        <DatePicker v-model="formEdit.fecha_fin" showIcon fluid placeholder="dd/mm/aaaa"
                            :minDate="formEdit.fecha_ini" />
                    </div>
                </div>
            </div>

            <!-- SECCIÓN 2: ESPACIOS — Configura días por espacio -->
            <div>
                <div class="flex items-center gap-2 mb-4 pl-3 border-l-4 border-[#06352c] dark:border-primary-400 py-1">
                    <i class="pi pi-building text-[#06352c] dark:text-primary-400 text-base"></i>
                    <span class="font-bold text-base text-[#06352c] dark:text-primary-400">Espacios — Configura días por
                        espacio</span>
                </div>

                <!-- GRID ÚNICO: espacios seleccionados + disponibles -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 items-start">

                    <!-- Espacios Seleccionados (tarjetas con control de días, estilo Paso 2 activo) -->
                    <template v-for="(esp, idx) in formEdit.espacios" :key="'sel-' + esp.espacio_id">
                        <div
                            class="border rounded-xl overflow-hidden transition-all duration-300 relative flex flex-col border-primary-500 ring-2 ring-primary-500/20 shadow-md bg-primary-50/50 dark:bg-primary-900/10">
                            <!-- Header Tarjeta -->
                            <div class="px-4 pt-4 pb-2">
                                <div class="flex justify-between items-start mb-1 gap-2">
                                    <h3
                                        class="font-bold text-base text-surface-900 dark:text-surface-0 leading-tight flex-1">
                                        <i class="pi pi-check text-primary mr-1 text-sm"></i>
                                        {{ getEspacioNombre(esp.espacio_id) }}
                                    </h3>
                                    <div class="flex items-center gap-1 shrink-0">
                                        <span
                                            class="text-[0.6rem] font-bold uppercase tracking-wider px-2 py-0.5 rounded border whitespace-nowrap"
                                            :style="{ backgroundColor: getBloqueColorRaw(esp.espacio_id) + '10', color: getBloqueColorRaw(esp.espacio_id), borderColor: getBloqueColorRaw(esp.espacio_id) + '40' }">
                                            {{ getBloqueLabel(esp.espacio_id) }}
                                        </span>
                                        <Button icon="pi pi-times" rounded text
                                            class="w-7 h-7 !p-0 text-red-500 hover:bg-red-100"
                                            @click="eliminarEspacioEdicion(idx)" />
                                    </div>
                                </div>
                                <div class="text-xs text-primary font-bold mb-1">
                                    Bs {{ formatCurrency(getPrecioTarifa(esp.tarifa_id)) }}/día
                                </div>
                            </div>
                            <!-- Body: control días -->
                            <div class="px-4 pb-3 border-t border-dashed border-surface pt-3">
                                <div class="flex gap-3">
                                    <div class="w-28 flex-shrink-0">
                                        <label
                                            class="text-[0.6rem] font-bold text-muted-color uppercase mb-1 block">Días
                                            Uso</label>
                                        <InputNumber v-model="esp.dias" showButtons :min="1"
                                            inputClass="w-full p-2 text-center font-bold text-sm"
                                            class="w-full !border-surface-200" decrementButtonIcon="pi pi-minus"
                                            incrementButtonIcon="pi pi-plus" />
                                    </div>
                                    <div class="flex-1 overflow-hidden">
                                        <label
                                            class="text-[0.6rem] font-bold text-muted-color uppercase mb-1 block">Tarifa
                                            Aplicada</label>
                                        <div
                                            class="text-xs font-bold text-primary bg-primary-50 dark:bg-primary-900/20 px-2 py-2 rounded border border-primary-200 dark:border-primary-800 truncate">
                                            <i class="pi pi-tag mr-1"></i>
                                            {{ getTarifaEventoLabel(esp.espacio_id) }}
                                        </div>
                                    </div>
                                </div>
                                <div class="flex justify-between items-center mt-2 pt-2 border-t border-surface/50">
                                    <span class="text-xs font-bold text-surface-600 dark:text-surface-400">Total
                                        Espacio</span>
                                    <span class="font-black text-sm text-primary">
                                        Bs {{ formatCurrency((getPrecioTarifa(esp.tarifa_id) || 0) * (esp.dias || 0)) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Solo espacios NO seleccionados (para agregar) -->
                    <div v-for="espacio in espaciosDisponibles.filter(e => !isEspacioInEdit(e.id))"
                        :key="'avail-' + espacio.id"
                        class="border rounded-xl overflow-hidden transition-all duration-300 relative flex flex-col h-full bg-surface-0 dark:bg-surface-800 border-surface-200 dark:border-surface-700 shadow-sm hover:shadow-md">

                        <!-- Header Tarjeta -->
                        <div class="px-4 pt-4 pb-2">
                            <div class="flex justify-between items-start mb-1 gap-2">
                                <h3
                                    class="font-bold text-base text-surface-900 dark:text-surface-0 leading-tight flex-1">
                                    {{ espacio.nombre }}
                                </h3>
                                <span
                                    class="text-[0.6rem] font-bold uppercase tracking-wider px-2 py-0.5 rounded border whitespace-nowrap"
                                    :style="{ backgroundColor: getBloqueColorRaw(espacio.id) + '10', color: getBloqueColorRaw(espacio.id), borderColor: getBloqueColorRaw(espacio.id) + '40' }">
                                    {{ getBloqueLabel(espacio.id) }}
                                </span>
                            </div>
                            <div class="text-xs text-surface-500 dark:text-surface-400 mb-1">
                                <span>{{ espacio.area_m2 || '-' }}m²</span>
                                <span v-if="espacio.aforo"> &bull; {{ espacio.aforo }} pax</span>
                            </div>
                            <div class="font-bold text-primary text-sm">
                                Bs {{ getSpaceMinPriceEdit(espacio.id) }}/día
                            </div>
                        </div>

                        <!-- Footer Tarjeta -->
                        <div
                            class="mt-auto px-4 pb-4 pt-3 border-t border-surface border-dashed dark:border-surface-700">
                            <Button label="+ Agregar" severity="secondary"
                                class="w-full text-sm !bg-surface-100 dark:!bg-surface-800 border-none !text-surface-700 dark:!text-surface-300 hover:!bg-surface-200 dark:hover:!bg-surface-700"
                                @click="toggleEspacioEdicion(espacio)" />
                        </div>
                    </div>

                </div>
            </div>

            <!-- SECCIÓN 3: SERVICIOS ADICIONALES -->
            <div>
                <div class="flex items-center gap-2 mb-4 pl-3 border-l-4 border-[#06352c] dark:border-primary-400 py-1">
                    <i class="pi pi-briefcase text-[#06352c] dark:text-primary-400 text-base"></i>
                    <span class="font-bold text-base text-[#06352c] dark:text-primary-400">Servicios Adicionales</span>
                </div>

                <!-- Cabecera tabular -->
                <div
                    class="hidden md:grid grid-cols-12 gap-3 px-4 py-2 text-[0.6rem] font-bold text-surface-500 uppercase tracking-widest border-b border-surface-200 mb-1">
                    <div class="col-span-1"></div>
                    <div class="col-span-3">SERVICIO</div>
                    <div class="col-span-3 text-center">CANT.</div>
                    <div class="col-span-2 text-center">DÍAS</div>
                    <div class="col-span-3 text-right">TOTAL</div>
                </div>

                <div v-for="sv in serviciosDisponibles" :key="sv.id"
                    class="grid grid-cols-12 gap-3 items-center px-4 py-3 rounded-xl transition-all mb-1"
                    :class="isServicioInEdit(sv.id)
                        ? 'bg-green-50/50 dark:bg-green-900/10 border border-green-200 dark:border-green-800/30'
                        : 'bg-white dark:bg-surface-800 border border-surface-100 dark:border-surface-700 hover:border-surface-300 dark:hover:border-surface-600'">

                    <!-- Checkbox -->
                    <div class="col-span-1 flex justify-center">
                        <Checkbox :modelValue="isServicioInEdit(sv.id)" binary
                            @update:modelValue="toggleServicioEdicion(sv)" />
                    </div>

                    <!-- Nombre -->
                    <div class="col-span-3">
                        <span class="font-bold text-sm text-surface-900 dark:text-surface-0 block leading-tight">{{
                            sv.nombre }}</span>
                        <span class="text-[0.65rem] text-surface-400 dark:text-surface-500 italic">Bs {{
                            formatCurrency(sv.precio) }}/{{ sv.unidad }}</span>
                    </div>

                    <!-- Cantidad -->
                    <div class="col-span-3 flex justify-center">
                        <template v-if="isServicioInEdit(sv.id)">
                            <div class="w-full max-w-[100px]">
                                <InputNumber v-model="getServicioEditData(sv.id).cantidad" showButtons :min="1"
                                    buttonLayout="stacked" class="w-full"
                                    inputClass="text-center font-bold text-sm w-full" decrementButtonIcon="pi pi-minus"
                                    incrementButtonIcon="pi pi-plus" />
                            </div>
                        </template>
                        <template v-else>
                            <span class="text-surface-300 text-sm block text-center">---</span>
                        </template>
                    </div>

                    <!-- Días -->
                    <div class="col-span-2 flex justify-center">
                        <template v-if="isServicioInEdit(sv.id) && !sv.porEv">
                            <div class="w-full max-w-[100px]">
                                <InputNumber v-model="getServicioEditData(sv.id).dias" showButtons :min="1"
                                    buttonLayout="stacked" class="w-full"
                                    inputClass="text-center font-bold text-sm w-full" decrementButtonIcon="pi pi-minus"
                                    incrementButtonIcon="pi pi-plus" />
                            </div>
                        </template>
                        <template v-else-if="isServicioInEdit(sv.id) && sv.porEv">
                            <span class="text-[0.65rem] text-orange-500 font-bold italic block text-center">Por
                                evento</span>
                        </template>
                        <template v-else>
                            <span class="text-surface-300 text-sm block text-center">---</span>
                        </template>
                    </div>

                    <!-- Total -->
                    <div class="col-span-3 text-right pr-2">
                        <template v-if="isServicioInEdit(sv.id)">
                            <span class="font-black text-sm text-[#06352c] dark:text-primary-300">
                                Bs {{ formatCurrency(calcularServicioEditSubtotal(sv)) }}
                            </span>
                        </template>
                        <template v-else>
                            <span class="text-surface-300 text-sm">---</span>
                        </template>
                    </div>
                </div>
            </div>

            <!-- SECCIÓN 4: TOTAL Y RESUMEN -->
            <div>
                <div class="text-right mb-2">
                    <span class="text-xs text-surface-500 dark:text-surface-400 font-medium">Subtotal servicios: Bs {{
                        formatCurrency(calcularTotalServiciosEdit) }}</span>
                </div>
                <div class="bg-[#06352c] text-white rounded-xl px-5 py-4 flex justify-between items-center shadow-lg">
                    <div>
                        <span class="text-sm font-bold text-white uppercase tracking-wider">TOTAL ACTUALIZADO</span>
                        <div class="text-xs text-white/60 mt-0.5">Incluye espacios y servicios</div>
                    </div>
                    <span class="text-2xl font-black text-white">Bs {{ formatCurrency(calcularTotalEdit) }}</span>
                </div>
            </div>

            <!-- SECCIÓN 5: MOTIVO DEL CAMBIO -->
            <div>
                <div class="flex items-center gap-2 mb-4 pl-3 border-l-4 border-[#06352c] dark:border-primary-400 py-1">
                    <i class="pi pi-pencil text-[#06352c] dark:text-primary-400 text-base"></i>
                    <span class="font-bold text-base text-[#06352c] dark:text-primary-400">Motivo del Cambio <span
                            class="text-red-500">*</span></span>
                </div>
                <div
                    class="bg-orange-50 dark:bg-orange-900/10 border border-orange-200 dark:border-orange-800/30 rounded-lg p-3 mb-3">
                    <div class="flex items-start gap-2 text-sm text-orange-700 dark:text-orange-300">
                        <i class="pi pi-exclamation-triangle mt-0.5 flex-shrink-0"></i>
                        <span>Este campo es obligatorio. El motivo quedará registrado en el historial del CRM para
                            auditoría.</span>
                    </div>
                </div>
                <Textarea v-model="formEdit.motivo" rows="3" class="w-full"
                    placeholder="Ej: Cliente solicitó cambiar de salón, reducir días y agregar mobiliario adicional..."
                    :class="{ 'border-red-400': !formEdit.motivo && submittedEdit }" />
            </div>

            <!-- SECCIÓN 6: BOTÓN DE ACCIÓN PRINCIPAL -->
            <div class="pt-4 border-t border-surface-200 dark:border-surface-700">
                <Button label="Guardar versión actualizada" icon="pi pi-save"
                    class="w-full !bg-[#06352c] dark:!bg-primary-600 !border-none !py-4 !text-base !font-bold shadow-xl hover:!bg-[#0a4a3e] dark:hover:!bg-primary-700 transition-all text-white"
                    @click="guardarVersionEditada" :loading="savingEdit" :disabled="savingEdit" />
            </div>

        </div>
    </Dialog>

    <!-- Modal de Vista Previa PDF (Reprint) -->
    <Dialog v-model:visible="showPdfPreview" modal :showHeader="false"
        class="!p-0 !border-none !overflow-hidden max-w-[1200px]" :style="{ width: '90vw' }"
        :contentStyle="{ padding: '0' }">

        <template #default>
            <!-- Header Modal PDF -->
            <div
                class="bg-[#06352c] dark:bg-surface-900 text-white px-6 py-4 flex flex-col md:flex-row justify-between items-center gap-4 border-b border-white/10 dark:border-surface-700">
                <div class="flex items-center gap-3">
                    <div class="bg-yellow-400 p-1.5 rounded-lg flex items-center justify-center shadow-lg">
                        <i class="pi pi-file-pdf text-xl text-[#06352c]"></i>
                    </div>
                    <div class="flex flex-col">
                        <span class="font-bold text-lg leading-none">Vista Previa del Documento</span>
                        <span class="text-[10px] text-white/60 uppercase tracking-widest mt-1">Reimpresión
                            Oficial</span>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <Button label="Imprimir / PDF" icon="pi pi-print" severity="secondary" @click="printPdfPreview"
                        class="!bg-white dark:!bg-surface-800 !text-[#06352c] dark:!text-primary-400 !border-none !py-2 !px-5 !font-bold shadow-lg hover:!bg-surface-100 dark:hover:!bg-surface-700 transition-all font-bold" />
                    <Button label="Guardar PDF" icon="pi pi-save" severity="success" @click="downloadPdfPreview"
                        class="!py-2 !px-5 !font-bold shadow-lg hover:scale-105 active:scale-95 transition-all" />
                    <div class="w-px h-8 bg-white/20 mx-1"></div>
                    <Button icon="pi pi-times" text rounded @click="showPdfPreview = false"
                        class="!text-white hover:bg-white/10 w-10 h-10" />
                </div>
            </div>

            <!-- Visor Iframe -->
            <div class="w-full h-[80vh] bg-surface-200 dark:bg-surface-950 overflow-hidden flex flex-col">
                <div class="flex-1 overflow-auto p-4 md:p-8 flex justify-center bg-surface-200 dark:bg-surface-950">
                    <iframe v-if="pdfUrl" :src="pdfUrl"
                        class="w-full max-w-[1000px] h-full border-none shadow-2xl bg-white rounded-sm"></iframe>
                    <div v-else class="flex-1 flex flex-col items-center justify-center gap-4 py-20">
                        <i class="pi pi-spin pi-spinner text-5xl text-primary"></i>
                        <p class="text-surface-600 dark:text-surface-400 font-medium text-xl">Preparando documento...
                        </p>
                    </div>
                </div>
                <!-- Footer Tip -->
                <div class="p-3 bg-[#f8fafc] dark:bg-surface-900 border-t border-surface text-center">
                    <span class="text-xs text-surface-600 dark:text-surface-400 flex items-center justify-center gap-2">
                        <i class="pi pi-info-circle text-primary"></i>
                        <span>Usa la opción <strong class="text-[#06352c] dark:text-primary">Imprimir / PDF</strong>
                            para obtener una copia perfecta.</span>
                    </span>
                </div>
            </div>
        </template>
    </Dialog>

    <!-- Modal de Confirmación de Reserva / Registro de Anticipo -->
    <Dialog v-model:visible="showModalReserva" modal :showHeader="false"
        class="!p-0 !border-none !overflow-hidden max-w-[800px]" :style="{ width: '95vw' }"
        :contentStyle="{ padding: '0' }">

        <template #default>
            <!-- Header Modal -->
            <div class="flex flex-col w-full">
                <div
                    class="flex items-center justify-between w-full bg-[#06352c] text-white p-3 pl-5 border-b border-[#0d4a3e]">
                    <div class="flex items-center gap-2.5">
                        <div class="border-2 border-white/30 p-1.5 rounded-lg flex items-center justify-center">
                            <i class="pi pi-check-square text-base text-white"></i>
                        </div>
                        <div class="flex flex-col">
                            <span class="font-bold text-sm leading-none uppercase tracking-wide text-white">CONFIRMACIÓN
                                DE RESERVA / REGISTRAR ANTICIPO</span>
                            <span class="text-[0.65rem] text-white/80 uppercase tracking-widest mt-0.5 font-bold">
                                {{ getInstitucionNombre() }} • PASO 1+2 EN UNO
                            </span>
                        </div>
                    </div>
                    <Button icon="pi pi-times" text rounded @click="showModalReserva = false"
                        class="!text-white hover:bg-white/10 w-9 h-9" />
                </div>
            </div>

            <div class="p-6 space-y-5 bg-surface-0 dark:bg-surface-900 overflow-y-auto max-h-[85vh]"
                style="font-family: var(--font-family) !important;">

                <div class="flex flex-col gap-1 mb-2">
                    <h2 class="text-xl font-black text-surface-900 dark:text-surface-0 m-0 leading-tight">{{
                        getInstitucionNombre() }}</h2>
                    <p class="text-xs text-surface-500 dark:text-surface-400 m-0 font-medium italic">
                        {{ cotizacion.evento?.descripcion }} &bull; {{ getEspaciosList() }} &bull; {{ totalDias }} días
                    </p>
                </div>
                <div
                    class="bg-[#0b3d33] dark:bg-surface-800 rounded-2xl p-5 shadow-xl border border-white/10 dark:border-surface-700">
                    <h3 class="uppercase tracking-widest mb-4 mt-0 text-center"
                        style="color: #ffffff !important; font-size: 14px !important; font-weight: 900 !important; opacity: 1 !important; text-shadow: 0 2px 4px rgba(0,0,0,0.5) !important; font-family: var(--font-family) !important;">
                        RESUMEN FINANCIERO AUTOMÁTICO
                    </h3>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <!-- Total -->
                        <div class="bg-white/5 border border-white/10 p-3.5 rounded-xl text-center">
                            <span class="text-[9px] text-white/40 uppercase font-black tracking-tighter">Total
                                Cotización</span>
                            <div class="text-lg font-black text-white mt-1">Bs {{ formatCurrency(subtotalReserva) }}
                            </div>
                        </div>
                        <!-- Anticipo -->
                        <div
                            class="bg-orange-500/10 border border-orange-500/30 p-4 rounded-xl text-center relative overflow-hidden">
                            <div class="absolute -right-2 -top-2 bg-orange-500/20 w-8 h-8 rounded-full blur-xl"></div>
                            <span class="text-[9px] text-orange-400 uppercase font-black tracking-tighter">Anticipo
                                20%</span>
                            <div class="text-lg font-black text-orange-500 mt-1">Bs {{ formatCurrency(anticipoReserva)
                            }}</div>
                            <span class="text-[8px] text-orange-400/60 block mt-1">Plazo: 2 días hábiles</span>
                        </div>
                        <!-- Saldo -->
                        <div class="bg-white/5 border border-white/10 p-4 rounded-xl text-center">
                            <span class="text-[9px] text-white/40 uppercase font-black tracking-tighter">Saldo
                                80%</span>
                            <div class="text-lg font-black text-white mt-1">Bs {{ formatCurrency(saldoReserva) }}</div>
                            <span class="text-[8px] text-white/30 block mt-1">Hasta: {{ formatDateShort(fechaLimitePago)
                            }}</span>
                        </div>
                        <!-- Garantía -->
                        <div class="bg-white/5 border border-white/10 p-4 rounded-xl text-center">
                            <span class="text-[9px] text-white/40 uppercase font-black tracking-tighter">Garantía
                                20%</span>
                            <div class="text-lg font-black text-white mt-1">Bs {{ formatCurrency(garantiaReserva) }}
                            </div>
                            <span class="text-[8px] text-white/30 block mt-1">Hasta: {{ formatDateShort(fechaLimitePago)
                            }}</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3">
                        <div
                            class="flex items-center gap-2 bg-orange-500/5 px-3 py-1.5 rounded-lg border border-orange-500/10">
                            <i class="pi pi-calendar-clock text-orange-400 text-xs"></i>
                            <span class="text-[10px] text-white/70">
                                <strong class="text-orange-400">Fecha límite pago saldo:</strong> {{
                                    formatDateShort(fechaLimitePago) }}
                                <span class="text-white/40 ml-1">(10 días antes del evento)</span>
                            </span>
                        </div>
                        <div class="flex items-center gap-2 bg-white/5 px-3 py-1.5 rounded-lg border border-white/10">
                            <i class="pi pi-shield text-white/40 text-xs"></i>
                            <span class="text-[10px] text-white/70">
                                <strong class="text-white/90">Fecha límite garantía:</strong> {{
                                    formatDateShort(fechaLimitePago) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- SECCIÓN 1: RESPALDO DE ACEPTACIÓN -->
                <div class="space-y-4">
                    <div
                        class="flex items-center gap-2 pl-3 border-l-4 border-[#06352c] dark:border-primary-400 py-0.5">
                        <i class="pi pi-file text-[#06352c] dark:text-primary-400"
                            style="font-size: 11px !important;"></i>
                        <h4 class="m-0"
                            style="font-size: 12px !important; font-weight: 800 !important; color: #06352c !important; font-family: var(--font-family) !important;">
                            <span class="dark:text-primary-400">1. Respaldo de aceptación del cliente</span>
                        </h4>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="flex flex-col gap-2">
                            <label
                                class="text-xs font-bold text-surface-500 dark:text-surface-400 uppercase tracking-widest">Tipo
                                de Respaldo <span class="text-red-500">*</span></label>
                            <Select v-model="formReserva.tipo_respaldo_id" :options="tiposRespaldo"
                                optionLabel="descripcion" optionValue="id" placeholder="Selecciona el tipo..."
                                class="w-full"
                                :class="{ 'p-invalid': submittedReserva && !formReserva.tipo_respaldo_id }" />
                        </div>
                        <div class="flex flex-col gap-2">
                            <label
                                class="text-xs font-bold text-surface-500 dark:text-surface-400 uppercase tracking-widest">Archivo
                                del respaldo <span class="text-red-500">*</span></label>
                            <div @click="$refs.fileRespaldo.click()"
                                class="flex items-center justify-center border-2 border-dashed rounded-xl p-3 bg-primary/5 hover:bg-primary/10 transition-all cursor-pointer"
                                :class="formReserva.archivo_respaldo ? 'border-primary dark:border-primary-400' : 'border-primary/20 dark:border-primary-800/30'">
                                <div class="flex items-center gap-2">
                                    <i class="pi"
                                        :class="formReserva.archivo_respaldo ? 'pi-check text-green-500' : 'pi-file-export text-primary dark:text-primary-400'"></i>
                                    <span class="text-sm font-bold truncate max-w-[150px]"
                                        :class="formReserva.archivo_respaldo ? 'text-green-600 dark:text-green-400' : 'text-primary dark:text-primary-400'">
                                        {{ formReserva.archivo_respaldo ? formReserva.archivo_respaldo.name :
                                            'Seleccionar archivo' }}
                                    </span>
                                </div>
                            </div>
                            <input type="file" ref="fileRespaldo" @change="onFileSelect($event, 'archivo_respaldo')"
                                class="hidden" accept=".pdf,.jpg,.jpeg,.png" />
                        </div>
                    </div>
                </div>

                <!-- SECCIÓN 2: BOUCHER DEL DEPÓSITO -->
                <div class="space-y-4 pt-4 border-t border-surface-100 dark:border-surface-800">
                    <div
                        class="flex items-center gap-2 pl-3 border-l-4 border-[#06352c] dark:border-primary-400 py-0.5">
                        <i class="pi pi-wallet text-[#06352c] dark:text-primary-400"
                            style="font-size: 11px !important;"></i>
                        <h4 class="m-0"
                            style="font-size: 12px !important; font-weight: 800 !important; color: #06352c !important; font-family: var(--font-family) !important;">
                            <span class="dark:text-primary-400">2. Boucher del depósito — anticipo 20% = Bs {{
                                formatCurrency(anticipoReserva) }}</span>
                        </h4>
                    </div>
                    <span class="text-surface-400 dark:text-surface-500 text-[10px] font-bold block -mt-3 pl-10">(Cta.
                        Corriente Fiscal Nro. 10000041434220)</span>

                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                        <!-- Upload Boucher -->
                        <div class="lg:col-span-12 xl:col-span-4 h-full">
                            <label
                                class="text-xs font-bold text-surface-500 dark:text-surface-400 uppercase tracking-widest block mb-2">Imagen
                                del boucher <span class="text-red-500">*</span></label>

                            <div v-if="boucherPreviewUrl"
                                class="relative group rounded-2xl overflow-hidden border-2 border-green-500/30 bg-surface-50 dark:bg-surface-800 shadow-lg">
                                <Image :src="boucherPreviewUrl" preview class="w-full h-[180px] object-cover"
                                    imageClass="w-full h-full object-cover rounded-2xl" />
                                <div
                                    class="absolute bottom-0 inset-x-0 bg-black/60 p-2 flex items-center justify-between backdrop-blur-sm">
                                    <span class="text-[9px] text-white font-bold truncate px-2">{{
                                        formReserva.archivo_boucher?.name }}</span>
                                    <Button icon="pi pi-refresh" text size="small" class="!p-1 !text-white"
                                        @click="$refs.fileBoucher.click()" />
                                </div>
                            </div>

                            <div v-else @click="$refs.fileBoucher.click()"
                                class="rounded-2xl border-2 border-dashed flex flex-col items-center justify-center gap-2 group cursor-pointer transition-all h-[135px] relative overflow-hidden border-red-300 dark:border-red-900/30 bg-red-50 dark:bg-red-900/10 hover:bg-red-100 dark:hover:bg-red-900/20">
                                <i
                                    class="pi pi-images text-2xl group-hover:scale-110 transition-transform text-red-500 dark:text-red-400"></i>
                                <span class="text-[10px] font-black uppercase text-red-600 dark:text-red-300">Adjuntar
                                    boucher (JPG, PNG)</span>
                                <p
                                    class="text-[9px] text-center px-4 leading-tight font-medium text-red-500 dark:text-red-400/60">
                                    Click para subir o arrastrar archivo</p>
                            </div>
                            <input type="file" ref="fileBoucher" @change="onFileSelect($event, 'archivo_boucher')"
                                class="hidden" accept=".jpg,.jpeg,.png" />
                        </div>

                        <!-- Data Fields -->
                        <div class="lg:col-span-12 xl:col-span-8 grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex flex-col gap-2">
                                <label
                                    class="text-xs font-bold text-surface-500 dark:text-surface-400 uppercase tracking-widest">Nº
                                    Comprobante <span class="text-red-500">*</span></label>
                                <InputText v-model="formReserva.nro_comprobante" placeholder="Ej: 203768"
                                    class="w-full" />
                            </div>
                            <div class="flex flex-col gap-2">
                                <label
                                    class="text-xs font-bold text-surface-500 dark:text-surface-400 uppercase tracking-widest">Banco
                                    / Institución <span class="text-red-500">*</span></label>
                                <Select v-model="formReserva.banco_id" :options="bancos" optionLabel="nombre"
                                    optionValue="id" placeholder="Selecciona banco..." class="w-full"
                                    :class="{ 'p-invalid': submittedReserva && !formReserva.banco_id }" />
                            </div>
                            <div class="flex flex-col gap-2">
                                <label
                                    class="text-xs font-bold text-surface-500 dark:text-surface-400 uppercase tracking-widest">Fecha
                                    de depósito <span class="text-red-500">*</span></label>
                                <DatePicker v-model="formReserva.fecha_deposito" fluid iconDisplay="input" showIcon
                                    placeholder="dd/mm/aaaa" />
                            </div>
                            <div class="flex flex-col gap-1.5 px-4 py-3 bg-surface-50 dark:bg-surface-800 rounded-xl border border-surface-200 dark:border-surface-700 transition-opacity"
                                :class="{ 'opacity-50 pointer-events-none': !isSeccion2Complete }">
                                <label
                                    class="text-xs font-bold text-surface-500 dark:text-surface-400 uppercase tracking-widest">
                                    Monto del boucher (Bs) <span class="text-red-500">*</span>
                                    <i v-if="!isSeccion2Complete" class="pi pi-lock ml-1 text-[8px] opacity-70"></i>
                                </label>
                                <div class="flex items-stretch border border-surface-300 dark:border-surface-700 rounded-xl overflow-hidden shadow-sm bg-white dark:bg-surface-800 transition-all"
                                    :class="[
                                        !isSeccion2Complete ? 'opacity-50 pointer-events-none' : 'focus-within:ring-2 focus-within:ring-primary/20',
                                        verificacionEstado === 'success' ? 'border-green-500 shadow-[0_0_10px_rgba(34,197,94,0.1)]' : '',
                                        verificacionEstado === 'error' ? 'border-red-500 shadow-[0_0_10px_rgba(239,68,68,0.1)]' : ''
                                    ]">
                                    <InputNumber v-model="formReserva.monto_deposito" class="flex-1" :unstyled="true"
                                        inputClass="w-full px-4 py-3 border-none font-bold text-lg text-primary dark:text-primary-400 focus:outline-none bg-transparent"
                                        mode="decimal" :minFractionDigits="2"
                                        :placeholder="isSeccion2Complete ? '0.00' : 'Adjunta el boucher primero'"
                                        :disabled="!isSeccion2Complete" />
                                    <button
                                        class="bg-[#0b3d33] dark:bg-primary-600 text-white px-6 uppercase font-black text-[10px] tracking-widest hover:bg-[#0a4a3e] dark:hover:bg-primary-700 transition-colors disabled:opacity-50 border-l border-white/10"
                                        :disabled="!isSeccion2Complete" @click="verificarMonto">
                                        Verificar
                                    </button>
                                </div>
                                <div class="flex flex-col gap-1 mt-1">
                                    <span v-if="verificacionMensaje"
                                        class="text-[10px] font-black uppercase tracking-tight"
                                        :class="verificacionEstado === 'success' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
                                        {{ verificacionMensaje }}
                                    </span>
                                    <span
                                        class="text-[10px] text-surface-600 dark:text-surface-400 font-bold italic">Esperado
                                        exacto: Bs {{ formatCurrency(anticipoReserva) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECCIÓN 3: REUNIÓN OPERATIVA -->
                <div class="space-y-4 pt-4 border-t border-surface-100 dark:border-surface-800">
                    <div
                        class="flex items-center gap-2 pl-3 border-l-4 border-[#06352c] dark:border-primary-400 py-0.5">
                        <i class="pi pi-calendar-plus text-[#06352c] dark:text-primary-400"
                            style="font-size: 11px !important;"></i>
                        <h4 class="m-0"
                            style="font-size: 12px !important; font-weight: 800 !important; color: #06352c !important; font-family: var(--font-family) !important;">
                            <span class="dark:text-primary-400">3. Programar Reunión Operativa (Art. 41)</span>
                        </h4>
                        <span
                            class="text-red-500 text-[10px] font-black uppercase tracking-tighter ml-auto">Obligatorio</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="flex flex-col gap-2">
                            <label
                                class="text-xs font-bold text-surface-500 dark:text-surface-400 uppercase tracking-widest text-[#06352c] dark:text-primary-400">Fecha</label>
                            <DatePicker v-model="formReserva.reunion_fecha" fluid showIcon placeholder="dd/mm/aaaa" />
                        </div>
                        <div class="flex flex-col gap-2">
                            <label
                                class="text-xs font-bold text-surface-500 dark:text-surface-400 uppercase tracking-widest text-[#06352c] dark:text-primary-400">Hora</label>
                            <InputMask v-model="formReserva.reunion_hora" mask="99:99" placeholder="00:00"
                                class="w-full" />
                        </div>
                        <div class="flex flex-col gap-2">
                            <label
                                class="text-xs font-bold text-surface-500 dark:text-surface-400 uppercase tracking-widest text-[#06352c] dark:text-primary-400">Modalidad</label>
                            <Select v-model="formReserva.reunion_modalidad" :options="modalidadesReserva"
                                optionLabel="label" optionValue="value" placeholder="Modalidad..." class="w-full" />
                        </div>
                    </div>

                    <div class="flex flex-col gap-2">
                        <label
                            class="text-xs font-bold text-surface-500 dark:text-surface-400 uppercase tracking-widest text-[#06352c] dark:text-primary-400">Observaciones</label>
                        <Textarea v-model="formReserva.observaciones_reunion" rows="2"
                            placeholder="Aspectos a coordinar..." class="w-full text-sm" />
                    </div>

                    <div
                        class="bg-orange-50 dark:bg-orange-900/10 border border-orange-200 dark:border-orange-800/30 p-4 rounded-lg flex items-center gap-3">
                        <i class="pi pi-info-circle text-orange-600 dark:text-orange-400 text-xl"></i>
                        <span class="text-sm text-orange-800 dark:text-orange-200 font-bold">Al guardar se enviará
                            alerta automática al Encargado de Montaje y Desmontaje.</span>
                    </div>
                </div>

                <!-- COMPROMISOS PENDIENTES SUMMARY -->
                <div
                    class="bg-surface-50 dark:bg-surface-800 border border-surface-200 dark:border-surface-700 p-5 rounded-2xl shadow-inner mb-4">
                    <h5 class="m-0 uppercase tracking-widest flex items-center gap-2 mb-3"
                        style="font-size: 13px !important; font-weight: 900 !important; color: #06352c !important; font-family: var(--font-family) !important;">
                        <i class="pi pi-briefcase text-sm dark:text-primary-400"></i> <span
                            class="dark:text-primary-400">Compromisos pendientes del cliente:</span>
                    </h5>
                    <ul class="m-0 p-0 pl-5 space-y-3 text-xs font-bold text-surface-700 dark:text-surface-300">
                        <li class="flex items-start gap-2">
                            <span class="text-primary dark:text-primary-400 mt-0.5">•</span>
                            <div class="flex flex-col">
                                <span class="leading-tight">Pago de saldo 80%: <span
                                        class="text-primary dark:text-primary-400 font-black">Bs {{
                                            formatCurrency(saldoReserva) }}</span></span>
                                <span class="text-[10px] text-surface-400 dark:text-surface-500 font-medium">Límite: {{
                                    formatDateShort(fechaLimitePago) }} • Cta Corriente Fiscal Nro 10000041454220</span>
                            </div>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-primary dark:text-primary-400 mt-0.5">•</span>
                            <div class="flex flex-col">
                                <span class="leading-tight">Pago de garantía 20%: <span
                                        class="text-primary dark:text-primary-400 font-black">Bs {{
                                            formatCurrency(garantiaReserva) }}</span></span>
                                <span class="text-[10px] text-surface-400 dark:text-surface-500 font-medium">Límite: {{
                                    formatDateShort(fechaLimitePago) }} • Cta Fondo Rotativo Nro 10000041433685</span>
                            </div>
                        </li>
                    </ul>
                </div>

            </div>

            <!-- Acciones Footer -->
            <div class="p-6 pt-0 border-t border-surface-200 dark:border-surface-800 pt-6">
                <Button label="Confirmar Reserva — Cerrar Venta" icon="pi pi-check-circle"
                    class="w-full !bg-[#06352c] dark:!bg-primary-600 !border-none !py-4 !text-base !font-bold shadow-xl hover:!bg-[#0a4a3e] dark:hover:!bg-primary-700 transition-all rounded-xl text-white"
                    @click="confirmarReserva" :loading="savingReserva" />
            </div>
        </template>
    </Dialog>
</template>

<script setup>
import { ref, onMounted, computed, reactive, watch } from 'vue';
import { useRoute } from 'vue-router';
import Image from 'primevue/image';
import cotizacionServices from '@/services/cotizacion.services';
import bancoServices from '@/services/banco.services';
import tiporespaldoServices from '@/services/tiporespaldo.services';
import pagoServices from '@/services/pago.services';
import eventoServices from '@/services/evento.services';
import espacioServices from '@/services/espacio.services';
import servicioServices from '@/services/servicio.services';
import tarifaServices from '@/services/tarifa.services';
import { useToast } from 'primevue/usetoast';

const route = useRoute();
const toast = useToast();
const loading = ref(true);
const cotizacion = ref(null);
const drawerVisible = ref(false);

// ============================================================================
// DATOS AUXILIARES PARA EL MODAL DE EDICIÓN
// ============================================================================
const tiposEvento = ref([]);
const espaciosDisponibles = ref([]);
const serviciosDisponibles = ref([]);
const tarifasPorEspacioEdit = ref({}); // caché de tarifas para el modal

// Estado del Modal
const showModalEdicion = ref(false);
const savingEdit = ref(false);
const submittedEdit = ref(false);
const editandoTipoEvento = ref(false); // true si ya se editó antes

// Estado para Vista Previa PDF
const showPdfPreview = ref(false);
const pdfUrl = ref(null);

// ============================================================================
// ESTADO PARA CONFIRMACIÓN DE RESERVA
// ============================================================================
const showModalReserva = ref(false);
const submittedReserva = ref(false);
const savingReserva = ref(false);

const bancos = ref([]);
const tiposRespaldo = ref([]);
const boucherPreviewUrl = ref(null);
const verificacionMensaje = ref('');
const verificacionEstado = ref(null); // 'success', 'error', null

const formReserva = reactive({
    tipo_respaldo_id: null,
    archivo_respaldo: null,
    archivo_boucher: null,
    nro_comprobante: '',
    banco_id: null,
    fecha_deposito: null,
    monto_deposito: 0,
    reunion_fecha: null,
    reunion_hora: '10:00',
    reunion_modalidad: 'Presencial',
    observaciones_reunion: ''
});

const loadCatalogosReserva = async () => {
    try {
        const [resBancos, resTipos] = await Promise.all([
            bancoServices.listar(),
            tiporespaldoServices.listar()
        ]);
        bancos.value = resBancos.data?.data || resBancos.data || [];
        tiposRespaldo.value = resTipos.data?.data || resTipos.data || [];
    } catch (e) {
        console.error('Error al cargar catálogos:', e);
    }
};

const modalidadesReserva = [
    { label: 'Presencial', value: 'Presencial' },
    { label: 'Virtual (Zoom/Meet)', value: 'Virtual' },
    { label: 'Llamada Telefónica', value: 'Llamada' }
];

const respaldosOptions = [
    { label: 'Carta de Solicitud', value: 'Carta' },
    { label: 'Correo Electrónico', value: 'Email' },
    { label: 'WhatsApp / Mensaje', value: 'WhatsApp' },
    { label: 'Formulario de Reserva', value: 'Formulario' }
];

const abrirModalReserva = () => {
    if (!cotizacion.value) return;

    // Resetear form
    formReserva.tipo_respaldo = null;
    formReserva.archivo_respaldo = null;
    formReserva.boucher_imagen = null;
    formReserva.nro_comprobante = '';
    formReserva.banco = null;
    formReserva.fecha_deposito = new Date();
    formReserva.monto_deposito = Number(((cotizacion.value?.monto_total || 0) * 0.2).toFixed(2));
    formReserva.reunion_fecha = cotizacion.value.fecha_ini ? new Date(cotizacion.value.fecha_ini) : null;
    formReserva.reunion_hora = '10:00';
    formReserva.reunion_modalidad = 'Presencial';
    formReserva.observaciones_reunion = '';

    submittedReserva.value = false;
    showModalReserva.value = true;
};

const onFileSelect = (event, field) => {
    const file = event.target.files[0];
    if (file) {
        formReserva[field] = file;

        // Generar preview si es boucher
        if (field === 'archivo_boucher') {
            if (boucherPreviewUrl.value) URL.revokeObjectURL(boucherPreviewUrl.value);
            boucherPreviewUrl.value = URL.createObjectURL(file);
        }
    }
};

const confirmarReserva = async () => {
    submittedReserva.value = true;

    // Validaciones básicas
    if (!formReserva.tipo_respaldo_id || !formReserva.archivo_respaldo ||
        !formReserva.archivo_boucher || !formReserva.nro_comprobante || !formReserva.banco_id) {
        toast.add({ severity: 'warn', summary: 'Atención', detail: 'Por favor complete todos los campos obligatorios y adjunte los archivos.', life: 3000 });
        return;
    }

    try {
        savingReserva.value = true;

        // 1. Registrar el Pago
        const formDataPago = new FormData();
        formDataPago.append('banco_id', formReserva.banco_id);
        formDataPago.append('nro_comprobante', formReserva.nro_comprobante);
        formDataPago.append('monto_pagado', formReserva.monto_deposito);
        formDataPago.append('fecha_deposito', formReserva.fecha_deposito.toISOString().split('T')[0]);
        formDataPago.append('boucher', formReserva.archivo_boucher);

        await pagoServices.registrarPago(cotizacion.value.id, formDataPago);

        // 2. Subir el Respaldo Legal
        const formDataRespaldo = new FormData();
        formDataRespaldo.append('id_tipo_respaldo', formReserva.tipo_respaldo_id);
        formDataRespaldo.append('archivo', formReserva.archivo_respaldo);

        await cotizacionServices.subirRespaldo(cotizacion.value.id, formDataRespaldo);

        // 3. Actualizar estado de la cotización (Backend debería hacerlo, pero refrescamos)
        await fetchCotizacion();

        toast.add({ severity: 'success', summary: 'Éxito', detail: 'Reserva confirmada y archivos registrados correctamente.', life: 3000 });
        showModalReserva.value = false;
    } catch (e) {
        console.error(e);
        toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudo completar el registro de reserva.', life: 3000 });
    } finally {
        savingReserva.value = false;
    }
};

const isSeccion2Complete = computed(() => {
    return formReserva.archivo_boucher &&
        formReserva.nro_comprobante &&
        formReserva.banco_id &&
        formReserva.fecha_deposito;
});

const verificarMonto = () => {
    if (!formReserva.monto_deposito && formReserva.monto_deposito !== 0) return;

    // Comparar con un margen de error pequeño para decimales
    const esperado = Number(anticipoReserva.value);
    const ingresado = Number(formReserva.monto_deposito);

    if (Math.abs(esperado - ingresado) < 0.01) {
        verificacionMensaje.value = '✅ Monto verificado correctamente';
        verificacionEstado.value = 'success';
    } else {
        verificacionMensaje.value = `❌ Monto incorrecto. El boucher debe ser exactamente Bs ${formatCurrency(esperado)}`;
        verificacionEstado.value = 'error';
    }
};

const subtotalReserva = computed(() => cotizacion.value?.monto_total || 0);
const anticipoReserva = computed(() => Number((subtotalReserva.value * 0.2).toFixed(2)));
const saldoReserva = computed(() => Number((subtotalReserva.value * 0.8).toFixed(2)));
const garantiaReserva = computed(() => Number((subtotalReserva.value * 0.2).toFixed(2)));

const fechaLimitePago = computed(() => {
    if (!cotizacion.value?.fecha_ini) return '-';
    const date = new Date(cotizacion.value.fecha_ini);
    date.setDate(date.getDate() - 10);
    return date.toISOString().split('T')[0];
});

// Modelo del formulario de edición
const formEdit = reactive({
    codigo: '',
    tipo_evento_id: null,
    descripcion: '',
    fecha_ini: null,
    fecha_fin: null,
    espacios: [],        // { espacio_id, dias, tarifa_id, precio_dia }
    servicios: [],       // { servicio_id, cantidad, dias, precio }
    motivo: ''
});

// ============================================================================
// CARGAR DATOS AUXILIARES
// ============================================================================
const cargarDatosAuxiliares = async () => {
    try {
        const [eventosRes, espaciosRes, serviciosRes] = await Promise.all([
            eventoServices.listar(),
            espacioServices.listar(),
            servicioServices.listar()
        ]);
        tiposEvento.value = eventosRes.data?.data || eventosRes.data || [];
        espaciosDisponibles.value = espaciosRes.data;
        serviciosDisponibles.value = serviciosRes.data;
    } catch (e) {
        console.error("Error al cargar datos auxiliares para edición", e);
    }
};

// ============================================================================
// ON MOUNTED
// ============================================================================
const fetchCotizacion = async () => {
    try {
        const id = route.params.id;
        const res = await cotizacionServices.mostrar(id);
        cotizacion.value = res.data;
    } catch (e) {
        toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudo cargar la cotización.', life: 5000 });
    }
};

// ============================================================================
// ON MOUNTED
// ============================================================================
onMounted(async () => {
    loading.value = true;
    try {
        await Promise.all([
            fetchCotizacion(),
            loadCatalogosReserva()
        ]);
        await cargarDatosAuxiliares();
    } catch (e) {
        console.error(e);
    } finally {
        loading.value = false;
    }
});

// PASOS
const stepsBase = [
    { id: 1, label: 'En gestión', short: 'En gestión', desc: 'Cotización y CRM', icon: 'pi pi-user' },
    { id: 2, label: 'Confirmación de Reserva', short: "Confirmación\nde Reserva", desc: 'Anticipo + documentos (Art.40)', icon: 'pi pi-file' },
    { id: 3, label: 'Reunión Operativa', short: "Reunión\nOperativa", desc: 'Agendar reunión operativa', icon: 'pi pi-calendar' },
    { id: 4, label: 'Pago Total + Garantía', short: "Pago Total +\nGarantía", desc: 'Garantía 20%', icon: 'pi pi-credit-card' },
    { id: 5, label: 'Informe de Inicio', short: "Informe de\nInicio", desc: 'Ejecutivo -> Jefe UCFCHM', icon: 'pi pi-file' },
    { id: 6, label: 'En Revisión Legal', short: "En Revisión\nLegal", desc: 'Extendido a área legal', icon: 'pi pi-briefcase' },
    { id: 7, label: 'Contrato Suscrito', short: "Contrato\nSuscrito", desc: 'Elaboración y firma (Art.44)', icon: 'pi pi-pencil' },
    { id: 8, label: 'Inicio Premontaje', short: "Inicio\nPremontaje", desc: 'Autorización de montaje', icon: 'pi pi-wrench' },
    { id: 9, label: 'Acta de Entrega', short: "Acta de\nEntrega", desc: 'Firma + anexo', icon: 'pi pi-home' },
    { id: 10, label: 'En Curso', short: "En Curso", desc: 'Evento activo', icon: 'pi pi-play' },
    { id: 11, label: 'Acta de Devolución', short: "Acta de\nDevolución", desc: 'Devolución conforme', icon: 'pi pi-replay' },
    { id: 12, label: 'Informe de Cierre', short: "Informe de\nCierre", desc: 'Evaluación final', icon: 'pi pi-flag' },
    { id: 13, label: 'Análisis de Garantía', short: "Análisis de\nGarantía", desc: 'Análisis', icon: 'pi pi-chart-pie' },
    { id: 14, label: 'Cerrado', short: "Cerrado", desc: 'Fin', icon: 'pi pi-check-circle' },
];

const approvalSteps = computed(() => {
    if (!cotizacion.value) return [];
    const pasoActual = cotizacion.value.paso || 1;
    return stepsBase.map(s => ({
        ...s,
        status: s.id < pasoActual ? 'completed' : (s.id === pasoActual ? 'current' : 'pending')
    }));
});

// Helpers
const formatDateShort = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    date.setMinutes(date.getMinutes() + date.getTimezoneOffset());
    return date.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: '2-digit' }).replace(/\//g, '-');
};

const formatDateTime = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleString('es-ES', {
        day: '2-digit', month: '2-digit', year: '2-digit',
        hour: '2-digit', minute: '2-digit'
    });
};

const totalDias = computed(() => {
    if (!cotizacion.value) return 1;
    const start = new Date(cotizacion.value.fecha_ini);
    const end = new Date(cotizacion.value.fecha_fin);
    return Math.max(1, Math.ceil(Math.abs(end - start) / (1000 * 60 * 60 * 24)) + 1);
});

const getInstitucionNombre = () => {
    if (!cotizacion.value?.clientes) return 'S/N';
    const inst = cotizacion.value.clientes.find(c => c.tipo_cliente_id === 1);
    const perso = cotizacion.value.clientes.find(c => c.tipo_cliente_id === 2);
    return inst ? inst.nombre : (perso ? perso.nombre : 'S/N');
};

const getInstitucionNit = () => {
    if (!cotizacion.value?.clientes) return '00000';
    const inst = cotizacion.value.clientes.find(c => c.tipo_cliente_id === 1);
    const perso = cotizacion.value.clientes.find(c => c.tipo_cliente_id === 2);
    return inst ? (inst.ci_nit || '-') : (perso ? (perso.ci_nit || '-') : '-');
};

const getContactoNombre = () => {
    if (!cotizacion.value?.clientes) return '-';
    const inst = cotizacion.value.clientes.find(c => c.tipo_cliente_id === 1);
    const perso = cotizacion.value.clientes.find(c => c.tipo_cliente_id === 2);
    if (inst && perso) return perso.nombre;
    if (!inst && perso) return perso.nombre;
    return '-';
};

const getContactoTelefono = () => {
    if (!cotizacion.value?.clientes) return '';
    const perso = cotizacion.value.clientes.find(c => c.tipo_cliente_id === 2);
    return perso ? perso.telefono || '' : '';
};

const getContactoEmail = () => {
    if (!cotizacion.value?.clientes) return '-';
    const perso = cotizacion.value.clientes.find(c => c.tipo_cliente_id === 2);
    const inst = cotizacion.value.clientes.find(c => c.tipo_cliente_id === 1);
    return perso?.email || inst?.email || '-';
};

const getEspaciosList = () => {
    if (!cotizacion.value || !cotizacion.value.tarifas) return '';
    const spaces = [...new Set(cotizacion.value.tarifas.map(t => `${t.espacio?.nombre} (${t.evento?.descripcion || t.tipo_tarifa?.nombre || 'Tarifa'})`))];
    return spaces.join(', ');
};

// Función para formatear moneda
const formatCurrency = (val) => Number(val || 0).toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

const getEstadoLabel = (paso) => {
    const found = stepsBase.find(s => s.id === paso);
    return found ? found.label : 'En gestión';
};

// ============================================================================
// HELPERS PARA BLOQUES (Modal edición)
// ============================================================================
const getBloqueColorRaw = (espacioId) => {
    if (!espaciosDisponibles.value.length) return '#cbd5e1';
    const s = espaciosDisponibles.value.find(e => Number(e.id) === Number(espacioId));
    return s?.bloque?.color || '#cbd5e1';
};

const getBloqueLabel = (espacioId) => {
    if (!espaciosDisponibles.value.length) return '';
    const s = espaciosDisponibles.value.find(e => Number(e.id) === Number(espacioId));
    return s?.bloque?.descripcion || '';
};

const getEspacioNombre = (espacioId) => {
    const s = espaciosDisponibles.value.find(e => Number(e.id) === Number(espacioId));
    return s?.nombre || 'Espacio';
};

const getSpaceMinPriceEdit = (espacioId) => {
    if (!tarifasPorEspacioEdit.value[espacioId]) return 0;
    const t = tarifasPorEspacioEdit.value[espacioId];
    if (!t.length) return 0;
    return Math.min(...t.map(item => Number(item.precio_dia || 0)));
};

const getTarifaEventoLabel = (espacioId) => {
    if (!formEdit.espacios) return '—';
    const e = formEdit.espacios.find(esp => Number(esp.espacio_id) === Number(espacioId));
    if (!e || !e.tarifa_id) return '—';
    const tarifas = tarifasPorEspacioEdit.value[espacioId] || [];
    const t = tarifas.find(tr => Number(tr.id) === Number(e.tarifa_id));
    if (!t) return '—';
    const eventoDesc = t.evento?.descripcion || 'Tarifa';
    const precio = Number(t.precio_dia || 0);
    return `${eventoDesc} • Bs ${precio.toLocaleString()}/d`;
};

// ============================================================================
// LÓGICA DEL MODAL DE EDICIÓN
// ============================================================================
const abrirModalEdicion = async () => {
    if (!cotizacion.value) return;

    // Verificar que esté en estado "En gestión" (paso 1)
    if (cotizacion.value.paso !== 1) {
        toast.add({
            severity: 'warn',
            summary: 'No editable',
            detail: 'Solo se puede editar cotizaciones en estado "En gestión".',
            life: 4000
        });
        return;
    }

    // Preparar formEdit desde los datos actuales
    formEdit.codigo = cotizacion.value.codigo || `COT-${String(cotizacion.value.id).padStart(3, '0')}`;
    formEdit.tipo_evento_id = cotizacion.value.evento?.id || cotizacion.value.evento_id;
    formEdit.descripcion = cotizacion.value.descripcion || '';
    formEdit.fecha_ini = cotizacion.value.fecha_ini ? new Date(cotizacion.value.fecha_ini) : null;
    formEdit.fecha_fin = cotizacion.value.fecha_fin ? new Date(cotizacion.value.fecha_fin) : null;
    formEdit.motivo = '';
    formEdit.espacios = [];
    formEdit.servicios = [];
    submittedEdit.value = false;

    // Mapear espacios (tarifas) a la estructura de edición
    if (cotizacion.value.tarifas) {
        // Agrupar por espacio_id
        const espacioMap = {};
        cotizacion.value.tarifas.forEach(ta => {
            const espId = ta.espacio?.id || ta.espacio_id;
            if (!espacioMap[espId]) {
                espacioMap[espId] = {
                    espacio_id: espId,
                    dias: Number(ta.pivot?.dias || 1),
                    tarifa_id: ta.id,
                    precio_dia: Number(ta.pivot?.precio_aplicado || ta.precio_dia || 0)
                };
            } else {
                espacioMap[espId].dias += Number(ta.pivot?.dias || 0);
            }
        });
        formEdit.espacios = Object.values(espacioMap);
    }

    // Mapear servicios
    if (cotizacion.value.servicios) {
        formEdit.servicios = cotizacion.value.servicios.map(sv => ({
            servicio_id: sv.id || sv.servicio_id,
            cantidad: Number(sv.pivot?.cantidad || 1),
            dias: Number(sv.pivot?.dias || 1),
            precio: Number(sv.pivot?.precio_aplicado || sv.precio || 0)
        }));
    }

    // Cargar tarifas de cada espacio en el modal
    await cargarTarifasParaEdicion();
    showModalEdicion.value = true;
};

const cargarTarifasParaEdicion = async () => {
    const espacioIds = [...new Set(formEdit.espacios.map(e => e.espacio_id))];
    for (const espId of espacioIds) {
        if (!tarifasPorEspacioEdit.value[espId]) {
            try {
                const { data } = await tarifaServices.obtenerPorEspacio(espId);
                tarifasPorEspacioEdit.value[espId] = data?.data || data || [];
            } catch (e) {
                tarifasPorEspacioEdit.value[espId] = [];
            }
        }
    }
};

const getTarifasOptionsEdit = (espacioId) => {
    const tarifas = tarifasPorEspacioEdit.value[espacioId] || [];
    // Filtrar por el evento seleccionado
    const eventoId = formEdit.tipo_evento_id;
    const filtradas = eventoId
        ? tarifas.filter(t => Number(t.evento_id) === Number(eventoId))
        : tarifas;
    return filtradas.map(t => ({
        id: t.id,
        label: `${t.evento?.descripcion || 'Evento'} • Bs ${Number(t.precio_dia).toLocaleString()}/d`,
        precio_dia: Number(t.precio_dia || 0)
    }));
};

const getPrecioTarifa = (tarifaId) => {
    if (!tarifaId) return 0;
    for (const key in tarifasPorEspacioEdit.value) {
        const found = tarifasPorEspacioEdit.value[key].find(t => Number(t.id) === Number(tarifaId));
        if (found) return Number(found.precio_dia || 0);
    }
    return 0;
};

const isEspacioInEdit = (espacioId) => {
    return formEdit.espacios.some(e => Number(e.espacio_id) === Number(espacioId));
};

const toggleEspacioEdicion = async (espacio) => {
    const idx = formEdit.espacios.findIndex(e => Number(e.espacio_id) === Number(espacio.id));
    if (idx > -1) {
        formEdit.espacios.splice(idx, 1);
    } else {
        // Cargar tarifas de este espacio si no están
        if (!tarifasPorEspacioEdit.value[espacio.id]) {
            try {
                const { data } = await tarifaServices.obtenerPorEspacio(espacio.id);
                tarifasPorEspacioEdit.value[espacio.id] = data?.data || data || [];
            } catch (e) {
                tarifasPorEspacioEdit.value[espacio.id] = [];
            }
        }
        const tarifasOpts = getTarifasOptionsEdit(espacio.id);
        formEdit.espacios.push({
            espacio_id: espacio.id,
            dias: 1,
            tarifa_id: tarifasOpts.length > 0 ? tarifasOpts[0].id : null,
            precio_dia: tarifasOpts.length > 0 ? tarifasOpts[0].precio_dia : 0
        });
    }
};

const eliminarEspacioEdicion = (idx) => {
    formEdit.espacios.splice(idx, 1);
};

// Servicios en edición
const isServicioInEdit = (servicioId) => {
    return formEdit.servicios.some(s => Number(s.servicio_id) === Number(servicioId));
};

const getServicioEditData = (servicioId) => {
    const found = formEdit.servicios.find(s => Number(s.servicio_id) === Number(servicioId));
    return found || { cantidad: 1, dias: 1, precio: 0 };
};

const toggleServicioEdicion = (servicio) => {
    const idx = formEdit.servicios.findIndex(s => Number(s.servicio_id) === Number(servicio.id));
    if (idx > -1) {
        formEdit.servicios.splice(idx, 1);
    } else {
        formEdit.servicios.push({
            servicio_id: servicio.id,
            cantidad: 1,
            dias: servicio.porEv ? 1 : 1,
            precio: Number(servicio.precio || 0)
        });
    }
};

const calcularServicioEditSubtotal = (servicio) => {
    const data = getServicioEditData(servicio.id);
    if (servicio.porEv) return (data.cantidad || 0) * (data.precio || 0);
    return (data.cantidad || 0) * (data.precio || 0) * (data.dias || 1);
};

const calcularTotalServiciosEdit = computed(() => {
    return formEdit.servicios.reduce((sum, s) => {
        const sv = serviciosDisponibles.value.find(sv => Number(sv.id) === Number(s.servicio_id));
        if (sv?.porEv) return sum + (s.cantidad || 0) * (s.precio || 0);
        return sum + (s.cantidad || 0) * (s.precio || 0) * (s.dias || 1);
    }, 0);
});

const calcularTotalEdit = computed(() => {
    // Total espacios
    const totalEspacios = formEdit.espacios.reduce((sum, e) => {
        return sum + (getPrecioTarifa(e.tarifa_id) || 0) * (e.dias || 0);
    }, 0);
    return totalEspacios + calcularTotalServiciosEdit.value;
});

const cerrarModalEdicion = () => {
    if (savingEdit.value) return;
    showModalEdicion.value = false;
};

// Reactividad de fechas: Actualizar días automáticamente
watch([() => formEdit.fecha_ini, () => formEdit.fecha_fin], ([newIni, newFin]) => {
    if (newIni && newFin) {
        const diffTime = Math.abs(newFin - newIni);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1; // +1 para ser inclusivo

        if (diffDays > 0) {
            // Actualizar días en espacios
            formEdit.espacios.forEach(esp => {
                esp.dias = diffDays;
            });
            // Actualizar días en servicios (los que no son por evento)
            formEdit.servicios.forEach(s => {
                const sv = serviciosDisponibles.value.find(serv => Number(serv.id) === Number(s.servicio_id));
                if (sv && !sv.porEv) {
                    s.dias = diffDays;
                }
            });
        }
    }
}, { deep: true });

const guardarVersionEditada = async () => {
    submittedEdit.value = true;

    if (!formEdit.motivo || formEdit.motivo.trim() === '') {
        toast.add({
            severity: 'warn',
            summary: 'Motivo requerido',
            detail: 'Debes describir el motivo del cambio para registrar la modificación.',
            life: 5000
        });
        return;
    }

    try {
        savingEdit.value = true;

        // Construir payload para actualización
        const payload = {
            descripcion: formEdit.descripcion,
            fecha_ini: formEdit.fecha_ini,
            fecha_fin: formEdit.fecha_fin,
            evento_id: formEdit.tipo_evento_id,
            motivo: formEdit.motivo,
            tarifas: formEdit.espacios.flatMap(e => {
                const items = [];
                const tarifaId = e.tarifa_id;
                const dias = e.dias || 1;
                const precioDia = getPrecioTarifa(tarifaId);
                if (tarifaId && dias > 0 && precioDia > 0) {
                    items.push({
                        id: tarifaId,
                        dias: dias,
                        precio_aplicado: precioDia
                    });
                }
                return items;
            }),
            servicios: formEdit.servicios.map(s => ({
                id: s.servicio_id,
                cantidad: s.cantidad || 1,
                dias: s.dias || 1,
                precio_aplicado: s.precio || 0
            }))
        };

        await cotizacionServices.actualizar(cotizacion.value.id, payload);

        toast.add({
            severity: 'success',
            summary: 'Versión guardada',
            detail: 'La cotización ha sido actualizada correctamente. Los cambios quedaron registrados.',
            life: 5000
        });

        showModalEdicion.value = false;
        savingEdit.value = false;

        // Recargar la cotización para reflejar los cambios
        const res = await cotizacionServices.mostrar(cotizacion.value.id);
        cotizacion.value = res.data;

    } catch (e) {
        savingEdit.value = false;
        const detail = e.response?.data?.message || 'No se pudo guardar la versión actualizada.';
        toast.add({ severity: 'error', summary: 'Error', detail, life: 5000 });
    }
};

const printing = ref(false);

const reimprimirCotizacion = async () => {
    if (!cotizacion.value) return;
    try {
        printing.value = true;

        // Si ya existe una URL previa, la liberamos
        if (pdfUrl.value) {
            window.URL.revokeObjectURL(pdfUrl.value);
            pdfUrl.value = null;
        }

        const response = await cotizacionServices.imprimir(cotizacion.value.id);
        const blob = new Blob([response.data], { type: 'application/pdf' });
        pdfUrl.value = window.URL.createObjectURL(blob);

        showPdfPreview.value = true;
        toast.add({ severity: 'success', summary: 'Éxito', detail: 'Vista previa generada', life: 2000 });
    } catch (e) {
        toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudo generar el documento.', life: 5000 });
    } finally {
        printing.value = false;
    }
};

const downloadPdfPreview = () => {
    if (!pdfUrl.value) return;
    const link = document.createElement('a');
    link.href = pdfUrl.value;
    link.setAttribute('download', `${cotizacion.value?.codigo || 'Cotizacion'}.pdf`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
};

const printPdfPreview = () => {
    if (!pdfUrl.value) return;
    const iframe = document.querySelector('iframe');
    if (iframe) {
        iframe.contentWindow.focus();
        iframe.contentWindow.print();
    } else {
        window.open(pdfUrl.value, '_blank').print();
    }
};

// Limpieza al cerrar el modal (u ocultarlo)
watch(showPdfPreview, (newVal) => {
    if (!newVal && pdfUrl.value) {
        window.URL.revokeObjectURL(pdfUrl.value);
        pdfUrl.value = null;
    }
});

import { onUnmounted } from 'vue';
onUnmounted(() => {
    if (pdfUrl.value) {
        window.URL.revokeObjectURL(pdfUrl.value);
    }
});
</script>

<style scoped>
.hide-scrollbar::-webkit-scrollbar {
    display: none;
}

.hide-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
