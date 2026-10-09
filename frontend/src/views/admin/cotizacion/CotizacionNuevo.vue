<template>
    <div class="card p-0 overflow-hidden">
        <Toast />
        <!-- Componente Stepper de PrimeVue para manejar los 4 pasos definidos -->
        <Stepper v-model:value="activeStep" class="basis-[40rem]">

            <!-- Lista de Pestañas (Header del Wizard) -->
            <StepList class="bg-surface-0 dark:bg-surface-900 border-b border-surface">
                <Step :value="0">1 Datos del Evento</Step>
                <Step :value="1">2 Espacios</Step>
                <Step :value="2">3 Servicios</Step>
                <Step :value="3">4 Resumen</Step>
            </StepList>

            <!-- Contenedor general de todos los Paneles (Vistas de cada paso) -->
            <StepPanels class="p-6 md:px-10">

                <!-- ============================================== -->
                <!-- PASO 1: DATOS DEL EVENTO                       -->
                <!-- ============================================== -->
                <StepPanel :value="0" v-slot="{ activateCallback }">
                    <div class="flex flex-col gap-8">
                        <div class="grid grid-cols-12 gap-8 lg:gap-12">

                            <!-- COLUMNA IZQUIERDA: Búsqueda y Selección de Clientes/Instituciones -->
                            <div
                                class="col-span-12 lg:col-span-7 flex flex-col gap-6 border-b lg:border-b-0 lg:border-r border-surface pb-6 lg:pb-0 lg:pr-10">

                                <!-- BLOQUE 1: Información de Entidad (Cliente Principal) -->
                                <div class="flex flex-col gap-3">
                                    <label
                                        class="font-bold text-surface-900 dark:text-surface-0 mb-1 lg:text-lg">Información
                                        del Cliente / Institución</label>

                                    <!-- Buscador Principal -->
                                    <div class="flex gap-2 items-start w-full">
                                        <Select v-model="cotizacion.entidad_id" :options="entidades" optionValue="id"
                                            filter placeholder="Seleccione un cliente o institución" class="flex-1"
                                            @change="onEntidadChange">
                                            <!-- Personalización de cómo se ve la opción seleccionada contraída -->
                                            <template #value="slotProps">
                                                <div v-if="slotProps.value" class="flex items-center gap-2 font-medium">
                                                    <i :class="selectedEntidad?.tipo === 'institucion' ? 'pi pi-building' : 'pi pi-user'"
                                                        class="text-primary hidden sm:inline"></i>
                                                    <span class="truncate">{{ selectedEntidad?.nombre }}</span>
                                                </div>
                                                <span v-else>{{ slotProps.placeholder }}</span>
                                            </template>
                                            <!-- Personalización de cómo se ve cada opción en la lista desplegable -->
                                            <template #option="slotProps">
                                                <div class="flex flex-col">
                                                    <span
                                                        class="text-[0.7rem] text-muted-color tracking-wider uppercase font-bold">{{
                                                            slotProps.option.nit || 'S/N' }} &bull; {{ slotProps.option.tipo
                                                        }}</span>
                                                    <span class="font-medium text-surface-900 dark:text-surface-0">{{
                                                        slotProps.option.nombre }}</span>
                                                </div>
                                            </template>
                                        </Select>
                                        <Button label="Nuevo" icon="pi pi-user-plus" severity="primary"
                                            @click="openModalNuevaEntidad" />
                                    </div>

                                    <!-- Tarjeta Visual que aparece CUANDO hay una entidad seleccionada -->
                                    <div v-if="selectedEntidad"
                                        class="border border-surface shadow-sm rounded-xl p-5 bg-surface-0 dark:bg-surface-900 relative mt-2 transition-all">

                                        <!-- Botones de Acción Agrupados en Top-Right (Editar y Cerrar) -->
                                        <div class="absolute top-3 right-3 flex items-center">
                                            <Button icon="pi pi-pencil" text rounded
                                                class="w-8 h-8 !p-0 text-cyan-500 hover:bg-cyan-50 dark:hover:bg-cyan-900/40"
                                                aria-label="Editar" />
                                            <Button icon="pi pi-times" text rounded
                                                class="w-8 h-8 !p-0 text-surface-400 hover:text-surface-800 dark:hover:text-surface-200"
                                                aria-label="Remover" @click="deseleccionarEntidad" />
                                        </div>

                                        <!-- Información Principal de la Entidad -->
                                        <div class="flex items-center gap-4 mb-4 pr-16 text-balance">
                                            <Avatar
                                                :icon="selectedEntidad.tipo === 'institucion' ? 'pi pi-building' : 'pi pi-user'"
                                                size="large" shape="circle" class="bg-primary text-primary-contrast" />
                                            <div class="flex flex-col">
                                                <span
                                                    class="font-bold text-lg text-surface-900 dark:text-surface-0 leading-tight">{{
                                                        selectedEntidad.nombre
                                                    }}</span>
                                                <span
                                                    class="text-sm text-primary font-medium flex items-center gap-1 mt-1"><i
                                                        class="pi pi-id-card text-xs"></i> {{ selectedEntidad.nit ||
                                                            'Sin CI/NIT' }}</span>
                                                <!-- Pastilla visual para indicar si fue creado en el Wizard (está en memoria) -->
                                                <span v-if="selectedEntidad.isNew"
                                                    class="text-[0.6rem] uppercase tracking-wider font-bold bg-orange-100 text-orange-600 px-2 py-0.5 rounded inline-block w-max mt-2">En
                                                    borrador</span>
                                            </div>
                                        </div>

                                        <!-- Footer de Tarjeta: Mostrar Email y Teléfono directamente si es Cliente (Persona) -->
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm text-surface-600 dark:text-surface-400 border-t border-surface pt-4"
                                            v-if="selectedEntidad.tipo === 'cliente'">
                                            <div
                                                class="flex items-center gap-2 font-medium bg-surface-100 dark:bg-surface-800 p-2.5 rounded-lg">
                                                <i class="pi pi-envelope text-primary"></i> <span class="truncate">{{
                                                    selectedEntidad.email || '—'
                                                }}</span>
                                            </div>
                                            <div
                                                class="flex items-center gap-2 font-medium bg-surface-100 dark:bg-surface-800 p-2.5 rounded-lg">
                                                <i class="pi pi-phone text-primary"></i> <span>{{
                                                    selectedEntidad.telefono || '—' }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Tutorial / Empty State cuando no hay seleccion -->
                                    <div v-else
                                        class="mt-2 p-5 rounded-xl bg-surface-50 dark:bg-surface-900 border border-surface-200 dark:border-surface-700">
                                        <div class="flex items-center gap-3 mb-3">
                                            <i class="pi pi-info-circle text-primary text-xl"></i>
                                            <span class="font-bold text-surface-900 dark:text-surface-0">¿Cómo
                                                seleccionar al Cliente?</span>
                                        </div>
                                        <ul
                                            class="text-sm text-surface-600 dark:text-surface-400 space-y-2 leading-relaxed ml-2">
                                            <li>&bull; <strong class="text-surface-800 dark:text-surface-200">Persona
                                                    Natural:</strong> Al seleccionar un
                                                "Cliente", este se asignará automáticamente como el contacto principal.
                                            </li>
                                            <li>&bull; <strong
                                                    class="text-surface-800 dark:text-surface-200">Instituciones:</strong>
                                                Al seleccionar una
                                                institución, aparecerá un segundo buscador para que asigne a la persona
                                                de contacto.</li>
                                            <li>&bull; Utilice el botón <strong class="text-primary">Nuevo</strong> si
                                                el cliente no se encuentra registrado
                                                en la base de datos.</li>
                                        </ul>
                                    </div>
                                </div>

                                <!-- BLOQUE 2: Información Persona de Contacto (Visible SOLO si es Institución) -->
                                <div class="flex flex-col gap-3 mt-4" v-if="selectedEntidad?.tipo === 'institucion'">
                                    <label
                                        class="font-bold text-surface-900 dark:text-surface-0 mb-1 lg:text-lg">Persona
                                        de Contacto</label>

                                    <!-- Buscador Secundario (Dependiente de Institución) -->
                                    <div class="flex gap-2 items-start w-full">
                                        <Select v-model="cotizacion.contacto_id" :options="personasOptions"
                                            optionValue="id" filter placeholder="Seleccione un contacto vinculado"
                                            class="flex-1" :disabled="!personasOptions.length">
                                            <template #value="slotProps">
                                                <div v-if="slotProps.value" class="flex items-center gap-2 font-medium">
                                                    <i class="pi pi-user text-primary hidden sm:inline"></i>
                                                    <span class="truncate">{{personasOptions.find(e => e.id ===
                                                        slotProps.value)?.nombre}}</span>
                                                </div>
                                                <span v-else-if="!personasOptions.length">No hay contactos
                                                    registrados</span>
                                                <span v-else>{{ slotProps.placeholder }}</span>
                                            </template>
                                            <template #option="slotProps">
                                                <div class="flex flex-col">
                                                    <span
                                                        class="text-[0.7rem] text-muted-color tracking-wider uppercase font-bold">{{
                                                            slotProps.option.nit || 'S/N' }}</span>
                                                    <span class="font-medium text-surface-900 dark:text-surface-0">{{
                                                        slotProps.option.nombre }}</span>
                                                </div>
                                            </template>
                                        </Select>
                                        <Button label="Nuevo" icon="pi pi-user-plus" severity="primary"
                                            @click="openModalNuevoContacto" />
                                    </div>

                                    <!-- Tarjeta Visual del Contacto Seleccionado -->
                                    <div v-if="selectedContacto"
                                        class="border border-surface shadow-sm rounded-xl p-5 bg-surface-0 dark:bg-surface-900 relative mt-2 transition-all">

                                        <!-- Botones de Acción Agrupados en Top-Right (Editar y Cerrar) -->
                                        <div class="absolute top-3 right-3 flex items-center">
                                            <Button icon="pi pi-pencil" text rounded
                                                class="w-8 h-8 !p-0 text-cyan-500 hover:bg-cyan-50 dark:hover:bg-cyan-900/40"
                                                aria-label="Editar" />
                                            <Button icon="pi pi-times" text rounded
                                                class="w-8 h-8 !p-0 text-surface-400 hover:text-surface-800 dark:hover:text-surface-200"
                                                aria-label="Remover" @click="deseleccionarContacto" />
                                        </div>

                                        <!-- Información Principal -->
                                        <div class="flex items-center gap-4 mb-4 pr-16 text-balance">
                                            <Avatar icon="pi pi-user" size="large" shape="circle"
                                                class="bg-primary text-primary-contrast" />
                                            <div class="flex flex-col">
                                                <span
                                                    class="font-bold text-lg text-surface-900 dark:text-surface-0 leading-tight">{{
                                                        selectedContacto.nombre }}</span>
                                                <span
                                                    class="text-sm text-primary font-medium flex items-center gap-1 mt-1"><i
                                                        class="pi pi-id-card text-xs"></i> {{ selectedContacto.nit ||
                                                            'Sin CI/NIT' }}</span>
                                                <span v-if="selectedContacto.isNew"
                                                    class="text-[0.6rem] uppercase tracking-wider font-bold bg-orange-100 text-orange-600 px-2 py-0.5 rounded inline-block w-max mt-2">En
                                                    borrador</span>
                                            </div>
                                        </div>

                                        <!-- Teléfonos / Emails del Contacto -->
                                        <div
                                            class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm text-surface-600 dark:text-surface-400 border-t border-surface pt-4">
                                            <div
                                                class="flex items-center gap-2 font-medium bg-surface-100 dark:bg-surface-800 p-2.5 rounded-lg">
                                                <i class="pi pi-envelope text-primary"></i> <span class="truncate">{{
                                                    selectedContacto.email || '—'
                                                }}</span>
                                            </div>
                                            <div
                                                class="flex items-center gap-2 font-medium bg-surface-100 dark:bg-surface-800 p-2.5 rounded-lg">
                                                <i class="pi pi-phone text-primary"></i> <span>{{
                                                    selectedContacto.telefono || '—' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- COLUMNA DERECHA: Fechas y Datos Adicionales del Evento -->
                            <div class="col-span-12 lg:col-span-5 flex flex-col gap-5">
                                <div
                                    class="font-bold text-surface-900 dark:text-surface-0 mb-1 lg:text-lg border-b lg:border-none border-surface pb-3 lg:pb-0">
                                    Detalles del Evento</div>

                                <div class="flex flex-col gap-2">
                                    <label for="tipo_evento" class="font-semibold text-sm">Tipo de Evento <span
                                            class="text-red-500">*</span></label>
                                    <Select id="tipo_evento" v-model="cotizacion.tipo_evento" :options="tiposEvento"
                                        optionLabel="descripcion" optionValue="id" placeholder="Selecciona el tipo"
                                        class="w-full" @change="onTipoEventoChange" />
                                </div>

                                <div class="flex flex-col gap-2">
                                    <label for="fecha_inicio" class="font-semibold text-sm">Fecha de Inicio <span
                                            class="text-red-500">*</span></label>
                                    <DatePicker id="fecha_inicio" v-model="cotizacion.fecha_inicio" showIcon fluid
                                        placeholder="dd/mm/aaaa" />
                                </div>

                                <div class="flex flex-col gap-2">
                                    <label for="fecha_fin" class="font-semibold text-sm">Fecha de Fin</label>
                                    <DatePicker id="fecha_fin" v-model="cotizacion.fecha_fin" showIcon fluid
                                        placeholder="dd/mm/aaaa" :minDate="cotizacion.fecha_inicio" />
                                </div>

                                <div class="flex flex-col gap-2">
                                    <label for="descripcion" class="font-semibold text-sm">Descripción del
                                        Evento</label>
                                    <Textarea id="descripcion" v-model="cotizacion.descripcion" rows="4" autoResize
                                        placeholder="Describe brevemente de qué tratará el evento..."
                                        class="bg-surface-50 dark:bg-surface-900 border-surface-200" />
                                </div>

                                <!-- Conteo Temporadas Display -->
                                <div v-if="conteoTemporadas.total > 0"
                                    class="mt-2 bg-primary-50 dark:bg-primary-900/20 border border-primary-200 dark:border-primary-800 p-4 rounded-xl">
                                    <h3
                                        class="text-sm font-bold text-primary-900 dark:text-primary-100 mb-3 flex items-center gap-2">
                                        <i class="pi pi-calendar"></i> Resumen de Temporadas ({{ conteoTemporadas.total
                                        }} días)
                                    </h3>
                                    <div class="flex gap-4">
                                        <div
                                            class="flex-1 bg-surface-0 dark:bg-surface-900 p-3 rounded-lg border border-surface shadow-sm">
                                            <div
                                                class="text-muted-color text-xs uppercase tracking-wider font-bold mb-1">
                                                Temporada Baja</div>
                                            <div class="text-2xl font-black text-surface-900 dark:text-surface-0">{{
                                                conteoTemporadas.baja.count }} <span
                                                    class="text-sm font-normal text-muted-color">días</span></div>
                                        </div>
                                        <div
                                            class="flex-1 bg-surface-0 dark:bg-surface-900 p-3 rounded-lg border border-surface shadow-sm relative overflow-hidden">
                                            <div
                                                class="absolute -right-4 -top-4 bg-orange-100 dark:bg-orange-900/40 w-16 h-16 rounded-full opacity-50">
                                            </div>
                                            <div
                                                class="text-muted-color text-xs uppercase tracking-wider font-bold mb-1 relative z-10">
                                                Temporada
                                                Alta</div>
                                            <div
                                                class="text-2xl font-black text-surface-900 dark:text-surface-0 relative z-10">
                                                {{
                                                    conteoTemporadas.alta.count }} <span
                                                    class="text-sm font-normal text-muted-color">días</span></div>
                                        </div>
                                    </div>
                                    <div class="mt-3 text-xs text-primary-700 dark:text-primary-300">
                                        <i class="pi pi-info-circle mr-1"></i> Los precios de los espacios varían según
                                        la temporada.
                                    </div>
                                </div>

                            </div>
                        </div>

                        <!-- Controles Inferiores (Pasar al Siguiente Paso) -->
                        <div class="flex pt-6 justify-end border-t border-surface mt-2">
                            <Button label="Seleccionar Espacios" icon="pi pi-arrow-right" iconPos="right"
                                :disabled="!isStep1Valid" @click="activateCallback(1)" class="w-full sm:w-auto" />
                        </div>
                    </div>
                </StepPanel>

                <!-- ============================================== -->
                <!-- PASO 2: ESPACIOS (Reservado para siguiente fase) -->
                <!-- ============================================== -->
                <StepPanel :value="1" v-slot="{ activateCallback }">
                    <div class="flex flex-col gap-5">
                        <!-- FILTROS -->
                        <div class="flex flex-wrap gap-4 items-center">
                            <div class="flex flex-wrap gap-2">
                                <Button label="Todos"
                                    :severity="activeTipoEspacio === 'todos' ? 'primary' : 'secondary'"
                                    :outlined="activeTipoEspacio !== 'todos'" class="rounded-full !px-4"
                                    @click="activeTipoEspacio = 'todos'" />
                                <Button v-for="tipo in tipoEspacios" :key="tipo.id" :label="tipo.nombre"
                                    :severity="activeTipoEspacio === tipo.id ? 'primary' : 'secondary'"
                                    :outlined="activeTipoEspacio !== tipo.id" class="rounded-full !px-4"
                                    @click="activeTipoEspacio = tipo.id" />
                            </div>

                            <div class="w-px h-8 bg-surface-200 dark:bg-surface-700 hidden sm:block"></div>

                            <div class="flex flex-wrap gap-2">
                                <Button v-for="bloque in bloquesConEspacios" :key="'bq_' + bloque.id"
                                    :outlined="activeBloque !== bloque.id"
                                    @click="activeBloque = activeBloque === bloque.id ? 'todos' : bloque.id"
                                    class="rounded-full !px-4 hover:shadow-md transition-all font-bold"
                                    :style="activeBloque === bloque.id ? { backgroundColor: bloque.color + '20', borderColor: bloque.color, color: bloque.color } : { color: bloque.color, borderColor: '#e2e8f0' }">
                                    <i class="pi pi-building text-sm mr-2" :style="{ color: bloque.color }"></i> {{
                                        bloque.descripcion
                                    }}
                                </Button>
                            </div>
                        </div>

                        <!-- GRID DE RESULTADOS -->
                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mt-2">
                            <div v-for="espacio in filteredEspacios" :key="espacio.id"
                                class="border rounded-xl overflow-hidden transition-all duration-300 relative flex flex-col h-full"
                                :class="{ 'border-primary-500 ring-2 ring-primary-500/20 shadow-md bg-primary-50/50 dark:bg-primary-900/10': isEspacioSelected(espacio.id), 'bg-surface-0 dark:bg-surface-900 border-surface-200 dark:border-surface-700 shadow-sm hover:shadow-md': !isEspacioSelected(espacio.id) }">

                                <!-- Header Tarjeta -->
                                <div class="px-5 pt-5 pb-3">
                                    <div class="flex justify-between items-start mb-2 gap-2">
                                        <h3
                                            class="font-bold text-lg text-surface-900 dark:text-surface-0 leading-tight flex-1">
                                            <i v-if="isEspacioSelected(espacio.id)"
                                                class="pi pi-check text-primary mr-1 text-sm"></i>
                                            {{ espacio.nombre }}
                                        </h3>
                                        <div class="flex items-center gap-1 shrink-0">
                                            <span
                                                class="text-[0.6rem] font-bold uppercase tracking-wider px-2 py-0.5 rounded border whitespace-nowrap"
                                                :style="{ backgroundColor: getBloqueColor(espacio.bloque_id) + '10', color: getBloqueColor(espacio.bloque_id), borderColor: getBloqueColor(espacio.bloque_id) + '40' }">
                                                {{ getTipoEspacioName(espacio.tipo_espacio_id) }}
                                            </span>
                                            <!-- Botón Cerrar -->
                                            <Button v-if="isEspacioSelected(espacio.id)" icon="pi pi-times" rounded text
                                                class="w-7 h-7 !p-0 text-red-500 hover:bg-red-100"
                                                @click="deselectEspacio(espacio.id)" />
                                        </div>
                                    </div>
                                </div>

                                <div class="px-5 pb-3">
                                    <div class="text-xs text-surface-500 dark:text-surface-400 mb-1">
                                        <span>{{ getTipoEspacioName(espacio.tipo_espacio_id) }}</span> &bull;
                                        <span>{{ espacio.area_m2 || '-' }}m²</span> &bull;
                                        <span>{{ espacio.aforo ? espacio.aforo + ' pax' : 'aforo libre' }}</span>
                                    </div>
                                    <div class="font-bold text-primary mb-2">
                                        Bs {{ getSpaceMinPrice(espacio.id).toLocaleString() }}/día
                                    </div>

                                    <p class="text-xs text-muted-color">Incluye: {{ espacio.descripcion }}.</p>
                                </div>

                                <!-- Footer Tarjeta -->
                                <div class="mt-auto px-5 pb-5 pt-3 border-t border-surface border-dashed">
                                    <!-- NO SELECC -->
                                    <Button v-if="!isEspacioSelected(espacio.id)" label="+ Agregar" severity="secondary"
                                        class="w-full text-sm !bg-surface-100 dark:!bg-surface-800 border-none !text-surface-700 dark:!text-surface-300 hover:!bg-surface-200 dark:hover:!bg-surface-700"
                                        @click="selectEspacio(espacio)" />

                                    <!-- SELECCIONADO -->
                                    <div v-else class="flex flex-col gap-3">
                                        <div class="flex gap-2">
                                            <div class="w-24">
                                                <label
                                                    class="text-[0.6rem] font-bold text-muted-color uppercase mb-1 block">Días
                                                    Uso</label>
                                                <InputNumber v-model="getEspacioData(espacio.id).dias" showButtons
                                                    :min="1"
                                                    :max="conteoTemporadas.total > 0 ? conteoTemporadas.total : 1"
                                                    inputClass="w-full p-2 text-center font-bold text-sm"
                                                    class="w-full !border-surface-200" decrementButtonIcon="pi pi-minus"
                                                    incrementButtonIcon="pi pi-plus"
                                                    @update:modelValue="recalcularDiasEspacio(espacio.id)" />
                                            </div>
                                            <div class="flex-1 overflow-hidden">
                                                <div class="flex items-center gap-1 mb-1">
                                                    <label
                                                        class="text-[0.6rem] font-bold text-muted-color uppercase block">Tarifa
                                                        Aplicada</label>
                                                    <!-- Botón toggle alta/baja (solo visible cuando hay ambas temporadas) -->
                                                    <Button v-if="conteoTemporadas.alta.count > 0 && conteoTemporadas.baja.count > 0"
                                                        v-tooltip.top="preferirBajaMap[espacio.id] ? 'Priorizando descuento de Baja. Click para Alta primero' : 'Priorizando descuento de Alta. Click para Baja primero'"
                                                        :icon="preferirBajaMap[espacio.id] ? 'pi pi-arrow-down' : 'pi pi-arrow-up'"
                                                        text rounded size="small"
                                                        :class="preferirBajaMap[espacio.id] ? '!text-blue-500 hover:bg-blue-50' : '!text-orange-500 hover:bg-orange-50'"
                                                        class="w-5 h-5 !p-0"
                                                        @click="togglePreferirBaja(espacio.id)" />
                                                </div>
                                                <div class="text-xs font-bold text-primary bg-primary-50 dark:bg-primary-900/20 px-2 py-2 rounded border border-primary-200 dark:border-primary-800 truncate">
                                                    <i class="pi pi-tag mr-1"></i>
                                                    {{ getTipoTarifaName() || 'Selecciona tipo de evento primero' }}
                                                </div>
                                            </div>
                                        </div>

                                        <div class="flex flex-col gap-1 mt-1 pt-2 border-t border-surface">
                                            <!-- Desglose por temporada -->
                                            <div v-if="conteoTemporadas.baja.count > 0"
                                                class="flex justify-between items-center text-[0.65rem] text-muted-color">
                                                <span>Temp. Baja: {{ (getEspacioData(espacio.id).precio_baja ||
                                                    0).toLocaleString() }}/d
                                                    &times; {{ getEspacioData(espacio.id)._dias_baja ?? conteoTemporadas.baja.count }}d</span>
                                                <span class="font-bold">Bs {{ ((getEspacioData(espacio.id).precio_baja
                                                    || 0) *
                                                    (getEspacioData(espacio.id)._dias_baja ?? conteoTemporadas.baja.count)).toLocaleString() }}</span>
                                            </div>
                                            <div v-if="conteoTemporadas.alta.count > 0"
                                                class="flex justify-between items-center text-[0.65rem] text-muted-color">
                                                <span>Temp. Alta: {{ (getEspacioData(espacio.id).precio_alta ||
                                                    0).toLocaleString() }}/d
                                                    &times; {{ getEspacioData(espacio.id)._dias_alta ?? conteoTemporadas.alta.count }}d</span>
                                                <span class="font-bold">Bs {{ ((getEspacioData(espacio.id).precio_alta
                                                    || 0) *
                                                    (getEspacioData(espacio.id)._dias_alta ?? conteoTemporadas.alta.count)).toLocaleString() }}</span>
                                            </div>

                                            <div
                                                class="flex justify-between items-center mt-1 pt-1 border-t border-surface/50">
                                                <span
                                                    class="text-xs font-bold text-surface-600 dark:text-surface-400">Total
                                                    Espacio</span>
                                                <span class="font-black text-sm text-primary">Bs {{
                                                    calculateSubtotal(espacio.id).toLocaleString() }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-if="filteredEspacios.length === 0"
                                class="col-span-full py-10 text-center text-muted-color border-2 border-dashed border-surface-200 dark:border-surface-700 rounded-xl">
                                <i class="pi pi-inbox text-4xl mb-3 text-surface-400"></i>
                                <p>No se encontraron espacios.</p>
                            </div>
                        </div>

                        <!-- BOTONERA INFERIOR FIJA -->
                        <div class="sticky bottom-4 bg-surface-0 dark:bg-surface-900 p-4 border border-surface shadow-[0_10px_40px_rgba(0,0,0,0.1)] mt-4 rounded-xl flex items-center justify-between flex-wrap gap-4 z-20 transition-all duration-500"
                            :class="{ 'translate-y-0 opacity-100': cotizacion.espacios.length > 0, 'translate-y-20 opacity-0 pointer-events-none hidden': cotizacion.espacios.length === 0 }">

                            <div
                                class="flex items-center gap-6 bg-surface-900 dark:bg-surface-800 text-surface-0 p-3 sm:px-6 w-full sm:w-auto rounded-xl flex-1 justify-between shadow-inner">
                                <div class="flex items-center gap-2 font-medium text-sm">
                                    <span class="bg-primary/20 text-primary-300 font-bold px-2 py-1 rounded">{{
                                        cotizacion.espacios.length }}</span>
                                    espacio{{ cotizacion.espacios.length !== 1 ? 's' : '' }} seleccionado{{
                                        cotizacion.espacios.length
                                            !== 1 ? 's' : '' }}
                                </div>
                                <div class="font-black text-2xl truncate text-primary-300">
                                    Bs {{ paso2Subtotal.toLocaleString() }}
                                </div>
                            </div>

                            <div class="flex gap-2 w-full sm:w-auto justify-between sm:justify-end">
                                <Button label="Volver" severity="secondary" @click="activateCallback(0)" />
                                <Button label="Servicios" icon="pi pi-arrow-right" iconPos="right" severity="primary"
                                    @click="activateCallback(2)" />
                            </div>
                        </div>

                        <div class="flex pt-4" v-if="cotizacion.espacios.length === 0">
                            <Button label="Volver" severity="secondary" icon="pi pi-arrow-left"
                                @click="activateCallback(0)" />
                            <Button label="Omitir Espacios" severity="secondary" text @click="activateCallback(2)"
                                class="ml-auto" />
                        </div>
                    </div>
                </StepPanel>

                <!-- ============================================== -->
                <!-- PASOS 3 Y 4: SERVICIOS Y RESUMEN               -->
                <!-- ============================================== -->
                <StepPanel :value="2" v-slot="{ activateCallback }">
                    <div class="flex flex-col gap-4">
                        <!-- Cabecera de servicios -->
                        <div class="flex items-center justify-between mb-2">
                            <div>
                                <h2 class="text-xl font-bold text-surface-900 dark:text-surface-0">Servicios Adicionales
                                </h2>
                                <p class="text-sm text-muted-color">Selecciona el mobiliario y equipos necesarios para
                                    tu evento.</p>
                            </div>
                            <div class="hidden md:block">
                                <span class="text-xs font-medium text-muted-color uppercase tracking-wider">Total
                                    Paso</span>
                                <div class="text-xl font-black text-primary">Bs {{ paso3Subtotal.toLocaleString() }}
                                </div>
                            </div>
                        </div>

                        <!-- Lista de servicios -->
                        <div class="flex flex-col gap-2">
                            <!-- Cabecera de columnas pro -->
                            <div
                                class="hidden md:flex items-center gap-4 px-4 py-1 text-[0.6rem] font-bold text-muted-color uppercase tracking-widest border-b border-transparent">
                                <div class="w-6"></div> <!-- Espacio checkbox -->
                                <div class="flex-1">Descripción del Servicio</div>
                                <div class="w-24 text-center">Cantidad</div>
                                <div class="w-24 text-center">Días</div>
                                <div class="w-32 text-right">Subtotal</div>
                            </div>

                            <div v-for="servicio in servicios" :key="servicio.id"
                                class="flex items-center gap-4 p-4 rounded-xl border transition-all duration-300"
                                :class="[
                                    isServicioSelected(servicio.id)
                                        ? 'bg-primary/5 border-primary shadow-sm'
                                        : 'bg-surface-0 dark:bg-surface-900 border-surface-200 dark:border-surface-700 hover:border-surface-400'
                                ]">
                                <!-- Checkbox de selección -->
                                <Checkbox :modelValue="isServicioSelected(servicio.id)" binary
                                    @update:modelValue="toggleServicio(servicio)" />

                                <!-- Información del servicio -->
                                <div class="flex-1 min-w-0 cursor-pointer" @click="toggleServicio(servicio)">
                                    <div
                                        class="font-bold text-surface-900 dark:text-surface-0 truncate text-sm sm:text-base">
                                        {{
                                            servicio.nombre }}</div>
                                    <div class="text-xs text-muted-color">
                                        Bs {{ Number(servicio.precio).toLocaleString() }}/{{ servicio.unidad }}
                                        <span v-if="servicio.nota" class="ml-1 opacity-70 italic">• {{ servicio.nota
                                            }}</span>
                                    </div>
                                </div>

                                <!-- Controles (Solo visibles si está seleccionado) -->
                                <div class="flex items-center gap-4 sm:gap-8" v-if="isServicioSelected(servicio.id)">
                                    <!-- Cantidad -->
                                    <div class="flex flex-col items-center w-32">
                                        <label
                                            class="md:hidden text-[0.6rem] font-bold text-muted-color uppercase mb-1">Cant.</label>
                                        <InputNumber v-model="getServicioData(servicio.id).cantidad" showButtons
                                            buttonLayout="horizontal" :min="1" class="w-full"
                                            inputClass="text-center font-bold w-full text-surface-900 dark:text-surface-0"
                                            incrementButtonIcon="pi pi-plus" decrementButtonIcon="pi pi-minus"
                                            incrementButtonClass="p-button-secondary"
                                            decrementButtonClass="p-button-secondary" />
                                    </div>

                                    <!-- Días (Solo si no es por evento) -->
                                    <div class="flex flex-col items-center w-32">
                                        <template v-if="!servicio.porEv">
                                            <label
                                                class="md:hidden text-[0.6rem] font-bold text-muted-color uppercase mb-1">Días</label>
                                            <InputNumber v-model="getServicioData(servicio.id).dias" showButtons
                                                buttonLayout="horizontal" :min="1" class="w-full"
                                                inputClass="text-center font-bold w-full text-surface-900 dark:text-surface-0"
                                                incrementButtonIcon="pi pi-plus" decrementButtonIcon="pi pi-minus"
                                                incrementButtonClass="p-button-secondary"
                                                decrementButtonClass="p-button-secondary" />
                                        </template>
                                        <div v-else class="text-[0.65rem] text-surface-400 italic">Por Evento</div>
                                    </div>

                                    <!-- Subtotal fila -->
                                    <div class="w-32 text-right">
                                        <label
                                            class="md:hidden text-[0.6rem] font-bold text-muted-color uppercase mb-1">Subtotal</label>
                                        <div class="text-sm sm:text-base font-black text-primary">
                                            Bs {{
                                                calculateServicioSubtotal(getServicioData(servicio.id)).toLocaleString() }}
                                        </div>
                                    </div>
                                </div>
                                <div v-else
                                    class="text-surface-300 dark:text-surface-600 italic text-xs hidden md:block w-[18rem] text-right pr-4">
                                    No seleccionado
                                </div>
                            </div>
                        </div>

                        <!-- Barra flotante totalizadora (Paso 3) -->
                        <div class="sticky bottom-4 bg-surface-900 dark:bg-surface-800 p-4 border border-surface-700 shadow-2xl mt-6 rounded-2xl flex items-center justify-between z-20 text-surface-0 transition-all"
                            :class="{ 'opacity-100 translate-y-0': cotizacion.servicios.length > 0, 'opacity-50 pointer-events-none': cotizacion.servicios.length === 0 }">
                            <div class="flex items-center gap-3">
                                <i class="pi pi-briefcase text-xl text-primary-300"></i>
                                <div>
                                    <div class="text-xs text-primary-200 font-medium">{{ cotizacion.servicios.length }}
                                        servicios seleccionados</div>
                                    <div class="text-2xl font-black text-white">Bs {{ paso3Subtotal.toLocaleString() }}
                                    </div>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <Button label="Volver" severity="secondary" text class="!text-surface-400"
                                    @click="activateCallback(1)" />
                                <Button label="Ver Resumen" severity="primary" class="!px-6 !py-3 !font-bold"
                                    @click="activateCallback(3)" />
                            </div>
                        </div>

                        <!-- Navegación básica si no hay nada seleccionado -->
                        <div class="flex pt-4 justify-between" v-if="cotizacion.servicios.length === 0">
                            <Button label="Volver" severity="secondary" icon="pi pi-arrow-left"
                                @click="activateCallback(1)" />
                            <Button label="Siguiente" variant="text" @click="activateCallback(3)" />
                        </div>
                    </div>
                </StepPanel>

                <StepPanel :value="3" v-slot="{ activateCallback }">
                    <div
                        class="flex flex-col bg-white dark:bg-surface-900 rounded-3xl shadow-2xl overflow-hidden border border-surface-200 dark:border-surface-800">
                        <!-- HEADER PREMIUM (Dark Green) - BALANCED & READABLE -->
                        <div class="bg-[#0b3d33] px-8 py-5 !text-white relative overflow-hidden">
                            <!-- Decoración sutil -->
                            <div class="absolute -right-24 -top-24 w-80 h-80 bg-white/5 rounded-full blur-3xl"></div>

                            <div class="flex flex-col md:flex-row justify-between items-center relative z-10">
                                <div class="flex flex-col">
                                    <span
                                        class="text-xs font-bold text-[#d4af37] uppercase tracking-wide leading-none !m-0 !p-0">Nueva
                                        Cotización</span>
                                    <h2 class="text-3xl font-bold !text-white !m-0 !p-0 leading-none">
                                        {{ entidades.find(e => e.id === cotizacion.entidad_id)?.nombre || 'Solicitud de Cotización' }}
                                    </h2>
                                    <div
                                        class="flex items-center gap-3 !text-white/70 text-sm font-medium leading-none !m-0 !p-0 mt-0.5">
                                        <div class="flex items-center gap-1">
                                            <i class="pi pi-user text-[0.8rem] opacity-50"></i>
                                            <span>{{ entidades.find(e => e.id === (cotizacion.contacto_id || cotizacion.entidad_id))?.nombre || 'Titular' }}</span>
                                        </div>
                                        <template v-if="entidades.find(e => e.id === (cotizacion.contacto_id || cotizacion.entidad_id))?.telefono">
                                            <div class="flex items-center gap-1">
                                                <i class="pi pi-whatsapp text-[0.8rem] opacity-50"></i>
                                                <span>+{{ entidades.find(e => e.id === (cotizacion.contacto_id || cotizacion.entidad_id))?.telefono_codigo || '591' }} {{ entidades.find(e => e.id === (cotizacion.contacto_id || cotizacion.entidad_id))?.telefono }}</span>
                                            </div>
                                        </template>
                                        <template v-if="entidades.find(e => e.id === (cotizacion.contacto_id || cotizacion.entidad_id))?.email">
                                            <div class="flex items-center gap-1">
                                                <i class="pi pi-envelope text-[0.8rem] opacity-50"></i>
                                                <span>{{ entidades.find(e => e.id === (cotizacion.contacto_id || cotizacion.entidad_id))?.email }}</span>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                <div class="text-right flex flex-col items-end">
                                    <span
                                        class="text-xs font-bold text-white/40 uppercase tracking-wide leading-none mb-1">{{ tiposEvento.find(e => e.id === cotizacion.tipo_evento)?.descripcion || 'Evento Social' }}</span>
                                    <div class="text-lg font-bold !text-[#d4af37] flex items-center gap-2 leading-none">
                                        <span>{{ cotizacion.fecha_inicio ? new
                                            Date(cotizacion.fecha_inicio).toLocaleDateString() : '-'
                                            }}</span>
                                        <span class="opacity-30">→</span>
                                        <span>{{ cotizacion.fecha_fin ? new
                                            Date(cotizacion.fecha_fin).toLocaleDateString() : '-'
                                            }}</span>
                                        <span class="ml-1 text-sm text-white/60 font-medium normal-case">({{
                                            conteoTemporadas.total }}
                                            días)</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- BODY: ESPACIOS -->
                        <div class="flex flex-col">
                            <div
                                class="bg-surface-50 dark:bg-surface-800/50 px-8 py-2 border-y border-surface-100 dark:border-surface-800">
                                <span
                                    class="text-xs font-bold text-surface-600 dark:text-surface-400 uppercase tracking-wide">Espacios
                                    ({{ cotizacion.espacios.length }})</span>
                            </div>

                            <div class="divide-y divide-surface-100 dark:divide-surface-800">
                                <div v-for="espacio in cotizacion.espacios" :key="espacio.espacio_id"
                                    class="px-8 py-1.5 hover:bg-surface-50 dark:hover:bg-surface-800/30 transition-colors">
                                    <div class="flex justify-between items-center text-surface-900 dark:text-surface-0">
                                        <div class="flex flex-col">
                                            <h4 class="font-bold text-base leading-none m-0 p-0">
                                                {{ espacios.find(e => e.id === espacio.espacio_id)?.nombre || 'Espacio Seleccionado' }}
                                            </h4>
                                            <div class="text-xs font-medium text-surface-500 dark:text-surface-400 mt-0.5 flex items-center gap-1.5 leading-none">
                                                <span>{{ getTipoTarifaName() }}</span>
                                                <span class="text-surface-300">·</span>
                                                <span>{{ espacio.dias }} día{{ espacio.dias !== 1 ? 's' : '' }}</span>
                                                <span class="text-surface-300">·</span>
                                                <span class="uppercase">{{ getFullBloqueLabel(espacio.espacio_id) }}</span>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-[0.65rem] text-surface-400 font-medium uppercase tracking-tight">
                                                Bs {{ espacio.precio_baja ? espacio.precio_baja.toLocaleString() : '0' }}/d baja
                                                <span v-if="espacio.precio_alta"> &middot; Bs {{ espacio.precio_alta.toLocaleString() }}/d alta</span>
                                            </div>
                                            <div class="text-xl font-black">Bs {{ calculateSubtotal(espacio.espacio_id).toLocaleString() }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div v-if="cotizacion.espacios.length === 0"
                                    class="p-6 text-center italic text-surface-400 text-xs">
                                    No se han seleccionado espacios.
                                </div>
                            </div>
                        </div>

                        <!-- BODY: SERVICIOS -->
                        <div class="flex flex-col">
                            <div
                                class="bg-surface-50 dark:bg-surface-800/50 px-8 py-2 border-y border-surface-100 dark:border-surface-800">
                                <span
                                    class="text-xs font-bold text-surface-600 dark:text-surface-400 uppercase tracking-wide">Servicios
                                    Adicionales ({{ cotizacion.servicios.length }})</span>
                            </div>

                            <div class="divide-y divide-surface-100 dark:divide-surface-800">
                                <div v-for="servicio in cotizacion.servicios" :key="servicio.servicio_id"
                                    class="px-8 py-2 hover:bg-surface-50 dark:hover:bg-surface-800/30 transition-colors">
                                    <div class="flex justify-between items-center text-surface-900 dark:text-surface-0">
                                        <div class="flex flex-col">
                                            <h4
                                                class="font-bold text-base leading-none m-0 p-0 text-surface-800 dark:text-surface-100">
                                                {{ servicio.nombre }}</h4>
                                            <div
                                                class="text-[0.7rem] font-medium text-surface-500 dark:text-surface-400 mt-0.5 leading-none">
                                                {{ servicio.cantidad }} {{ servicio.unidad }} <span
                                                    v-if="!servicio.porEv">&times; {{
                                                    servicio.dias || conteoTemporadas.total }} días</span>
                                                <span v-else>(por evento)</span>
                                            </div>
                                        </div>
                                        <div class="text-xl font-black text-surface-900 dark:text-surface-0">
                                            Bs {{ calculateServicioSubtotal(servicio).toLocaleString() }}
                                        </div>
                                    </div>
                                </div>
                                <div v-if="cotizacion.servicios.length === 0"
                                    class="p-6 text-center italic text-surface-400 text-xs">
                                    No se han seleccionado servicios adicionales.
                                </div>
                            </div>
                        </div>

                        <!-- TOTAL FOOTER -->
                        <div
                            class="mt-auto border-t-2 border-surface-900 dark:border-[#2a2a2a] px-6 py-4 flex flex-col gap-2 bg-surface-50/50 dark:bg-surface-800/20">
                            <div
                                class="flex justify-end gap-8 text-xs font-bold text-surface-600 dark:text-surface-400 uppercase tracking-tight">
                                <span>Subtotal Espacios: Bs {{ paso2Subtotal.toLocaleString() }}</span>
                                <span>Subtotal Servicios: Bs {{ paso3Subtotal.toLocaleString() }}</span>
                            </div>

                            <div class="flex justify-between items-center">
                                <!-- Nota legal -->
                                <div class="px-8 mt-2">
                                    <p class="text-sm text-surface-400 dark:text-surface-500 italic pb-4">
                                        * Todos los precios incluyen impuestos de ley (IT/IVA).
                                    </p>
                                </div>
                                <div class="flex items-baseline gap-4">
                                    <span
                                        class="text-base font-black text-surface-900 dark:text-surface-0 uppercase tracking-tighter">Total
                                        Cotización</span>
                                    <span class="text-4xl font-black text-[#e11d48]">Bs {{ (paso2Subtotal +
                                        paso3Subtotal).toLocaleString() }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- BARRA DE ACCIONES DE DISEÑO -->
                        <div
                            class="flex justify-between items-center px-6 py-3 bg-surface-100 dark:bg-surface-800/80 border-t border-surface-200 dark:border-surface-700">
                            <Button label="Editar" icon="pi pi-pencil" severity="secondary" @click="activateCallback(2)"
                                outlined class="!py-1.5 !px-4 text-xs" />

                            <div class="flex gap-2">
                                <Button label="Vista Previa" icon="pi pi-eye" severity="secondary"
                                    @click="generarPreviewPDF" class="!font-bold shadow-sm !py-1.5 !px-4 text-xs" />
                                <Button label="Imprimir" icon="pi pi-print" severity="info"
                                    class="bg-[#0b3d33] !border-none !font-bold shadow-md !py-1.5 !px-4 text-xs"
                                    @click="imprimirCotizacion" />
                                <Button label="Guardar Cotización" icon="pi pi-save" severity="danger"
                                    class="!bg-[#e11d48] !border-none !px-6 !py-2 !font-black shadow-lg hover:!scale-105 transition-transform text-sm"
                                    @click="guardarCotizacionCompleta" :loading="isSubmitting" />
                            </div>
                        </div>
                    </div>

                    <!-- RESUMEN FINAL FLOTANTE (Referencia Imagen 2) -->
                    <div class="flex justify-end mt-4 mr-4 animate-fadein">
                        <div class="text-right flex flex-col gap-1">
                            <span
                                class="text-[0.65rem] font-black text-surface-500 uppercase tracking-[0.2em] mb-1">Resumen</span>
                            <div class="text-xs text-surface-700 dark:text-surface-300">
                                <span class="font-bold">Espacios:</span>
                                {{cotizacion.espacios.map(esp => espacios.find(e => e.id ===
                                    esp.espacio_id)?.nombre).join(', ') ||
                                'Ninguno' }}
                            </div>
                            <div class="text-xs text-surface-700 dark:text-surface-300">
                                <span class="font-bold">Servicios:</span>
                                {{cotizacion.servicios.map(s => s.nombre).join(', ') || 'Ninguno'}}
                            </div>
                            <div class="text-xs text-surface-700 dark:text-surface-300">
                                <span class="font-bold">Días:</span> {{ conteoTemporadas.total }}
                            </div>
                        </div>
                    </div>
                </StepPanel>
            </StepPanels>
        </Stepper>

        <!-- ============================================================== -->
        <!-- MODAL: CREACIÓN TEMPORAL DE ENTIDADES O CONTACTOS              -->
        <!-- Se abre cuando se hace click en el botón "Nuevo" de búsquedas. -->
        <!-- ============================================================== -->
        <Dialog v-model:visible="showModalCliente" :header="isCreatingContacto ? 'Nuevo Contacto' : 'Nueva Entidad'"
            :style="{ width: '32rem' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }" modal>

            <!-- Cabecera del modal enriquecida -->
            <template #header>
                <div class="flex flex-col">
                    <span class="text-xl font-bold text-surface-900 dark:text-surface-0" v-if="isCreatingContacto">Nuevo
                        Contacto</span>
                    <span class="text-xl font-bold text-surface-900 dark:text-surface-0"
                        v-else-if="nuevoRegistro.tipo === 'institucion'">Nueva Institución</span>
                    <span class="text-xl font-bold text-surface-900 dark:text-surface-0" v-else>Nuevo Cliente</span>

                    <span class="text-muted-color text-sm mt-1" v-if="isCreatingContacto">Registrar una persona
                        vinculada a la institución.</span>
                    <span class="text-muted-color text-sm mt-1" v-else>Crear un nuevo registro local en la
                        cotización.</span>
                </div>
            </template>

            <div class="flex flex-col gap-5 mt-4">
                <!-- Elección del Tipo de Entidad (Visible solo al crear entidad principal) -->
                <div class="flex flex-col gap-3" v-if="!isCreatingContacto">
                    <label class="font-bold text-sm text-surface-900 dark:text-surface-0">Tipo de Entidad</label>
                    <div class="flex gap-6">
                        <div class="flex items-center">
                            <RadioButton v-model="nuevoRegistro.tipo" inputId="tipo_cliente" name="tipo"
                                value="cliente" />
                            <label for="tipo_cliente" class="ml-2 font-medium cursor-pointer">Cliente (Persona)</label>
                        </div>
                        <div class="flex items-center">
                            <RadioButton v-model="nuevoRegistro.tipo" inputId="tipo_institucion" name="tipo"
                                value="institucion" />
                            <label for="tipo_institucion" class="ml-2 font-medium cursor-pointer">Institución /
                                Empresa</label>
                        </div>
                    </div>
                </div>

                <!-- Campos de Formulario -->
                <div class="flex flex-col gap-2">
                    <label for="new_nit" class="font-bold text-sm text-surface-900 dark:text-surface-0">CI / NIT</label>
                    <InputText id="new_nit" v-model="nuevoRegistro.nit" placeholder="Documento de identidad o NIT"
                        class="p-inputtext-sm" />
                </div>
                <div class="flex flex-col gap-2">
                    <label for="new_nombre" class="font-bold text-sm text-surface-900 dark:text-surface-0">Nombre o
                        Razón Social
                        <span class="text-red-500">*</span></label>
                    <InputText id="new_nombre" v-model="nuevoRegistro.nombre" placeholder="Nombre completo o Empresa"
                        class="p-inputtext-sm" />
                </div>
                
                <!-- Grid de contacto: Celular + Email (fila 1), Teléfono fijo (fila 2, solo institución) -->
                <div class="grid grid-cols-2 gap-x-4 gap-y-4 mt-2">

                    <!-- CELULAR con selector de país -->
                    <div class="flex flex-col gap-1 col-span-2 sm:col-span-1">
                        <label class="font-bold text-xs text-surface-500 uppercase tracking-wider">
                            Celular <span class="text-surface-400 font-normal normal-case tracking-normal text-[0.7rem]">(WhatsApp)</span>
                        </label>
                        <div class="flex items-stretch w-full">
                            <!-- Bandera + código — ancho justo para mostrar solo la bandera y +XXX -->
                            <Select
                                v-model="nuevoRegistro.telefono_codigo"
                                :options="countryCodes"
                                optionValue="code"
                                optionLabel="label"
                                filter
                                filterPlaceholder="Buscar país..."
                                class="w-[6.5rem] shrink-0"
                                :pt="{ root: { class: 'rounded-r-none border-r-0 hover:z-10 focus:z-10' } }">
                                <template #value="{ value }">
                                    <span v-if="value" class="font-semibold text-sm tracking-tight flex items-center h-full">
                                        {{ countryCodes.find(c => c.code === value)?.flag }}&thinsp;+{{ value }}
                                    </span>
                                </template>
                                <template #option="{ option }">
                                    <span class="text-sm">{{ option.label }}</span>
                                </template>
                            </Select>
                            <InputText
                                v-model="nuevoRegistro.telefono"
                                placeholder="70000000"
                                class="flex-1 min-w-0 w-full p-inputtext-sm !rounded-l-none focus:z-10 relative"
                                type="tel" inputmode="numeric" />
                        </div>
                        <div v-if="nuevoRegistro.telefono" class="flex items-center gap-1 text-[0.68rem] text-green-600 font-medium leading-none mt-0.5">
                            <i class="pi pi-whatsapp text-[10px]"></i>
                            wa.me/{{ nuevoRegistro.telefono_codigo }}{{ nuevoRegistro.telefono }}
                        </div>
                    </div>

                    <!-- EMAIL -->
                    <div class="flex flex-col gap-1 col-span-2 sm:col-span-1">
                        <label for="new_email" class="font-bold text-xs text-surface-500 uppercase tracking-wider">Email</label>
                        <InputText id="new_email" v-model="nuevoRegistro.email" placeholder="correo@ejemplo.com"
                            class="p-inputtext-sm w-full" />
                    </div>

                    <!-- TELÉFONO FIJO — solo visible para instituciones -->
                    <div v-if="nuevoRegistro.tipo === 'institucion' || isCreatingContacto === false && nuevoRegistro.tipo === 'institucion'"
                         class="flex flex-col gap-1 col-span-2">
                        <label for="new_telefono_fijo" class="font-bold text-xs text-surface-500 uppercase tracking-wider">
                            Teléfono de Oficina
                            <span class="text-surface-400 font-normal normal-case tracking-normal text-[0.7rem]">(fijo / central)</span>
                        </label>
                        <div class="flex items-stretch w-full">
                            <span class="text-surface-500 text-sm font-medium px-3 flex items-center border border-r-0 border-surface-300 dark:border-surface-700 rounded-l-md bg-surface-50 dark:bg-surface-800 whitespace-nowrap shrink-0">
                                <i class="pi pi-phone text-xs mr-1"></i>Fijo
                            </span>
                            <InputText id="new_telefono_fijo" v-model="nuevoRegistro.telefono_fijo"
                                placeholder="2-XXXXXXX" class="flex-1 min-w-0 w-full p-inputtext-sm !rounded-l-none focus:z-10 relative" type="tel" inputmode="numeric" />
                        </div>
                    </div>

                </div>
            </div>

            <template #footer>
                <div class="flex gap-3 justify-end pt-5 border-t border-surface mt-4">
                    <Button label="Cancelar" severity="secondary" outlined @click="showModalCliente = false" />
                    <Button label="Guardar Borrador" severity="primary" @click="guardarNuevoRegistro"
                        :disabled="!nuevoRegistro.nombre" />
                </div>
            </template>
        </Dialog>

        <Dialog v-model:visible="showPdfPreview" modal :style="{ width: '95vw', maxWidth: '1400px' }"
            :breakpoints="{ '960px': '98vw' }" class="p-0 overflow-hidden shadow-2xl" :closable="false"
            :pt="{ mask: { style: 'backdrop-filter: blur(4px)' } }">
            <template #header>
                <div
                    class="flex items-center justify-between w-full bg-[#06352c] text-white p-4 pl-6 border-b border-[#0d4a3e]">
                    <div class="flex items-center gap-3">
                        <div class="bg-yellow-400 p-1.5 rounded-lg flex items-center justify-center">
                            <i class="pi pi-file-pdf text-xl text-[#06352c]"></i>
                        </div>
                        <div class="flex flex-col">
                            <span class="font-bold text-lg leading-none">Vista Previa del Documento</span>
                            <span class="text-[0.65rem] text-white/60 uppercase tracking-widest mt-1">Previsualización
                                en tiempo real</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <Button label="Imprimir / PDF" icon="pi pi-print" severity="secondary" @click="printPdfPreview"
                            class="!bg-white !text-[#06352c] !border-none !py-2 !px-5 !font-bold shadow-lg hover:!bg-surface-100" />
                        <Button label="Guardar PDF" icon="pi pi-save" severity="success" @click="downloadPdfPreview"
                            class="!py-2 !px-5 !font-bold shadow-lg" />
                        <div class="w-px h-8 bg-white/20 mx-1"></div>
                        <Button icon="pi pi-times" text rounded @click="showPdfPreview = false"
                            class="!text-white hover:bg-white/10 w-10 h-10" />
                    </div>
                </div>
            </template>

            <div class="w-full h-[85vh] bg-surface-200 dark:bg-surface-950 overflow-hidden flex flex-col">
                <div class="flex-1 overflow-auto p-4 md:p-8 flex justify-center bg-surface-200 dark:bg-surface-950">
                    <iframe v-if="pdfUrl" :src="pdfUrl" data-pdf-preview
                        class="w-full max-w-[1000px] h-full border-none shadow-2xl bg-white rounded-sm"></iframe>
                    <div v-else class="flex-1 flex flex-col items-center justify-center gap-4 py-20">
                        <i class="pi pi-spin pi-spinner text-5xl text-primary"></i>
                        <p class="text-surface-600 dark:text-surface-400 font-medium text-xl">Generando documento
                            oficial...</p>
                    </div>
                </div>
                <!-- Tip Footer -->
                <div class="p-3 bg-[#f8fafc] dark:bg-surface-900 border-t border-surface text-center">
                    <span class="text-xs text-surface-600 dark:text-surface-400 flex items-center justify-center gap-2">
                        <i class="pi pi-info-circle text-primary"></i>
                        <span>Usa <strong class="text-[#06352c] dark:text-primary">Imprimir / PDF</strong> para obtener
                            una
                            copia física o digital perfecta del documento.</span>
                    </span>
                </div>
            </div>
        </Dialog>

        <!-- Loading / Success Overlay -->
        <transition name="fade">
            <div v-if="isReloading" class="fixed inset-0 z-[2000] flex items-center justify-center bg-white/80 dark:bg-surface-900/80 backdrop-blur-md">
                <div class="flex flex-col items-center bg-white dark:bg-surface-950 p-8 rounded-2xl shadow-2xl border border-surface-200 dark:border-surface-800 min-w-[320px] max-w-[90%] text-center transform transition-all">
                    <div class="w-20 h-20 bg-green-100 dark:bg-green-900/40 rounded-full flex items-center justify-center mb-5 animate-bounce">
                        <i class="pi pi-check text-green-500 dark:text-green-400" style="font-size: 3rem"></i>
                    </div>
                    <h3 class="text-xl font-bold text-surface-900 dark:text-surface-0 mb-2">¡Guardado Exitoso!</h3>
                    <p class="text-surface-500 dark:text-surface-400 text-sm">Todo está listo. Recargando la vista...</p>
                </div>
            </div>
        </transition>
    </div>
</template>
<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import { useToast } from 'primevue/usetoast';
import clienteServices from '@/services/cliente.services';
import eventoServices from '@/services/evento.services';
import cotizacionServices from '@/services/cotizacion.services';
import temporadaServices from '@/services/temporada.services';
import { useCountryCodes } from '@/composables/useCountryCodes';

import tarifaServices from '@/services/tarifa.services';
import espacioServices from '@/services/espacio.services';
import bloqueServices from '@/services/bloque.services';
import tipoEspacioServices from '@/services/tipoespacio.services';
import servicioServices from '@/services/servicio.services';

const { countryCodes, defaultCode, buildWhatsAppUrl } = useCountryCodes();

// ============================================================================
// ESTADO GENERAL DEL WIZARD
// ============================================================================
// activeStep  maneja qué panel del Stepper está visible (0=Datos, 1=Espacios, etc.)
const activeStep = ref(0);
const showPdfPreview = ref(false);
const pdfUrl = ref(null);

const printPdfPreview = () => {
    const iframe = document.querySelector('iframe[data-pdf-preview]');
    if (iframe) {
        iframe.contentWindow.print();
    }
};

const downloadPdfPreview = () => {
    if (pdfUrl.value) {
        const link = document.createElement('a');
        link.href = pdfUrl.value;
        link.download = `Cotizacion_${new Date().toISOString().split('T')[0]}.pdf`;
        link.click();
    }
};

// ============================================================================
// DATOS DE BASE DE DATOS
// ============================================================================
// Unimos clientes e instituciones en un solo arreglo reactivo para alimentar el buscador principal.
const entidades = ref([]);

const tiposEvento = ref([]);
const temporadas = ref([]);
const bloques = ref([]);
const tipoEspacios = ref([]);
const espacios = ref([]);
const tarifas = ref([]);

const servicios = ref([]);

onMounted(async () => {

    listarClientes()
    listarEventos()
    listarTemporadas()
    listarTipoEspacios()
    listarBloques()
    listarEspacios()
    listarTarifas()
    listarServicios()
});

async function listarClientes() {
    try {
        const { data } = await clienteServices.index();
        // Mapeamos los datos de la API al formato esperado por la UI
        entidades.value = data.map(c => ({
            id: c.id,
            nombre: c.nombre,
            nit: c.ci_nit,
            tipo: c.tipo_cliente_id === 1 ? 'institucion' : 'cliente',
            telefono: c.telefono,
            email: c.correo,
            cliente_id: c.cliente_id // Referencia para saber a qué institución pertenece si es contacto
        }));
    } catch (e) {
        console.error("Error al cargar las entidades", e);
    }
}

async function listarEventos() {
    try {
        const { data } = await eventoServices.listar();
        tiposEvento.value = data?.data ? data.data : data;
    } catch (e) {
        console.error("Error al cargar los eventos", e);
    }
}

async function listarTemporadas() {
    try {
        const { data } = await temporadaServices.listar();
        temporadas.value = data;
        console.log(data);
    } catch (e) {
        console.error("Error al cargar las temporadas", e);
    }
}

async function listarTipoEspacios() {
    try {
        const { data } = await tipoEspacioServices.listar();
        tipoEspacios.value = data;
    } catch (e) { console.error("Error al cargar tipo_espacios", e); }
}

async function listarBloques() {
    try {
        const { data } = await bloqueServices.listar();
        bloques.value = data;
    } catch (e) { console.error("Error al cargar bloques", e); }
}

async function listarEspacios() {
    try {
        const { data } = await espacioServices.listar();
        espacios.value = data;
    } catch (e) { console.error("Error al cargar espacios", e); }
}

async function listarTarifas() {
    try {
        const { data } = await tarifaServices.listar();
        // Soporte para respuestas directas o envueltas en .data (Laravel Resources/Pagination)
        tarifas.value = data?.data ? data.data : data;
    } catch (e) { console.error("Error al cargar tarifas globales", e); }
}

async function listarServicios() {
    try {
        const { data } = await servicioServices.listar();
        servicios.value = data;
    } catch (e) { console.error("Error al cargar servicios", e); }
}

// Diccionario de caché local para tarifas cargadas al vuelo por espacio
const tarifasPorEspacio = ref({});

// Arreglo dinámico que guardará las personas (contactos) asociadas a una institución seleccionada.
const personasOptions = ref([]);

// ============================================================================
// MODELO DE LA COTIZACIÓN ACTUAL
// ============================================================================
// Este objeto guarda los datos que se están llenando en el formulario actual.
const cotizacion = ref({
    entidad_id: null,   // ID del Cliente o Institución principal
    contacto_id: null,  // ID de la persona de contacto (puede ser el mismo cliente)
    tipo_evento: null,  // Objeto con el tipo de evento
    fecha_inicio: null, // Fecha seleccionada
    fecha_fin: null,
    descripcion: '',
    espacios: [],        // Arreglo con los espacios agregados y sus configuraciones
    servicios: []
});


// ============================================================================
// PROPIEDADES COMPUTADAS & VALIDACIONES
// ============================================================================
// Extrae el objeto completo de la entidad actualmente seleccionada de la lista global
const selectedEntidad = computed(() => entidades.value.find(e => e.id === cotizacion.value.entidad_id));

// Extrae el objeto completo del contacto actualmente seleccionado
const selectedContacto = computed(() => entidades.value.find(e => e.id === cotizacion.value.contacto_id));

// Valida si el Paso 1 (Datos del Evento) está listo para avanzar.
// Se requiere cliente, contacto, tipo de evento y fecha de inicio.
const isStep1Valid = computed(() => {
    return cotizacion.value.entidad_id &&
        cotizacion.value.contacto_id &&
        cotizacion.value.tipo_evento &&
        cotizacion.value.fecha_inicio;
});

// Calcula cuántos días caen en temporada alta y baja según las fechas seleccionadas
const conteoTemporadas = computed(() => {
    const inicio = cotizacion.value.fecha_inicio;
    let fin = cotizacion.value.fecha_fin;

    // Si no hay fecha de inicio, retorna todo en 0
    if (!inicio) return { alta: { count: 0, id: null }, baja: { count: 0, id: null }, total: 0 };

    // Si no hay fecha final seleccionada aún, usamos la misma que el inicio (evento de 1 día)
    if (!fin) fin = inicio;

    const count = {
        alta: { count: 0, id: null },
        baja: { count: 0, id: null },
        total: 0
    };

    let currentDate = new Date(inicio);
    const endDate = new Date(fin);
    currentDate.setHours(0, 0, 0, 0);
    endDate.setHours(0, 0, 0, 0);

    if (endDate < currentDate) return count;

    while (currentDate <= endDate) {
        count.total++;
        const currentMonth = currentDate.getMonth() + 1;

        const temporada = temporadas.value.find(t =>
            currentMonth >= t.mes_inicio && currentMonth <= t.mes_fin
        );

        if (temporada) {
            if (temporada.descripcion.toLowerCase().includes('alta')) {
                count.alta.count++;
                count.alta.id = temporada.id;
            } else {
                count.baja.count++;
                count.baja.id = temporada.id;
            }
        }
        currentDate.setDate(currentDate.getDate() + 1);
    }
    return count;
});

// ============================================================================
// COMPUTADAS: SELECCIÓN DE ESPACIOS (PASO 2)
// ============================================================================
const activeTipoEspacio = ref('todos'); // 'todos' o ID del tipo
const activeBloque = ref('todos');      // 'todos' o ID del bloque

// Pre-calcula la lista de espacios aplicando los filtros de pestañas.
const filteredEspacios = computed(() => {
    let result = espacios.value;

    // Filtro por Tipo de Espacio   
    if (activeTipoEspacio.value !== 'todos') {
        result = result.filter(e => e.tipo_espacio_id === activeTipoEspacio.value);
    }

    // Filtro por Bloque
    if (activeBloque.value !== 'todos') {
        result = result.filter(e => e.bloque_id === activeBloque.value);
    }

    return result;
});

// Solo muestra bloques que tienen al menos un espacio asignado
const bloquesConEspacios = computed(() => {
    const bloqueIdsUsados = new Set(espacios.value.map(e => e.bloque_id).filter(Boolean));
    return bloques.value.filter(b => bloqueIdsUsados.has(b.id));
});

// Helper para UI
const getTipoTarifaName = () => {
    // Retorna el nombre del tipo de evento seleccionado en lugar del hardcoded 'Tarifa del Evento'
    const eventoId = cotizacion.value.tipo_evento;
    if (!eventoId) return 'Tarifa del Evento';
    const ev = tiposEvento.value.find(e => e.id === eventoId);
    return ev ? ev.descripcion : 'Tarifa del Evento';
};

const getTarifaDisplayName = (espacioId, valueId) => {
    if (!valueId) return 'Seleccione';
    const t = getTarifasOptions(espacioId).find(tr => tr.id === valueId);
    if (!t) return 'Tarifa Seleccionada';
    // Mostramos el nombre del evento + temporada para identificar la tarifa
    const eventoNombre = t.evento?.descripcion || t.evento_nombre || '';
    const tempNombre = t.temporada?.descripcion || t.temporada_nombre || '';
    return [eventoNombre, tempNombre].filter(Boolean).join(' • ') || ('T ' + t.id);
};

const getOptionTarifaDisplayName = (option) => {
    if (!option) return '';
    const eventoNombre = option.evento?.descripcion || option.evento_nombre || '';
    const tempNombre = option.temporada?.descripcion || option.temporada_nombre || '';
    return [eventoNombre, tempNombre].filter(Boolean).join(' • ') || ('T ' + option.id);
};

const isEspacioSelected = (espacioId) => {
    return cotizacion.value.espacios.some(e => e.espacio_id === espacioId);
};

const getEspacioData = (espacioId) => {
    return cotizacion.value.espacios.find(e => Number(e.espacio_id) === Number(espacioId)) || {};
};

const getTarifasOptions = (espacioId) => {
    // Filtrar solo las tarifas que corresponden al evento seleccionado
    const eventoId = cotizacion.value.tipo_evento;
    const tarifasDelEspacio = tarifasPorEspacio.value[espacioId] || [];
    if (eventoId) {
        return tarifasDelEspacio.filter(t => Number(t.evento_id) === Number(eventoId));
    }
    return tarifasDelEspacio;
};

// Vigilamos cambios en las temporadas para refrescar precios Y sincronizar días
watch(() => conteoTemporadas.value, (newVal) => {
    const eventoId = cotizacion.value.tipo_evento;
    if (!eventoId) return;
    cotizacion.value.espacios.forEach(sp => {
        // Sincronizar días al total de días recalculado
        sp.dias = newVal.total > 0 ? newVal.total : 1;
        autoSeleccionarTarifa(sp.espacio_id, eventoId);
    });
}, { deep: true });

// Vigilamos cambios en el tipo de evento para refrescar tarifas de todos los espacios seleccionados
watch(() => cotizacion.value.tipo_evento, (nuevoEventoId) => {
    if (!nuevoEventoId) return;
    cotizacion.value.espacios.forEach(sp => {
        sp.evento_id = nuevoEventoId;
        autoSeleccionarTarifa(sp.espacio_id, nuevoEventoId);
    });
});

// Estado por espacio: si el toggle está en 'preferirBaja', los días extra se descuentan de baja primero
const preferirBajaMap = ref({});

const togglePreferirBaja = (espacioId) => {
    preferirBajaMap.value[espacioId] = !preferirBajaMap.value[espacioId];
    recalcularDiasEspacio(espacioId);
};

/**
 * Recalcula la distribución de días entre alta y baja al cambiar manualmente el campo días.
 * Si preferirBaja=true: reduce primero de los días bajos, si no alcanza, reduce de los altos.
 * Si preferirBaja=false: reduce primero de los días altos, luego de los bajos.
 */
const recalcularDiasEspacio = (espacioId) => {
    const sp = cotizacion.value.espacios.find(e => Number(e.espacio_id) === Number(espacioId));
    if (!sp) return;

    const totalMaximo = conteoTemporadas.value.total;
    const diasUsados = Math.min(sp.dias || 1, totalMaximo);

    const maxBaja = conteoTemporadas.value.baja.count;
    const maxAlta = conteoTemporadas.value.alta.count;
    const preferBaja = !!preferirBajaMap.value[espacioId];

    let asignadoBaja, asignadoAlta;

    if (preferBaja) {
        // Descuenta días primero de los días bajos
        asignadoBaja = Math.min(diasUsados, maxBaja);
        asignadoAlta = Math.min(diasUsados - asignadoBaja, maxAlta);
    } else {
        // Descuenta días primero de los días altos (default original)
        asignadoAlta = Math.min(diasUsados, maxAlta);
        asignadoBaja = Math.min(diasUsados - asignadoAlta, maxBaja);
    }

    sp._dias_baja = asignadoBaja;
    sp._dias_alta = asignadoAlta;
};

const calculateSubtotal = (espacioId) => {
    const sp = getEspacioData(espacioId);
    if (!sp || !sp.tipo_tarifa_id) return 0;

    // Usar los días distribuidos si fueron calculados, de lo contrario usar los de temporada
    const diasBaja = sp._dias_baja !== undefined ? sp._dias_baja : (conteoTemporadas.value.baja.count || 0);
    const diasAlta = sp._dias_alta !== undefined ? sp._dias_alta : (conteoTemporadas.value.alta.count || 0);

    const subBaja = diasBaja * (sp.precio_baja || 0);
    const subAlta = diasAlta * (sp.precio_alta || 0);

    return subBaja + subAlta;
};

// Obtener el precio más bajo "desde" de las tarifas asociadas a un espacio
const getSpaceMinPrice = (espacioId) => {
    // Tarifas generales para obtener el precio MINIMO en la visual inicial (Card Grid)
    const t = tarifas.value.filter(tariff => Number(tariff.espacio_id) === Number(espacioId));
    if (!t || t.length === 0) return 0;
    // Buscar la menor
    return Math.min(...t.map(item => Number(item.precio_dia || 0)));
};

// Determina el color del bloque buscando por ID
const getBloqueColor = (bloqueId) => {
    const b = bloques.value.find(bk => bk.id === bloqueId);
    return b ? b.color : '#cbd5e1'; // Gris por default
};

const getFullBloqueLabel = (espacioId) => {
    const s = espacios.value.find(e => Number(e.id) === Number(espacioId));
    if (!s || !s.bloque_id) return '';
    const b = bloques.value.find(bk => Number(bk.id) === Number(s.bloque_id));
    return b ? `Bloque ${b.descripcion}` : '';
};

const getTipoEspacioName = (tipoId) => {
    const te = tipoEspacios.value.find(t => t.id === tipoId);
    return te ? te.nombre : '';
};

// ============================================================================
// MÉTODOS DE LÓGICA DE NEGOCIO Y UI INTERACCIÓN
// ============================================================================

/**
 * Agrega un espacio al arreglo reactivo de la cotización.
 */
const selectEspacio = async (espacio) => {
    // Evitar dobles
    if (cotizacion.value.espacios.find(e => e.espacio_id === espacio.id)) return;

    const eventoId = cotizacion.value.tipo_evento;
    if (!eventoId) {
        toast.add({ severity: 'warn', summary: 'Selecciona un Evento', detail: 'Debes seleccionar el tipo de evento en el Paso 1 antes de elegir espacios.', life: 4000 });
        return;
    }

    cotizacion.value.espacios.push({
        espacio_id: espacio.id,
        dias: conteoTemporadas.value.total > 0 ? conteoTemporadas.value.total : 1,
        tipo_tarifa_id: null,
        evento_id: eventoId, // Guardamos el evento_id para filtrar tarifas
        precio_baja: 0,
        precio_alta: 0,
        precio_aplicado: 0
    });

    // Cargar tarifas básicas del espacio para visualización
    if (!tarifasPorEspacio.value[espacio.id]) {
        try {
            // Intentamos cargar usando el nuevo endpoint con evento_id
            const { data } = await tarifaServices.obtenerPorEspacio(espacio.id);
            tarifasPorEspacio.value[espacio.id] = data;
        } catch (e) {
            console.error("No se pudo extraer las tarifas dinámicamente", e)
        }
    }

    // Auto-seleccionar la tarifa que corresponde al evento seleccionado
    await autoSeleccionarTarifa(espacio.id, eventoId);
};

/**
 * Auto-selecciona la tarifa que corresponde al evento_id actual.
 * Busca en el endpoint filtrado por evento: GET /api/tarifas/filtro/{espacioId}/{temporadaId}/{eventoId}
 */
const autoSeleccionarTarifa = async (espacioId, eventoId) => {
    const index = cotizacion.value.espacios.findIndex(e => Number(e.espacio_id) === Number(espacioId));
    if (index === -1) return;

    const parseItem = (item) => ({
        precio: item ? Number(item.precio_dia || 0) : 0,
        id: item ? item.id : null
    });

    // PROCESAR TEMPORADA BAJA
    if (conteoTemporadas.value.baja.count > 0 && conteoTemporadas.value.baja.id) {
        try {
            const { data } = await tarifaServices.filtrarPorEvento(espacioId, conteoTemporadas.value.baja.id, eventoId);
            const body = data?.data ? data.data : data;
            const item = Array.isArray(body) ? body[0] : body;
            const result = parseItem(item);
            cotizacion.value.espacios[index].precio_baja = result.precio;
            cotizacion.value.espacios[index].tarifa_id_baja = result.id;
            cotizacion.value.espacios[index].tipo_tarifa_id = result.id;
        } catch (e) {
            console.warn(`No se encontró tarifa para espacio=${espacioId}, temp=baja, evento=${eventoId}`, e);
            cotizacion.value.espacios[index].precio_baja = 0;
            cotizacion.value.espacios[index].tarifa_id_baja = null;
        }
    } else {
        cotizacion.value.espacios[index].precio_baja = 0;
        cotizacion.value.espacios[index].tarifa_id_baja = null;
    }

    // PROCESAR TEMPORADA ALTA
    if (conteoTemporadas.value.alta.count > 0 && conteoTemporadas.value.alta.id) {
        try {
            const { data } = await tarifaServices.filtrarPorEvento(espacioId, conteoTemporadas.value.alta.id, eventoId);
            const body = data?.data ? data.data : data;
            const item = Array.isArray(body) ? body[0] : body;
            const result = parseItem(item);
            cotizacion.value.espacios[index].precio_alta = result.precio;
            cotizacion.value.espacios[index].tarifa_id_alta = result.id;
            if (!cotizacion.value.espacios[index].tipo_tarifa_id) {
                cotizacion.value.espacios[index].tipo_tarifa_id = result.id;
            }
        } catch (e) {
            console.warn(`No se encontró tarifa para espacio=${espacioId}, temp=alta, evento=${eventoId}`, e);
            cotizacion.value.espacios[index].precio_alta = 0;
            cotizacion.value.espacios[index].tarifa_id_alta = null;
        }
    } else {
        cotizacion.value.espacios[index].precio_alta = 0;
        cotizacion.value.espacios[index].tarifa_id_alta = null;
    }
};

/**
 * Retorna el nombre descriptivo de la tarifa seleccionada para un espacio.
 */
const tarifaSeleccionada = (espacioId) => {
    const sp = getEspacioData(espacioId);
    if (!sp?.tipo_tarifa_id) return null;
    return sp.tipo_tarifa_id;
};

/**
 * Cuando cambia el tipo de evento, limpiamos los espacios seleccionados
 * porque las tarifas disponibles cambian.
 */
const onTipoEventoChange = () => {
    // Limpiar espacios seleccionados porque las tarifas dependen del evento
    cotizacion.value.espacios = [];
    // También limpiamos la caché de tarifas por espacio
    tarifasPorEspacio.value = {};
};

// Función para cargar los precios específicos de temporada al elegir un TIPO de tarifa (mantenida por compatibilidad)
const onTipoTarifaChange = async (espacioId, tipoId) => {
    if (!tipoId) return;
    const eventoId = cotizacion.value.tipo_evento;
    if (!eventoId) return;
    await autoSeleccionarTarifa(espacioId, eventoId);
};

// ============================================================================
// LÓGICA DE SERVICIOS (PASO 3)
// ============================================================================
const toggleServicio = (servicio) => {
    const index = cotizacion.value.servicios.findIndex(s => Number(s.servicio_id) === Number(servicio.id));
    if (index > -1) {
        cotizacion.value.servicios.splice(index, 1);
    } else {
        cotizacion.value.servicios.push({
            servicio_id: servicio.id,
            nombre: servicio.nombre,
            precio: Number(servicio.precio),
            cantidad: 1,
            dias: servicio.porEv ? 1 : (conteoTemporadas.value.total || 1),
            porEv: servicio.porEv,
            unidad: servicio.unidad
        });
    }
};

const isServicioSelected = (id) => {
    return cotizacion.value.servicios.some(s => Number(s.servicio_id) === Number(id));
};

const getServicioData = (id) => {
    return cotizacion.value.servicios.find(s => Number(s.servicio_id) === Number(id)) || {};
};

const calculateServicioSubtotal = (s) => {
    if (s.porEv) return (s.cantidad || 0) * (s.precio || 0);
    return (s.cantidad || 0) * (s.precio || 0) * (s.dias || 0);
};

const paso3Subtotal = computed(() => {
    return cotizacion.value.servicios.reduce((sum, s) => sum + calculateServicioSubtotal(s), 0);
});

/**
 * Quita un espacio del arreglo.
 */
const deselectEspacio = (espacioId) => {
    cotizacion.value.espacios = cotizacion.value.espacios.filter(e => e.espacio_id !== espacioId);
};

// Computa el costo total parcial visible en la barra flotante (Paso 2)
const paso2Subtotal = computed(() => {
    let sum = 0;
    cotizacion.value.espacios.forEach(sp => {
        if (sp.tipo_tarifa_id) {
            const sub = calculateSubtotal(sp.espacio_id);
            sp.precio_aplicado = sub;
            sum += sub;
        }
    });
    return sum;
});

/**
 * Evento disparado cuando el usuario elige una entidad (Cliente o Institución) en el primer Select.
 * - Si es Cliente: Se asume que él mismo es el contacto, por lo que se auto-asignan los IDs.
 * - Si es Institución: Filtramos la lista de todas las personas vinculadas a esa institución para mostrarlas en el segundo Select.
 */
const onEntidadChange = () => {
    cotizacion.value.contacto_id = null; // Reiniciar contacto al cambiar de entidad
    if (selectedEntidad.value?.tipo === 'cliente') {
        cotizacion.value.contacto_id = selectedEntidad.value.id; // Autoasignar si es persona
    } else if (selectedEntidad.value?.tipo === 'institucion') {
        // Cargar contactos vinculados a la institución seleccionada
        personasOptions.value = entidades.value.filter(e => e.tipo === 'cliente' && e.cliente_id === selectedEntidad.value.id);
    }
};

/**
 * Limpia la selección principal y por ende también el contacto.
 */
const deseleccionarEntidad = () => {
    cotizacion.value.entidad_id = null;
    cotizacion.value.contacto_id = null;
};

/**
 * Limpia únicamente la selección del contacto vinculado.
 */
const deseleccionarContacto = () => {
    cotizacion.value.contacto_id = null;
};

// ============================================================================
// GESTIÓN DEL MODAL PARA CREACIÓN EN MUB (MEMORIA)
// ============================================================================
const showModalCliente = ref(false); // Define si el modal está abierto o cerrado.
const isCreatingContacto = ref(false); // True: se crea un contacto para una Inst. / False: se crea una Entidad principal.

// Modelo temporal para el formulario del modal.
const nuevoRegistro = ref({ nit: '', nombre: '', telefono: '', telefono_codigo: defaultCode, telefono_fijo: '', email: '', tipo: 'cliente' });

/**
 * Prepara y abre el modal para crear un Cliente o Institución completamente nueva (Entidad principal)
 */
const openModalNuevaEntidad = () => {
    isCreatingContacto.value = false;
    nuevoRegistro.value = { nit: '', nombre: '', telefono: '', telefono_codigo: defaultCode, telefono_fijo: '', email: '', tipo: 'cliente' };
    showModalCliente.value = true;
};

/**
 * Prepara y abre el modal para crear un Cliente (Persona) que será vinculado como contacto a la institución seleccionada.
 */
const openModalNuevoContacto = () => {
    isCreatingContacto.value = true;
    nuevoRegistro.value = { nit: '', nombre: '', telefono: '', telefono_codigo: defaultCode, telefono_fijo: '', email: '', tipo: 'cliente' };
    showModalCliente.value = true;
};

/**
 * Guarda en memoria el registro temporal, genera un ID único provisional y 
 * lo enlaza con la cotización en curso. No hace petición a API todavía.
 */
const guardarNuevoRegistro = () => {
    const newId = 'temp_' + Date.now();
    const newItem = { id: newId, ...nuevoRegistro.value, isNew: true };

    entidades.value.push(newItem); // Agregamos a la lista global en memoria

    if (isCreatingContacto.value) {
        // Enlazar el contacto nuevo a la institución actualmente seleccionada
        if (selectedEntidad.value) {
            newItem.cliente_id = selectedEntidad.value.id;
        }
        personasOptions.value.push(newItem); // Añadirlo a la lista visual del select secundario
        cotizacion.value.contacto_id = newId; // Auto-seleccionarlo
    } else {
        // Si era una nueva entidad principal, auto-seleccionarla
        cotizacion.value.entidad_id = newId;
        onEntidadChange(); // Activar la lógica reactiva (llenar contacto si es cliente, etc.)
    }
    const entidadSeleccionada = entidades.value.find(e => e.id === cotizacion.value.entidad_id);
    const contactoSeleccionado = entidades.value.find(e => e.id === cotizacion.value.contacto_id);

    console.group('%c[WIZARD] Datos para backend', 'color: #06b6d4; font-weight: bold');

    console.log('%c[ENTIDAD PRINCIPAL]', 'color: #f59e0b; font-weight: bold', {
        id:      entidadSeleccionada?.id,             // 'temp_...' si es borrador, número si existe
        tipo:    entidadSeleccionada?.tipo,            // 'cliente' (persona) o 'institucion'
        nombre:  entidadSeleccionada?.nombre,
        nit:     entidadSeleccionada?.nit,
        isNew:   entidadSeleccionada?.isNew ?? false,  // true = hay que crear en DB antes de guardar cotización
        // Si tipo==='institucion', el backend deberá recibir también el contacto abajo
    });

    console.log('%c[CONTACTO / PERSONA]', 'color: #22c55e; font-weight: bold', {
        id:         contactoSeleccionado?.id,
        tipo:       contactoSeleccionado?.tipo,        // siempre 'cliente' (persona)
        nombre:     contactoSeleccionado?.nombre,
        nit:        contactoSeleccionado?.nit,
        telefono:   contactoSeleccionado?.telefono,
        email:      contactoSeleccionado?.email,
        cliente_id: contactoSeleccionado?.cliente_id, // FK → id de la institución (si aplica)
        isNew:      contactoSeleccionado?.isNew ?? false,
    });

    console.groupEnd();
    showModalCliente.value = false; // Cerrar Modal
};

const router = useRouter();
const toast = useToast();
const isSubmitting = ref(false);
const isReloading = ref(false);

const imprimirCotizacion = () => {
    generarPreviewPDF();
};

/**
 * Construye el payload unificado para guardar o previsualizar la cotización.
 * @param {string|number} entidadId ID de la entidad
 * @param {string|number} contactoId ID del contacto
 * @param {boolean} isPreview Si es vista previa inyectamos datos completos para el PDF
 */
const buildCotizacionPayload = (entidadId, contactoId, isPreview = false) => {
    const payload = {
        descripcion: cotizacion.value.descripcion || `Cotización para Evento`,
        fecha_ini:   cotizacion.value.fecha_inicio,
        fecha_fin:   cotizacion.value.fecha_fin,
        evento_id:   cotizacion.value.tipo_evento, 
        clientes:    [],
        tarifas:     [],
        servicios:   []
    };

    if (entidadId) {
        let obj = { id: entidadId };
        if (isPreview) {
            const ent = entidades.value.find(e => e.id === entidadId);
            if (ent) Object.assign(obj, ent);
        }
        payload.clientes.push(obj);
    }
    
    if (contactoId && contactoId !== entidadId) {
        let obj = { id: contactoId };
        if (isPreview) {
            const cont = entidades.value.find(e => e.id === contactoId);
            if (cont) Object.assign(obj, cont);
        }
        payload.clientes.push(obj);
    }

    cotizacion.value.espacios.forEach(sp => {
        const diasBaja = sp._dias_baja !== undefined ? sp._dias_baja : conteoTemporadas.value.baja.count;
        const diasAlta = sp._dias_alta !== undefined ? sp._dias_alta : conteoTemporadas.value.alta.count;

        if (sp.tarifa_id_baja && diasBaja > 0) {
            payload.tarifas.push({ id: sp.tarifa_id_baja, dias: diasBaja, precio_aplicado: sp.precio_baja });
        }
        if (sp.tarifa_id_alta && diasAlta > 0) {
            payload.tarifas.push({ id: sp.tarifa_id_alta, dias: diasAlta, precio_aplicado: sp.precio_alta });
        }
    });

    cotizacion.value.servicios.forEach(sv => {
        payload.servicios.push({
            id: sv.servicio_id, cantidad: sv.cantidad, dias: sv.dias || conteoTemporadas.value.total || 1, precio_aplicado: sv.precio
        });
    });

    return payload;
};

const guardarCotizacionCompleta = async () => {
    if (isSubmitting.value) return;

    try {
        isSubmitting.value = true;

        // Usaremos estas variables para rastrear los IDs reales con los que
        // se guardará la cotización. Empezamos con los IDs que ya están en
        // el formulario (pueden ser reales o temporales si son "borrador").
        // Obtener IDs al inicio
        let entidad_id_real = cotizacion.value.entidad_id;
        let contacto_id_real = cotizacion.value.contacto_id;

        // Bandera para saber si la Entidad y el Contacto son exactamente
        // el mismo registro (Escenario: Solo Persona Natural)
        const isSameEntityAndContact = entidad_id_real === contacto_id_real;

        // Guardamos los mensajes extra para el toast de éxito
        const extras = [];

        // Obtenemos los objetos completos de la entidad y el contacto seleccionados
        const entidadObj   = entidades.value.find(e => e.id === entidad_id_real);
        const contactoObj  = entidades.value.find(e => e.id === contacto_id_real);

        // ─────────────────────────────────────────────────────────────────
        // ESCENARIO A/B: La entidad principal es nueva ("En borrador")
        // ─────────────────────────────────────────────────────────────────
        if (entidadObj?.isNew) {
            const tipoId = entidadObj.tipo === 'institucion' ? 1 : 2;
            const res = await clienteServices.crearDesdeWizard(entidadObj);
            
            // Reemplazamos el ID temporal por el ID real
            entidad_id_real = res.data.id;

            // Si el contacto en el wizard es EXACTAMENTE la misma entidad
            // (Persona Natural), entonces no hace falta crear "otro" contacto,
            // y además debemos sincronizar el ID del contacto al mismo tiempo.
            if (isSameEntityAndContact) {
                contacto_id_real = entidad_id_real;
                cotizacion.value.contacto_id = entidad_id_real;
            }

            // Liberar memoria temporal
            entidadObj.id     = entidad_id_real;
            entidadObj.isNew  = false;
            cotizacion.value.entidad_id = entidad_id_real;

            const label = entidadObj.tipo === 'institucion' ? 'Institución' : 'Cliente';
            extras.push(`✓ ${label} "${entidadObj.nombre}" registrada.`);
        }

        // ─────────────────────────────────────────────────────────────────
        // ESCENARIO B/C: El contacto (persona) es nuevo ("En borrador")
        // Puede ser:
        //   B) La institución existía, pero creamos un nuevo contacto en el Wizard
        //   C) Tanto la institución como el contacto son nuevos
        // En ambos casos el contacto SIEMPRE es tipo 2 (Persona de contacto)
        // ─────────────────────────────────────────────────────────────────
        if (contactoObj?.isNew) {
            // crearDesdeWizard envía tipo_cliente_id=2 (Persona de contacto)
            // y cliente_id ya con el ID real de la institución
            const res = await clienteServices.crearDesdeWizard({
                ...contactoObj,
                cliente_id: entidad_id_real || null,
            });

            // Reemplazamos el ID temporal del contacto por el ID real
            contacto_id_real = res.data.id;

            // LIBERAR DE MEMORIA TEMPORAL:
            // Igual que con la entidad: marcamos isNew=false para evitar
            // una creación duplicada si el usuario reintenta después de un error.
            contactoObj.id    = contacto_id_real;
            contactoObj.isNew = false;
            cotizacion.value.contacto_id = contacto_id_real; // sincronizar el formulario

            extras.push(`✓ Persona de contacto "${contactoObj.nombre}" registrada.`);
        }

        // ─────────────────────────────────────────────────────────────────
        // ESCENARIO D (implícito): Ambos ya existen en la BD → no se hace
        // ningún POST a /clientes, simplemente usamos los IDs reales directamente.
        // ─────────────────────────────────────────────────────────────────

        // Ahora construimos el payload de la cotización con los IDs reales ya resueltos
        const payload = buildCotizacionPayload(entidad_id_real, contacto_id_real);

        // Si tenemos un nombre de entidad, podemos inyectarlo a la descripcion si quedo vacía
        if (!cotizacion.value.descripcion && entidadObj?.nombre) {
            payload.descripcion = `Cotización para ${entidadObj.nombre}`;
        }

        // Guardamos la cotización completa en la base de datos
        const response = await cotizacionServices.guardar(payload);

        // Termina el request
        isSubmitting.value = false;

        // Capturamos el código devuelto por el backend
        const codigoGenerado = response.data?.codigo || response.data?.data?.codigo || 'Registrada exitosamente';

        // Mostramos un toast de éxito con el código
        const extraMsg = extras.length > 0 ? '\n' + extras.join('\n') : '';
        toast.add({
            severity: 'success',
            summary:  `Cotización ${codigoGenerado}`,
            detail:   `La cotización se guardó exitosamente.${extraMsg}`,
            life:     8000
        });

        // Activamos la animación de recarga
        isReloading.value = true;

        // Recargamos la página después de 2.5 segundos para apreciar la animación
        setTimeout(() => {
            window.location.reload();
        }, 2500);

    } catch (e) {
        isSubmitting.value = false;
        console.error("Error al guardar la cotización", e);
        const detail = e.response?.data?.message || 'No se pudo guardar. Verifique que todos los campos estén completos.';
        toast.add({
            severity: 'error',
            summary:  'Error al Guardar',
            detail:   detail,
            life:     5000
        });
    }
};

const generarPreviewPDF = async () => {
    try {
        // El backend generará el PDF y pintará "NO REGISTRADA - VISTA PREVIA"
        const payload = buildCotizacionPayload(cotizacion.value.entidad_id, cotizacion.value.contacto_id, true);

        const response = await cotizacionServices.preview(payload);

        // Verificar si el blob es realmente un JSON (error)
        if (response.data.type === 'application/json') {
            const reader = new FileReader();
            reader.onload = () => {
                const errorData = JSON.parse(reader.result);
                toast.add({
                    severity: 'error',
                    summary: 'Error de Servidor',
                    detail: errorData.message || 'Error al generar PDF',
                    life: 5000
                });
            };
            reader.readAsText(response.data);
            return;
        }

        const blob = new Blob([response.data], { type: 'application/pdf' });

        if (pdfUrl.value) URL.revokeObjectURL(pdfUrl.value);
        // Añadimos parámetros para ocultar el toolbar del navegador y centrar la hoja
        pdfUrl.value = window.URL.createObjectURL(blob) + '#toolbar=0&navpanes=0&scrollbar=1&view=FitH';
        showPdfPreview.value = true;
    } catch (e) {
        console.error("Error al generar vista previa", e);
        toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudo conectar con el servicio de previsualización.', life: 3000 });
    }
};
</script>

<style scoped>
/* ========================================================================= */
/* CSS ESPECÍFICO PARA PERSONALIZAR EL STEPPER DE PRIMEVUE                   */
/* ========================================================================= */

/* Da padding horizontal a la lista de pestañas */
:deep(.p-step-list) {
    padding: 0 1rem;
}

/* Base style de cada pestaña. Borramos el fondo transparente borde. */
:deep(.p-step) {
    padding: 1.25rem 1rem;
    border-bottom: 2px solid transparent;
}

/* Indicador de pestaña activa que imita un sub-borde contínuo */
:deep(.p-step-active) {
    border-bottom: 2px solid var(--p-primary-color) !important;
}

/* Transiciones para la pantalla de carga */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
