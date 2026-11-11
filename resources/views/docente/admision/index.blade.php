<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight flex items-center gap-2">
                    <svg class="w-7 h-7 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span>Mis Entrevistas de Admisión {{ $anoPostula ? $anoPostula : '(Todos los Años)' }}</span>
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Descargue el formato oficial, realice las entrevistas a los padres de familia y suba los archivos procesados.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <!-- Dropdown Descargar Plantilla -->
                <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                    <button @click="open = !open" type="button" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        Descargar Plantilla
                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div x-show="open" x-cloak class="absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-20 py-1">
                        <a href="{{ route('docente.admision.plantilla', ['nivel' => 'primaria']) }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-700">
                            <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                            Plantilla Primaria (1° Grado)
                        </a>
                        <a href="{{ route('docente.admision.plantilla', ['nivel' => 'secundaria']) }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-700">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                            Plantilla Secundaria
                        </a>
                    </div>
                </div>

                <!-- Botón Llenar Entrevista en Línea -->
                <a href="{{ route('docente.admision.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-emerald-600 to-teal-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:from-emerald-700 hover:to-teal-700 shadow-md shadow-emerald-200 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Llenar Entrevista en Línea
                </a>

                <!-- Botón Subir Entrevistas -->
                <button x-data @click="window.dispatchEvent(new CustomEvent('open-docente-import-modal'))" type="button" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                    </svg>
                    Subir Fichas Llenadas
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-6" x-data="{
        showImportModal: false,
        showDetailModal: false,
        selectedEntrevista: null,
        openDetail(entrevista) {
            this.selectedEntrevista = entrevista;
            this.showDetailModal = true;
        }
    }" @open-docente-import-modal.window="showImportModal = true">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Alertas Flash -->
            @if(session('success'))
                <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="font-medium text-sm">{{ session('success') }}</span>
                    </div>
                    <button type="button" @click="$el.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">&times;</button>
                </div>
            @endif

            @if(session('warning'))
                <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <span class="font-medium text-sm">{{ session('warning') }}</span>
                    </div>
                    <button type="button" @click="$el.parentElement.remove()" class="text-amber-500 hover:text-amber-700">&times;</button>
                </div>
            @endif

            @if(session('error'))
                <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="font-medium text-sm">{{ session('error') }}</span>
                    </div>
                    <button type="button" @click="$el.parentElement.remove()" class="text-rose-500 hover:text-rose-700">&times;</button>
                </div>
            @endif

            <!-- Tarjetas de Métricas del Docente -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Mis Entrevistas</p>
                        <p class="text-2xl font-black text-indigo-600 mt-1">{{ $totalMisEntrevistas }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Conformes</p>
                        <p class="text-2xl font-black text-emerald-600 mt-1">{{ $totalConformes }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">No Conformes</p>
                        <p class="text-2xl font-black text-rose-600 mt-1">{{ $totalNoConformes }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Ya Matriculados</p>
                        <p class="text-2xl font-black text-blue-600 mt-1">{{ $totalMatriculados }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Filtros -->
            <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                <form method="GET" action="{{ route('docente.admision.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Año Postulación</label>
                        <select name="ano_postula" onchange="this.form.submit()" class="w-full text-xs rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 font-semibold">
                            <option value="todos" {{ $anoPostula === null ? 'selected' : '' }}>Todos los Años</option>
                            @foreach($todosLosAnos as $anio)
                                <option value="{{ $anio }}" {{ $anoPostula == $anio ? 'selected' : '' }}>{{ $anio }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Conformidad</label>
                        <select name="resultado_conformidad" class="w-full text-xs rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Todas</option>
                            <option value="CONFORME" {{ request('resultado_conformidad') === 'CONFORME' ? 'selected' : '' }}>Conforme</option>
                            <option value="NO CONFORME" {{ request('resultado_conformidad') === 'NO CONFORME' ? 'selected' : '' }}>No Conforme</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Estado</label>
                        <select name="estado" class="w-full text-xs rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Todos</option>
                            <option value="nuevo_ingresante" {{ request('estado') === 'nuevo_ingresante' ? 'selected' : '' }}>Nuevo Ingresante</option>
                            <option value="matriculado" {{ request('estado') === 'matriculado' ? 'selected' : '' }}>Matriculado</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Buscar (DNI / Nombre)</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="DNI o Apellidos..." class="w-full text-xs rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div class="flex items-end gap-2">
                        <button type="submit" class="w-full py-2 bg-gray-900 text-white rounded-lg text-xs font-semibold hover:bg-gray-800 transition">
                            Filtrar
                        </button>
                        <a href="{{ route('docente.admision.index', ['ano_postula' => $anoPostula]) }}" class="px-3 py-2 bg-gray-100 text-gray-700 rounded-lg text-xs font-semibold hover:bg-gray-200 transition" title="Limpiar Filtros">
                            ↺
                        </a>
                    </div>
                </form>
            </div>

            <!-- Tabla de Entrevistas del Docente -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm text-left">
                        <thead class="bg-gray-50 text-gray-600 font-semibold text-xs uppercase tracking-wider">
                            <tr>
                                <th class="px-5 py-3">Postulante</th>
                                <th class="px-5 py-3">Grado Postula</th>
                                <th class="px-5 py-3">Padres / Contacto</th>
                                <th class="px-5 py-3">Fecha Entrevista</th>
                                <th class="px-5 py-3 text-center">Conformidad</th>
                                <th class="px-5 py-3 text-center">Estado Admisión</th>
                                <th class="px-5 py-3 text-right">Ficha</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($entrevistas as $entrevista)
                                <tr class="hover:bg-gray-50/75 transition">
                                    <td class="px-5 py-4">
                                        <div class="font-bold text-gray-900">
                                            {{ trim(str_replace(',', '', $entrevista->nombre_completo_estudiante ?? '')) ? $entrevista->nombre_completo_estudiante : '(Borrador sin nombre)' }}
                                        </div>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-mono font-medium bg-gray-100 text-gray-800">
                                                DNI: {{ $entrevista->estudiante_dni && !str_starts_with($entrevista->estudiante_dni, 'BORRADOR') ? $entrevista->estudiante_dni : 'Pendiente' }}
                                            </span>
                                        </div>
                                    </td>

                                    <td class="px-5 py-4">
                                        <div class="font-semibold text-gray-800">{{ $entrevista->grado_postula }}</div>
                                        <div class="text-xs text-gray-500 capitalize flex items-center gap-1.5 mt-0.5">
                                            <span>{{ $entrevista->nivel }}</span>
                                            <span class="text-gray-300">·</span>
                                            <span class="font-bold text-indigo-700 bg-indigo-50 px-1.5 py-0.5 rounded border border-indigo-200">Año {{ $entrevista->ano_lectivo_postula }}</span>
                                        </div>
                                    </td>

                                    <td class="px-5 py-4">
                                        <div class="text-xs font-medium text-gray-800">
                                            {{ $entrevista->apoderado_nombres ?: ($entrevista->padre_nombres ?: $entrevista->madre_nombres) }}
                                        </div>
                                        @php
                                            $tel = $entrevista->apoderado_telefono ?: ($entrevista->padre_telefono ?: $entrevista->madre_telefono);
                                        @endphp
                                        @if($tel)
                                            <div class="text-xs text-gray-500 mt-0.5">Tel: {{ $tel }}</div>
                                        @endif
                                    </td>

                                    <td class="px-5 py-4 text-xs text-gray-600">
                                        {{ $entrevista->fecha_entrevista ? $entrevista->fecha_entrevista->format('d/m/Y') : 'Sin fecha' }}
                                    </td>

                                    <td class="px-5 py-4 text-center">
                                        @if($entrevista->resultado_conformidad === 'CONFORME')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                CONFORME
                                            </span>
                                        @elseif($entrevista->resultado_conformidad === 'NO CONFORME')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                NO CONFORME
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-700 border border-gray-300">
                                                <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                                                Pendiente
                                            </span>
                                        @endif
                                    </td>

                                    <td class="px-5 py-4 text-center">
                                        @if($entrevista->estado === 'matriculado')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                Matriculado ({{ $entrevista->seccionAsignada?->grado?->nombre }} {{ $entrevista->seccionAsignada?->seccion?->nombre }})
                                            </span>
                                        @elseif($entrevista->esBorrador())
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                                En Progreso (Borrador)
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                Completada
                                            </span>
                                        @endif
                                    </td>

                                    <td class="px-5 py-4 text-right space-x-1.5">
                                        @if($entrevista->esBorrador())
                                            <a href="{{ route('docente.admision.edit', $entrevista) }}" class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-amber-500 text-white rounded-lg text-xs font-bold hover:bg-amber-600 shadow-sm transition" title="Continuar llenando la ficha">
                                                <svg class="w-3.5 h-3.5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                                Continuar
                                            </a>
                                        @endif

                                        <button type="button" @click="openDetail({{ json_encode($entrevista) }})" class="p-1.5 text-gray-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition" title="Ver Respuestas de la Entrevista">
                                            <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-5 py-12 text-center text-gray-500">
                                        <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        <p class="font-medium text-base text-gray-700">Aún no tiene entrevistas subidas</p>
                                        <p class="text-xs text-gray-500 mt-1">Descargue la plantilla en Excel, llene los datos de las entrevistas y súbalas aquí.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($entrevistas->hasPages())
                    <div class="px-5 py-4 border-t border-gray-100">
                        {{ $entrevistas->links() }}
                    </div>
                @endif
            </div>

            <!-- MODAL: Subir Entrevistas (Docente) -->
            <div x-show="showImportModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div x-show="showImportModal" @click="showImportModal = false" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                    <div x-show="showImportModal" class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                        <form method="POST" action="{{ route('docente.admision.importar') }}" enctype="multipart/form-data">
                            @csrf
                            <div class="bg-white px-6 pt-6 pb-4">
                                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                                    <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                        </svg>
                                        Subir Mis Fichas de Entrevista
                                    </h3>
                                    <button type="button" @click="showImportModal = false" class="text-gray-400 hover:text-gray-600">&times;</button>
                                </div>

                                <div class="mt-4 space-y-4">
                                    <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg text-xs text-blue-800 flex items-start gap-2">
                                        <svg class="w-4 h-4 text-blue-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <div>
                                            Las fichas que suba quedarán registradas a su nombre (<strong>{{ Auth::user()->name }}</strong>). La dirección/administración procederá con la asignación de sección y matrícula oficial.
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Año de Postulación</label>
                                        <input type="number" name="ano_postula" value="{{ $anoActual }}" required class="w-full text-sm rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Archivos Excel (.xlsx)</label>
                                        <input type="file" name="archivos[]" multiple accept=".xlsx,.xls" required class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                        <p class="text-[11px] text-gray-500 mt-1">Puede seleccionar uno o varios archivos simultáneamente.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gray-50 px-6 py-3 flex items-center justify-end gap-3 rounded-b-2xl">
                                <button type="button" @click="showImportModal = false" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-xs font-semibold text-gray-700 hover:bg-gray-50 transition">
                                    Cancelar
                                </button>
                                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-xs font-semibold hover:bg-indigo-700 transition">
                                    Subir Fichas
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- MODAL: Detalle de Respuestas (Docente) -->
            <div x-show="showDetailModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div x-show="showDetailModal" @click="showDetailModal = false" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                    <div x-show="showDetailModal" class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full">
                        <template x-if="selectedEntrevista">
                            <div class="bg-white">
                                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50">
                                    <div>
                                        <h3 class="text-lg font-bold text-gray-900" x-text="`Ficha: ${selectedEntrevista.estudiante_apellido_paterno || ''} ${selectedEntrevista.estudiante_apellido_materno || ''}, ${selectedEntrevista.estudiante_nombres || ''}`"></h3>
                                        <p class="text-xs text-gray-500 mt-0.5" x-text="`Postula a: ${selectedEntrevista.grado_postula} (${selectedEntrevista.nivel})`"></p>
                                    </div>
                                    <button type="button" @click="showDetailModal = false" class="text-gray-400 hover:text-gray-600 text-2xl font-light">&times;</button>
                                </div>

                                <div class="px-6 py-5 max-h-[75vh] overflow-y-auto space-y-6">
                                    <!-- Datos Postulante -->
                                    <div class="bg-indigo-50/50 p-4 rounded-xl border border-indigo-100">
                                        <h4 class="text-xs font-bold text-indigo-900 uppercase tracking-wider mb-3">1. Datos del Postulante</h4>
                                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                                            <div>
                                                <span class="text-gray-500 block">DNI:</span>
                                                <span class="font-bold text-gray-800 font-mono" x-text="selectedEntrevista.estudiante_dni"></span>
                                            </div>
                                            <div>
                                                <span class="text-gray-500 block">Fecha Nacimiento:</span>
                                                <span class="font-bold text-gray-800" x-text="selectedEntrevista.estudiante_fecha_nacimiento ? selectedEntrevista.estudiante_fecha_nacimiento.substring(0, 10) : 'No registrada'"></span>
                                            </div>
                                            <div>
                                                <span class="text-gray-500 block">Colegio Procedencia:</span>
                                                <span class="font-bold text-gray-800" x-text="selectedEntrevista.colegio_procedencia || 'No registrado'"></span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Datos Padres -->
                                    <div class="bg-gray-50 p-4 rounded-xl border border-gray-200">
                                        <h4 class="text-xs font-bold text-gray-900 uppercase tracking-wider mb-3">2. Padres de Familia</h4>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                                            <div class="p-3 bg-white rounded-lg border border-gray-100">
                                                <span class="font-bold text-gray-700 block mb-1">Padre:</span>
                                                <p class="font-semibold text-gray-900" x-text="selectedEntrevista.padre_nombres || 'No registrado'"></p>
                                                <p class="text-gray-500" x-text="`DNI: ${selectedEntrevista.padre_dni || '-'} | Tel: ${selectedEntrevista.padre_telefono || '-'}`"></p>
                                            </div>
                                            <div class="p-3 bg-white rounded-lg border border-gray-100">
                                                <span class="font-bold text-gray-700 block mb-1">Madre:</span>
                                                <p class="font-semibold text-gray-900" x-text="selectedEntrevista.madre_nombres || 'No registrada'"></p>
                                                <p class="text-gray-500" x-text="`DNI: ${selectedEntrevista.madre_dni || '-'} | Tel: ${selectedEntrevista.madre_telefono || '-'}`"></p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Cuestionario -->
                                    <div>
                                        <h4 class="text-xs font-bold text-gray-900 uppercase tracking-wider mb-3">3. Cuestionario de Entrevista</h4>
                                        <template x-if="selectedEntrevista.respuestas_cuestionario && Object.keys(selectedEntrevista.respuestas_cuestionario).length > 0">
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs">
                                                <template x-for="(valor, clave) in selectedEntrevista.respuestas_cuestionario" :key="clave">
                                                    <div class="p-3 rounded-lg border border-gray-100 bg-gray-50/75">
                                                        <span class="font-bold text-gray-600 block uppercase text-[10px]" x-text="clave.replace(/_/g, ' ')"></span>
                                                        <span class="font-medium text-gray-900 mt-1 block"
                                                              x-text="Array.isArray(valor) ? valor.map(v => typeof v === 'object' ? (v.nombre + (v.parentesco ? ' ('+v.parentesco+')' : '') + (v.dni ? ' - DNI: '+v.dni : '')) : v).join(' ; ') : (valor || 'Sin respuesta')"></span>
                                                    </div>
                                                </template>
                                            </div>
                                        </template>
                                        <template x-if="!selectedEntrevista.respuestas_cuestionario || Object.keys(selectedEntrevista.respuestas_cuestionario).length === 0">
                                            <p class="text-xs text-gray-500 italic">No se encontraron respuestas registradas.</p>
                                        </template>
                                    </div>

                                    <div class="p-4 rounded-xl bg-gray-50 border border-gray-200 flex justify-between items-center text-xs">
                                        <span class="text-gray-500">Dictamen de Conformidad:</span>
                                        <span class="font-bold" :class="selectedEntrevista.resultado_conformidad === 'CONFORME' ? 'text-emerald-600' : 'text-rose-600'" x-text="selectedEntrevista.resultado_conformidad"></span>
                                    </div>
                                </div>

                                <div class="px-6 py-3 bg-gray-50 border-t border-gray-100 flex justify-end">
                                    <button type="button" @click="showDetailModal = false" class="px-4 py-2 bg-gray-800 text-white rounded-lg text-xs font-semibold hover:bg-gray-700 transition">
                                        Cerrar
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>

