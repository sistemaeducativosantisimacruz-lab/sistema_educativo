<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ $routeBack }}" class="p-2 bg-white rounded-lg border border-gray-200 text-gray-500 hover:text-gray-800 hover:bg-gray-50 transition shadow-sm" title="Volver al listado">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>
                <div>
                    <h2 class="font-bold text-xl text-gray-800 leading-tight flex items-center gap-2">
                        <span>📋 Ficha de Entrevista a Padres</span>
                        <span class="text-xs px-2.5 py-0.5 rounded-full font-semibold {{ $entrevista->esBorrador() ? 'bg-amber-100 text-amber-800 border border-amber-200' : ($entrevista->estaCompletada() ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-blue-100 text-blue-800') }}">
                            {{ $entrevista->id ? ($entrevista->esBorrador() ? 'En Progreso (Borrador)' : 'Completada') : 'Nueva Ficha en Línea' }}
                        </span>
                    </h2>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Proceso de Admisión y Continuidad · Entrevistador: <strong>{{ Auth::user()->name }}</strong>
                    </p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" 
         x-data="entrevistaForm({
            entrevistaId: {{ $entrevista->id ? $entrevista->id : 'null' }},
            anoLectivo: {{ $entrevista->ano_lectivo_postula ?? $anoActual }},
            nivel: '{{ $entrevista->nivel ?? $nivel }}',
            gradoPostula: '{{ $entrevista->grado_postula ?? ($nivel === 'secundaria' ? '1° Secundaria' : '1° Primaria') }}',
            estudianteDni: '{{ $entrevista->estudiante_dni && !str_starts_with($entrevista->estudiante_dni, 'BORRADOR') ? $entrevista->estudiante_dni : '' }}',
            estudianteNombres: '{{ addslashes($entrevista->estudiante_nombres ?? '') }}',
            estudianteApellidoPaterno: '{{ addslashes($entrevista->estudiante_apellido_paterno ?? '') }}',
            estudianteApellidoMaterno: '{{ addslashes($entrevista->estudiante_apellido_materno ?? '') }}',
            estudianteSexo: '{{ $entrevista->estudiante_sexo ?? '' }}',
            estudianteFechaNacimiento: '{{ $entrevista->estudiante_fecha_nacimiento?->format('Y-m-d') ?? '' }}',
            colegioProcedencia: '{{ addslashes($entrevista->colegio_procedencia ?? '') }}',
            
            // Padres
            padreDni: '{{ $entrevista->padre_dni ?? '' }}',
            padreNombres: '{{ addslashes($entrevista->padre_nombres ?? '') }}',
            padreTelefono: '{{ $entrevista->padre_telefono ?? '' }}',
            padreOcupacion: '{{ addslashes($entrevista->padre_ocupacion ?? '') }}',
            padreGradoEstudios: '{{ addslashes($entrevista->padre_grado_estudios ?? '') }}',
            padreDomicilio: '{{ addslashes($entrevista->padre_domicilio ?? '') }}',

            madreDni: '{{ $entrevista->madre_dni ?? '' }}',
            madreNombres: '{{ addslashes($entrevista->madre_nombres ?? '') }}',
            madreTelefono: '{{ $entrevista->madre_telefono ?? '' }}',
            madreOcupacion: '{{ addslashes($entrevista->madre_ocupacion ?? '') }}',
            madreGradoEstudios: '{{ addslashes($entrevista->madre_grado_estudios ?? '') }}',
            madreDomicilio: '{{ addslashes($entrevista->madre_domicilio ?? '') }}',

            apoderadoDni: '{{ $entrevista->apoderado_dni ?? '' }}',
            apoderadoNombres: '{{ addslashes($entrevista->apoderado_nombres ?? '') }}',
            apoderadoTelefono: '{{ $entrevista->apoderado_telefono ?? '' }}',
            apoderadoOcupacion: '{{ addslashes($entrevista->apoderado_ocupacion ?? '') }}',
            apoderadoGradoEstudios: '{{ addslashes($entrevista->apoderado_grado_estudios ?? '') }}',
            apoderadoDomicilio: '{{ addslashes($entrevista->apoderado_domicilio ?? '') }}',
            apoderadoParentesco: '{{ addslashes($entrevista->apoderado_parentesco ?? '') }}',

            // Respuestas cuestionario
            respuestas: {{ json_encode($entrevista->respuestas_cuestionario ?? []) }},
            hermanosDatos: {{ json_encode($entrevista->hermanos_datos ?? []) }},
            conformidad: '{{ $entrevista->resultado_conformidad ?? 'CONFORME' }}',
            fechaEntrevista: '{{ $entrevista->fecha_entrevista?->format('Y-m-d') ?? now()->toDateString() }}',
            observaciones: '{{ addslashes($entrevista->observaciones ?? '') }}',

            // Rutas AJAX
            storeUrl: '{{ $routeStore }}',
            cerrarUrlTemplate: '{{ $routeCerrar }}',
            buscarEstudiantesUrl: '{{ $routeBuscarEstudiantes }}',
            backUrl: '{{ $routeBack }}',
            csrfToken: '{{ csrf_token() }}'
         })" x-init="initForm()">

        <!-- BARRA FLOTANTE DE ESTADO Y ACCIONES RÁPIDAS -->
        <div class="sticky top-4 z-40 mb-6 bg-white/95 backdrop-blur-md rounded-2xl p-4 shadow-lg border border-gray-200/80 flex flex-wrap items-center justify-between gap-3 transition-all">
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2">
                    <template x-if="isSaving">
                        <span class="inline-flex items-center gap-1.5 text-xs text-amber-600 font-medium">
                            <svg class="animate-spin h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            Guardando automáticamente...
                        </span>
                    </template>
                    <template x-if="!isSaving && lastSaved">
                        <span class="inline-flex items-center gap-1.5 text-xs text-emerald-600 font-medium">
                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span x-text="`Guardado a las ${lastSaved}`"></span>
                        </span>
                    </template>
                    <template x-if="!isSaving && !lastSaved">
                        <span class="text-xs text-gray-400">Autoguardado activado</span>
                    </template>
                </div>

                <div class="h-4 w-px bg-gray-200 hidden sm:block"></div>

                <!-- CONTADOR DE CAMPOS PENDIENTES -->
                <div>
                    <template x-if="camposPendientesCount > 0">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                            <span>⏳ Quedan <strong x-text="camposPendientesCount"></strong> campos requeridos pendientes</span>
                        </span>
                    </template>
                    <template x-if="camposPendientesCount === 0">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <span>✅ ¡Todos los campos obligatorios completos!</span>
                        </span>
                    </template>
                </div>
            </div>

            <!-- BOTONES DE GUARDADO -->
            <div class="flex items-center gap-2.5">
                <button type="button" @click="guardarProgresoManual()" :disabled="isSaving"
                        class="px-3.5 py-2 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 rounded-xl text-xs font-semibold shadow-sm transition flex items-center gap-1.5 disabled:opacity-50">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                    </svg>
                    <span>Guardar Progreso</span>
                </button>

                <button type="button" @click="registrarYCerrar()" :disabled="isSaving || isClosing"
                        :class="camposPendientesCount === 0 ? 'bg-gradient-to-r from-emerald-600 to-teal-600 text-white shadow-emerald-200 hover:from-emerald-700 hover:to-teal-700' : 'bg-gray-100 text-gray-400 border border-gray-200 cursor-not-allowed'"
                        class="px-4 py-2 rounded-xl text-xs font-bold shadow-md transition flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span x-text="isClosing ? 'Registrando...' : 'Registrar y Cerrar'"></span>
                </button>
            </div>
        </div>

        <form @submit.prevent="registrarYCerrar()" class="space-y-6">

            <!-- 1. ENCABEZADO Y BUSCADOR DE ESTUDIANTE EXISTENTE (REINSCRIPCIÓN / PASO DE GRADO) -->
            <div class="bg-gradient-to-br from-indigo-500/10 via-white to-purple-500/10 rounded-2xl p-6 shadow-sm border border-indigo-100">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-4 border-b border-indigo-100/60">
                    <div>
                        <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                            <span>🎓 Nivel y Grado de Postulación</span>
                        </h3>
                        <p class="text-xs text-gray-500 mt-0.5">Seleccione el nivel y grado para el cual se realiza la entrevista.</p>
                    </div>

                    <!-- SELECTOR DE NIVEL Y AÑO -->
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="flex items-center bg-gray-100 p-1 rounded-xl">
                            <button type="button" @click="cambiarNivel('primaria')"
                                    :class="nivel === 'primaria' ? 'bg-white shadow-sm text-indigo-700 font-bold' : 'text-gray-500 hover:text-gray-800'"
                                    class="px-3.5 py-1.5 rounded-lg text-xs transition">
                                🎒 Primaria
                            </button>
                            <button type="button" @click="cambiarNivel('secundaria')"
                                    :class="nivel === 'secundaria' ? 'bg-white shadow-sm text-indigo-700 font-bold' : 'text-gray-500 hover:text-gray-800'"
                                    class="px-3.5 py-1.5 rounded-lg text-xs transition">
                                🏛️ Secundaria
                            </button>
                        </div>

                        <div>
                            <select x-model="gradoPostula" @change="triggerAutosave()"
                                    class="text-xs font-semibold rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 py-1.5">
                                <template x-if="nivel === 'primaria'">
                                    <optgroup label="Primaria">
                                        @foreach($gradosPrimaria as $gp)
                                            <option value="{{ $gp->nombre }}">{{ $gp->nombre }}</option>
                                        @endforeach
                                    </optgroup>
                                </template>
                                <template x-if="nivel === 'secundaria'">
                                    <optgroup label="Secundaria">
                                        @foreach($gradosSecundaria as $gs)
                                            <option value="{{ $gs->nombre }}">{{ $gs->nombre }}</option>
                                        @endforeach
                                    </optgroup>
                                </template>
                            </select>
                        </div>

                        <div class="flex items-center gap-1.5 bg-white px-3 py-1.5 rounded-xl border border-gray-200 text-xs text-gray-600">
                            <span class="font-medium">Año:</span>
                            <input type="number" x-model.number="anoLectivo" @change="triggerAutosave()" class="w-16 text-xs p-0 border-0 font-bold text-indigo-700 focus:ring-0">
                        </div>
                    </div>
                </div>

                <!-- BUSCADOR EN VIVO DE ESTUDIANTE EXISTENTE (PARA ARRASTRAR DATOS) -->
                <div class="mt-4 pt-2">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 mb-2">
                        <label class="text-xs font-bold text-indigo-900 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            <span>¿El estudiante ya estudia o estudió en la institución? (Paso de grado o actualización de datos):</span>
                        </label>
                        <span class="text-[11px] text-gray-500">Escriba DNI o Apellidos para autocompletar</span>
                    </div>

                    <div class="relative" @click.away="searchEstudianteResults = []">
                        <input type="text" x-model="searchEstudianteQuery" @input.debounce.300ms="buscarEstudianteExistente()"
                               placeholder="🔍 Buscar por DNI o Nombres del estudiante actual..."
                               class="w-full text-xs rounded-xl border-indigo-200 focus:border-indigo-500 focus:ring-indigo-500 bg-white/80 py-2.5 px-3 shadow-inner">
                        
                        <!-- LISTA DE RESULTADOS DE BÚSQUEDA -->
                        <div x-show="searchEstudianteResults.length > 0" x-cloak
                             class="absolute left-0 right-0 top-full mt-1 bg-white rounded-xl shadow-xl border border-indigo-100 max-h-60 overflow-y-auto z-50 divide-y divide-gray-100">
                            <template x-for="est in searchEstudianteResults" :key="est.id">
                                <button type="button" @click="cargarDatosEstudianteExistente(est)"
                                        class="w-full text-left p-3 hover:bg-indigo-50 transition flex items-center justify-between group">
                                    <div>
                                        <div class="font-bold text-xs text-gray-800 group-hover:text-indigo-700" x-text="est.nombre_completo"></div>
                                        <div class="text-[11px] text-gray-500 flex items-center gap-2 mt-0.5">
                                            <span>DNI: <strong x-text="est.dni"></strong></span>
                                            <span class="text-gray-300">·</span>
                                            <span>Grado actual: <span x-text="est.grado_actual || 'Sin matrícula'"></span></span>
                                            <span class="text-gray-300">·</span>
                                            <span class="px-1.5 py-0.2 rounded font-semibold text-[10px]"
                                                  :class="est.sexo === 'M' ? 'bg-blue-100 text-blue-700' : 'bg-pink-100 text-pink-700'"
                                                  x-text="est.sexo === 'M' ? '👦 Masculino' : (est.sexo === 'F' ? '👧 Femenino' : 'Sexo s/d')"></span>
                                        </div>
                                    </div>
                                    <span class="px-2.5 py-1 bg-indigo-100 text-indigo-700 rounded-lg text-xs font-semibold group-hover:bg-indigo-600 group-hover:text-white transition">
                                        Cargar Datos →
                                    </span>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. IDENTIFICACIÓN DEL ESTUDIANTE -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4 flex items-center gap-2 border-b pb-2">
                    <span class="w-6 h-6 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center text-xs font-bold">1</span>
                    <span>Datos de Identificación del Estudiante</span>
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    <!-- DNI -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">
                            D.N.I. del Estudiante <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" x-model="estudianteDni" @input="triggerAutosave()" maxlength="8"
                               placeholder="Ej.: 71234567"
                               class="w-full text-xs rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 font-mono font-bold tracking-wider">
                        <span x-show="estudianteDni.length > 0 && estudianteDni.length < 8" class="text-[10px] text-amber-600 mt-0.5 block">
                            Debe tener 8 dígitos
                        </span>
                    </div>

                    <!-- GÉNERO / SEXO (CONDICIONAL DE NORMAS) -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">
                            Género / Sexo <span class="text-rose-500">*</span>
                        </label>
                        <div class="grid grid-cols-2 gap-2">
                            <button type="button" @click="setSexo('M')"
                                    :class="estudianteSexo === 'M' ? 'bg-blue-600 text-white shadow-md shadow-blue-100 border-blue-600' : 'bg-gray-50 text-gray-700 border-gray-200 hover:bg-gray-100'"
                                    class="py-2 px-2 rounded-xl text-xs font-bold border transition flex items-center justify-center gap-1.5">
                                <span>👦 Masculino</span>
                            </button>
                            <button type="button" @click="setSexo('F')"
                                    :class="estudianteSexo === 'F' ? 'bg-pink-600 text-white shadow-md shadow-pink-100 border-pink-600' : 'bg-gray-50 text-gray-700 border-gray-200 hover:bg-gray-100'"
                                    class="py-2 px-2 rounded-xl text-xs font-bold border transition flex items-center justify-center gap-1.5">
                                <span>👧 Femenino</span>
                            </button>
                        </div>
                    </div>

                    <!-- Nombres -->
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">
                            Nombres del Estudiante <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" x-model="estudianteNombres" @input="triggerAutosave()"
                               placeholder="Ej.: Lucía Fernanda"
                               class="w-full text-xs rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 uppercase">
                    </div>

                    <!-- Apellido Paterno -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">
                            Apellido Paterno <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" x-model="estudianteApellidoPaterno" @input="triggerAutosave()"
                               placeholder="Ej.: Pérez"
                               class="w-full text-xs rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 uppercase">
                    </div>

                    <!-- Apellido Materno -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">
                            Apellido Materno
                        </label>
                        <input type="text" x-model="estudianteApellidoMaterno" @input="triggerAutosave()"
                               placeholder="Ej.: Rojas"
                               class="w-full text-xs rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 uppercase">
                    </div>

                    <!-- Fecha de Nacimiento -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">
                            Fecha de Nacimiento
                        </label>
                        <input type="date" x-model="estudianteFechaNacimiento" @change="triggerAutosave()"
                               class="w-full text-xs rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <!-- Colegio de Procedencia -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">
                            Colegio de Procedencia
                        </label>
                        <input type="text" x-model="colegioProcedencia" @input="triggerAutosave()"
                               placeholder="Ej.: I.E.P. Santísima Cruz"
                               class="w-full text-xs rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>
            </div>

            <!-- 3. DATOS DE LOS PADRES Y APODERADO -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4 flex items-center justify-between border-b pb-2">
                    <div class="flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs font-bold">2</span>
                        <span>Datos de los Padres de Familia y Apoderado</span>
                    </div>
                    <span class="text-[11px] font-normal text-gray-400">Complete al menos uno de los progenitores o apoderado</span>
                </h3>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- PADRE -->
                    <div class="p-4 rounded-xl bg-blue-50/40 border border-blue-100 space-y-3">
                        <div class="font-bold text-xs text-blue-900 uppercase flex items-center gap-1.5">
                            <span>👨 Datos del Padre</span>
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-gray-600 mb-0.5">DNI del Padre</label>
                            <input type="text" x-model="padreDni" @input="triggerAutosave()" maxlength="8" placeholder="8 dígitos"
                                   class="w-full text-xs rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-gray-600 mb-0.5">Nombres y Apellidos</label>
                            <input type="text" x-model="padreNombres" @input="triggerAutosave()" placeholder="Ej.: Juan Pérez Díaz"
                                   class="w-full text-xs rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500 uppercase">
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[11px] font-semibold text-gray-600 mb-0.5">Teléfono</label>
                                <input type="text" x-model="padreTelefono" @input="triggerAutosave()" placeholder="Ej.: 987654321"
                                       class="w-full text-xs rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-gray-600 mb-0.5">Ocupación</label>
                                <input type="text" x-model="padreOcupacion" @input="triggerAutosave()" placeholder="Ej.: Agricultor"
                                       class="w-full text-xs rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500 uppercase">
                            </div>
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-gray-600 mb-0.5">Grado de Estudios</label>
                            <select x-model="padreGradoEstudios" @change="triggerAutosave()" class="w-full text-xs rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">-- Seleccionar --</option>
                                <option value="Primaria incompleta">Primaria incompleta</option>
                                <option value="Primaria completa">Primaria completa</option>
                                <option value="Secundaria incompleta">Secundaria incompleta</option>
                                <option value="Secundaria completa">Secundaria completa</option>
                                <option value="Superior técnico">Superior técnico</option>
                                <option value="Superior universitario">Superior universitario</option>
                                <option value="Postgrado">Postgrado</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-gray-600 mb-0.5">Domicilio</label>
                            <input type="text" x-model="padreDomicilio" @input="triggerAutosave()" placeholder="Ej.: Av. Grau 123"
                                   class="w-full text-xs rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <!-- MADRE -->
                    <div class="p-4 rounded-xl bg-pink-50/40 border border-pink-100 space-y-3">
                        <div class="font-bold text-xs text-pink-900 uppercase flex items-center justify-between">
                            <span>👩 Datos de la Madre</span>
                            <button type="button" @click="madreDomicilio = padreDomicilio; triggerAutosave()" 
                                    class="text-[10px] text-pink-600 hover:underline">
                                Mismo domicilio que padre
                            </button>
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-gray-600 mb-0.5">DNI de la Madre</label>
                            <input type="text" x-model="madreDni" @input="triggerAutosave()" maxlength="8" placeholder="8 dígitos"
                                   class="w-full text-xs rounded-lg border-gray-300 focus:ring-pink-500 focus:border-pink-500">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-gray-600 mb-0.5">Nombres y Apellidos</label>
                            <input type="text" x-model="madreNombres" @input="triggerAutosave()" placeholder="Ej.: María Rojas Vega"
                                   class="w-full text-xs rounded-lg border-gray-300 focus:ring-pink-500 focus:border-pink-500 uppercase">
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[11px] font-semibold text-gray-600 mb-0.5">Teléfono</label>
                                <input type="text" x-model="madreTelefono" @input="triggerAutosave()" placeholder="Ej.: 987654321"
                                       class="w-full text-xs rounded-lg border-gray-300 focus:ring-pink-500 focus:border-pink-500">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-gray-600 mb-0.5">Ocupación</label>
                                <input type="text" x-model="madreOcupacion" @input="triggerAutosave()" placeholder="Ej.: Comerciante"
                                       class="w-full text-xs rounded-lg border-gray-300 focus:ring-pink-500 focus:border-pink-500 uppercase">
                            </div>
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-gray-600 mb-0.5">Grado de Estudios</label>
                            <select x-model="madreGradoEstudios" @change="triggerAutosave()" class="w-full text-xs rounded-lg border-gray-300 focus:ring-pink-500 focus:border-pink-500">
                                <option value="">-- Seleccionar --</option>
                                <option value="Primaria incompleta">Primaria incompleta</option>
                                <option value="Primaria completa">Primaria completa</option>
                                <option value="Secundaria incompleta">Secundaria incompleta</option>
                                <option value="Secundaria completa">Secundaria completa</option>
                                <option value="Superior técnico">Superior técnico</option>
                                <option value="Superior universitario">Superior universitario</option>
                                <option value="Postgrado">Postgrado</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-gray-600 mb-0.5">Domicilio</label>
                            <input type="text" x-model="madreDomicilio" @input="triggerAutosave()" placeholder="Ej.: Av. Grau 123"
                                   class="w-full text-xs rounded-lg border-gray-300 focus:ring-pink-500 focus:border-pink-500">
                        </div>
                    </div>

                    <!-- APODERADO -->
                    <div class="p-4 rounded-xl bg-purple-50/40 border border-purple-100 space-y-3">
                        <div class="font-bold text-xs text-purple-900 uppercase flex items-center justify-between">
                            <span>🤝 Apoderado (si corresponde)</span>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[11px] font-semibold text-gray-600 mb-0.5">Parentesco</label>
                                <input type="text" x-model="apoderadoParentesco" @input="triggerAutosave()" placeholder="Ej.: Tía, Abuela"
                                       class="w-full text-xs rounded-lg border-gray-300 focus:ring-purple-500 focus:border-purple-500 uppercase">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-gray-600 mb-0.5">DNI Apoderado</label>
                                <input type="text" x-model="apoderadoDni" @input="triggerAutosave()" maxlength="8" placeholder="8 dígitos"
                                       class="w-full text-xs rounded-lg border-gray-300 focus:ring-purple-500 focus:border-purple-500">
                            </div>
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-gray-600 mb-0.5">Nombres y Apellidos</label>
                            <input type="text" x-model="apoderadoNombres" @input="triggerAutosave()" placeholder="Ej.: Rosa Díaz Vega"
                                   class="w-full text-xs rounded-lg border-gray-300 focus:ring-purple-500 focus:border-purple-500 uppercase">
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[11px] font-semibold text-gray-600 mb-0.5">Teléfono</label>
                                <input type="text" x-model="apoderadoTelefono" @input="triggerAutosave()" placeholder="Ej.: 987654321"
                                       class="w-full text-xs rounded-lg border-gray-300 focus:ring-purple-500 focus:border-purple-500">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-gray-600 mb-0.5">Ocupación</label>
                                <input type="text" x-model="apoderadoOcupacion" @input="triggerAutosave()" placeholder="Ej.: Docente"
                                       class="w-full text-xs rounded-lg border-gray-300 focus:ring-purple-500 focus:border-purple-500 uppercase">
                            </div>
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-gray-600 mb-0.5">Grado de Estudios</label>
                            <select x-model="apoderadoGradoEstudios" @change="triggerAutosave()" class="w-full text-xs rounded-lg border-gray-300 focus:ring-purple-500 focus:border-purple-500">
                                <option value="">-- Seleccionar --</option>
                                <option value="Primaria incompleta">Primaria incompleta</option>
                                <option value="Primaria completa">Primaria completa</option>
                                <option value="Secundaria incompleta">Secundaria incompleta</option>
                                <option value="Secundaria completa">Secundaria completa</option>
                                <option value="Superior técnico">Superior técnico</option>
                                <option value="Superior universitario">Superior universitario</option>
                                <option value="Postgrado">Postgrado</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-gray-600 mb-0.5">Domicilio</label>
                            <input type="text" x-model="apoderadoDomicilio" @input="triggerAutosave()" placeholder="Ej.: Jr. Lima 456"
                                   class="w-full text-xs rounded-lg border-gray-300 focus:ring-purple-500 focus:border-purple-500">
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. SITUACIÓN FAMILIAR Y CONVIVENCIA (PREGUNTAS 18, 19, 20) -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4 flex items-center gap-2 border-b pb-2">
                    <span class="w-6 h-6 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center text-xs font-bold">3</span>
                    <span>Situación Familiar y Convivencia</span>
                </h3>

                <div class="space-y-5">
                    <!-- PREGUNTA 18: NÚMERO DE HERMANOS -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                        <div class="md:col-span-3">
                            <label class="block text-xs font-bold text-gray-800">
                                18. Número total de hermanos del estudiante
                            </label>
                            <p class="text-[11px] text-gray-500">Colocar 0 si es hijo único.</p>
                        </div>
                        <div>
                            <input type="number" min="0" x-model.number="respuestas.num_hermanos" @input="triggerAutosave()"
                                   class="w-full text-xs font-bold rounded-xl border-gray-300 focus:ring-amber-500 focus:border-amber-500">
                        </div>
                    </div>

                    <!-- PREGUNTA 19: ¿CON QUIÉN VIVE EL ESTUDIANTE? (CHECKS MULTI-SELECCIÓN) -->
                    <div class="p-4 rounded-xl bg-amber-50/40 border border-amber-200/60">
                        <label class="block text-xs font-bold text-gray-800 mb-1">
                            19. Personas con quienes vive el estudiante <span class="text-rose-500">*</span>
                        </label>
                        <p class="text-[11px] text-gray-500 mb-3">Marque todas las opciones que correspondan para reflejar con quién convive actualmente el alumno:</p>

                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-2.5">
                            <template x-for="item in opcionesConvivencia" :key="item.id">
                                <label class="flex items-center gap-2 p-2.5 rounded-xl border transition cursor-pointer text-xs"
                                       :class="viveConSelected.includes(item.id) ? 'bg-amber-100/80 border-amber-400 font-bold text-amber-900' : 'bg-white border-gray-200 text-gray-700 hover:bg-gray-50'">
                                    <input type="checkbox" :value="item.id" x-model="viveConSelected" @change="actualizarViveCon()"
                                           class="rounded border-gray-300 text-amber-600 focus:ring-amber-500">
                                    <span x-text="`${item.icon} ${item.label}`"></span>
                                </label>
                            </template>
                        </div>

                        <!-- Opción Otro especificar -->
                        <div class="mt-3" x-show="viveConSelected.includes('Otro')">
                            <label class="block text-[11px] font-semibold text-gray-700 mb-0.5">Especifique otros familiares o personas con quienes vive:</label>
                            <input type="text" x-model="viveConOtro" @input="actualizarViveCon()"
                                   placeholder="Ej.: Tía materna, prima hermana, bisabuela..."
                                   class="w-full text-xs rounded-lg border-amber-300 focus:ring-amber-500 focus:border-amber-500 bg-white">
                        </div>
                    </div>

                    <!-- PREGUNTA 20 & TABLA DE HERMANOS EN LA IE (ARMADO DE FAMILIAS) -->
                    <div class="p-4 rounded-xl bg-indigo-50/40 border border-indigo-200/60 space-y-3">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div>
                                <label class="block text-xs font-bold text-gray-900">
                                    20. ¿Tiene hermanos que estudian actualmente en la Institución Educativa?
                                </label>
                                <p class="text-[11px] text-gray-500">Permite asociar y unificar los registros familiares en el sistema.</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" @click="setTieneHermanos('Sí')"
                                        :class="respuestas.hermanos_en_ie === 'Sí' ? 'bg-indigo-600 text-white font-bold' : 'bg-white text-gray-700 border border-gray-300'"
                                        class="px-4 py-1.5 rounded-lg text-xs transition">
                                    Sí
                                </button>
                                <button type="button" @click="setTieneHermanos('No')"
                                        :class="respuestas.hermanos_en_ie === 'No' ? 'bg-gray-700 text-white font-bold' : 'bg-white text-gray-700 border border-gray-300'"
                                        class="px-4 py-1.5 rounded-lg text-xs transition">
                                    No
                                </button>
                            </div>
                        </div>

                        <!-- PANEL DINÁMICO DE HERMANOS -->
                        <div x-show="respuestas.hermanos_en_ie === 'Sí'" x-cloak class="mt-3 pt-3 border-t border-indigo-100 space-y-3">
                            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                                <div class="relative flex-1" @click.away="searchHermanoResults = []">
                                    <input type="text" x-model="searchHermanoQuery" @input.debounce.300ms="buscarHermano()"
                                           placeholder="Buscar hermano(a) por DNI o Nombres..."
                                           class="w-full text-xs rounded-lg border-indigo-200 focus:ring-indigo-500 focus:border-indigo-500 bg-white py-2 px-3">

                                    <div x-show="searchHermanoResults.length > 0" x-cloak
                                         class="absolute left-0 right-0 top-full mt-1 bg-white rounded-lg shadow-lg border border-indigo-100 max-h-48 overflow-y-auto z-50 divide-y divide-gray-100">
                                        <template x-for="h in searchHermanoResults" :key="h.id">
                                            <button type="button" @click="anadirHermanoDesdeBusqueda(h)"
                                                    class="w-full text-left p-2.5 hover:bg-indigo-50 text-xs flex items-center justify-between">
                                                <div>
                                                    <span class="font-bold text-gray-800" x-text="h.nombre_completo"></span>
                                                    <span class="text-gray-400 text-[11px]" x-text="` (DNI: ${h.dni} · ${h.grado_actual || 'Sin aula'})`"></span>
                                                </div>
                                                <span class="text-[11px] text-indigo-600 font-bold">+ Añadir</span>
                                            </button>
                                        </template>
                                    </div>
                                </div>

                                <button type="button" @click="anadirHermanoManual()"
                                        class="px-3 py-2 bg-indigo-600 text-white rounded-lg text-xs font-semibold hover:bg-indigo-700 transition flex items-center justify-center gap-1">
                                    <span>+ Añadir Manualmente</span>
                                </button>
                            </div>

                            <!-- TABLA DE HERMANOS REGISTRADOS -->
                            <div class="overflow-x-auto bg-white rounded-xl border border-indigo-100 shadow-inner">
                                <table class="w-full text-xs text-left">
                                    <thead class="bg-indigo-50/70 text-indigo-900 uppercase font-semibold text-[10px]">
                                        <tr>
                                            <th class="p-2.5">DNI</th>
                                            <th class="p-2.5">Nombres y Apellidos</th>
                                            <th class="p-2.5">Grado / Nivel</th>
                                            <th class="p-2.5 text-center w-12">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        <template x-for="(hermano, index) in hermanosDatos" :key="index">
                                            <tr class="hover:bg-indigo-50/30">
                                                <td class="p-2.5 font-mono" x-text="hermano.dni || 'S/D'"></td>
                                                <td class="p-2.5 font-medium uppercase" x-text="hermano.nombre"></td>
                                                <td class="p-2.5 text-gray-500" x-text="hermano.grado || '—'"></td>
                                                <td class="p-2.5 text-center">
                                                    <button type="button" @click="eliminarHermano(index)" class="text-rose-500 hover:text-rose-700 font-bold" title="Quitar">
                                                        🗑️
                                                    </button>
                                                </td>
                                            </tr>
                                        </template>
                                        <template x-if="hermanosDatos.length === 0">
                                            <tr>
                                                <td colspan="4" class="p-3 text-center text-gray-400 italic">
                                                    No se han añadido hermanos en la IE aún. Use el buscador de arriba.
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- PREGUNTA 23: AXIOLOGÍA INSTITUCIONAL -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3 bg-gray-50 rounded-xl border border-gray-200">
                        <div class="text-xs text-gray-800">
                            <strong>23. Axiología de la IE:</strong> La axiología de la institución educativa es promover la <strong>COMUNIÓN y PARTICIPACIÓN</strong> a la luz del evangelio y de la espiritualidad Agustiniana. ¿Acepta?
                        </div>
                        <select x-model="respuestas.acuerdo_comunion" @change="triggerAutosave()" class="text-xs rounded-lg border-gray-300 font-bold">
                            <option value="Sí">Sí</option>
                            <option value="No">No</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- 5. VIDA DE FE Y SACRAMENTOS (PREGUNTA 27.3 MULTI-CHECK) -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4 flex items-center gap-2 border-b pb-2">
                    <span class="w-6 h-6 rounded-lg bg-teal-100 text-teal-700 flex items-center justify-center text-xs font-bold">4</span>
                    <span>Vida de Fe del Estudiante y la Familia</span>
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">24. Parroquia a la que asiste</label>
                        <input type="text" x-model="respuestas.parroquia" @input="triggerAutosave()" placeholder="Ej.: Parroquia San Juan Bautista"
                               class="w-full text-xs rounded-xl border-gray-300 focus:ring-teal-500 focus:border-teal-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">25. Comunidad zonal a la que pertenece</label>
                        <input type="text" x-model="respuestas.comunidad_zonal" @input="triggerAutosave()" placeholder="Ej.: Comunidad Santa Rosa"
                               class="w-full text-xs rounded-xl border-gray-300 focus:ring-teal-500 focus:border-teal-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">26.1 Bautizo celebrado</label>
                        <select x-model="respuestas.sacramento_bautizo" @change="triggerAutosave()" class="w-full text-xs rounded-xl border-gray-300 focus:ring-teal-500 font-semibold">
                            <option value="Sí">Sí</option>
                            <option value="No">No</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">26.2 Eucaristía / Primera Comunión</label>
                        <select x-model="respuestas.sacramento_eucaristia" @change="triggerAutosave()" class="w-full text-xs rounded-xl border-gray-300 focus:ring-teal-500 font-semibold">
                            <option value="Sí">Sí</option>
                            <option value="No">No</option>
                        </select>
                    </div>

                    <!-- SACRAMENTO CONFIRMACIÓN (SOLO SECUNDARIA) -->
                    <div x-show="nivel === 'secundaria'">
                        <label class="block text-xs font-semibold text-gray-700 mb-1">26.3 Sacramento de Confirmación</label>
                        <select x-model="respuestas.sacramento_confirmacion" @change="triggerAutosave()" class="w-full text-xs rounded-xl border-gray-300 focus:ring-teal-500 font-semibold">
                            <option value="Sí">Sí</option>
                            <option value="No">No</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">27.1 ¿Considera importante la Eucaristía?</label>
                        <select x-model="respuestas.eucaristia_importante" @change="triggerAutosave()" class="w-full text-xs rounded-xl border-gray-300 focus:ring-teal-500 font-semibold">
                            <option value="Sí">Sí</option>
                            <option value="No">No</option>
                        </select>
                    </div>

                    <div class="md:col-span-3">
                        <label class="block text-xs font-semibold text-gray-700 mb-1">27.2 ¿Por qué?</label>
                        <input type="text" x-model="respuestas.eucaristia_por_que" @input="triggerAutosave()" placeholder="Ej.: Fortalece la fe y la unión familiar..."
                               class="w-full text-xs rounded-xl border-gray-300 focus:ring-teal-500 focus:border-teal-500">
                    </div>

                    <!-- PREGUNTA 27.3: DÍAS DE EUCARISTÍA (MULTI-CHECK) -->
                    <div class="md:col-span-3 p-4 rounded-xl bg-teal-50/50 border border-teal-200">
                        <label class="block text-xs font-bold text-teal-900 mb-1">
                            27.3 ¿Qué día(s) participa en la Eucaristía? (Selección múltiple)
                        </label>
                        <p class="text-[11px] text-gray-500 mb-3">Marque los días en que la familia acude habitualmente:</p>
                        
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                            <template x-for="dia in opcionesEucaristiaDias" :key="dia">
                                <label class="flex items-center gap-2 p-2.5 rounded-xl border transition cursor-pointer text-xs"
                                       :class="eucaristiaDiasSelected.includes(dia) ? 'bg-teal-100 border-teal-400 font-bold text-teal-900' : 'bg-white border-gray-200 text-gray-700 hover:bg-gray-50'">
                                    <input type="checkbox" :value="dia" x-model="eucaristiaDiasSelected" @change="actualizarEucaristiaDia()"
                                           class="rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                                    <span x-text="dia"></span>
                                </label>
                            </template>
                        </div>

                        <div class="mt-2.5" x-show="eucaristiaDiasSelected.includes('Otro')">
                            <input type="text" x-model="eucaristiaDiaOtro" @input="actualizarEucaristiaDia()"
                                   placeholder="Especifique otros días o celebraciones..."
                                   class="w-full text-xs rounded-lg border-teal-300 focus:ring-teal-500 bg-white">
                        </div>
                    </div>
                </div>
            </div>

            <!-- 6. SITUACIÓN CONYUGAL Y SALUD -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4 flex items-center gap-2 border-b pb-2">
                    <span class="w-6 h-6 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs font-bold">5</span>
                    <span>Situación Conyugal y Salud del Estudiante</span>
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">28. ¿Padres Casados?</label>
                        <select x-model="respuestas.padres_casados" @change="triggerAutosave()" class="w-full text-xs rounded-xl border-gray-300 font-semibold">
                            <option value="Sí">Sí</option>
                            <option value="No">No</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">28.1 Estado Conyugal</label>
                        <select x-model="respuestas.estado_conyugal" @change="triggerAutosave()" class="w-full text-xs rounded-xl border-gray-300 font-semibold">
                            <option value="">-- Seleccionar --</option>
                            <option value="Religioso">Religioso</option>
                            <option value="Civil">Civil</option>
                            <option value="Civil y Religioso">Civil y Religioso</option>
                            <option value="Conviviente">Conviviente</option>
                            <option value="Separados">Separados</option>
                            <option value="Divorciados">Divorciados</option>
                            <option value="Madre soltera">Madre soltera</option>
                            <option value="Padre soltero">Padre soltero</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">29. ¿Padece alguna enfermedad?</label>
                        <select x-model="respuestas.enfermedad" @change="triggerAutosave()" class="w-full text-xs rounded-xl border-gray-300 font-semibold">
                            <option value="No">No</option>
                            <option value="Sí">Sí</option>
                        </select>
                    </div>

                    <div x-show="respuestas.enfermedad === 'Sí'" class="md:col-span-3 grid grid-cols-1 sm:grid-cols-2 gap-4 p-3 bg-rose-50 rounded-xl border border-rose-100">
                        <div>
                            <label class="block text-xs font-semibold text-rose-800 mb-1">30. ¿Conoce el diagnóstico?</label>
                            <select x-model="respuestas.conoce_diagnostico" @change="triggerAutosave()" class="w-full text-xs rounded-xl border-rose-300 font-semibold">
                                <option value="Sí">Sí</option>
                                <option value="No">No</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-rose-800 mb-1">31. ¿Cuál es la condición / diagnóstico?</label>
                            <input type="text" x-model="respuestas.diagnostico_cual" @input="triggerAutosave()" placeholder="Detallar diagnóstico..."
                                   class="w-full text-xs rounded-xl border-rose-300">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">32. Patria Potestad (en padres separados)</label>
                        <input type="text" x-model="respuestas.patria_potestad" @input="triggerAutosave()" placeholder="Ej.: Ambos padres / Solo mamá"
                               class="w-full text-xs rounded-xl border-gray-300">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-gray-700 mb-1">33. Persona sin patria potestad dispuesta a apoyar</label>
                        <input type="text" x-model="respuestas.persona_dispuesta" @input="triggerAutosave()" placeholder="Ej.: Sí, apoya económicamente y en visitas"
                               class="w-full text-xs rounded-xl border-gray-300">
                    </div>
                </div>
            </div>

            <!-- 7. CUMPLIMIENTO DE NORMAS (CONDICIONADO POR SEXO: CORTE CABELLO VS TRENZAS) -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4 flex items-center justify-between border-b pb-2">
                    <div class="flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-violet-100 text-violet-700 flex items-center justify-center text-xs font-bold">6</span>
                        <span>Cumplimiento de Normas Institucionales</span>
                    </div>
                    <span class="text-xs px-2.5 py-0.5 rounded-full font-bold"
                          :class="estudianteSexo === 'M' ? 'bg-blue-100 text-blue-700' : (estudianteSexo === 'F' ? 'bg-pink-100 text-pink-700' : 'bg-gray-100 text-gray-500')"
                          x-text="estudianteSexo === 'M' ? '👦 Normas aplicables: Masculino' : (estudianteSexo === 'F' ? '👧 Normas aplicables: Femenino' : '⚠️ Seleccione el sexo del estudiante')">
                    </span>
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- HORARIO DE ENTRADA -->
                    <div class="p-3 rounded-xl border border-gray-200 flex items-center justify-between">
                        <span class="text-xs text-gray-800">
                            <strong>Horario de entrada:</strong> Ingreso a la IE es 6:45 am. Puerta se cierra 7:00 am.
                        </span>
                        <select x-model="respuestas.norma_horario" @change="triggerAutosave()" class="text-xs rounded-lg border-gray-300 font-bold ml-2">
                            <option value="Sí">Sí</option>
                            <option value="No">No</option>
                        </select>
                    </div>

                    <!-- CORTE DE CABELLO ESCOLAR (HABILITADO SOLO PARA MASCULINO) -->
                    <div class="p-3 rounded-xl border transition"
                         :class="estudianteSexo === 'M' ? 'border-blue-300 bg-blue-50/30' : 'border-gray-200 bg-gray-50 opacity-60'">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-xs font-bold" :class="estudianteSexo === 'M' ? 'text-blue-900' : 'text-gray-500'">
                                    Corte de cabello escolar sin modas ni cortes en las cejas
                                </span>
                                <template x-if="estudianteSexo === 'F'">
                                    <span class="block text-[11px] text-pink-600 font-semibold mt-0.5">
                                        🔒 Bloqueado: No aplica (Estudiante femenina)
                                    </span>
                                </template>
                            </div>
                            <select x-model="respuestas.norma_corte_cabello" @change="triggerAutosave()"
                                    :disabled="estudianteSexo === 'F'"
                                    class="text-xs rounded-lg border-gray-300 font-bold ml-2">
                                <option value="Sí">Sí</option>
                                <option value="No">No</option>
                                <option value="No aplica">No aplica</option>
                            </select>
                        </div>
                    </div>

                    <!-- CABELLO TRENZADO (HABILITADO SOLO PARA FEMENINO) -->
                    <div class="p-3 rounded-xl border transition"
                         :class="estudianteSexo === 'F' ? 'border-pink-300 bg-pink-50/30' : 'border-gray-200 bg-gray-50 opacity-60'">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-xs font-bold" :class="estudianteSexo === 'F' ? 'text-pink-900' : 'text-gray-500'">
                                    Cabello trenzado, sin maquillaje ni accesorios
                                </span>
                                <template x-if="estudianteSexo === 'M'">
                                    <span class="block text-[11px] text-blue-600 font-semibold mt-0.5">
                                        🔒 Bloqueado: No aplica (Estudiante masculino)
                                    </span>
                                </template>
                            </div>
                            <select x-model="respuestas.norma_cabello_trenzado" @change="triggerAutosave()"
                                    :disabled="estudianteSexo === 'M'"
                                    class="text-xs rounded-lg border-gray-300 font-bold ml-2">
                                <option value="Sí">Sí</option>
                                <option value="No">No</option>
                                <option value="No aplica">No aplica</option>
                            </select>
                        </div>
                    </div>

                    <!-- UNIFORME COMPLETO -->
                    <div class="p-3 rounded-xl border border-gray-200 flex items-center justify-between">
                        <span class="text-xs text-gray-800">
                            <strong>Uso de uniforme completo</strong> según indicación en el RI.
                        </span>
                        <select x-model="respuestas.norma_uniforme" @change="triggerAutosave()" class="text-xs rounded-lg border-gray-300 font-bold ml-2">
                            <option value="Sí">Sí</option>
                            <option value="No">No</option>
                        </select>
                    </div>

                    <!-- ASISTENCIA Y PUNTUALIDAD -->
                    <div class="p-3 rounded-xl border border-gray-200 flex items-center justify-between">
                        <span class="text-xs text-gray-800">
                            <strong>Asistencia y puntualidad</strong> diaria según horario establecido.
                        </span>
                        <select x-model="respuestas.norma_asistencia" @change="triggerAutosave()" class="text-xs rounded-lg border-gray-300 font-bold ml-2">
                            <option value="Sí">Sí</option>
                            <option value="No">No</option>
                        </select>
                    </div>

                    <!-- COMPROMISO ECONÓMICO -->
                    <div class="p-3 rounded-xl border border-gray-200 flex items-center justify-between">
                        <span class="text-xs text-gray-800">
                            <strong>Compromiso económico de pensiones</strong> en fechas establecidas.
                        </span>
                        <select x-model="respuestas.compromiso_pago" @change="triggerAutosave()" class="text-xs rounded-lg border-gray-300 font-bold ml-2">
                            <option value="Sí">Sí</option>
                            <option value="No">No</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- 8. OBLIGACIONES Y AUTORIZACIONES -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4 flex items-center gap-2 border-b pb-2">
                    <span class="w-6 h-6 rounded-lg bg-orange-100 text-orange-700 flex items-center justify-center text-xs font-bold">7</span>
                    <span>Obligaciones de Padres y Autorizaciones</span>
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                    <div class="p-2.5 bg-gray-50 rounded-xl border flex items-center justify-between text-xs">
                        <span>Reuniones de aula</span>
                        <select x-model="respuestas.oblig_reuniones_aula" @change="triggerAutosave()" class="rounded-lg text-xs font-semibold py-1">
                            <option value="Sí">Sí</option>
                            <option value="No">No</option>
                        </select>
                    </div>

                    <div class="p-2.5 bg-gray-50 rounded-xl border flex items-center justify-between text-xs">
                        <span>Atención a Padres</span>
                        <select x-model="respuestas.oblig_atencion_padres" @change="triggerAutosave()" class="rounded-lg text-xs font-semibold py-1">
                            <option value="Sí">Sí</option>
                            <option value="No">No</option>
                        </select>
                    </div>

                    <div class="p-2.5 bg-gray-50 rounded-xl border flex items-center justify-between text-xs">
                        <span>Escuela para Padres</span>
                        <select x-model="respuestas.oblig_escuela_padres" @change="triggerAutosave()" class="rounded-lg text-xs font-semibold py-1">
                            <option value="Sí">Sí</option>
                            <option value="No">No</option>
                        </select>
                    </div>

                    <div class="p-2.5 bg-gray-50 rounded-xl border flex items-center justify-between text-xs">
                        <span>Conservación de ambientes</span>
                        <select x-model="respuestas.oblig_conservacion" @change="triggerAutosave()" class="rounded-lg text-xs font-semibold py-1">
                            <option value="Sí">Sí</option>
                            <option value="No">No</option>
                        </select>
                    </div>

                    <div class="p-2.5 bg-gray-50 rounded-xl border flex items-center justify-between text-xs">
                        <span>Multa por inasistencia APAFA</span>
                        <select x-model="respuestas.oblig_multa_apafa" @change="triggerAutosave()" class="rounded-lg text-xs font-semibold py-1">
                            <option value="Sí">Sí</option>
                            <option value="No">No</option>
                        </select>
                    </div>

                    <div class="p-2.5 bg-gray-50 rounded-xl border flex items-center justify-between text-xs">
                        <span>Integración de aulas</span>
                        <select x-model="respuestas.acuerdo_integracion_aulas" @change="triggerAutosave()" class="rounded-lg text-xs font-semibold py-1">
                            <option value="Sí">Sí</option>
                            <option value="No">No</option>
                        </select>
                    </div>

                    <div class="p-2.5 bg-gray-50 rounded-xl border flex items-center justify-between text-xs">
                        <span>Comunicaciones WhatsApp / llamada</span>
                        <select x-model="respuestas.autoriza_comunicacion" @change="triggerAutosave()" class="rounded-lg text-xs font-semibold py-1">
                            <option value="Sí">Sí</option>
                            <option value="No">No</option>
                        </select>
                    </div>

                    <div class="p-2.5 bg-gray-50 rounded-xl border flex items-center justify-between text-xs">
                        <span>Uso de fotos y videos</span>
                        <select x-model="respuestas.autoriza_fotos_videos" @change="triggerAutosave()" class="rounded-lg text-xs font-semibold py-1">
                            <option value="Sí">Sí</option>
                            <option value="No">No</option>
                        </select>
                    </div>

                    <div class="p-2.5 bg-gray-50 rounded-xl border flex items-center justify-between text-xs">
                        <span>Atención médica de emergencia</span>
                        <select x-model="respuestas.autoriza_atencion_medica" @change="triggerAutosave()" class="rounded-lg text-xs font-semibold py-1">
                            <option value="Sí">Sí</option>
                            <option value="No">No</option>
                        </select>
                    </div>
                </div>

                <!-- RETIRO DEL ESTUDIANTE -->
                <div class="mt-4 pt-4 border-t border-gray-100 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">¿Autoriza que regrese solo a casa?</label>
                        <select x-model="respuestas.autoriza_regresa_solo" @change="triggerAutosave()" class="w-full text-xs rounded-xl border-gray-300 font-semibold">
                            <option value="No">No (Será recogido)</option>
                            <option value="Sí">Sí (Regresa solo)</option>
                        </select>
                    </div>
                    <div x-show="respuestas.autoriza_regresa_solo === 'No'">
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Nombre de quien recoge</label>
                        <input type="text" x-model="respuestas.recoge_nombre" @input="triggerAutosave()" placeholder="Ej.: Rosa Díaz"
                               class="w-full text-xs rounded-xl border-gray-300">
                    </div>
                    <div x-show="respuestas.autoriza_regresa_solo === 'No'">
                        <label class="block text-xs font-semibold text-gray-700 mb-1">DNI de quien recoge</label>
                        <input type="text" x-model="respuestas.recoge_dni" @input="triggerAutosave()" maxlength="8" placeholder="8 dígitos"
                               class="w-full text-xs rounded-xl border-gray-300">
                    </div>
                </div>
            </div>

            <!-- 9. CONFORMIDAD FINAL Y CIERRE -->
            <div class="bg-gradient-to-br from-gray-900 to-indigo-950 text-white rounded-2xl p-6 shadow-xl">
                <h3 class="text-sm font-bold uppercase tracking-wider mb-4 flex items-center gap-2 border-b border-gray-800 pb-2">
                    <span class="w-6 h-6 rounded-lg bg-indigo-500 text-white flex items-center justify-center text-xs font-bold">8</span>
                    <span>Conformidad de Entrevista y Cierre</span>
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-xs font-bold uppercase mb-2">
                            Resultado de Conformidad <span class="text-rose-400">*</span>
                        </label>
                        <div class="grid grid-cols-2 gap-2">
                            <button type="button" @click="conformidad = 'CONFORME'; triggerAutosave()"
                                    :class="conformidad === 'CONFORME' ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-900/50 border-emerald-500' : 'bg-gray-800 text-gray-400 border-gray-700 hover:bg-gray-700'"
                                    class="py-2.5 px-3 rounded-xl text-xs font-bold border transition text-center">
                                🟢 CONFORME
                            </button>
                            <button type="button" @click="conformidad = 'NO CONFORME'; triggerAutosave()"
                                    :class="conformidad === 'NO CONFORME' ? 'bg-rose-600 text-white shadow-lg shadow-rose-900/50 border-rose-500' : 'bg-gray-800 text-gray-400 border-gray-700 hover:bg-gray-700'"
                                    class="py-2.5 px-3 rounded-xl text-xs font-bold border transition text-center">
                                🔴 NO CONFORME
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase mb-2">Fecha de Entrevista</label>
                        <input type="date" x-model="fechaEntrevista" @change="triggerAutosave()"
                               class="w-full text-xs rounded-xl border-gray-700 bg-gray-800 text-white focus:border-indigo-400 focus:ring-indigo-400">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase mb-2">Docente Entrevistador a Cargo</label>
                        <div class="p-2.5 bg-gray-800/80 rounded-xl border border-gray-700 text-xs text-gray-300">
                            👤 <strong>{{ Auth::user()->name }}</strong>
                        </div>
                    </div>

                    <div class="md:col-span-3">
                        <label class="block text-xs font-bold uppercase mb-2">Observaciones Adicionales de la Entrevista</label>
                        <textarea x-model="observaciones" @input="triggerAutosave()" rows="2"
                                  placeholder="Notas o acuerdos particulares con la familia..."
                                  class="w-full text-xs rounded-xl border-gray-700 bg-gray-800 text-white focus:border-indigo-400 focus:ring-indigo-400"></textarea>
                    </div>
                </div>

                <!-- BOTONES DE ACCIÓN FINAL -->
                <div class="mt-6 pt-4 border-t border-gray-800 flex flex-wrap items-center justify-between gap-4">
                    <span class="text-xs text-gray-400">
                        * Recuerde que puede guardar el progreso en cualquier momento y cerrar la ficha cuando esté completa.
                    </span>

                    <div class="flex items-center gap-3">
                        <button type="button" @click="guardarProgresoManual()" :disabled="isSaving"
                                class="px-5 py-2.5 bg-gray-800 hover:bg-gray-700 border border-gray-700 text-white rounded-xl text-xs font-bold transition shadow-sm">
                            💾 Guardar Progreso
                        </button>

                        <button type="button" @click="registrarYCerrar()" :disabled="isSaving || isClosing"
                                :class="camposPendientesCount === 0 ? 'bg-emerald-500 hover:bg-emerald-600 text-white shadow-lg shadow-emerald-500/30' : 'bg-gray-700 text-gray-400 cursor-not-allowed'"
                                class="px-6 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2">
                            <span>🔒 Registrar y Cerrar Entrevista</span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- SCRIPT ALPINE DEL FORMULARIO -->
    <script>
    function entrevistaForm(config) {
        return {
            entrevistaId: config.entrevistaId,
            anoLectivo: config.anoLectivo,
            nivel: config.nivel,
            gradoPostula: config.gradoPostula,
            estudianteDni: config.estudianteDni,
            estudianteNombres: config.estudianteNombres,
            estudianteApellidoPaterno: config.estudianteApellidoPaterno,
            estudianteApellidoMaterno: config.estudianteApellidoMaterno,
            estudianteSexo: config.estudianteSexo,
            estudianteFechaNacimiento: config.estudianteFechaNacimiento,
            colegioProcedencia: config.colegioProcedencia,

            padreDni: config.padreDni,
            padreNombres: config.padreNombres,
            padreTelefono: config.padreTelefono,
            padreOcupacion: config.padreOcupacion,
            padreGradoEstudios: config.padreGradoEstudios,
            padreDomicilio: config.padreDomicilio,

            madreDni: config.madreDni,
            madreNombres: config.madreNombres,
            madreTelefono: config.madreTelefono,
            madreOcupacion: config.madreOcupacion,
            madreGradoEstudios: config.madreGradoEstudios,
            madreDomicilio: config.madreDomicilio,

            apoderadoDni: config.apoderadoDni,
            apoderadoNombres: config.apoderadoNombres,
            apoderadoTelefono: config.apoderadoTelefono,
            apoderadoOcupacion: config.apoderadoOcupacion,
            apoderadoGradoEstudios: config.apoderadoGradoEstudios,
            apoderadoDomicilio: config.apoderadoDomicilio,
            apoderadoParentesco: config.apoderadoParentesco,

            respuestas: Object.assign({
                num_hermanos: 0,
                vive_con: '',
                hermanos_en_ie: 'No',
                hermanos_en_ie_cantidad: 0,
                hermanos_en_ie_nombres: '',
                acuerdo_comunion: 'Sí',
                parroquia: '',
                comunidad_zonal: '',
                sacramento_bautizo: 'Sí',
                sacramento_eucaristia: 'Sí',
                sacramento_confirmacion: 'No',
                eucaristia_importante: 'Sí',
                eucaristia_por_que: '',
                eucaristia_dia: '',
                padres_casados: 'Sí',
                estado_conyugal: '',
                enfermedad: 'No',
                conoce_diagnostico: 'No aplica',
                diagnostico_cual: '',
                patria_potestad: 'Ambos padres',
                persona_dispuesta: '',
                oblig_reuniones_aula: 'Sí',
                oblig_atencion_padres: 'Sí',
                oblig_escuela_padres: 'Sí',
                oblig_conservacion: 'Sí',
                oblig_actividades_ie: 'Sí',
                oblig_multa_apafa: 'Sí',
                norma_horario: 'Sí',
                norma_corte_cabello: 'Sí',
                norma_cabello_trenzado: 'No aplica',
                norma_uniforme: 'Sí',
                norma_asistencia: 'Sí',
                compromiso_pago: 'Sí',
                autoriza_comunicacion: 'Sí',
                autoriza_fotos_videos: 'Sí',
                autoriza_actividades: 'Sí',
                autoriza_atencion_medica: 'Sí',
                autoriza_regresa_solo: 'No',
                recoge_nombre: '',
                recoge_dni: '',
                acuerdo_integracion_aulas: 'Sí'
            }, config.respuestas || {}),

            hermanosDatos: config.hermanosDatos || [],
            conformidad: config.conformidad || 'CONFORME',
            fechaEntrevista: config.fechaEntrevista,
            observaciones: config.observaciones,

            // Opciones Pregunta 19 (Con quién vive)
            opcionesConvivencia: [
                { id: 'Papá', label: 'Papá', icon: '👨' },
                { id: 'Mamá', label: 'Mamá', icon: '👩' },
                { id: 'Hermanos', label: 'Hermanos', icon: '👦' },
                { id: 'Abuelos', label: 'Abuelos', icon: '👵' },
                { id: 'Tíos', label: 'Tíos', icon: '🧑' },
                { id: 'Primos', label: 'Primos', icon: '🧒' },
                { id: 'Padrastro', label: 'Padrastro', icon: '👤' },
                { id: 'Madrastra', label: 'Madrastra', icon: '👤' },
                { id: 'Tutor legal', label: 'Tutor legal', icon: '⚖️' },
                { id: 'Otro', label: 'Otro(s)', icon: '➕' }
            ],
            viveConSelected: [],
            viveConOtro: '',

            // Opciones Pregunta 27.3 (Días Eucaristía)
            opcionesEucaristiaDias: ['Sábados', 'Domingos', 'Días de semana', 'Misas especiales', 'Otro'],
            eucaristiaDiasSelected: [],
            eucaristiaDiaOtro: '',

            // Búsqueda estudiante existente
            searchEstudianteQuery: '',
            searchEstudianteResults: [],

            // Búsqueda hermano
            searchHermanoQuery: '',
            searchHermanoResults: [],

            // Estados de guardado
            isSaving: false,
            isClosing: false,
            lastSaved: null,
            debounceTimer: null,

            initForm() {
                // Parsear vive_con inicial
                if (this.respuestas.vive_con) {
                    const partes = this.respuestas.vive_con.split(',').map(s => s.trim());
                    partes.forEach(p => {
                        const existe = this.opcionesConvivencia.find(o => o.id === p);
                        if (existe) {
                            this.viveConSelected.push(p);
                        } else if (p) {
                            if (!this.viveConSelected.includes('Otro')) this.viveConSelected.push('Otro');
                            this.viveConOtro = p;
                        }
                    });
                }
                if (this.viveConSelected.length === 0) {
                    this.viveConSelected = ['Papá', 'Mamá'];
                    this.actualizarViveCon();
                }

                // Parsear eucaristia_dia inicial
                if (this.respuestas.eucaristia_dia) {
                    const dias = this.respuestas.eucaristia_dia.split(',').map(s => s.trim());
                    dias.forEach(d => {
                        if (this.opcionesEucaristiaDias.includes(d)) {
                            this.eucaristiaDiasSelected.push(d);
                        } else if (d) {
                            if (!this.eucaristiaDiasSelected.includes('Otro')) this.eucaristiaDiasSelected.push('Otro');
                            this.eucaristiaDiaOtro = d;
                        }
                    });
                }
                if (this.eucaristiaDiasSelected.length === 0) {
                    this.eucaristiaDiasSelected = ['Domingos'];
                    this.actualizarEucaristiaDia();
                }

                // Ajustar regla de corte según sexo inicial
                if (this.estudianteSexo) {
                    this.setSexo(this.estudianteSexo, false);
                }
            },

            cambiarNivel(nuevoNivel) {
                this.nivel = nuevoNivel;
                if (nuevoNivel === 'secundaria' && !this.gradoPostula.toLowerCase().includes('secundaria')) {
                    this.gradoPostula = '1° Secundaria';
                } else if (nuevoNivel === 'primaria' && !this.gradoPostula.toLowerCase().includes('primaria')) {
                    this.gradoPostula = '1° Primaria';
                }
                this.triggerAutosave();
            },

            setSexo(sexo, autosave = true) {
                this.estudianteSexo = sexo;
                if (sexo === 'M') {
                    this.respuestas.norma_corte_cabello = (this.respuestas.norma_corte_cabello === 'No aplica') ? 'Sí' : this.respuestas.norma_corte_cabello;
                    this.respuestas.norma_cabello_trenzado = 'No aplica';
                } else if (sexo === 'F') {
                    this.respuestas.norma_cabello_trenzado = (this.respuestas.norma_cabello_trenzado === 'No aplica') ? 'Sí' : this.respuestas.norma_cabello_trenzado;
                    this.respuestas.norma_corte_cabello = 'No aplica';
                }
                if (autosave) this.triggerAutosave();
            },

            setTieneHermanos(val) {
                this.respuestas.hermanos_en_ie = val;
                if (val === 'No') {
                    this.hermanosDatos = [];
                }
                this.triggerAutosave();
            },

            actualizarViveCon() {
                const seleccionados = this.viveConSelected.filter(id => id !== 'Otro');
                if (this.viveConSelected.includes('Otro') && this.viveConOtro.trim()) {
                    seleccionados.push(this.viveConOtro.trim());
                }
                this.respuestas.vive_con = seleccionados.join(', ');
                this.triggerAutosave();
            },

            actualizarEucaristiaDia() {
                const dias = this.eucaristiaDiasSelected.filter(d => d !== 'Otro');
                if (this.eucaristiaDiasSelected.includes('Otro') && this.eucaristiaDiaOtro.trim()) {
                    dias.push(this.eucaristiaDiaOtro.trim());
                }
                this.respuestas.eucaristia_dia = dias.join(', ');
                this.triggerAutosave();
            },

            async buscarEstudianteExistente() {
                const q = this.searchEstudianteQuery.trim();
                if (q.length < 2) {
                    this.searchEstudianteResults = [];
                    return;
                }
                try {
                    const res = await fetch(`${config.buscarEstudiantesUrl}?q=${encodeURIComponent(q)}`);
                    const data = await res.json();
                    this.searchEstudianteResults = data;
                } catch (e) {
                    console.error('Error buscando estudiante:', e);
                }
            },

            cargarDatosEstudianteExistente(est) {
                this.estudianteDni = est.dni || '';
                this.estudianteNombres = est.nombres || '';
                this.estudianteApellidoPaterno = est.apellido_paterno || '';
                this.estudianteApellidoMaterno = est.apellido_materno || '';
                this.estudianteFechaNacimiento = est.fecha_nacimiento || '';
                this.colegioProcedencia = est.colegio_procedencia || 'I.E.P. Santísima Cruz';
                
                if (est.sexo) {
                    this.setSexo(est.sexo, false);
                }

                if (est.grado_siguiente) {
                    this.gradoPostula = est.grado_siguiente;
                    if (est.nivel_siguiente) this.nivel = est.nivel_siguiente;
                }

                if (est.padre) {
                    this.padreDni = est.padre.dni || this.padreDni;
                    this.padreNombres = est.padre.nombres || this.padreNombres;
                    this.padreTelefono = est.padre.telefono || this.padreTelefono;
                    this.padreDomicilio = est.padre.domicilio || this.padreDomicilio;
                }

                if (est.madre) {
                    this.madreDni = est.madre.dni || this.madreDni;
                    this.madreNombres = est.madre.nombres || this.madreNombres;
                    this.madreTelefono = est.madre.telefono || this.madreTelefono;
                    this.madreDomicilio = est.madre.domicilio || this.madreDomicilio;
                }

                if (est.apoderado) {
                    this.apoderadoDni = est.apoderado.dni || this.apoderadoDni;
                    this.apoderadoNombres = est.apoderado.nombres || this.apoderadoNombres;
                    this.apoderadoTelefono = est.apoderado.telefono || this.apoderadoTelefono;
                    this.apoderadoDomicilio = est.apoderado.domicilio || this.apoderadoDomicilio;
                    this.apoderadoParentesco = est.apoderado.parentesco || this.apoderadoParentesco;
                }

                this.searchEstudianteResults = [];
                this.searchEstudianteQuery = '';
                this.triggerAutosave();
            },

            async buscarHermano() {
                const q = this.searchHermanoQuery.trim();
                if (q.length < 2) {
                    this.searchHermanoResults = [];
                    return;
                }
                try {
                    const res = await fetch(`${config.buscarEstudiantesUrl}?q=${encodeURIComponent(q)}`);
                    const data = await res.json();
                    this.searchHermanoResults = data;
                } catch (e) {
                    console.error('Error buscando hermanos:', e);
                }
            },

            anadirHermanoDesdeBusqueda(h) {
                if (this.hermanosDatos.some(x => x.dni && x.dni === h.dni)) {
                    alert('Este hermano ya ha sido añadido a la lista.');
                    return;
                }
                this.hermanosDatos.push({
                    id: h.id,
                    dni: h.dni,
                    nombre: h.nombre_completo,
                    grado: h.grado_actual || ''
                });
                this.searchHermanoResults = [];
                this.searchHermanoQuery = '';
                this.respuestas.hermanos_en_ie = 'Sí';
                this.triggerAutosave();
            },

            anadirHermanoManual() {
                const nombre = prompt('Ingrese nombres y apellidos del hermano(a):');
                if (!nombre || !nombre.trim()) return;
                const grado = prompt('Ingrese grado o nivel en que estudia (ej.: 3° Primaria):') || '';
                const dni = prompt('Ingrese DNI del hermano (opcional):') || '';

                this.hermanosDatos.push({
                    nombre: nombre.trim().toUpperCase(),
                    grado: grado.trim(),
                    dni: dni.trim()
                });
                this.respuestas.hermanos_en_ie = 'Sí';
                this.triggerAutosave();
            },

            eliminarHermano(index) {
                this.hermanosDatos.splice(index, 1);
                if (this.hermanosDatos.length === 0) {
                    this.respuestas.hermanos_en_ie = 'No';
                }
                this.triggerAutosave();
            },

            get camposPendientesCount() {
                let pendientes = 0;
                if (!this.estudianteDni || this.estudianteDni.length < 8) pendientes++;
                if (!this.estudianteNombres || !this.estudianteNombres.trim()) pendientes++;
                if (!this.estudianteApellidoPaterno || !this.estudianteApellidoPaterno.trim()) pendientes++;
                if (!this.estudianteSexo) pendientes++;
                if (!this.gradoPostula) pendientes++;
                if (!this.conformidad) pendientes++;
                if (!this.padreNombres && !this.madreNombres && !this.apoderadoNombres) pendientes++;
                return pendientes;
            },

            triggerAutosave() {
                clearTimeout(this.debounceTimer);
                this.debounceTimer = setTimeout(() => {
                    this.guardarProgresoAutomatico();
                }, 1200);
            },

            prepararPayload(estadoEntrevista = 'en_progreso') {
                return {
                    _token: config.csrfToken,
                    entrevista_id: this.entrevistaId,
                    ano_lectivo_postula: this.anoLectivo,
                    nivel: this.nivel,
                    grado_postula: this.gradoPostula,
                    estudiante_dni: this.estudianteDni,
                    estudiante_nombres: this.estudianteNombres,
                    estudiante_apellido_paterno: this.estudianteApellidoPaterno,
                    estudiante_apellido_materno: this.estudianteApellidoMaterno,
                    estudiante_sexo: this.estudianteSexo,
                    estudiante_fecha_nacimiento: this.estudianteFechaNacimiento,
                    colegio_procedencia: this.colegioProcedencia,

                    padre_dni: this.padreDni,
                    padre_nombres: this.padreNombres,
                    padre_telefono: this.padreTelefono,
                    padre_ocupacion: this.padreOcupacion,
                    padre_grado_estudios: this.padreGradoEstudios,
                    padre_domicilio: this.padreDomicilio,

                    madre_dni: this.madreDni,
                    madre_nombres: this.madreNombres,
                    madreTelefono: this.madreTelefono,
                    madreOcupacion: this.madreOcupacion,
                    madreGradoEstudios: this.madreGradoEstudios,
                    madreDomicilio: this.madreDomicilio,

                    apoderado_dni: this.apoderadoDni,
                    apoderado_nombres: this.apoderadoNombres,
                    apoderado_telefono: this.apoderadoTelefono,
                    apoderado_ocupacion: this.apoderadoOcupacion,
                    apoderado_grado_estudios: this.apoderadoGradoEstudios,
                    apoderado_domicilio: this.apoderadoDomicilio,
                    apoderado_parentesco: this.apoderadoParentesco,

                    hermanos_datos: this.hermanosDatos,
                    resultado_conformidad: this.conformidad,
                    fecha_entrevista: this.fechaEntrevista,
                    observaciones: this.observaciones,
                    estado_entrevista: estadoEntrevista,

                    // Cuestionario completo
                    ...this.respuestas
                };
            },

            async guardarProgresoAutomatico() {
                if (this.isSaving || this.isClosing) return;
                this.isSaving = true;

                try {
                    const payload = this.prepararPayload('en_progreso');
                    const res = await fetch(config.storeUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': config.csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    });

                    const data = await res.json();
                    if (data.success) {
                        this.entrevistaId = data.entrevista_id;
                        this.lastSaved = data.saved_at || new Date().toLocaleTimeString();
                    }
                } catch (e) {
                    console.error('Error en autoguardado:', e);
                } finally {
                    this.isSaving = false;
                }
            },

            async guardarProgresoManual() {
                this.isSaving = true;
                try {
                    const payload = this.prepararPayload('en_progreso');
                    const res = await fetch(config.storeUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': config.csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    });

                    const data = await res.json();
                    if (data.success) {
                        this.entrevistaId = data.entrevista_id;
                        this.lastSaved = data.saved_at || new Date().toLocaleTimeString();
                        alert('✅ Progreso guardado con éxito. Puede seguir llenando o volver más tarde.');
                    } else {
                        alert('⚠️ ' + (data.mensaje || 'Error al guardar'));
                    }
                } catch (e) {
                    alert('Error de conexión al guardar progreso.');
                } finally {
                    this.isSaving = false;
                }
            },

            async registrarYCerrar() {
                if (this.camposPendientesCount > 0) {
                    alert(`Faltan ${this.camposPendientesCount} campo(s) obligatorio(s). Por favor verifique DNI (8 dígitos), nombres, apellido paterno, género y datos de los padres.`);
                    return;
                }

                if (!confirm('¿Confirma que desea registrar y cerrar esta entrevista? Una vez cerrada quedará registrada en el sistema de admisión.')) {
                    return;
                }

                this.isClosing = true;
                try {
                    const payload = this.prepararPayload('completada');
                    // Usar endpoint de cierre con el ID correspondiente
                    const cerrarUrl = this.entrevistaId
                        ? config.cerrarUrlTemplate.replace('__ID__', this.entrevistaId)
                        : config.storeUrl;

                    const res = await fetch(cerrarUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': config.csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    });

                    const data = await res.json();
                    if (data.success) {
                        alert('🎉 ¡Entrevista registrada y cerrada exitosamente!');
                        window.location.href = data.redirect || config.backUrl;
                    } else {
                        alert('⚠️ ' + (data.mensaje || 'Error al cerrar entrevista'));
                        this.isClosing = false;
                    }
                } catch (e) {
                    alert('Error de conexión al registrar y cerrar entrevista.');
                    this.isClosing = false;
                }
            }
        };
    }
    </script>
</x-app-layout>

