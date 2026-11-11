<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight flex items-center gap-2">
                    <svg class="w-7 h-7 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span>Admisión e Ingresantes {{ $anoPostula ? $anoPostula : '(Todos los Años)' }}</span>
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Gestión de fichas de entrevista a padres para nuevos postulantes (A Primaria y Secundaria).
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
                        <a href="{{ route('admin.admision.plantilla', ['nivel' => 'primaria']) }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-700">
                            <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                            Plantilla Primaria
                        </a>
                        <a href="{{ route('admin.admision.plantilla', ['nivel' => 'secundaria']) }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-700">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                            Plantilla Secundaria
                        </a>
                    </div>
                </div>

                <!-- Botón Llenar Entrevista en Línea -->
                <a href="{{ route('admin.admision.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-emerald-600 to-teal-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:from-emerald-700 hover:to-teal-700 shadow-md shadow-emerald-200 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Llenar Entrevista en Línea
                </a>

                <!-- Botón Asignar Rol de Entrevistador -->
                <button x-data @click="window.dispatchEvent(new CustomEvent('open-entrevistadores-modal'))" type="button" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    Asignar Rol de Entrevistador
                </button>

                <!-- Botón Subir Entrevistas -->
                <button x-data @click="window.dispatchEvent(new CustomEvent('open-import-modal'))" type="button" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                    </svg>
                    Importar Entrevistas
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-6" x-data="{
        showImportModal: false,
        showAssignModal: false,
        showDetailModal: false,
        showEntrevistadoresModal: false,
        searchDocente: '',
        docentesList: {{ json_encode($docentesParaAsignar->map(fn($d) => [
            'id' => $d->id,
            'dni' => $d->dni ?: '',
            'nombre_completo' => $d->nombre_completo,
            'nivel' => $d->nivel ?: 'General',
            'es_entrevistador' => (bool)$d->es_entrevistador,
            'updating' => false,
        ])) }},
        async toggleDocente(docente) {
            docente.updating = true;
            try {
                const res = await fetch(`{{ url('admin/admision/docentes') }}/${docente.id}/toggle-entrevistador`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                if (data.success) {
                    docente.es_entrevistador = data.es_entrevistador;
                }
            } catch (e) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Error', 'No se pudo actualizar el rol de entrevistador', 'error');
                } else {
                    alert('No se pudo actualizar el rol de entrevistador');
                }
            } finally {
                docente.updating = false;
            }
        },
        get filteredDocentes() {
            if (!this.searchDocente.trim()) return this.docentesList;
            const s = this.searchDocente.toLowerCase();
            return this.docentesList.filter(d => 
                (d.nombre_completo && d.nombre_completo.toLowerCase().includes(s)) ||
                (d.dni && d.dni.toLowerCase().includes(s))
            );
        },
        get totalEntrevistadores() {
            return this.docentesList.filter(d => d.es_entrevistador).length;
        },
        selectedEntrevista: null,
        openAssign(entrevista) {
            this.selectedEntrevista = entrevista;
            this.showAssignModal = true;
        },
        openDetail(entrevista) {
            this.selectedEntrevista = entrevista;
            this.showDetailModal = true;
        }
    }" @open-import-modal.window="showImportModal = true"
       @open-entrevistadores-modal.window="showEntrevistadoresModal = true">
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

            <!-- Tarjetas de Métricas -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                <!-- Total Postulantes -->
                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Postulantes</p>
                        <p class="text-2xl font-black text-gray-900 mt-1">{{ $totalPostulantes }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                </div>

                <!-- Conformes -->
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

                <!-- No Conformes -->
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

                <!-- Pendientes de Asignar -->
                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Pendientes Asignar</p>
                        <p class="text-2xl font-black text-amber-600 mt-1">{{ $totalPendientes }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>

                <!-- Ya Matriculados -->
                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Matriculados</p>
                        <p class="text-2xl font-black text-blue-600 mt-1">{{ $totalMatriculados }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Panel de Filtros -->
            <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                <form method="GET" action="{{ route('admin.admision.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-3">
                    <!-- Año -->
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Año Postulación</label>
                        <select name="ano_postula" onchange="this.form.submit()" class="w-full text-xs rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 font-semibold">
                            <option value="todos" {{ $anoPostula === null ? 'selected' : '' }}>Todos los Años</option>
                            @foreach($todosLosAnos as $anio)
                                <option value="{{ $anio }}" {{ $anoPostula == $anio ? 'selected' : '' }}>{{ $anio }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Nivel -->
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Nivel</label>
                        <select name="nivel" class="w-full text-xs rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Todos</option>
                            <option value="primaria" {{ request('nivel') === 'primaria' ? 'selected' : '' }}>Primaria</option>
                            <option value="secundaria" {{ request('nivel') === 'secundaria' ? 'selected' : '' }}>Secundaria</option>
                        </select>
                    </div>

                    <!-- Grado -->
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Grado Postula</label>
                        <select name="grado_postula" class="w-full text-xs rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Todos</option>
                            @foreach($gradosPostulaDisponibles as $gp)
                                <option value="{{ $gp }}" {{ request('grado_postula') === $gp ? 'selected' : '' }}>{{ $gp }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Conformidad -->
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Conformidad</label>
                        <select name="resultado_conformidad" class="w-full text-xs rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Todas</option>
                            <option value="CONFORME" {{ request('resultado_conformidad') === 'CONFORME' ? 'selected' : '' }}>Conforme</option>
                            <option value="NO CONFORME" {{ request('resultado_conformidad') === 'NO CONFORME' ? 'selected' : '' }}>No Conforme</option>
                        </select>
                    </div>

                    <!-- Estado -->
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Estado</label>
                        <select name="estado" class="w-full text-xs rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Todos</option>
                            <option value="nuevo_ingresante" {{ request('estado') === 'nuevo_ingresante' ? 'selected' : '' }}>Nuevo Ingresante</option>
                            <option value="matriculado" {{ request('estado') === 'matriculado' ? 'selected' : '' }}>Matriculado</option>
                        </select>
                    </div>

                    <!-- Búsqueda -->
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Buscar (DNI / Nombre)</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Ej. 74839201 o Torres" class="w-full text-xs rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <!-- Botones de Acción -->
                    <div class="flex items-end gap-2">
                        <button type="submit" class="w-full py-2 bg-gray-900 text-white rounded-lg text-xs font-semibold hover:bg-gray-800 transition">
                            Filtrar
                        </button>
                        <a href="{{ route('admin.admision.index', ['ano_postula' => $anoPostula]) }}" class="px-3 py-2 bg-gray-100 text-gray-700 rounded-lg text-xs font-semibold hover:bg-gray-200 transition" title="Limpiar Filtros">
                            ↺
                        </a>
                    </div>
                </form>
            </div>

            <!-- Tabla de Postulantes -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm text-left">
                        <thead class="bg-gray-50 text-gray-600 font-semibold text-xs uppercase tracking-wider">
                            <tr>
                                <th class="px-5 py-3">Postulante</th>
                                <th class="px-5 py-3">Grado Postula</th>
                                <th class="px-5 py-3">Familia / Contacto</th>
                                <th class="px-5 py-3">Entrevistador</th>
                                <th class="px-5 py-3 text-center">Conformidad</th>
                                <th class="px-5 py-3 text-center">Estado Actual</th>
                                <th class="px-5 py-3 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($entrevistas as $entrevista)
                                <tr class="hover:bg-gray-50/75 transition">
                                    <!-- Postulante -->
                                    <td class="px-5 py-4">
                                        <div class="font-bold text-gray-900">
                                            {{ trim(str_replace(',', '', $entrevista->nombre_completo_estudiante ?? '')) ? $entrevista->nombre_completo_estudiante : '(Borrador sin nombre)' }}
                                        </div>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-mono font-medium bg-gray-100 text-gray-800">
                                                DNI: {{ $entrevista->estudiante_dni && !str_starts_with($entrevista->estudiante_dni, 'BORRADOR') ? $entrevista->estudiante_dni : 'Pendiente' }}
                                            </span>
                                            @if($entrevista->estudiante_fecha_nacimiento)
                                                <span class="text-xs text-gray-500">
                                                    Nac: {{ $entrevista->estudiante_fecha_nacimiento->format('d/m/Y') }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Grado Postula -->
                                    <td class="px-5 py-4">
                                        <div class="font-semibold text-gray-800">{{ $entrevista->grado_postula }}</div>
                                        <div class="text-xs text-gray-500 capitalize flex items-center gap-1.5 mt-0.5">
                                            <span>{{ $entrevista->nivel }}</span>
                                            <span class="text-gray-300">·</span>
                                            <span class="font-bold text-indigo-700 bg-indigo-50 px-1.5 py-0.5 rounded border border-indigo-200">Año {{ $entrevista->ano_lectivo_postula }}</span>
                                        </div>
                                        @if($entrevista->colegio_procedencia)
                                            <div class="text-xs text-indigo-600 truncate max-w-xs mt-0.5" title="Colegio de procedencia">
                                                Col: {{ $entrevista->colegio_procedencia }}
                                            </div>
                                        @endif
                                    </td>

                                    <!-- Familia / Contacto -->
                                    <td class="px-5 py-4">
                                        @if($entrevista->apoderado_nombres)
                                            <div class="text-xs font-semibold text-gray-800">
                                                {{ $entrevista->apoderado_nombres }} ({{ $entrevista->apoderado_parentesco ?: 'Apod.' }})
                                            </div>
                                        @elseif($entrevista->padre_nombres)
                                            <div class="text-xs font-semibold text-gray-800">
                                                P: {{ $entrevista->padre_nombres }}
                                            </div>
                                        @elseif($entrevista->madre_nombres)
                                            <div class="text-xs font-semibold text-gray-800">
                                                M: {{ $entrevista->madre_nombres }}
                                            </div>
                                        @endif

                                        @php
                                            $tel = $entrevista->apoderado_telefono ?: ($entrevista->padre_telefono ?: $entrevista->madre_telefono);
                                        @endphp
                                        @if($tel)
                                            <a href="tel:{{ $tel }}" class="inline-flex items-center gap-1 text-xs text-indigo-600 hover:underline mt-0.5">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                                </svg>
                                                {{ $tel }}
                                            </a>
                                        @endif
                                    </td>

                                    <!-- Entrevistador -->
                                    <td class="px-5 py-4">
                                        <div class="text-xs font-medium text-gray-800">
                                            {{ $entrevista->docenteEntrevistador?->name ?: 'Docente Registrado' }}
                                        </div>
                                        <div class="text-xs text-gray-500 mt-0.5">
                                            {{ $entrevista->fecha_entrevista ? $entrevista->fecha_entrevista->format('d/m/Y') : 'Sin fecha' }}
                                        </div>
                                    </td>

                                    <!-- Conformidad -->
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

                                    <!-- Estado Actual -->
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

                                    <!-- Acciones -->
                                    <td class="px-5 py-4 text-right space-x-1.5">
                                        <!-- Continuar o Editar -->
                                        @if($entrevista->esBorrador())
                                            <a href="{{ route('admin.admision.edit', $entrevista) }}" class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-amber-500 text-white rounded-lg text-xs font-bold hover:bg-amber-600 shadow-sm transition" title="Continuar llenando la ficha">
                                                <svg class="w-3.5 h-3.5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                                Continuar
                                            </a>
                                        @else
                                            <a href="{{ route('admin.admision.edit', $entrevista) }}" class="p-1.5 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition inline-block" title="Editar Ficha">
                                                <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </a>
                                        @endif

                                        <!-- Ver Detalle -->
                                        <button type="button" @click="openDetail({{ json_encode($entrevista) }})" class="p-1.5 text-gray-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition" title="Ver Ficha y Respuestas de la Entrevista">
                                            <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </button>

                                        <!-- Asignar Sección / Matricular -->
                                        @if($entrevista->estado === 'nuevo_ingresante')
                                            <button type="button" @click="openAssign({{ json_encode($entrevista) }})" class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-emerald-600 text-white rounded-lg text-xs font-bold hover:bg-emerald-700 shadow-sm transition" title="Asignar Sección y Matricular">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                                                </svg>
                                                Matricular
                                            </button>

                                            <!-- Eliminar Ficha -->
                                            <form method="POST" action="{{ route('admin.admision.destroy', $entrevista) }}" class="inline" onsubmit="return confirm('¿Está seguro de eliminar esta ficha de entrevista?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1.5 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition" title="Eliminar Ficha">
                                                    <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-5 py-12 text-center text-gray-500">
                                        <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        <p class="font-medium text-base text-gray-700">No se encontraron fichas de entrevista registradas</p>
                                        <p class="text-xs text-gray-500 mt-1">Haga clic en "Importar Entrevistas" para subir archivos Excel de entrevistas de padres de familia.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                @if($entrevistas->hasPages())
                    <div class="px-5 py-4 border-t border-gray-100">
                        {{ $entrevistas->links() }}
                    </div>
                @endif
            </div>

            <!-- MODAL: Subir / Importar Entrevistas Excel -->
            <div x-show="showImportModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div x-show="showImportModal" @click="showImportModal = false" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                    <div x-show="showImportModal" class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                        <form method="POST" action="{{ route('admin.admision.importar') }}" enctype="multipart/form-data">
                            @csrf
                            <div class="bg-white px-6 pt-6 pb-4">
                                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                                    <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                        </svg>
                                        Importar Fichas de Entrevista Excel
                                    </h3>
                                    <button type="button" @click="showImportModal = false" class="text-gray-400 hover:text-gray-600">&times;</button>
                                </div>

                                <div class="mt-4 space-y-4">
                                    <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg text-xs text-blue-800">
                                        <div class="flex items-start gap-2">
                                            <svg class="w-4 h-4 text-amber-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <div>
                                                <strong>Aislamiento de Postulantes:</strong> Los estudiantes importados se guardan como <em>"Nuevo Ingresante"</em>. No aparecerán en las listas de alumnos activos ni en calificaciones ni mensualidades hasta que usted les asigne una sección.
                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Año de Postulación</label>
                                        <input type="number" name="ano_postula" value="{{ $anoActual }}" required class="w-full text-sm rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Docente Entrevistador (Opcional)</label>
                                        <select name="docente_id" class="w-full text-sm rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                            <option value="">-- Registrar como Administrador Actual --</option>
                                            @foreach($docentes as $docente)
                                                <option value="{{ $docente->id }}">{{ $docente->name }}</option>
                                            @endforeach
                                        </select>
                                        <p class="text-[11px] text-gray-500 mt-1">Si no se especifica, se asignará a su usuario administrador.</p>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Archivos Excel (.xlsx)</label>
                                        <input type="file" name="archivos[]" multiple accept=".xlsx,.xls" required class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                        <p class="text-[11px] text-gray-500 mt-1">Puede seleccionar uno o varios archivos a la vez.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gray-50 px-6 py-3 flex items-center justify-end gap-3 rounded-b-2xl">
                                <button type="button" @click="showImportModal = false" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-xs font-semibold text-gray-700 hover:bg-gray-50 transition">
                                    Cancelar
                                </button>
                                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-xs font-semibold hover:bg-indigo-700 transition">
                                    Procesar Fichas
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- MODAL: Asignar Sección y Formalizar Matrícula -->
            <div x-show="showAssignModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div x-show="showAssignModal" @click="showAssignModal = false" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                    <div x-show="showAssignModal" class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                        <template x-if="selectedEntrevista">
                            <form method="POST" :action="`{{ url('admin/admision') }}/${selectedEntrevista.id}/matricular`">
                                @csrf
                                <div class="bg-white px-6 pt-6 pb-4">
                                    <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                                        <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                                            <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            Asignar Sección y Formalizar Matrícula
                                        </h3>
                                        <button type="button" @click="showAssignModal = false" class="text-gray-400 hover:text-gray-600">&times;</button>
                                    </div>

                                    <div class="mt-4 space-y-4">
                                        <!-- Resumen del Postulante -->
                                        <div class="p-3 bg-gray-50 border border-gray-200 rounded-lg">
                                            <p class="text-xs text-gray-500 font-semibold uppercase">Postulante</p>
                                            <p class="text-sm font-bold text-gray-900" x-text="`${selectedEntrevista.estudiante_apellido_paterno || ''} ${selectedEntrevista.estudiante_apellido_materno || ''}, ${selectedEntrevista.estudiante_nombres || ''}`"></p>
                                            <div class="flex items-center gap-4 text-xs text-gray-600 mt-1">
                                                <span>DNI: <strong class="font-mono" x-text="selectedEntrevista.estudiante_dni"></strong></span>
                                                <span>Grado Postula: <strong class="text-indigo-600" x-text="selectedEntrevista.grado_postula"></strong></span>
                                            </div>
                                        </div>

                                        <!-- Selector de Sección -->
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">
                                                Sección de Destino <span class="text-rose-500">*</span>
                                            </label>
                                            <select name="grado_seccion_id" required class="w-full text-sm rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                                <option value="">-- Seleccionar Sección --</option>
                                                @foreach($gradoSecciones as $gs)
                                                    <option value="{{ $gs->id }}">
                                                        {{ $gs->grado->nombre }} - Sección {{ $gs->seccion->nombre }} ({{ ucfirst($gs->grado->nivel) }} · Año {{ $gs->anoLectivo?->anio }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-lg text-xs text-emerald-800 flex items-start gap-2">
                                            <svg class="w-4 h-4 text-emerald-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <div>
                                                <strong>Acción del Sistema:</strong> Al matricular, se creará el usuario del estudiante, sus apoderados oficiales y la matrícula activa. A partir de este momento formará parte de los reportes y listas oficiales del aula.
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-gray-50 px-6 py-3 flex items-center justify-end gap-3 rounded-b-2xl">
                                    <button type="button" @click="showAssignModal = false" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-xs font-semibold text-gray-700 hover:bg-gray-50 transition">
                                        Cancelar
                                    </button>
                                    <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-xs font-semibold hover:bg-emerald-700 transition">
                                        Confirmar y Matricular
                                    </button>
                                </div>
                            </form>
                        </template>
                    </div>
                </div>
            </div>

            <!-- MODAL: Detalle Completo de Ficha y Respuestas -->
            <div x-show="showDetailModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div x-show="showDetailModal" @click="showDetailModal = false" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                    <div x-show="showDetailModal" class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full">
                        <template x-if="selectedEntrevista">
                            <div class="bg-white">
                                <!-- Modal Header -->
                                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50">
                                    <div>
                                        <h3 class="text-lg font-bold text-gray-900" x-text="`Ficha de Entrevista: ${selectedEntrevista.estudiante_apellido_paterno || ''} ${selectedEntrevista.estudiante_apellido_materno || ''}, ${selectedEntrevista.estudiante_nombres || ''}`"></h3>
                                        <p class="text-xs text-gray-500 mt-0.5" x-text="`Postula a: ${selectedEntrevista.grado_postula} (${selectedEntrevista.nivel}) · Año ${selectedEntrevista.ano_lectivo_postula}`"></p>
                                    </div>
                                    <button type="button" @click="showDetailModal = false" class="text-gray-400 hover:text-gray-600 text-2xl font-light">&times;</button>
                                </div>

                                <!-- Modal Content Body -->
                                <div class="px-6 py-5 max-h-[75vh] overflow-y-auto space-y-6">
                                    <!-- Datos Estudiante -->
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

                                    <!-- Datos Padres y Apoderado -->
                                    <div class="bg-gray-50 p-4 rounded-xl border border-gray-200">
                                        <h4 class="text-xs font-bold text-gray-900 uppercase tracking-wider mb-3">2. Datos de los Padres y Apoderado</h4>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                                            <!-- Padre -->
                                            <div class="p-3 bg-white rounded-lg border border-gray-100">
                                                <span class="font-bold text-gray-700 block mb-1">Padre:</span>
                                                <p class="font-semibold text-gray-900" x-text="selectedEntrevista.padre_nombres || 'No registrado'"></p>
                                                <p class="text-gray-500" x-text="`DNI: ${selectedEntrevista.padre_dni || '-'} | Tel: ${selectedEntrevista.padre_telefono || '-'}`"></p>
                                                <p class="text-gray-500" x-text="`Ocupación: ${selectedEntrevista.padre_ocupacion || '-'} | Estudios: ${selectedEntrevista.padre_grado_estudios || '-'}`"></p>
                                                <p class="text-gray-500 mt-1" x-text="`Dirección: ${selectedEntrevista.padre_domicilio || '-'}`"></p>
                                            </div>
                                            <!-- Madre -->
                                            <div class="p-3 bg-white rounded-lg border border-gray-100">
                                                <span class="font-bold text-gray-700 block mb-1">Madre:</span>
                                                <p class="font-semibold text-gray-900" x-text="selectedEntrevista.madre_nombres || 'No registrada'"></p>
                                                <p class="text-gray-500" x-text="`DNI: ${selectedEntrevista.madre_dni || '-'} | Tel: ${selectedEntrevista.madre_telefono || '-'}`"></p>
                                                <p class="text-gray-500" x-text="`Ocupación: ${selectedEntrevista.madre_ocupacion || '-'} | Estudios: ${selectedEntrevista.madre_grado_estudios || '-'}`"></p>
                                                <p class="text-gray-500 mt-1" x-text="`Dirección: ${selectedEntrevista.madre_domicilio || '-'}`"></p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Respuestas del Cuestionario -->
                                    <div>
                                        <h4 class="text-xs font-bold text-gray-900 uppercase tracking-wider mb-3">3. Respuestas de la Entrevista y Cuestionario</h4>
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
                                            <p class="text-xs text-gray-500 italic">No se registraron respuestas adicionales en este archivo.</p>
                                        </template>
                                    </div>

                                    <!-- Dictamen y Archivo -->
                                    <div class="p-4 rounded-xl bg-gray-50 border border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs">
                                        <div>
                                            <span class="text-gray-500 block">Archivo Original:</span>
                                            <span class="font-mono font-medium text-gray-800" x-text="selectedEntrevista.archivo_excel_origen || 'No especificado'"></span>
                                        </div>
                                        <div>
                                            <span class="text-gray-500 block">Resultado Conformidad:</span>
                                            <span class="font-bold" :class="selectedEntrevista.resultado_conformidad === 'CONFORME' ? 'text-emerald-600' : 'text-rose-600'" x-text="selectedEntrevista.resultado_conformidad"></span>
                                        </div>
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

            <!-- MODAL: Asignar Rol de Entrevistador a Docentes -->
            <div x-show="showEntrevistadoresModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div x-show="showEntrevistadoresModal" @click="showEntrevistadoresModal = false" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                    <div x-show="showEntrevistadoresModal" class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                        <div class="bg-white">
                            <!-- Modal Header -->
                            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                        </svg>
                                        Asignar Rol de Entrevistador
                                    </h3>
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        Solo los docentes con este rol activo podrán ver la opción de <strong>Admisión 2027</strong> en su panel y subir fichas.
                                    </p>
                                </div>
                                <button type="button" @click="showEntrevistadoresModal = false" class="text-gray-400 hover:text-gray-600 text-2xl font-light">&times;</button>
                            </div>

                            <!-- Search & Counters -->
                            <div class="px-6 pt-4 pb-2 border-b border-gray-100 bg-white">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                    <div class="relative flex-1">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                            </svg>
                                        </div>
                                        <input type="text" x-model="searchDocente" placeholder="Buscar docente por nombre o DNI..." class="w-full pl-9 text-xs rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                    <div class="flex items-center gap-2 shrink-0">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                            <span class="w-2 h-2 rounded-full bg-indigo-600 mr-1.5"></span>
                                            <span x-text="`${totalEntrevistadores} asignados`"></span>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Docentes List -->
                            <div class="px-6 py-2 max-h-[55vh] overflow-y-auto divide-y divide-gray-100">
                                <template x-for="docente in filteredDocentes" :key="docente.id">
                                    <div class="py-3 flex items-center justify-between gap-3 hover:bg-gray-50/75 rounded-lg px-2 transition">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-full flex items-center justify-center font-bold text-xs" :class="docente.es_entrevistador ? 'bg-indigo-100 text-indigo-700 border border-indigo-200' : 'bg-gray-100 text-gray-600'">
                                                <span x-text="docente.nombre_completo.substring(0, 2).toUpperCase()"></span>
                                            </div>
                                            <div>
                                                <p class="text-xs font-bold text-gray-900" x-text="docente.nombre_completo"></p>
                                                <div class="flex items-center gap-2 mt-0.5">
                                                    <span class="text-[11px] font-mono text-gray-500" x-text="`DNI: ${docente.dni || 'Sin DNI'}`"></span>
                                                    <span class="text-gray-300">·</span>
                                                    <span class="text-[11px] text-gray-500 capitalize" x-text="docente.nivel"></span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-3">
                                            <span x-show="docente.es_entrevistador" class="hidden sm:inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-100 text-emerald-800">
                                                Entrevistador
                                            </span>
                                            <span x-show="!docente.es_entrevistador" class="hidden sm:inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-gray-100 text-gray-600">
                                                Sin rol
                                            </span>

                                            <!-- Toggle Switch Button -->
                                            <button type="button" @click="toggleDocente(docente)" :disabled="docente.updating" class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2" :class="docente.es_entrevistador ? 'bg-indigo-600' : 'bg-gray-200'">
                                                <span class="sr-only">Asignar rol</span>
                                                <span aria-hidden="true" class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out" :class="docente.es_entrevistador ? 'translate-x-5' : 'translate-x-0'"></span>
                                            </button>
                                        </div>
                                    </div>
                                </template>

                                <template x-if="filteredDocentes.length === 0">
                                    <div class="py-8 text-center text-xs text-gray-500">
                                        No se encontraron docentes con ese nombre o DNI.
                                    </div>
                                </template>
                            </div>

                            <!-- Modal Footer -->
                            <div class="px-6 py-3 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                                <span class="text-[11px] text-gray-500">
                                    Los cambios se guardan automáticamente al activar el interruptor.
                                </span>
                                <button type="button" @click="showEntrevistadoresModal = false" class="px-4 py-2 bg-gray-800 text-white rounded-lg text-xs font-semibold hover:bg-gray-700 transition">
                                    Listo
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>

