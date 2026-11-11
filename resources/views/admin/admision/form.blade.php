<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ $routeBack }}" class="p-2 bg-white rounded-lg border border-gray-300 text-gray-700 hover:text-black hover:bg-gray-100 transition shadow-sm" title="Volver al listado">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>
                <div>
                    <h2 class="font-black text-xl text-black leading-tight flex items-center gap-2">
                        <span>Ficha de Entrevista a Padres de Familia</span>
                        <span class="text-xs px-3 py-1 rounded-full font-black tracking-wider uppercase border {{ $entrevista->esBorrador() ? 'bg-amber-100 text-black border-amber-400' : ($entrevista->estaCompletada() ? 'bg-emerald-100 text-black border-emerald-400' : 'bg-gray-100 text-black border-gray-400') }}">
                            {{ $entrevista->id ? ($entrevista->esBorrador() ? 'En Progreso (Borrador)' : 'Completada') : 'Nueva Ficha en Línea' }}
                        </span>
                    </h2>
                    <p class="text-xs text-gray-700 mt-0.5">
                        Proceso de Admisión y Continuidad (Paso de Grado) · Docente Entrevistador: <strong class="text-black font-black">{{ Auth::user()->name }}</strong>
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
            conformidad: '{{ $entrevista->resultado_conformidad ?? '' }}',
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
        <div class="sticky top-4 z-40 mb-6 bg-white/95 backdrop-blur-md rounded-2xl p-4 shadow-lg border border-gray-300 flex flex-wrap items-center justify-between gap-3 transition-all">
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2">
                    <template x-if="isSaving">
                        <span class="inline-flex items-center gap-1.5 text-xs text-amber-700 font-bold">
                            <svg class="animate-spin h-4 w-4 text-amber-600" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            Guardando automáticamente...
                        </span>
                    </template>
                    <template x-if="!isSaving && lastSaved">
                        <span class="inline-flex items-center gap-1.5 text-xs text-emerald-800 font-bold">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span x-text="`Guardado a las ${lastSaved}`"></span>
                        </span>
                    </template>
                    <template x-if="!isSaving && !lastSaved">
                        <span class="text-xs text-gray-600 font-semibold">Autoguardado activado</span>
                    </template>
                </div>

                <div class="h-4 w-px bg-gray-300 hidden sm:block"></div>

                <!-- CONTADOR DE CAMPOS PENDIENTES -->
                <div>
                    <template x-if="camposPendientesCount > 0">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-amber-100 text-black border border-amber-300">
                            <svg class="w-3.5 h-3.5 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>Quedan <strong class="text-black underline font-black" x-text="camposPendientesCount"></strong> campos obligatorios pendientes</span>
                        </span>
                    </template>
                    <template x-if="camposPendientesCount === 0">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-emerald-100 text-black border border-emerald-300">
                            <svg class="w-3.5 h-3.5 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>Todos los campos obligatorios completos</span>
                        </span>
                    </template>
                </div>
            </div>

            <!-- BOTONES DE GUARDADO -->
            <div class="flex items-center gap-2.5">
                <button type="button" @click="guardarProgresoManual()" :disabled="isSaving"
                        class="px-4 py-2 bg-white border border-gray-400 text-black hover:bg-gray-100 rounded-xl text-xs font-black shadow-sm transition flex items-center gap-1.5 disabled:opacity-50">
                    <svg class="w-4 h-4 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                    </svg>
                    <span>Guardar Progreso</span>
                </button>

                <button type="button" @click="registrarYCerrar()" :disabled="isSaving || isClosing"
                        :class="camposPendientesCount === 0 ? 'bg-black text-white hover:bg-gray-800 shadow-md border border-black' : 'bg-gray-200 text-gray-500 border border-gray-300 cursor-not-allowed'"
                        class="px-4 py-2 rounded-xl text-xs font-black shadow transition flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span x-text="isClosing ? 'Registrando...' : 'Registrar y Cerrar'"></span>
                </button>
            </div>
        </div>

        <form @submit.prevent="registrarYCerrar()" class="space-y-6">

            <!-- 1. ENCABEZADO Y BUSCADOR DE ESTUDIANTE EXISTENTE (REINSCRIPCIÓN / PASO DE GRADO) -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border-2 border-gray-300">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-4 border-b border-gray-200">
                    <div>
                        <h3 class="text-base font-black text-black flex items-center gap-2">
                            <span>Nivel y Grado de Postulación o Continuidad</span>
                        </h3>
                        <p class="text-xs text-gray-600 mt-0.5">Seleccione el nivel y grado para el cual se realiza la entrevista.</p>
                    </div>

                    <!-- SELECTOR DE NIVEL Y AÑO -->
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="flex items-center bg-gray-100 p-1 rounded-xl border border-gray-300">
                            <button type="button" @click="cambiarNivel('primaria')"
                                    :class="nivel === 'primaria' ? 'bg-black text-white font-black shadow-sm' : 'text-gray-700 hover:text-black font-bold'"
                                    class="px-4 py-1.5 rounded-lg text-xs transition">
                                Primaria
                            </button>
                            <button type="button" @click="cambiarNivel('secundaria')"
                                    :class="nivel === 'secundaria' ? 'bg-black text-white font-black shadow-sm' : 'text-gray-700 hover:text-black font-bold'"
                                    class="px-4 py-1.5 rounded-lg text-xs transition">
                                Secundaria
                            </button>
                        </div>

                        <div>
                            <select x-model="gradoPostula" @change="triggerAutosave()"
                                    class="text-xs font-black rounded-xl border-gray-400 text-black focus:border-black focus:ring-black py-1.5">
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

                        <div class="flex items-center gap-1.5 bg-white px-3 py-1.5 rounded-xl border border-gray-400 text-xs text-black">
                            <span class="font-black text-black">Año:</span>
                            <input type="number" x-model.number="anoLectivo" @change="triggerAutosave()" class="w-16 text-xs p-0 border-0 font-black text-black focus:ring-0">
                        </div>
                    </div>
                </div>

                <!-- BUSCADOR EN VIVO DE ESTUDIANTE EXISTENTE (PARA ARRASTRAR DATOS) -->
                <div class="mt-4 pt-2 bg-gray-50 p-4 rounded-xl border border-gray-300">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 mb-2">
                        <label class="text-xs font-black text-black flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            <span>¿EL ESTUDIANTE YA ESTUDIA O ESTUDIÓ EN LA INSTITUCIÓN? (PASO DE GRADO O TRASLADO INTERNO)</span>
                        </label>
                        <span class="text-[11px] text-gray-600 font-semibold">Escriba DNI o Apellidos para autocompletar</span>
                    </div>

                    <div class="relative" @click.away="searchEstudianteResults = []">
                        <input type="text" x-model="searchEstudianteQuery" @input.debounce.300ms="buscarEstudianteExistente()"
                               placeholder="Buscar por DNI o Apellidos del estudiante registrado..."
                               class="w-full text-xs rounded-xl border-gray-400 focus:border-black focus:ring-black bg-white py-2.5 px-3 shadow-sm font-semibold text-black">
                        
                        <!-- LISTA DE RESULTADOS DE BÚSQUEDA -->
                        <div x-show="searchEstudianteResults.length > 0" x-cloak
                             class="absolute left-0 right-0 top-full mt-1 bg-white rounded-xl shadow-xl border-2 border-gray-400 max-h-60 overflow-y-auto z-50 divide-y divide-gray-200">
                            <template x-for="est in searchEstudianteResults" :key="est.id">
                                <button type="button" @click="cargarDatosEstudianteExistente(est)"
                                        class="w-full text-left p-3 hover:bg-gray-100 transition flex items-center justify-between group">
                                    <div>
                                        <div class="font-black text-xs text-black group-hover:underline" x-text="est.nombre_completo"></div>
                                        <div class="text-[11px] text-gray-600 flex items-center gap-2 mt-0.5">
                                            <span>DNI: <strong class="text-black font-black" x-text="est.dni"></strong></span>
                                            <span class="text-gray-400">·</span>
                                            <span>Grado actual: <strong class="text-black" x-text="est.grado_actual || 'Sin matrícula'"></strong></span>
                                            <span class="text-gray-400">·</span>
                                            <span class="px-2 py-0.5 rounded font-black text-[10px] border"
                                                  :class="est.sexo === 'M' ? 'bg-blue-50 text-blue-900 border-blue-300' : 'bg-pink-50 text-pink-900 border-pink-300'"
                                                  x-text="est.sexo === 'M' ? 'Masculino' : (est.sexo === 'F' ? 'Femenino' : 'Sexo s/d')"></span>
                                        </div>
                                    </div>
                                    <span class="px-3 py-1 bg-black text-white rounded-lg text-xs font-black group-hover:bg-gray-800 transition">
                                        Cargar Datos →
                                    </span>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. IDENTIFICACIÓN DEL ESTUDIANTE -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border-2 border-gray-300">
                <h3 class="text-sm font-black text-black uppercase tracking-wider mb-4 flex items-center gap-2 border-b-2 border-gray-200 pb-2">
                    <span class="w-6 h-6 rounded-lg bg-black text-white flex items-center justify-center text-xs font-black">1</span>
                    <span>Datos de Identificación del Estudiante</span>
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    <!-- DNI -->
                    <div>
                        <label class="block text-xs font-black text-black uppercase mb-1">
                            D.N.I. del Estudiante <span class="text-rose-600 font-black">*</span>
                        </label>
                        <input type="text" x-model="estudianteDni" @input="triggerAutosave()" maxlength="8"
                               placeholder="Ej.: 71234567"
                               class="w-full text-xs rounded-xl border-gray-400 focus:border-black focus:ring-black font-mono font-black text-black tracking-wider">
                        <span x-show="estudianteDni.length > 0 && estudianteDni.length < 8" class="text-[10px] text-amber-700 font-bold mt-0.5 block">
                            Debe tener 8 dígitos
                        </span>
                    </div>

                    <!-- GÉNERO / SEXO (CONDICIONAL DE NORMAS) -->
                    <div>
                        <label class="block text-xs font-black text-black uppercase mb-1">
                            Género / Sexo <span class="text-rose-600 font-black">*</span>
                        </label>
                        <div class="grid grid-cols-2 gap-2">
                            <button type="button" @click="setSexo('M')"
                                    :class="estudianteSexo === 'M' ? 'bg-black text-white font-black shadow-md border-black' : 'bg-gray-100 text-black border-gray-300 font-bold hover:bg-gray-200'"
                                    class="py-2 px-2 rounded-xl text-xs border-2 transition flex items-center justify-center gap-1.5">
                                <span>Masculino (M)</span>
                            </button>
                            <button type="button" @click="setSexo('F')"
                                    :class="estudianteSexo === 'F' ? 'bg-black text-white font-black shadow-md border-black' : 'bg-gray-100 text-black border-gray-300 font-bold hover:bg-gray-200'"
                                    class="py-2 px-2 rounded-xl text-xs border-2 transition flex items-center justify-center gap-1.5">
                                <span>Femenino (F)</span>
                            </button>
                        </div>
                    </div>

                    <!-- Nombres -->
                    <div class="md:col-span-2">
                        <label class="block text-xs font-black text-black uppercase mb-1">
                            Nombres del Estudiante <span class="text-rose-600 font-black">*</span>
                        </label>
                        <input type="text" x-model="estudianteNombres" @input="triggerAutosave()"
                               placeholder="Ej.: Lucía Fernanda"
                               class="w-full text-xs rounded-xl border-gray-400 focus:border-black focus:ring-black uppercase font-bold text-black">
                    </div>

                    <!-- Apellido Paterno -->
                    <div>
                        <label class="block text-xs font-black text-black uppercase mb-1">
                            Apellido Paterno <span class="text-rose-600 font-black">*</span>
                        </label>
                        <input type="text" x-model="estudianteApellidoPaterno" @input="triggerAutosave()"
                               placeholder="Ej.: Pérez"
                               class="w-full text-xs rounded-xl border-gray-400 focus:border-black focus:ring-black uppercase font-bold text-black">
                    </div>

                    <!-- Apellido Materno -->
                    <div>
                        <label class="block text-xs font-black text-black uppercase mb-1">
                            Apellido Materno
                        </label>
                        <input type="text" x-model="estudianteApellidoMaterno" @input="triggerAutosave()"
                               placeholder="Ej.: Rojas"
                               class="w-full text-xs rounded-xl border-gray-400 focus:border-black focus:ring-black uppercase font-bold text-black">
                    </div>

                    <!-- Fecha de Nacimiento -->
                    <div>
                        <label class="block text-xs font-black text-black uppercase mb-1">
                            Fecha de Nacimiento
                        </label>
                        <input type="date" x-model="estudianteFechaNacimiento" @change="triggerAutosave()"
                               class="w-full text-xs rounded-xl border-gray-400 focus:border-black focus:ring-black font-semibold text-black">
                    </div>

                    <!-- Colegio de Procedencia -->
                    <div>
                        <label class="block text-xs font-black text-black uppercase mb-1">
                            Colegio de Procedencia
                        </label>
                        <input type="text" x-model="colegioProcedencia" @input="triggerAutosave()"
                               placeholder="Ej.: I.E.P. Santísima Cruz"
                               class="w-full text-xs rounded-xl border-gray-400 focus:border-black focus:ring-black font-semibold text-black">
                    </div>
                </div>
            </div>

            <!-- 3. DATOS DE LOS PADRES Y APODERADO -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border-2 border-gray-300">
                <h3 class="text-sm font-black text-black uppercase tracking-wider mb-4 flex items-center justify-between border-b-2 border-gray-200 pb-2">
                    <div class="flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-black text-white flex items-center justify-center text-xs font-black">2</span>
                        <span>Datos de los Padres de Familia y Apoderado</span>
                    </div>
                    <span class="text-[11px] font-bold text-black">Complete al menos uno de los progenitores o apoderado</span>
                </h3>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- PADRE -->
                    <div class="p-4 rounded-xl bg-gray-50 border-2 border-gray-300 space-y-3">
                        <div class="font-black text-xs text-black uppercase flex items-center gap-1.5 border-b border-gray-300 pb-1.5">
                            <span>Datos del Padre</span>
                        </div>
                        <div>
                            <label class="block text-[11px] font-black text-black mb-0.5">DNI del Padre</label>
                            <input type="text" x-model="padreDni" @input="triggerAutosave()" maxlength="8" placeholder="8 dígitos"
                                   class="w-full text-xs rounded-lg border-gray-400 focus:ring-black focus:border-black text-black font-mono">
                        </div>
                        <div>
                            <label class="block text-[11px] font-black text-black mb-0.5">Nombres y Apellidos</label>
                            <input type="text" x-model="padreNombres" @input="triggerAutosave()" placeholder="Ej.: Juan Pérez Díaz"
                                   class="w-full text-xs rounded-lg border-gray-400 focus:ring-black focus:border-black uppercase font-bold text-black">
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[11px] font-black text-black mb-0.5">Teléfono</label>
                                <input type="text" x-model="padreTelefono" @input="triggerAutosave()" placeholder="Ej.: 987654321"
                                       class="w-full text-xs rounded-lg border-gray-400 focus:ring-black focus:border-black text-black font-semibold">
                            </div>
                            <div>
                                <label class="block text-[11px] font-black text-black mb-0.5">Ocupación</label>
                                <input type="text" x-model="padreOcupacion" @input="triggerAutosave()" placeholder="Ej.: Agricultor"
                                       class="w-full text-xs rounded-lg border-gray-400 focus:ring-black focus:border-black uppercase text-black font-semibold">
                            </div>
                        </div>
                        <div>
                            <label class="block text-[11px] font-black text-black mb-0.5">Grado de Estudios</label>
                            <select x-model="padreGradoEstudios" @change="triggerAutosave()" class="w-full text-xs rounded-lg border-gray-400 focus:ring-black focus:border-black text-black font-semibold">
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
                            <label class="block text-[11px] font-black text-black mb-0.5">Domicilio</label>
                            <input type="text" x-model="padreDomicilio" @input="triggerAutosave()" placeholder="Ej.: Av. Grau 123"
                                   class="w-full text-xs rounded-lg border-gray-400 focus:ring-black focus:border-black text-black font-semibold">
                        </div>
                    </div>

                    <!-- MADRE -->
                    <div class="p-4 rounded-xl bg-gray-50 border-2 border-gray-300 space-y-3">
                        <div class="font-black text-xs text-black uppercase flex items-center justify-between border-b border-gray-300 pb-1.5">
                            <span>Datos de la Madre</span>
                            <button type="button" @click="madreDomicilio = padreDomicilio; triggerAutosave()" 
                                    class="text-[10px] text-black font-black underline">
                                Mismo domicilio que padre
                            </button>
                        </div>
                        <div>
                            <label class="block text-[11px] font-black text-black mb-0.5">DNI de la Madre</label>
                            <input type="text" x-model="madreDni" @input="triggerAutosave()" maxlength="8" placeholder="8 dígitos"
                                   class="w-full text-xs rounded-lg border-gray-400 focus:ring-black focus:border-black text-black font-mono">
                        </div>
                        <div>
                            <label class="block text-[11px] font-black text-black mb-0.5">Nombres y Apellidos</label>
                            <input type="text" x-model="madreNombres" @input="triggerAutosave()" placeholder="Ej.: María Rojas Vega"
                                   class="w-full text-xs rounded-lg border-gray-400 focus:ring-black focus:border-black uppercase font-bold text-black">
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[11px] font-black text-black mb-0.5">Teléfono</label>
                                <input type="text" x-model="madreTelefono" @input="triggerAutosave()" placeholder="Ej.: 987654321"
                                       class="w-full text-xs rounded-lg border-gray-400 focus:ring-black focus:border-black text-black font-semibold">
                            </div>
                            <div>
                                <label class="block text-[11px] font-black text-black mb-0.5">Ocupación</label>
                                <input type="text" x-model="madreOcupacion" @input="triggerAutosave()" placeholder="Ej.: Comerciante"
                                       class="w-full text-xs rounded-lg border-gray-400 focus:ring-black focus:border-black uppercase text-black font-semibold">
                            </div>
                        </div>
                        <div>
                            <label class="block text-[11px] font-black text-black mb-0.5">Grado de Estudios</label>
                            <select x-model="madreGradoEstudios" @change="triggerAutosave()" class="w-full text-xs rounded-lg border-gray-400 focus:ring-black focus:border-black text-black font-semibold">
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
                            <label class="block text-[11px] font-black text-black mb-0.5">Domicilio</label>
                            <input type="text" x-model="madreDomicilio" @input="triggerAutosave()" placeholder="Ej.: Av. Grau 123"
                                   class="w-full text-xs rounded-lg border-gray-400 focus:ring-black focus:border-black text-black font-semibold">
                        </div>
                    </div>

                    <!-- APODERADO -->
                    <div class="p-4 rounded-xl bg-gray-50 border-2 border-gray-300 space-y-3">
                        <div class="font-black text-xs text-black uppercase flex items-center justify-between border-b border-gray-300 pb-1.5">
                            <span>Datos del Apoderado (si corresponde)</span>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[11px] font-black text-black mb-0.5">Parentesco</label>
                                <input type="text" x-model="apoderadoParentesco" @input="triggerAutosave()" placeholder="Ej.: Tía, Abuela"
                                       class="w-full text-xs rounded-lg border-gray-400 focus:ring-black focus:border-black uppercase text-black font-semibold">
                            </div>
                            <div>
                                <label class="block text-[11px] font-black text-black mb-0.5">DNI Apoderado</label>
                                <input type="text" x-model="apoderadoDni" @input="triggerAutosave()" maxlength="8" placeholder="8 dígitos"
                                       class="w-full text-xs rounded-lg border-gray-400 focus:ring-black focus:border-black text-black font-mono">
                            </div>
                        </div>
                        <div>
                            <label class="block text-[11px] font-black text-black mb-0.5">Nombres y Apellidos</label>
                            <input type="text" x-model="apoderadoNombres" @input="triggerAutosave()" placeholder="Ej.: Rosa Díaz Vega"
                                   class="w-full text-xs rounded-lg border-gray-400 focus:ring-black focus:border-black uppercase font-bold text-black">
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[11px] font-black text-black mb-0.5">Teléfono</label>
                                <input type="text" x-model="apoderadoTelefono" @input="triggerAutosave()" placeholder="Ej.: 987654321"
                                       class="w-full text-xs rounded-lg border-gray-400 focus:ring-black focus:border-black text-black font-semibold">
                            </div>
                            <div>
                                <label class="block text-[11px] font-black text-black mb-0.5">Ocupación</label>
                                <input type="text" x-model="apoderadoOcupacion" @input="triggerAutosave()" placeholder="Ej.: Docente"
                                       class="w-full text-xs rounded-lg border-gray-400 focus:ring-black focus:border-black uppercase text-black font-semibold">
                            </div>
                        </div>
                        <div>
                            <label class="block text-[11px] font-black text-black mb-0.5">Grado de Estudios</label>
                            <select x-model="apoderadoGradoEstudios" @change="triggerAutosave()" class="w-full text-xs rounded-lg border-gray-400 focus:ring-black focus:border-black text-black font-semibold">
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
                            <label class="block text-[11px] font-black text-black mb-0.5">Domicilio</label>
                            <input type="text" x-model="apoderadoDomicilio" @input="triggerAutosave()" placeholder="Ej.: Jr. Lima 456"
                                   class="w-full text-xs rounded-lg border-gray-400 focus:ring-black focus:border-black text-black font-semibold">
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. SITUACIÓN FAMILIAR Y CONVIVENCIA (PREGUNTAS 18, 19, 20) -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border-2 border-gray-300">
                <h3 class="text-sm font-black text-black uppercase tracking-wider mb-4 flex items-center gap-2 border-b-2 border-gray-200 pb-2">
                    <span class="w-6 h-6 rounded-lg bg-black text-white flex items-center justify-center text-xs font-black">3</span>
                    <span>Situación Familiar y Convivencia</span>
                </h3>

                <div class="space-y-5">
                    <!-- PREGUNTA 18: NÚMERO DE HERMANOS -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                        <div class="md:col-span-3">
                            <label class="block text-xs font-black text-black">
                                18. Número total de hermanos del estudiante
                            </label>
                            <p class="text-[11px] text-gray-600 font-semibold">Colocar 0 si es hijo único.</p>
                        </div>
                        <div>
                            <input type="number" min="0" x-model.number="respuestas.num_hermanos" @input="triggerAutosave()"
                                   class="w-full text-xs font-black text-black rounded-xl border-gray-400 focus:ring-black focus:border-black">
                        </div>
                    </div>

                    <!-- PREGUNTA 19: ¿CON QUIÉN VIVE EL ESTUDIANTE? (CHECKS MULTI-SELECCIÓN) -->
                    <div class="p-4 rounded-xl bg-gray-50 border-2 border-gray-400">
                        <label class="block text-sm font-black text-black mb-1">
                            19. PERSONAS CON QUIENES VIVE EL ESTUDIANTE <span class="text-rose-600 font-black">*</span>
                        </label>
                        <p class="text-[11px] text-black font-bold mb-3">Marque todas las opciones que correspondan para armar la lista de convivencia:</p>

                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-2.5">
                            <template x-for="item in opcionesConvivencia" :key="item.id">
                                <label class="flex items-center gap-2 p-3 rounded-xl border-2 transition cursor-pointer text-xs"
                                       :class="viveConSelected.includes(item.id) ? 'bg-black text-white border-black font-black shadow-sm' : 'bg-white border-gray-300 text-black font-black hover:bg-gray-100'">
                                    <input type="checkbox" :value="item.id" x-model="viveConSelected" @change="actualizarViveCon()"
                                           class="rounded border-gray-400 text-black focus:ring-black">
                                    <span x-text="item.label"></span>
                                </label>
                            </template>
                        </div>

                        <!-- Opción Otro especificar -->
                        <div class="mt-3" x-show="viveConSelected.includes('Otro')">
                            <label class="block text-[11px] font-black text-black mb-0.5">Especifique otros familiares o personas con quienes vive:</label>
                            <input type="text" x-model="viveConOtro" @input="actualizarViveCon()"
                                   placeholder="Ej.: Tía materna, prima hermana, bisabuela..."
                                   class="w-full text-xs rounded-lg border-gray-400 focus:ring-black focus:border-black bg-white font-bold text-black">
                        </div>
                    </div>

                    <!-- PREGUNTA 20 & TABLA DE HERMANOS EN LA IE (ARMADO DE FAMILIAS) -->
                    <div class="p-4 rounded-xl bg-gray-50 border-2 border-gray-400 space-y-3">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div>
                                <label class="block text-sm font-black text-black">
                                    20. ¿TIENE HERMANOS QUE ESTUDIAN ACTUALMENTE EN LA INSTITUCIÓN EDUCATIVA?
                                </label>
                                <p class="text-[11px] text-black font-bold">Permite asociar y unificar los registros familiares en el sistema.</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" @click="setTieneHermanos('Sí')"
                                        :class="respuestas.hermanos_en_ie === 'Sí' ? 'bg-black text-white font-black border-black' : 'bg-white text-black border border-gray-400 font-bold'"
                                        class="px-5 py-2 rounded-xl text-xs border-2 transition">
                                    Sí
                                </button>
                                <button type="button" @click="setTieneHermanos('No')"
                                        :class="respuestas.hermanos_en_ie === 'No' ? 'bg-black text-white font-black border-black' : 'bg-white text-black border border-gray-400 font-bold'"
                                        class="px-5 py-2 rounded-xl text-xs border-2 transition">
                                    No
                                </button>
                            </div>
                        </div>

                        <!-- PANEL DINÁMICO DE HERMANOS -->
                        <div x-show="respuestas.hermanos_en_ie === 'Sí'" x-cloak class="mt-3 pt-3 border-t-2 border-gray-300 space-y-3">
                            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                                <div class="relative flex-1" @click.away="searchHermanoResults = []">
                                    <input type="text" x-model="searchHermanoQuery" @input.debounce.300ms="buscarHermano()"
                                           placeholder="Buscar hermano(a) por DNI o Nombres..."
                                           class="w-full text-xs rounded-lg border-gray-400 focus:ring-black focus:border-black bg-white py-2 px-3 font-semibold text-black">

                                    <div x-show="searchHermanoResults.length > 0" x-cloak
                                         class="absolute left-0 right-0 top-full mt-1 bg-white rounded-lg shadow-lg border-2 border-gray-400 max-h-48 overflow-y-auto z-50 divide-y divide-gray-200">
                                        <template x-for="h in searchHermanoResults" :key="h.id">
                                            <button type="button" @click="anadirHermanoDesdeBusqueda(h)"
                                                    class="w-full text-left p-2.5 hover:bg-gray-100 text-xs flex items-center justify-between">
                                                <div>
                                                    <span class="font-black text-black" x-text="h.nombre_completo"></span>
                                                    <span class="text-gray-700 font-bold text-[11px]" x-text="` (DNI: ${h.dni} · ${h.grado_actual || 'Sin aula'})`"></span>
                                                </div>
                                                <span class="text-[11px] text-black font-black underline">+ Añadir</span>
                                            </button>
                                        </template>
                                    </div>
                                </div>

                                <button type="button" @click="anadirHermanoManual()"
                                        class="px-4 py-2 bg-black text-white rounded-lg text-xs font-black hover:bg-gray-800 transition flex items-center justify-center gap-1">
                                    <span>+ Añadir Manualmente</span>
                                </button>
                            </div>

                            <!-- TABLA DE HERMANOS REGISTRADOS -->
                            <div class="overflow-x-auto bg-white rounded-xl border-2 border-gray-300 shadow-sm">
                                <table class="w-full text-xs text-left">
                                    <thead class="bg-gray-200 text-black uppercase font-black text-[10px] border-b border-gray-300">
                                        <tr>
                                            <th class="p-2.5">DNI</th>
                                            <th class="p-2.5">Nombres y Apellidos</th>
                                            <th class="p-2.5">Grado / Nivel</th>
                                            <th class="p-2.5 text-center w-12">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        <template x-for="(hermano, index) in hermanosDatos" :key="index">
                                            <tr class="hover:bg-gray-50">
                                                <td class="p-2.5 font-mono font-black text-black" x-text="hermano.dni || 'S/D'"></td>
                                                <td class="p-2.5 font-bold uppercase text-black" x-text="hermano.nombre"></td>
                                                <td class="p-2.5 text-black font-semibold" x-text="hermano.grado || '—'"></td>
                                                <td class="p-2.5 text-center">
                                                    <button type="button" @click="eliminarHermano(index)" class="text-rose-600 hover:text-rose-900 font-black p-1" title="Quitar hermano">
                                                        <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                        </svg>
                                                    </button>
                                                </td>
                                            </tr>
                                        </template>
                                        <template x-if="hermanosDatos.length === 0">
                                            <tr>
                                                <td colspan="4" class="p-3 text-center text-gray-500 font-semibold italic">
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
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3 bg-gray-50 rounded-xl border border-gray-300">
                        <div class="text-xs text-black font-bold">
                            <strong class="font-black">23. Axiología de la IE:</strong> La axiología de la institución educativa es promover la <strong class="underline">COMUNIÓN y PARTICIPACIÓN</strong> a la luz del evangelio y de la espiritualidad Agustiniana. ¿Acepta?
                        </div>
                        <select x-model="respuestas.acuerdo_comunion" @change="triggerAutosave()" class="text-xs rounded-lg border-gray-400 font-black text-black">
                            <option value="">-- Seleccionar --</option>
                            <option value="Sí">Sí</option>
                            <option value="No">No</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- 5. VIDA DE FE Y SACRAMENTOS (PREGUNTA 27.3 MULTI-CHECK) -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border-2 border-gray-300">
                <h3 class="text-sm font-black text-black uppercase tracking-wider mb-4 flex items-center gap-2 border-b-2 border-gray-200 pb-2">
                    <span class="w-6 h-6 rounded-lg bg-black text-white flex items-center justify-center text-xs font-black">4</span>
                    <span>Vida de Fe del Estudiante y la Familia</span>
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-black text-black mb-1">24. Parroquia a la que asiste</label>
                        <input type="text" x-model="respuestas.parroquia" @input="triggerAutosave()" placeholder="Ej.: Parroquia San Juan Bautista"
                               class="w-full text-xs rounded-xl border-gray-400 focus:ring-black focus:border-black font-semibold text-black">
                    </div>
                    <div>
                        <label class="block text-xs font-black text-black mb-1">25. Comunidad zonal a la que pertenece</label>
                        <input type="text" x-model="respuestas.comunidad_zonal" @input="triggerAutosave()" placeholder="Ej.: Comunidad Santa Rosa"
                               class="w-full text-xs rounded-xl border-gray-400 focus:ring-black focus:border-black font-semibold text-black">
                    </div>
                    <div>
                        <label class="block text-xs font-black text-black mb-1">26.1 Bautizo celebrado</label>
                        <select x-model="respuestas.sacramento_bautizo" @change="triggerAutosave()" class="w-full text-xs rounded-xl border-gray-400 focus:ring-black font-black text-black">
                            <option value="">-- Seleccionar --</option>
                            <option value="Sí">Sí</option>
                            <option value="No">No</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-black text-black mb-1">26.2 Eucaristía / Primera Comunión</label>
                        <select x-model="respuestas.sacramento_eucaristia" @change="triggerAutosave()" class="w-full text-xs rounded-xl border-gray-400 focus:ring-black font-black text-black">
                            <option value="">-- Seleccionar --</option>
                            <option value="Sí">Sí</option>
                            <option value="No">No</option>
                        </select>
                    </div>

                    <!-- SACRAMENTO CONFIRMACIÓN (SOLO SECUNDARIA) -->
                    <div x-show="nivel === 'secundaria'">
                        <label class="block text-xs font-black text-black mb-1">26.3 Sacramento de Confirmación</label>
                        <select x-model="respuestas.sacramento_confirmacion" @change="triggerAutosave()" class="w-full text-xs rounded-xl border-gray-400 focus:ring-black font-black text-black">
                            <option value="">-- Seleccionar --</option>
                            <option value="Sí">Sí</option>
                            <option value="No">No</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-black text-black mb-1">27.1 ¿Considera importante la Eucaristía?</label>
                        <select x-model="respuestas.eucaristia_importante" @change="triggerAutosave()" class="w-full text-xs rounded-xl border-gray-400 focus:ring-black font-black text-black">
                            <option value="">-- Seleccionar --</option>
                            <option value="Sí">Sí</option>
                            <option value="No">No</option>
                        </select>
                    </div>

                    <div class="md:col-span-3">
                        <label class="block text-xs font-black text-black mb-1">27.2 ¿Por qué?</label>
                        <input type="text" x-model="respuestas.eucaristia_por_que" @input="triggerAutosave()" placeholder="Ej.: Fortalece la fe y la unión familiar..."
                               class="w-full text-xs rounded-xl border-gray-400 focus:ring-black focus:border-black font-semibold text-black">
                    </div>

                    <!-- PREGUNTA 27.3: DÍAS DE EUCARISTÍA (MULTI-CHECK) -->
                    <div class="md:col-span-3 p-4 rounded-xl bg-gray-50 border-2 border-gray-400">
                        <label class="block text-sm font-black text-black mb-1">
                            27.3 ¿QUÉ DÍA(S) PARTICIPA EN LA EUCARISTÍA? (SELECCIÓN MÚLTIPLE)
                        </label>
                        <p class="text-[11px] text-black font-bold mb-3">Marque todos los días en que la familia acude habitualmente:</p>
                        
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                            <template x-for="dia in opcionesEucaristiaDias" :key="dia">
                                <label class="flex items-center gap-2 p-3 rounded-xl border-2 transition cursor-pointer text-xs"
                                       :class="eucaristiaDiasSelected.includes(dia) ? 'bg-black text-white border-black font-black shadow-sm' : 'bg-white border-gray-300 text-black font-black hover:bg-gray-100'">
                                    <input type="checkbox" :value="dia" x-model="eucaristiaDiasSelected" @change="actualizarEucaristiaDia()"
                                           class="rounded border-gray-400 text-black focus:ring-black">
                                    <span x-text="dia"></span>
                                </label>
                            </template>
                        </div>

                        <div class="mt-2.5" x-show="eucaristiaDiasSelected.includes('Otro')">
                            <input type="text" x-model="eucaristiaDiaOtro" @input="actualizarEucaristiaDia()"
                                   placeholder="Especifique otros días o celebraciones..."
                                   class="w-full text-xs rounded-lg border-gray-400 focus:ring-black bg-white font-bold text-black">
                        </div>
                    </div>
                </div>
            </div>

            <!-- 6. SITUACIÓN CONYUGAL Y SALUD -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border-2 border-gray-300">
                <h3 class="text-sm font-black text-black uppercase tracking-wider mb-4 flex items-center gap-2 border-b-2 border-gray-200 pb-2">
                    <span class="w-6 h-6 rounded-lg bg-black text-white flex items-center justify-center text-xs font-black">5</span>
                    <span>Situación Conyugal y Salud del Estudiante</span>
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-black text-black mb-1">28. ¿Padres Casados?</label>
                        <select x-model="respuestas.padres_casados" @change="triggerAutosave()" class="w-full text-xs rounded-xl border-gray-400 font-black text-black">
                            <option value="">-- Seleccionar --</option>
                            <option value="Sí">Sí</option>
                            <option value="No">No</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-black text-black mb-1">28.1 Estado Conyugal</label>
                        <select x-model="respuestas.estado_conyugal" @change="triggerAutosave()" class="w-full text-xs rounded-xl border-gray-400 font-black text-black">
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
                        <label class="block text-xs font-black text-black mb-1">29. ¿Padece alguna enfermedad?</label>
                        <select x-model="respuestas.enfermedad" @change="triggerAutosave()" class="w-full text-xs rounded-xl border-gray-400 font-black text-black">
                            <option value="">-- Seleccionar --</option>
                            <option value="No">No</option>
                            <option value="Sí">Sí</option>
                        </select>
                    </div>

                    <div x-show="respuestas.enfermedad === 'Sí'" class="md:col-span-3 grid grid-cols-1 sm:grid-cols-2 gap-4 p-3 bg-gray-50 rounded-xl border-2 border-gray-400">
                        <div>
                            <label class="block text-xs font-black text-black mb-1">30. ¿Conoce el diagnóstico?</label>
                            <select x-model="respuestas.conoce_diagnostico" @change="triggerAutosave()" class="w-full text-xs rounded-xl border-gray-400 font-black text-black">
                                <option value="">-- Seleccionar --</option>
                                <option value="Sí">Sí</option>
                                <option value="No">No</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-black text-black mb-1">31. ¿Cuál es la condición / diagnóstico?</label>
                            <input type="text" x-model="respuestas.diagnostico_cual" @input="triggerAutosave()" placeholder="Detallar diagnóstico..."
                                   class="w-full text-xs rounded-xl border-gray-400 font-semibold text-black">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-black text-black mb-1">32. Patria Potestad (en padres separados)</label>
                        <input type="text" x-model="respuestas.patria_potestad" @input="triggerAutosave()" placeholder="Ej.: Ambos padres / Solo mamá"
                               class="w-full text-xs rounded-xl border-gray-400 font-semibold text-black">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-black text-black mb-1">33. Persona sin patria potestad dispuesta a apoyar</label>
                        <input type="text" x-model="respuestas.persona_dispuesta" @input="triggerAutosave()" placeholder="Ej.: Sí, apoya económicamente y en visitas"
                               class="w-full text-xs rounded-xl border-gray-400 font-semibold text-black">
                    </div>
                </div>
            </div>

            <!-- 7. CUMPLIMIENTO DE NORMAS (CONDICIONADO POR SEXO: CORTE CABELLO VS TRENZAS) -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border-2 border-gray-300">
                <h3 class="text-sm font-black text-black uppercase tracking-wider mb-4 flex items-center justify-between border-b-2 border-gray-200 pb-2">
                    <div class="flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-black text-white flex items-center justify-center text-xs font-black">6</span>
                        <span>Cumplimiento de Normas Institucionales</span>
                    </div>
                    <span class="text-xs px-3 py-1 rounded-full font-black border-2 border-black"
                          :class="estudianteSexo === 'M' ? 'bg-blue-100 text-black' : (estudianteSexo === 'F' ? 'bg-pink-100 text-black' : 'bg-gray-100 text-black')"
                          x-text="estudianteSexo === 'M' ? 'NORMAS APLICABLES: ESTUDIANTE MASCULINO' : (estudianteSexo === 'F' ? 'NORMAS APLICABLES: ESTUDIANTE FEMENINA' : 'ATENCIÓN: SELECCIONE EL SEXO DEL ESTUDIANTE')">
                    </span>
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- HORARIO DE ENTRADA -->
                    <div class="p-3 rounded-xl border-2 border-gray-300 flex items-center justify-between">
                        <span class="text-xs text-black">
                            <strong class="font-black text-black">Horario de entrada:</strong> Ingreso a la IE es 6:45 am. Puerta se cierra 7:00 am.
                        </span>
                        <select x-model="respuestas.norma_horario" @change="triggerAutosave()" class="text-xs rounded-lg border-gray-400 font-black text-black ml-2">
                            <option value="">-- Seleccionar --</option>
                            <option value="Sí">Sí</option>
                            <option value="No">No</option>
                        </select>
                    </div>

                    <!-- CORTE DE CABELLO ESCOLAR (HABILITADO SOLO PARA MASCULINO) -->
                    <div class="p-3 rounded-xl border-2 transition"
                         :class="estudianteSexo === 'M' ? 'border-black bg-white' : 'border-gray-200 bg-gray-100 opacity-60'">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-xs font-black text-black">
                                    Corte de cabello escolar sin modas ni cortes en las cejas
                                </span>
                                <template x-if="estudianteSexo === 'F'">
                                    <span class="block text-[11px] text-gray-700 font-bold mt-0.5">
                                        No aplica (Estudiante femenina)
                                    </span>
                                </template>
                            </div>
                            <select x-model="respuestas.norma_corte_cabello" @change="triggerAutosave()"
                                    :disabled="estudianteSexo === 'F'"
                                    class="text-xs rounded-lg border-gray-400 font-black text-black ml-2">
                                <option value="">-- Seleccionar --</option>
                                <option value="Sí">Sí</option>
                                <option value="No">No</option>
                                <option value="No aplica">No aplica</option>
                            </select>
                        </div>
                    </div>

                    <!-- CABELLO TRENZADO (HABILITADO SOLO PARA FEMENINO) -->
                    <div class="p-3 rounded-xl border-2 transition"
                         :class="estudianteSexo === 'F' ? 'border-black bg-white' : 'border-gray-200 bg-gray-100 opacity-60'">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-xs font-black text-black">
                                    Cabello trenzado, sin maquillaje ni accesorios ni maquillaje
                                </span>
                                <template x-if="estudianteSexo === 'M'">
                                    <span class="block text-[11px] text-gray-700 font-bold mt-0.5">
                                        No aplica (Estudiante masculino)
                                    </span>
                                </template>
                            </div>
                            <select x-model="respuestas.norma_cabello_trenzado" @change="triggerAutosave()"
                                    :disabled="estudianteSexo === 'M'"
                                    class="text-xs rounded-lg border-gray-400 font-black text-black ml-2">
                                <option value="">-- Seleccionar --</option>
                                <option value="Sí">Sí</option>
                                <option value="No">No</option>
                                <option value="No aplica">No aplica</option>
                            </select>
                        </div>
                    </div>

                    <!-- UNIFORME COMPLETO -->
                    <div class="p-3 rounded-xl border-2 border-gray-300 flex items-center justify-between">
                        <span class="text-xs text-black">
                            <strong class="font-black text-black">Uso de uniforme completo</strong> según indicación en el RI.
                        </span>
                        <select x-model="respuestas.norma_uniforme" @change="triggerAutosave()" class="text-xs rounded-lg border-gray-400 font-black text-black ml-2">
                            <option value="">-- Seleccionar --</option>
                            <option value="Sí">Sí</option>
                            <option value="No">No</option>
                        </select>
                    </div>

                    <!-- ASISTENCIA Y PUNTUALIDAD -->
                    <div class="p-3 rounded-xl border-2 border-gray-300 flex items-center justify-between">
                        <span class="text-xs text-black">
                            <strong class="font-black text-black">Asistencia y puntualidad</strong> diaria según horario establecido.
                        </span>
                        <select x-model="respuestas.norma_asistencia" @change="triggerAutosave()" class="text-xs rounded-lg border-gray-400 font-black text-black ml-2">
                            <option value="">-- Seleccionar --</option>
                            <option value="Sí">Sí</option>
                            <option value="No">No</option>
                        </select>
                    </div>

                    <!-- COMPROMISO ECONÓMICO -->
                    <div class="p-3 rounded-xl border-2 border-gray-300 flex items-center justify-between">
                        <span class="text-xs text-black">
                            <strong class="font-black text-black">Compromiso económico de pensiones</strong> en fechas establecidas.
                        </span>
                        <select x-model="respuestas.compromiso_pago" @change="triggerAutosave()" class="text-xs rounded-lg border-gray-400 font-black text-black ml-2">
                            <option value="">-- Seleccionar --</option>
                            <option value="Sí">Sí</option>
                            <option value="No">No</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- 8. OBLIGACIONES Y AUTORIZACIONES -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border-2 border-gray-300">
                <h3 class="text-sm font-black text-black uppercase tracking-wider mb-4 flex items-center gap-2 border-b-2 border-gray-200 pb-2">
                    <span class="w-6 h-6 rounded-lg bg-black text-white flex items-center justify-center text-xs font-black">7</span>
                    <span>Obligaciones de Padres y Autorizaciones</span>
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                    <div class="p-3 bg-gray-50 rounded-xl border border-gray-300 flex items-center justify-between text-xs font-bold text-black">
                        <span>Reuniones de aula</span>
                        <select x-model="respuestas.oblig_reuniones_aula" @change="triggerAutosave()" class="rounded-lg text-xs font-black py-1 border-gray-400 text-black">
                            <option value="">-- Seleccionar --</option>
                            <option value="Sí">Sí</option>
                            <option value="No">No</option>
                        </select>
                    </div>

                    <div class="p-3 bg-gray-50 rounded-xl border border-gray-300 flex items-center justify-between text-xs font-bold text-black">
                        <span>Atención a Padres</span>
                        <select x-model="respuestas.oblig_atencion_padres" @change="triggerAutosave()" class="rounded-lg text-xs font-black py-1 border-gray-400 text-black">
                            <option value="">-- Seleccionar --</option>
                            <option value="Sí">Sí</option>
                            <option value="No">No</option>
                        </select>
                    </div>

                    <div class="p-3 bg-gray-50 rounded-xl border border-gray-300 flex items-center justify-between text-xs font-bold text-black">
                        <span>Escuela para Padres</span>
                        <select x-model="respuestas.oblig_escuela_padres" @change="triggerAutosave()" class="rounded-lg text-xs font-black py-1 border-gray-400 text-black">
                            <option value="">-- Seleccionar --</option>
                            <option value="Sí">Sí</option>
                            <option value="No">No</option>
                        </select>
                    </div>

                    <div class="p-3 bg-gray-50 rounded-xl border border-gray-300 flex items-center justify-between text-xs font-bold text-black">
                        <span>Conservación de ambientes</span>
                        <select x-model="respuestas.oblig_conservacion" @change="triggerAutosave()" class="rounded-lg text-xs font-black py-1 border-gray-400 text-black">
                            <option value="">-- Seleccionar --</option>
                            <option value="Sí">Sí</option>
                            <option value="No">No</option>
                        </select>
                    </div>

                    <div class="p-3 bg-gray-50 rounded-xl border border-gray-300 flex items-center justify-between text-xs font-bold text-black">
                        <span>Multa por inasistencia APAFA</span>
                        <select x-model="respuestas.oblig_multa_apafa" @change="triggerAutosave()" class="rounded-lg text-xs font-black py-1 border-gray-400 text-black">
                            <option value="">-- Seleccionar --</option>
                            <option value="Sí">Sí</option>
                            <option value="No">No</option>
                        </select>
                    </div>

                    <div class="p-3 bg-gray-50 rounded-xl border border-gray-300 flex items-center justify-between text-xs font-bold text-black">
                        <span>Integración de aulas</span>
                        <select x-model="respuestas.acuerdo_integracion_aulas" @change="triggerAutosave()" class="rounded-lg text-xs font-black py-1 border-gray-400 text-black">
                            <option value="">-- Seleccionar --</option>
                            <option value="Sí">Sí</option>
                            <option value="No">No</option>
                        </select>
                    </div>

                    <div class="p-3 bg-gray-50 rounded-xl border border-gray-300 flex items-center justify-between text-xs font-bold text-black">
                        <span>Comunicaciones WhatsApp / llamada</span>
                        <select x-model="respuestas.autoriza_comunicacion" @change="triggerAutosave()" class="rounded-lg text-xs font-black py-1 border-gray-400 text-black">
                            <option value="">-- Seleccionar --</option>
                            <option value="Sí">Sí</option>
                            <option value="No">No</option>
                        </select>
                    </div>

                    <div class="p-3 bg-gray-50 rounded-xl border border-gray-300 flex items-center justify-between text-xs font-bold text-black">
                        <span>Uso de fotos y videos</span>
                        <select x-model="respuestas.autoriza_fotos_videos" @change="triggerAutosave()" class="rounded-lg text-xs font-black py-1 border-gray-400 text-black">
                            <option value="">-- Seleccionar --</option>
                            <option value="Sí">Sí</option>
                            <option value="No">No</option>
                        </select>
                    </div>

                    <div class="p-3 bg-gray-50 rounded-xl border border-gray-300 flex items-center justify-between text-xs font-bold text-black">
                        <span>Atención médica de emergencia</span>
                        <select x-model="respuestas.autoriza_atencion_medica" @change="triggerAutosave()" class="rounded-lg text-xs font-black py-1 border-gray-400 text-black">
                            <option value="">-- Seleccionar --</option>
                            <option value="Sí">Sí</option>
                            <option value="No">No</option>
                        </select>
                    </div>
                </div>

                <!-- RETIRO DEL ESTUDIANTE -->
                <div class="mt-4 pt-4 border-t-2 border-gray-200 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-black text-black mb-1">¿Autoriza que regrese solo a casa?</label>
                        <select x-model="respuestas.autoriza_regresa_solo" @change="triggerAutosave()" class="w-full text-xs rounded-xl border-gray-400 font-black text-black">
                            <option value="">-- Seleccionar --</option>
                            <option value="No">No (Será recogido)</option>
                            <option value="Sí">Sí (Regresa solo)</option>
                        </select>
                    </div>
                    <div x-show="respuestas.autoriza_regresa_solo === 'No'">
                        <label class="block text-xs font-black text-black mb-1">Nombre de quien recoge</label>
                        <input type="text" x-model="respuestas.recoge_nombre" @input="triggerAutosave()" placeholder="Ej.: Rosa Díaz"
                               class="w-full text-xs rounded-xl border-gray-400 font-bold text-black">
                    </div>
                    <div x-show="respuestas.autoriza_regresa_solo === 'No'">
                        <label class="block text-xs font-black text-black mb-1">DNI de quien recoge</label>
                        <input type="text" x-model="respuestas.recoge_dni" @input="triggerAutosave()" maxlength="8" placeholder="8 dígitos"
                               class="w-full text-xs rounded-xl border-gray-400 font-mono font-bold text-black">
                    </div>
                </div>
            </div>

            <!-- 9. CONFORMIDAD FINAL Y CIERRE -->
            <div class="bg-black text-white rounded-2xl p-6 shadow-xl border-2 border-black">
                <h3 class="text-sm font-black uppercase tracking-wider mb-4 flex items-center gap-2 border-b border-gray-700 pb-2">
                    <span class="w-6 h-6 rounded-lg bg-white text-black flex items-center justify-center text-xs font-black">8</span>
                    <span>Conformidad de Entrevista y Cierre</span>
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-xs font-black uppercase tracking-wider mb-2 text-white">
                            RESULTADO DE CONFORMIDAD <span class="text-rose-400">*</span>
                        </label>
                        <div class="grid grid-cols-2 gap-2">
                            <button type="button" @click="marcarConformidad('CONFORME')"
                                    :title="puedeDarConformidad ? 'Marcar entrevista como CONFORME' : 'Bloqueado: debe completar todos los campos obligatorios antes de dar conformidad'"
                                    :class="conformidad === 'CONFORME' ? 'bg-emerald-500 text-black shadow-lg font-black border-2 border-white' : (puedeDarConformidad ? 'bg-gray-800 text-emerald-400 border-2 border-emerald-600 hover:bg-gray-700 font-black' : 'bg-gray-900 text-gray-500 border-2 border-gray-800 opacity-60 cursor-not-allowed font-bold')"
                                    class="py-3 px-3 rounded-xl text-xs border transition text-center uppercase">
                                CONFORME
                            </button>
                            <button type="button" @click="marcarConformidad('NO CONFORME')"
                                    :class="conformidad === 'NO CONFORME' ? 'bg-rose-500 text-white shadow-lg font-black border-2 border-white' : 'bg-gray-800 text-gray-300 border-2 border-gray-700 font-bold hover:bg-gray-700'"
                                    class="py-3 px-3 rounded-xl text-xs border transition text-center uppercase">
                                NO CONFORME
                            </button>
                        </div>
                        <template x-if="!puedeDarConformidad">
                            <p class="text-[11px] text-amber-400 font-semibold mt-2">
                                Para marcar CONFORME debe responder todos los campos obligatorios pendientes (<span x-text="camposFaltantesSinConformidadCount"></span> faltantes).
                            </p>
                        </template>
                    </div>

                    <div>
                        <label class="block text-xs font-black uppercase tracking-wider mb-2 text-white">Fecha de Entrevista</label>
                        <input type="date" x-model="fechaEntrevista" @change="triggerAutosave()"
                               class="w-full text-xs rounded-xl border-gray-700 bg-gray-900 text-white font-bold focus:border-white focus:ring-white">
                    </div>

                    <div>
                        <label class="block text-xs font-black uppercase tracking-wider mb-2 text-white">Docente Entrevistador a Cargo</label>
                        <div class="p-2.5 bg-gray-900 rounded-xl border border-gray-700 text-xs text-white font-bold">
                            <strong>{{ Auth::user()->name }}</strong>
                        </div>
                    </div>

                    <div class="md:col-span-3">
                        <label class="block text-xs font-black uppercase tracking-wider mb-2 text-white">Observaciones Adicionales de la Entrevista</label>
                        <textarea x-model="observaciones" @input="triggerAutosave()" rows="2"
                                  placeholder="Notas o acuerdos particulares con la familia..."
                                  class="w-full text-xs rounded-xl border-gray-700 bg-gray-900 text-white font-semibold focus:border-white focus:ring-white"></textarea>
                    </div>
                </div>

                <!-- BOTONES DE ACCIÓN FINAL -->
                <div class="mt-6 pt-4 border-t border-gray-800 flex flex-wrap items-center justify-between gap-4">
                    <span class="text-xs text-gray-400 font-medium">
                        * Recuerde que puede guardar el progreso en cualquier momento y cerrar la ficha cuando esté completa.
                    </span>

                    <div class="flex items-center gap-3">
                        <button type="button" @click="guardarProgresoManual()" :disabled="isSaving"
                                class="px-5 py-2.5 bg-gray-800 hover:bg-gray-700 border border-gray-600 text-white rounded-xl text-xs font-black transition shadow-sm">
                            Guardar Progreso
                        </button>

                        <button type="button" @click="registrarYCerrar()" :disabled="isSaving || isClosing"
                                :class="camposPendientesCount === 0 ? 'bg-white hover:bg-gray-100 text-black shadow-lg font-black border-2 border-white' : 'bg-gray-800 text-gray-500 cursor-not-allowed border border-gray-700'"
                                class="px-6 py-2.5 rounded-xl text-xs font-black transition flex items-center gap-2">
                            <span>Registrar y Cerrar Entrevista</span>
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
                num_hermanos: '',
                vive_con: '',
                hermanos_en_ie: '',
                hermanos_en_ie_cantidad: '',
                hermanos_en_ie_nombres: '',
                acuerdo_comunion: '',
                parroquia: '',
                comunidad_zonal: '',
                sacramento_bautizo: '',
                sacramento_eucaristia: '',
                sacramento_confirmacion: '',
                eucaristia_importante: '',
                eucaristia_por_que: '',
                eucaristia_dia: '',
                padres_casados: '',
                estado_conyugal: '',
                enfermedad: '',
                conoce_diagnostico: '',
                diagnostico_cual: '',
                patria_potestad: '',
                persona_dispuesta: '',
                oblig_reuniones_aula: '',
                oblig_atencion_padres: '',
                oblig_escuela_padres: '',
                oblig_conservacion: '',
                oblig_actividades_ie: '',
                oblig_multa_apafa: '',
                norma_horario: '',
                norma_corte_cabello: '',
                norma_cabello_trenzado: '',
                norma_uniforme: '',
                norma_asistencia: '',
                compromiso_pago: '',
                autoriza_comunicacion: '',
                autoriza_fotos_videos: '',
                autoriza_actividades: '',
                autoriza_atencion_medica: '',
                autoriza_regresa_solo: '',
                recoge_nombre: '',
                recoge_dni: '',
                acuerdo_integracion_aulas: ''
            }, config.respuestas || {}),

            hermanosDatos: config.hermanosDatos || [],
            conformidad: config.conformidad || '',
            fechaEntrevista: config.fechaEntrevista,
            observaciones: config.observaciones,

            // Opciones Pregunta 19 (Con quién vive) - SIN EMOJIS
            opcionesConvivencia: [
                { id: 'Papá', label: 'Papá' },
                { id: 'Mamá', label: 'Mamá' },
                { id: 'Hermanos', label: 'Hermanos' },
                { id: 'Abuelos', label: 'Abuelos' },
                { id: 'Tíos', label: 'Tíos' },
                { id: 'Primos', label: 'Primos' },
                { id: 'Padrastro', label: 'Padrastro' },
                { id: 'Madrastra', label: 'Madrastra' },
                { id: 'Tutor legal', label: 'Tutor legal' },
                { id: 'Otro', label: 'Otro(s)' }
            ],
            viveConSelected: [],
            viveConOtro: '',

            // Opciones Pregunta 27.3 (Días Eucaristía) - SIN EMOJIS
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
                // Parsear vive_con inicial si existe
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

                // Parsear eucaristia_dia inicial si existe
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
                    if (this.respuestas.norma_corte_cabello === 'No aplica') {
                        this.respuestas.norma_corte_cabello = '';
                    }
                    this.respuestas.norma_cabello_trenzado = 'No aplica';
                } else if (sexo === 'F') {
                    if (this.respuestas.norma_cabello_trenzado === 'No aplica') {
                        this.respuestas.norma_cabello_trenzado = '';
                    }
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
                    // FILTRAR PARA NO MOSTRAR AL MISMO ESTUDIANTE QUE SE ESTÁ ENTREVISTANDO
                    const dniActual = (this.estudianteDni || '').trim();
                    this.searchHermanoResults = data.filter(h => {
                        if (dniActual && h.dni && h.dni.trim() === dniActual) {
                            return false;
                        }
                        return true;
                    });
                } catch (e) {
                    console.error('Error buscando hermanos:', e);
                }
            },

            anadirHermanoDesdeBusqueda(h) {
                // VALIDACIÓN: NO PERMITIR AÑADIRSE AL MISMO ESTUDIANTE COMO HERMANO
                const dniActual = (this.estudianteDni || '').trim();
                if (dniActual && h.dni && h.dni.trim() === dniActual) {
                    alert('No se puede agregar al mismo estudiante que se está entrevistando como su propio hermano.');
                    return;
                }

                if (this.hermanosDatos.some(x => x.dni && x.dni.trim() === h.dni.trim())) {
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
                const dni = (prompt('Ingrese DNI del hermano (opcional):') || '').trim();

                // VALIDACIÓN: NO PERMITIR DNI DEL MISMO ESTUDIANTE
                const dniActual = (this.estudianteDni || '').trim();
                if (dni && dniActual && dni === dniActual) {
                    alert('No se puede agregar al mismo estudiante como su propio hermano.');
                    return;
                }

                this.hermanosDatos.push({
                    nombre: nombre.trim().toUpperCase(),
                    grado: grado.trim(),
                    dni: dni
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

            obtenerCamposFaltantes() {
                const faltantes = [];

                // 1. Datos del Estudiante
                if (!this.estudianteDni || this.estudianteDni.length < 8) {
                    faltantes.push('DNI del Estudiante (8 dígitos)');
                }
                if (!this.estudianteNombres || !this.estudianteNombres.trim()) {
                    faltantes.push('Nombres del Estudiante');
                }
                if (!this.estudianteApellidoPaterno || !this.estudianteApellidoPaterno.trim()) {
                    faltantes.push('Apellido Paterno del Estudiante');
                }
                if (!this.estudianteSexo) {
                    faltantes.push('Género / Sexo del Estudiante');
                }
                if (!this.gradoPostula) {
                    faltantes.push('Grado al que postula');
                }

                // 2. Datos de Padres o Apoderado
                if (!this.padreNombres && !this.madreNombres && !this.apoderadoNombres) {
                    faltantes.push('Datos de al menos un Padre, Madre o Apoderado');
                }

                // 3. Dinámica Familiar
                if (!this.respuestas.vive_con || !this.respuestas.vive_con.trim()) {
                    faltantes.push('Pregunta 19: ¿Con quién vive el estudiante?');
                }
                if (!this.respuestas.hermanos_en_ie) {
                    faltantes.push('Pregunta 20: ¿Tiene hermanos en la IE?');
                } else if (this.respuestas.hermanos_en_ie === 'Sí' && this.hermanosDatos.length === 0) {
                    faltantes.push('Pregunta 20: Añadir al menos un hermano en la IE');
                }
                if (!this.respuestas.acuerdo_comunion) {
                    faltantes.push('Pregunta 23: Axiología Institucional');
                }

                // 4. Fe y Sacramentos
                if (!this.respuestas.sacramento_bautizo) {
                    faltantes.push('Pregunta 26.1: Bautizo celebrado');
                }
                if (!this.respuestas.sacramento_eucaristia) {
                    faltantes.push('Pregunta 26.2: Primera Comunión');
                }
                if (this.nivel === 'secundaria' && !this.respuestas.sacramento_confirmacion) {
                    faltantes.push('Pregunta 26.3: Confirmación');
                }
                if (!this.respuestas.eucaristia_importante) {
                    faltantes.push('Pregunta 27.1: Importancia de la Eucaristía');
                }

                // 5. Situación Conyugal y Salud
                if (!this.respuestas.padres_casados) {
                    faltantes.push('Pregunta 28: ¿Padres casados?');
                }
                if (!this.respuestas.enfermedad) {
                    faltantes.push('Pregunta 29: ¿Padece alguna enfermedad?');
                }

                // 6. Normas Institucionales
                if (!this.respuestas.norma_horario) {
                    faltantes.push('Norma: Horario de entrada');
                }
                if (this.estudianteSexo === 'M' && (!this.respuestas.norma_corte_cabello || this.respuestas.norma_corte_cabello === 'No aplica')) {
                    faltantes.push('Norma: Corte de cabello escolar');
                }
                if (this.estudianteSexo === 'F' && (!this.respuestas.norma_cabello_trenzado || this.respuestas.norma_cabello_trenzado === 'No aplica')) {
                    faltantes.push('Norma: Cabello trenzado sin maquillaje');
                }
                if (!this.respuestas.norma_uniforme) {
                    faltantes.push('Norma: Uso de uniforme completo');
                }
                if (!this.respuestas.norma_asistencia) {
                    faltantes.push('Norma: Asistencia y puntualidad');
                }
                if (!this.respuestas.compromiso_pago) {
                    faltantes.push('Compromiso económico de pensiones');
                }

                // 7. Obligaciones y Autorizaciones
                if (!this.respuestas.oblig_reuniones_aula) {
                    faltantes.push('Obligación: Reuniones de aula');
                }
                if (!this.respuestas.oblig_atencion_padres) {
                    faltantes.push('Obligación: Atención a Padres');
                }
                if (!this.respuestas.oblig_escuela_padres) {
                    faltantes.push('Obligación: Escuela para Padres');
                }
                if (!this.respuestas.oblig_conservacion) {
                    faltantes.push('Obligación: Conservación de ambientes');
                }
                if (!this.respuestas.oblig_multa_apafa) {
                    faltantes.push('Obligación: Multa APAFA');
                }
                if (!this.respuestas.acuerdo_integracion_aulas) {
                    faltantes.push('Obligación: Integración de aulas');
                }
                if (!this.respuestas.autoriza_comunicacion) {
                    faltantes.push('Autorización: Comunicaciones WhatsApp');
                }
                if (!this.respuestas.autoriza_fotos_videos) {
                    faltantes.push('Autorización: Fotos y videos');
                }
                if (!this.respuestas.autoriza_atencion_medica) {
                    faltantes.push('Autorización: Atención médica');
                }
                if (!this.respuestas.autoriza_regresa_solo) {
                    faltantes.push('Autorización: Retiro de estudiante (¿Regresa solo?)');
                } else if (this.respuestas.autoriza_regresa_solo === 'No' && (!this.respuestas.recoge_nombre || !this.respuestas.recoge_nombre.trim())) {
                    faltantes.push('Nombre de la persona autorizada a recoger al estudiante');
                }

                return faltantes;
            },

            get camposFaltantesSinConformidadCount() {
                return this.obtenerCamposFaltantes().length;
            },

            get puedeDarConformidad() {
                return this.obtenerCamposFaltantes().length === 0;
            },

            get camposPendientesCount() {
                const faltantes = this.obtenerCamposFaltantes();
                return faltantes.length + (!this.conformidad ? 1 : 0);
            },

            marcarConformidad(tipo) {
                if (tipo === 'CONFORME') {
                    const faltantes = this.obtenerCamposFaltantes();
                    if (faltantes.length > 0) {
                        alert('No se puede dar CONFORMIDAD si aún hay ' + faltantes.length + ' campos obligatorios sin marcar o sin responder:\n\n• ' + faltantes.slice(0, 6).join('\n• ') + (faltantes.length > 6 ? '\n... y ' + (faltantes.length - 6) + ' campos más.' : '') + '\n\nPor favor complete dichos campos primero.');
                        return;
                    }
                }
                this.conformidad = tipo;
                this.triggerAutosave();
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
                        alert('Progreso guardado con éxito. Puede seguir llenando o volver más tarde.');
                    } else {
                        alert(data.mensaje || 'Error al guardar');
                    }
                } catch (e) {
                    alert('Error de conexión al guardar progreso.');
                } finally {
                    this.isSaving = false;
                }
            },

            async registrarYCerrar() {
                const faltantes = this.obtenerCamposFaltantes();
                if (faltantes.length > 0) {
                    alert('No se puede registrar y cerrar la entrevista porque faltan responder ' + faltantes.length + ' campo(s) obligatorio(s):\n\n• ' + faltantes.slice(0, 6).join('\n• ') + (faltantes.length > 6 ? '\n... y ' + (faltantes.length - 6) + ' campos más.' : ''));
                    return;
                }

                if (!this.conformidad) {
                    alert('Debe seleccionar el Resultado de Conformidad (CONFORME o NO CONFORME) para poder cerrar la entrevista.');
                    return;
                }

                if (!confirm('¿Confirma que desea registrar y cerrar esta entrevista? Una vez cerrada quedará registrada en el sistema de admisión.')) {
                    return;
                }

                this.isClosing = true;
                try {
                    const payload = this.prepararPayload('completada');
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
                        alert('Entrevista registrada y cerrada exitosamente.');
                        window.location.href = data.redirect || config.backUrl;
                    } else {
                        alert(data.mensaje || 'Error al cerrar entrevista');
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
