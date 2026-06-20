<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Gestión de Grados y Secciones') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="{ 
        showModal: false, 
        showTutorModal: false,
        showDetalleModal: false,
        detalleLoading: false,
        tutorLoading: false,
        isEditingDocentes: false,
        savingDocentes: false,
        showConfirmEditDocentes: false,
        detalleData: { seccion_nombre: '', estudiantes: [], docentes: [], cursos_con_docentes: [] },
        selectedGs: null,
        docentes: {{ $docentes->toJson() }},
        assignedTutors: {{ json_encode($assignedTutors ?? []) }},
        /* Estado para el modal de tutores */
        originalTutorId: null,
        originalCotutorId: null,
        tutorNombreActual: null,
        cotutorNombreActual: null,
        nuevoTutorId: null,
        nuevoCotutorId: null,
        get tutorCambiando() {
            return this.originalTutorId && this.nuevoTutorId && String(this.nuevoTutorId) !== String(this.originalTutorId);
        },
        get cotutorCambiando() {
            return this.originalCotutorId && this.nuevoCotutorId && String(this.nuevoCotutorId) !== String(this.originalCotutorId);
        },
        async abrirTutorModal(gs) {
            this.selectedGs = gs;
            this.tutorLoading = true;
            this.showTutorModal = true;
            this.nuevoTutorId = null;
            this.nuevoCotutorId = null;
            this.originalTutorId = null;
            this.originalCotutorId = null;
            this.tutorNombreActual = null;
            this.cotutorNombreActual = null;
            try {
                const url = '/admin/grado-secciones/' + gs.id + '/tutores';
                const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                const data = await res.json();
                this.originalTutorId    = data.tutor_id   ? String(data.tutor_id)   : null;
                this.originalCotutorId  = data.cotutor_id ? String(data.cotutor_id) : null;
                this.nuevoTutorId       = this.originalTutorId;
                this.nuevoCotutorId     = this.originalCotutorId;
                this.tutorNombreActual  = data.tutor_nombre   || null;
                this.cotutorNombreActual= data.cotutor_nombre || null;
            } catch(e) {
                console.error(e);
            } finally {
                this.tutorLoading = false;
            }
        },
        async abrirDetalle(gsId) {
            this.detalleLoading = true;
            this.showDetalleModal = true;
            this.isEditingDocentes = false;
            this.detalleData = { seccion_nombre: '', estudiantes: [], docentes: [], cursos_con_docentes: [] };
            try {
                const url = '/admin/grado-secciones/' + gsId + '/detalle';
                const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                const data = await res.json();
                if (data.cursos_con_docentes) {
                    data.cursos_con_docentes.forEach(c => {
                        c.docente_id = c.docente_id ? String(c.docente_id) : '';
                    });
                }
                this.detalleData = data;
            } catch(e) {
                console.error(e);
            } finally {
                this.detalleLoading = false;
            }
        },
        async toggleEditDocentes() {
            if (!this.isEditingDocentes) {
                this.showConfirmEditDocentes = true;
            } else {
                this.savingDocentes = true;
                try {
                    const url = '/admin/grado-secciones/' + this.detalleData.grado_seccion_id + '/docentes';
                    const payload = {
                        _method: 'PATCH',
                        _token: '{{ csrf_token() }}',
                        asignaciones: this.detalleData.cursos_con_docentes.map(c => ({
                            curso_id: c.curso_id,
                            docente_id: c.docente_id || null
                        }))
                    };
                    
                    const res = await fetch(url, {
                        method: 'POST',
                        headers: { 
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify(payload)
                    });
                    
                    if (!res.ok) throw new Error('Error al guardar');
                    
                    this.detalleData.cursos_con_docentes.forEach(c => {
                        if (c.docente_id) {
                            const found = c.disponibles.find(d => String(d.id) === String(c.docente_id));
                            if (found) c.docente_nombre = found.nombre;
                        } else {
                            c.docente_nombre = null;
                        }
                    });
                    this.isEditingDocentes = false;
                } catch(e) {
                    alert('Ocurrió un error al guardar los docentes.');
                    console.error(e);
                } finally {
                    this.savingDocentes = false;
                }
            }
        },
        confirmarEdicion() {
            this.isEditingDocentes = true;
            this.showConfirmEditDocentes = false;
        },
        get availableDocentesTutor() {
            if (!this.selectedGs) return [];
            return this.docentes.filter(d => {
                if (d.nivel !== this.selectedGs.nivel) return false;
                const assignedGsId = Object.keys(this.assignedTutors).find(id => this.assignedTutors[id] == d.id);
                if (assignedGsId && assignedGsId != this.selectedGs.id) return false;
                return true;
            });
        },
        get availableDocentesCotutor() {
            if (!this.selectedGs) return [];
            return this.docentes.filter(d => {
                if (d.nivel !== this.selectedGs.nivel) return false;
                return true;
            });
        },
        /* Modal de confirmación de eliminación */
        showDeleteModal: false,
        deleteGsId: null,
        deleteGsName: '',
        deleteEstudiantesCount: 0,
        deleteTimer: 5,
        deleteInterval: null,
        deleteCanConfirm: false,
        abrirDeleteModal(id, nombre, estudiantes) {
            this.deleteGsId = id;
            this.deleteGsName = nombre;
            this.deleteEstudiantesCount = estudiantes;
            this.showDeleteModal = true;
            this.deleteTimer = 5;
            this.deleteCanConfirm = false;
            if(this.deleteInterval) clearInterval(this.deleteInterval);
            this.deleteInterval = setInterval(() => {
                this.deleteTimer--;
                if(this.deleteTimer <= 0) {
                    clearInterval(this.deleteInterval);
                    this.deleteCanConfirm = true;
                }
            }, 1000);
        },
        cerrarDeleteModal() {
            this.showDeleteModal = false;
            if(this.deleteInterval) clearInterval(this.deleteInterval);
        },
        /* ── Nómina Modal ──────────────────────────────── */
        showNominaModal: false,
        nominaLoading: false,
        nominaData: { seccion_nombre: '', anio: '', nivel: '', tutor: null, cotutor: null, estudiantes: [] },
        async abrirNomina(gsId) {
            this.nominaLoading = true;
            this.showNominaModal = true;
            this.nominaData = { seccion_nombre: '', anio: '', nivel: '', tutor: null, cotutor: null, estudiantes: [] };
            try {
                const url = '/admin/grado-secciones/' + gsId + '/detalle';
                const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                const data = await res.json();
                this.nominaData = data;
            } catch(e) {
                console.error(e);
            } finally {
                this.nominaLoading = false;
            }
        }
    }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                <div>
                    <h3 class="text-2xl font-bold text-gray-800">Apertura de Secciones</h3>
                    <p class="text-gray-600">Combina los grados con las letras de sección disponibles para el año escolar activo.</p>
                </div>
                <button @click="showModal = true" class="bg-yellow-600 hover:bg-yellow-700 text-white font-bold py-3 px-6 rounded shadow transition flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    Añadir Nueva Sección
                </button>
            </div>

            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative shadow-sm" role="alert">
                    <span class="block sm:inline font-medium">{{ session('success') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative shadow-sm" role="alert">
                    <span class="block sm:inline font-medium">{{ session('error') }}</span>
                </div>
            @endif

            <!-- ─── Filtros de búsqueda ────────────────────────────────────── -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 border border-gray-200">
                <div class="p-5" x-data="{
                    nivel: '{{ request('nivel') }}',
                    grado_id: '{{ request('grado_id') }}',
                    seccion_id: '{{ request('seccion_id') }}',
                    grados: {{ $grados->toJson() }},
                    get filteredGrados() {
                        if (!this.nivel) return this.grados;
                        return this.grados.filter(g => g.nivel === this.nivel);
                    }
                }">
                    <form action="{{ route('admin.grado-secciones.index') }}" method="GET">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                            <!-- Filtro por nivel educativo -->
                            <div>
                                <label for="f_nivel" class="block text-xs font-bold text-gray-600 uppercase mb-1">Nivel Educativo</label>
                                <select name="nivel" id="f_nivel" x-model="nivel" @change="grado_id = ''; seccion_id = ''" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                    <option value="">Todos los niveles</option>
                                    <option value="primaria">Primaria</option>
                                    <option value="secundaria">Secundaria</option>
                                </select>
                            </div>
                            <!-- Filtro por grado -->
                            <div>
                                <label for="f_grado_id" class="block text-xs font-bold text-gray-600 uppercase mb-1">Grado Escolar</label>
                                <select name="grado_id" id="f_grado_id" x-model="grado_id" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                    <option value="">Todos los grados</option>
                                    <template x-for="grado in filteredGrados" :key="grado.id">
                                        <option :value="grado.id" x-text="grado.nombre" :selected="grado.id == grado_id"></option>
                                    </template>
                                </select>
                            </div>
                            <!-- Filtro por sección específica -->
                            <div>
                                <label for="f_seccion_id" class="block text-xs font-bold text-gray-600 uppercase mb-1">Letra de Sección</label>
                                <select name="seccion_id" id="f_seccion_id" x-model="seccion_id" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                    <option value="">Todas las letras</option>
                                    @foreach($secciones as $seccion)
                                        <option value="{{ $seccion->id }}" {{ request('seccion_id') == $seccion->id ? 'selected' : '' }}>
                                            Sección "{{ $seccion->nombre }}"
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="flex gap-2 mt-4">
                            <button type="submit" class="bg-yellow-600 hover:bg-yellow-700 text-white text-sm font-bold py-2 px-5 rounded shadow transition">
                                Filtrar
                            </button>
                            <a href="{{ route('admin.grado-secciones.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 text-sm font-bold py-2 px-5 rounded transition flex items-center justify-center">
                                Limpiar
                            </a>
                        </div>
                    </form>
                </div>
            </div>


            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <div class="p-0 text-gray-900">
                    @if(!$anoActivo)
                        <div class="text-center py-10 bg-red-50 text-red-700 font-bold">
                            No hay un año lectivo activo. No puedes aperturar secciones.
                        </div>
                    @elseif($gradoSeccionesAgrupadas->isEmpty())
                        <div class="text-center py-12 text-gray-500 bg-gray-50">
                            No hay secciones aperturadas para el año lectivo activo. Haz clic en "Añadir Nueva Sección".
                        </div>
                    @else
                        {{-- Agrupar por nivel para mostrar encabezados --}}
                        @php
                            $porNivel = $gradoSeccionesAgrupadas->groupBy(fn($secs) => $secs->first()->grado->nivel);
                        @endphp

                        <div class="p-6" style="display:flex; flex-direction:column; gap:0;">
                            @foreach(['primaria' => 'Primaria', 'secundaria' => 'Secundaria'] as $nivelKey => $nivelLabel)
                                @if($porNivel->has($nivelKey))
                                    @php
                                        $isPrimaria = $nivelKey === 'primaria';

                                        /* ── Colores Primaria: amarillo suave ── */
                                        /* ── Colores Secundaria: celeste suave  ── */
                                        $bannerBg        = $isPrimaria ? '#fefce8' : '#f0f9ff';
                                        $bannerBorder    = $isPrimaria ? '#fde047' : '#7dd3fc';  /* borde lateral */
                                        $bannerBorderAll = $isPrimaria ? '#fef08a' : '#bae6fd';  /* borde exterior */
                                        $badgeBg         = $isPrimaria ? '#facc15' : '#38bdf8';
                                        $badgeText       = $isPrimaria ? '#713f12' : '#0c4a6e';
                                        $titleColor      = $isPrimaria ? '#854d0e' : '#075985';
                                        $lineColor       = $isPrimaria ? '#fde68a' : '#bae6fd';

                                        $cardHeaderBg    = $isPrimaria ? '#fefce8' : '#f0f9ff';
                                        $cardHeaderBorder= $isPrimaria ? '#fef08a' : '#bae6fd';
                                        $dotBg           = $isPrimaria ? '#facc15' : '#38bdf8';
                                        $gradoColor      = $isPrimaria ? '#713f12' : '#0c4a6e';
                                        $countColor      = $isPrimaria ? '#a16207' : '#0369a1';

                                        $letraBg         = $isPrimaria ? '#fef9c3' : '#e0f2fe';
                                        $letraText       = $isPrimaria ? '#713f12' : '#0c4a6e';
                                        $letraBorder     = $isPrimaria ? '#fde68a' : '#bae6fd';
                                    @endphp

                                    {{-- Separador visible entre Primaria y Secundaria --}}
                                    @if(!$isPrimaria && $porNivel->has('primaria'))
                                        <div style="margin: 2.5rem 0; display:flex; align-items:center; gap:1rem;">
                                            <div style="flex:1; height:2px; background: linear-gradient(to right, #e5e7eb, transparent);"></div>
                                            <span style="font-size:0.65rem; font-weight:700; letter-spacing:0.1em; color:#9ca3af; text-transform:uppercase;">─── Nivel Secundaria ───</span>
                                            <div style="flex:1; height:2px; background: linear-gradient(to left, #e5e7eb, transparent);"></div>
                                        </div>
                                    @else
                                        <div style="margin-bottom: 0;"></div>
                                    @endif

                                    {{-- Banner de nivel --}}
                                    <div>
                                        <div style="display:flex; align-items:center; gap:1rem; margin-bottom:1.5rem; padding:0.75rem 1.25rem;
                                                    background:{{ $bannerBg }}; border:1px solid {{ $bannerBorderAll }};
                                                    border-left:4px solid {{ $bannerBorder }}; border-radius:0.75rem;">
                                            <span style="font-size:0.7rem; font-weight:800; text-transform:uppercase; letter-spacing:0.08em;
                                                         padding:0.2rem 0.75rem; border-radius:9999px;
                                                         background:{{ $badgeBg }}; color:{{ $badgeText }};">
                                                {{ $nivelLabel }}
                                            </span>
                                            <span style="font-size:0.875rem; font-weight:700; color:{{ $titleColor }};">
                                                {{ $porNivel[$nivelKey]->count() }} {{ $porNivel[$nivelKey]->count() === 1 ? 'grado' : 'grados' }} aperturados
                                            </span>
                                            <div style="flex:1; height:1px; background:{{ $lineColor }};"></div>
                                        </div>

                                        {{-- Grid de tarjetas por grado --}}
                                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                                            @foreach($porNivel[$nivelKey]->sortBy(fn($s) => optional($s->first()->grado)->orden ?? 0) as $gradoId => $seccionesDelGrado)
                                                @php $primerGs = $seccionesDelGrado->first(); @endphp
                                                <div class="rounded-xl overflow-hidden shadow-sm" style="border:1px solid #e5e7eb;">
                                                    {{-- Cabecera de grado --}}
                                                    <div style="background:{{ $cardHeaderBg }}; border-bottom:1px solid {{ $cardHeaderBorder }}; padding:0.75rem 1rem; display:flex; align-items:center; justify-content:space-between;">
                                                        <div style="display:flex; align-items:center; gap:0.5rem;">
                                                            <span style="width:0.625rem; height:0.625rem; border-radius:9999px; background:{{ $dotBg }}; display:inline-block;"></span>
                                                            <span style="font-weight:800; color:{{ $gradoColor }}; font-size:0.875rem;">
                                                                {{ $primerGs->grado->nombre }}
                                                            </span>
                                                        </div>
                                                        <span style="font-size:0.75rem; font-weight:600; color:{{ $countColor }};">
                                                            {{ $seccionesDelGrado->count() }} {{ $seccionesDelGrado->count() === 1 ? 'sección' : 'secciones' }}
                                                        </span>
                                                    </div>

                                                    {{-- Filas de secciones --}}
                                                    <div class="divide-y divide-gray-100">
                                                        @foreach($seccionesDelGrado as $gs)
                                                            <div class="px-4 py-3">
                                                                {{-- Letra + tutores --}}
                                                                <div class="flex items-start justify-between gap-3">
                                                                    <div class="flex items-center gap-2 shrink-0">
                                                                        <span style="width:2rem; height:2rem; border-radius:9999px; font-weight:800; font-size:0.875rem;
                                                                                     display:flex; align-items:center; justify-content:center;
                                                                                     background:{{ $letraBg }}; color:{{ $letraText }}; border:1px solid {{ $letraBorder }};">
                                                                            {{ $gs->seccion->nombre }}
                                                                        </span>
                                                                        <span class="text-xs text-gray-500 font-medium">{{ $anoActivo->anio }}</span>
                                                                    </div>

                                                                    {{-- Info tutores --}}
                                                                    <div class="flex-1 min-w-0">
                                                                        @if($gs->tutor)
                                                                            <div class="text-xs text-gray-700 truncate">
                                                                                <span class="font-bold text-gray-500">Tutor:</span>
                                                                                {{ $gs->tutor->apellido_paterno }}, {{ $gs->tutor->nombres }}
                                                                            </div>
                                                                        @else
                                                                            <div class="text-xs text-red-500 font-semibold">Sin tutor</div>
                                                                        @endif
                                                                        @if($gs->cotutor)
                                                                            <div class="text-xs text-gray-500 truncate mt-0.5">
                                                                                <span class="font-bold">Co-tutor:</span>
                                                                                {{ $gs->cotutor->apellido_paterno }}, {{ $gs->cotutor->nombres }}
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                </div>

                                                                <div class="grid grid-cols-2 gap-1.5 mt-2.5">
                                                                    <button @click="abrirTutorModal({{ json_encode([
                                                                        'id'     => $gs->id,
                                                                        'nombre' => $gs->grado->nombre . ' - Sección ' . $gs->seccion->nombre,
                                                                        'nivel'  => $gs->grado->nivel,
                                                                    ]) }})"
                                                                        class="text-xs font-bold text-blue-700 bg-blue-50 hover:bg-blue-100 border border-blue-200 px-2 py-1.5 rounded-lg transition text-center">
                                                                        Tutores
                                                                    </button>

                                                                    <button @click="abrirDetalle({{ $gs->id }})"
                                                                        class="text-xs font-bold text-green-700 bg-green-50 hover:bg-green-100 border border-green-200 px-2 py-1.5 rounded-lg transition text-center">
                                                                        Ver
                                                                    </button>

                                                                    <button @click="abrirNomina({{ $gs->id }})"
                                                                        class="text-xs font-bold text-violet-700 bg-violet-50 hover:bg-violet-100 border border-violet-200 px-2 py-1.5 rounded-lg transition text-center">
                                                                        Nómina
                                                                    </button>

                                                                    <form id="deleteForm_{{ $gs->id }}" action="{{ route('admin.grado-secciones.destroy', $gs) }}" method="POST">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="button" @click="abrirDeleteModal({{ $gs->id }}, '{{ $gs->grado->nombre }} - {{ $gs->seccion->nombre }}', {{ $gs->matriculas_count ?? 0 }})"
                                                                            class="w-full text-xs font-bold text-red-700 bg-red-50 hover:bg-red-100 border border-red-200 px-2 py-1.5 rounded-lg transition text-center">
                                                                            ✕ Cerrar
                                                                        </button>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

        </div>

        <div x-show="showModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" x-cloak>
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                
                <div x-show="showModal" 
                     x-transition:enter="ease-out duration-300" 
                     x-transition:enter-start="opacity-0" 
                     x-transition:enter-end="opacity-100" 
                     x-transition:leave="ease-in duration-200" 
                     x-transition:leave-start="opacity-100" 
                     x-transition:leave-end="opacity-0" 
                     class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
                     @click="showModal = false" aria-hidden="true"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                
                <div x-show="showModal" 
                     x-transition:enter="ease-out duration-300" 
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                     x-transition:leave="ease-in duration-200" 
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                     class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-xl leading-6 font-bold text-gray-900" id="modal-title">
                                    Aperturar Nueva Sección
                                </h3>
                                <p class="text-sm text-gray-500 mt-2 mb-4">Selecciona un grado y acompáñalo con una letra de sección para crear el aula en el año escolar actual.</p>
                                
                                <div class="mt-4">
                                    <form action="{{ route('admin.grado-secciones.store') }}" method="POST" id="crearSeccionForm">
                                        @csrf
                                        
                                        @if(!$anoActivo)
                                            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                                                <strong class="font-bold">Error!</strong>
                                                <span class="block sm:inline">No hay un año lectivo activo. Debes configurar uno antes de crear secciones.</span>
                                            </div>
                                        @else
                                            <div x-data="{ 
                                                nivel: '', 
                                                grados: {{ $grados->toJson() }},
                                                get filteredGrados() {
                                                    return this.nivel ? this.grados.filter(g => g.nivel === this.nivel) : [];
                                                }
                                            }">
                                                <div class="mb-4">
                                                    <label for="nivel" class="block text-sm font-bold text-gray-700">Nivel Educativo *</label>
                                                    <select x-model="nivel" id="nivel" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-yellow-500 rounded-md shadow-sm bg-gray-50" required>
                                                        <option value="">-- Seleccione el Nivel --</option>
                                                        <option value="primaria">Primaria</option>
                                                        <option value="secundaria">Secundaria</option>
                                                    </select>
                                                </div>

                                                <div class="mb-4">
                                                    <label for="grado_id" class="block text-sm font-bold text-gray-700">Grado Escolar *</label>
                                                    <select name="grado_id" id="grado_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-yellow-500 rounded-md shadow-sm bg-indigo-50" required :disabled="!nivel">
                                                        <option value="">-- Seleccione el Grado --</option>
                                                        <template x-for="grado in filteredGrados" :key="grado.id">
                                                            <option :value="grado.id" x-text="grado.nombre"></option>
                                                        </template>
                                                    </select>
                                                    @error('grado_id') <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                                                </div>
                                            </div>
                                            
                                            <div class="mb-4">
                                                <label for="seccion_id" class="block text-sm font-bold text-gray-700">Letra de la Sección *</label>
                                                <select name="seccion_id" id="seccion_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-yellow-500 rounded-md shadow-sm bg-indigo-50" required>
                                                    <option value="">-- Seleccione la Sección --</option>
                                                    @foreach($secciones as $seccion)
                                                        <option value="{{ $seccion->id }}" {{ old('seccion_id') == $seccion->id ? 'selected' : '' }}>
                                                            Sección "{{ $seccion->nombre }}"
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('seccion_id') <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                                            </div>
                                        @endif
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t">
                        <button type="submit" form="crearSeccionForm" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-yellow-600 text-base font-medium text-white hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 sm:ml-3 sm:w-auto sm:text-sm" {{ !$anoActivo ? 'disabled' : '' }}>
                            Aperturar Sección
                        </button>
                        <button type="button" @click="showModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="showTutorModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-tutores" role="dialog" aria-modal="true" x-cloak>
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showTutorModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showTutorModal = false" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div x-show="showTutorModal" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">

                                <h3 class="text-xl leading-6 font-bold text-gray-900 mb-1">Asignar Tutores</h3>
                                <p class="text-sm text-gray-500 mb-4">
                                    Sección: <span class="font-bold text-indigo-700" x-text="selectedGs ? selectedGs.nombre : ''"></span>
                                </p>

                                <div x-show="tutorLoading" class="flex items-center justify-center py-6">
                                    <svg class="animate-spin h-6 w-6 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                                    </svg>
                                    <span class="ml-2 text-gray-500 text-sm">Cargando asignación actual...</span>
                                </div>

                                <div x-show="!tutorLoading">

                                    <div x-show="tutorCambiando"
                                         class="mb-4 flex items-start gap-2 rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0 text-amber-500 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                                        </svg>
                                        <div>
                                            <p class="font-bold">¿Cambiar el tutor actual?</p>
                                            <p class="mt-0.5">El docente <span class="font-semibold" x-text="'\"' + tutorNombreActual + '\"'"></span> dejará de ver este curso de Tutoría en su panel. Los registros y notas de los alumnos <strong>no se verán afectados</strong>.</p>
                                        </div>
                                    </div>

                                    <div x-show="cotutorCambiando"
                                         class="mb-4 flex items-start gap-2 rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0 text-amber-500 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                                        </svg>
                                        <div>
                                            <p class="font-bold">¿Cambiar el co-tutor actual?</p>
                                            <p class="mt-0.5">El co-tutor <span class="font-semibold" x-text="'\"' + cotutorNombreActual + '\"'"></span> será reemplazado.</p>
                                        </div>
                                    </div>

                                    <form :action="selectedGs ? '/admin/grado-secciones/' + selectedGs.id + '/tutores' : '#'" method="POST" id="asignarTutoresForm">
                                        @csrf
                                        @method('PATCH')

                                        <div class="mb-4">
                                            <label class="block text-sm font-bold text-gray-700 mb-1">
                                                Docente Tutor Principal
                                            </label>
                                            <div x-show="tutorNombreActual && !tutorCambiando"
                                                 class="mb-2 inline-flex items-center gap-1.5 rounded-full bg-blue-100 text-blue-800 text-xs font-semibold px-3 py-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                                <span>Actual: <span x-text="tutorNombreActual"></span></span>
                                            </div>
                                            <select name="tutor_id"
                                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-yellow-500 rounded-md shadow-sm bg-gray-50"
                                                    x-model="nuevoTutorId">
                                                <option value="">-- Sin Tutor --</option>
                                                <template x-for="docente in availableDocentesTutor" :key="docente.id">
                                                    <option :value="String(docente.id)"
                                                            x-text="docente.apellido_paterno + ' ' + docente.apellido_materno + ', ' + docente.nombres">
                                                    </option>
                                                </template>
                                            </select>
                                        </div>

                                        <div class="mb-4">
                                            <label class="block text-sm font-bold text-gray-700 mb-1">
                                                Docente Co-tutor <span class="font-normal text-gray-400">(Opcional)</span>
                                            </label>
                                            <div x-show="cotutorNombreActual && !cotutorCambiando"
                                                 class="mb-2 inline-flex items-center gap-1.5 rounded-full bg-indigo-100 text-indigo-800 text-xs font-semibold px-3 py-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                                <span>Actual: <span x-text="cotutorNombreActual"></span></span>
                                            </div>
                                            <select name="cotutor_id"
                                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-yellow-500 rounded-md shadow-sm bg-indigo-50"
                                                    x-model="nuevoCotutorId">
                                                <option value="">-- Sin Co-tutor --</option>
                                                <template x-for="docente in availableDocentesCotutor" :key="docente.id">
                                                    <option :value="String(docente.id)"
                                                            x-text="docente.apellido_paterno + ' ' + docente.apellido_materno + ', ' + docente.nombres">
                                                    </option>
                                                </template>
                                            </select>
                                        </div>

                                        <div class="mt-2 flex items-start gap-2 rounded-lg bg-sky-50 border border-sky-200 px-3 py-2.5 text-xs text-sky-700">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 mt-0.5 text-sky-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 110 20A10 10 0 0112 2z"/>
                                            </svg>
                                            <span>El Tutor recibirá automáticamente acceso al curso de <strong>Tutoría</strong> de esta sección en su panel docente.</span>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t">
                        <button type="submit" form="asignarTutoresForm"
                                :disabled="tutorLoading"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed sm:ml-3 sm:w-auto sm:text-sm">
                            <span x-show="tutorCambiando || cotutorCambiando">Confirmar Cambio</span>
                            <span x-show="!tutorCambiando && !cotutorCambiando">Guardar Tutores</span>
                        </button>
                        <button type="button" @click="showTutorModal = false"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="showDetalleModal"
             class="fixed inset-0 z-50"
             aria-labelledby="modal-detalle" role="dialog" aria-modal="true" x-cloak>

            <div class="absolute inset-0 bg-gray-900 bg-opacity-50" @click="showDetalleModal = false"></div>

            <div class="relative z-10 flex min-h-full items-center justify-center p-4 pointer-events-none">

                <div class="pointer-events-auto w-full max-w-4xl bg-white rounded-2xl shadow-2xl flex flex-col"
                     style="max-height: 85vh; border: 2px solid #16a34a; box-shadow: 0 0 0 4px rgba(22,163,74,0.15), 0 25px 50px -12px rgba(0,0,0,0.4);"
                     @click.stop>

                    <div class="flex items-center justify-between px-5 py-3 rounded-t-2xl shrink-0" 
                         :style="detalleData.nivel === 'primaria' ? 'background: linear-gradient(to right, #ca8a04, #eab308);' : 'background: linear-gradient(to right, #0284c7, #0ea5e9);'">
                        <div>
                            <h3 class="text-base font-extrabold text-white" id="modal-detalle">Detalle de Sección</h3>
                            <div class="mt-1 inline-block px-3 py-1 rounded-lg text-white text-xs font-bold shadow-sm" style="background-color: rgba(255,255,255,0.25); border: 1px solid rgba(255,255,255,0.4);">
                                <span x-text="detalleData.seccion_nombre"></span>
                            </div>
                        </div>
                        <button @click="showDetalleModal = false"
                                class="ml-4 shrink-0 rounded-lg p-1.5 transition"
                                style="background-color: rgba(255,255,255,0.2); color: white;"
                                onmouseover="this.style.backgroundColor='rgba(255,255,255,0.35)'"
                                onmouseout="this.style.backgroundColor='rgba(255,255,255,0.2)'"
                                title="Cerrar">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div x-show="detalleLoading" class="flex items-center justify-center py-10 shrink-0">
                        <svg class="animate-spin h-6 w-6 text-green-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                        </svg>
                        <span class="ml-2 text-gray-500 text-sm">Cargando datos...</span>
                    </div>

                    <div x-show="!detalleLoading" class="flex-1 overflow-y-auto min-h-0">
                        <div class="flex flex-col md:flex-row divide-y md:divide-y-0 md:divide-x divide-gray-100">

                            <div class="p-4 flex flex-col w-full md:w-1/2">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-indigo-100 text-indigo-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                    </span>
                                    <h4 class="font-bold text-gray-700 text-sm">Estudiantes</h4>
                                    <span class="ml-auto text-xs font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-full"
                                          x-text="detalleData.estudiantes.length + ' alumnos'"></span>
                                </div>
                                <div class="rounded-lg border border-gray-200 overflow-hidden">
                                    <div>
                                        <table class="w-full text-xs">
                                            <thead class="bg-indigo-50 sticky top-0">
                                                <tr>
                                                    <th class="text-left px-2 py-2 font-bold text-indigo-700 border-b border-indigo-100 w-5">#</th>
                                                    <th class="text-left px-2 py-2 font-bold text-indigo-700 border-b border-indigo-100 w-20">DNI</th>
                                                    <th class="text-left px-2 py-2 font-bold text-indigo-700 border-b border-indigo-100">Apellidos</th>
                                                    <th class="text-left px-2 py-2 font-bold text-indigo-700 border-b border-indigo-100">Nombres</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <template x-if="detalleData.estudiantes.length === 0">
                                                    <tr><td colspan="4" class="text-center py-8 text-gray-400 italic">Sin estudiantes matriculados.</td></tr>
                                                </template>
                                                <template x-for="(est, idx) in detalleData.estudiantes" :key="est.id">
                                                    <tr class="border-b border-gray-100 last:border-0 hover:bg-indigo-50 transition-colors">
                                                        <td class="px-2 py-1.5 text-gray-400" x-text="idx + 1"></td>
                                                        <td class="px-2 py-1.5 font-mono text-gray-500" x-text="est.dni"></td>
                                                        <td class="px-2 py-1.5 font-semibold text-gray-800" x-text="est.apellidos"></td>
                                                        <td class="px-2 py-1.5 text-gray-600" x-text="est.nombres"></td>
                                                    </tr>
                                                </template>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="p-4 flex flex-col w-full md:w-1/2">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-amber-100 text-amber-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 7v-7m0 0l-9-5m9 5l9-5"/>
                                        </svg>
                                    </span>
                                    <h4 class="font-bold text-gray-700 text-sm">Docentes</h4>
                                    <span class="text-xs font-bold text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full"
                                          x-text="(detalleData.cursos_con_docentes ? detalleData.cursos_con_docentes.length : 0) + ' cursos'"></span>
                                    
                                    <button @click="toggleEditDocentes()"
                                            class="ml-auto flex items-center justify-center w-9 h-9 rounded-lg transition-colors shadow-sm"
                                            :style="isEditingDocentes ? 'background-color: #059669; color: white;' : 'background-color: #16a34a; color: white;'"
                                            :title="isEditingDocentes ? 'Guardar Cambios' : 'Editar Docentes'"
                                            onmouseover="this.style.filter='brightness(1.1)'"
                                            onmouseout="this.style.filter='brightness(1)'">
                                        <svg x-show="!isEditingDocentes" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                        <svg x-show="isEditingDocentes" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                    </button>
                                </div>
                                <div class="rounded-lg border border-gray-200 overflow-hidden relative">
                                    <div>
                                        <div x-show="savingDocentes" class="absolute inset-0 bg-white/80 z-10 flex flex-col items-center justify-center">
                                            <svg class="animate-spin h-6 w-6 text-green-600 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg>
                                            <span class="text-xs font-bold text-gray-700">Guardando...</span>
                                        </div>

                                        <template x-if="detalleData.cursos_con_docentes && detalleData.cursos_con_docentes.length === 0">
                                            <div class="text-center py-8 text-gray-400 italic text-xs">Sin cursos configurados.</div>
                                        </template>
                                        <template x-for="(curso, idx) in detalleData.cursos_con_docentes" :key="curso.curso_id">
                                            <div class="flex items-start gap-3 px-3 py-2.5 border-b border-gray-100 last:border-0 hover:bg-amber-50 transition-colors">
                                                <span class="flex-shrink-0 w-5 text-xs text-gray-400 pt-0.5" x-text="idx + 1 + '.'"></span>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-xs font-extrabold text-amber-700 uppercase tracking-wide" x-text="curso.curso_nombre"></p>
                                                    
                                                    <template x-if="!isEditingDocentes">
                                                        <p class="text-xs mt-0.5" :class="curso.docente_id ? 'text-gray-600' : 'text-gray-400 font-semibold italic'" x-text="curso.docente_id ? curso.docente_nombre : '- Sin docente asignado -'"></p>
                                                    </template>

                                                    <template x-if="isEditingDocentes">
                                                        <select :value="curso.docente_id" @change="curso.docente_id = $event.target.value" class="mt-1 block w-full py-1 px-2 border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded text-xs bg-white">
                                                            <option value="">- Sin docente -</option>
                                                            <template x-for="disp in curso.disponibles" :key="disp.id">
                                                                <option :value="String(disp.id)" :selected="curso.docente_id === String(disp.id)" x-text="disp.nombre"></option>
                                                            </template>
                                                        </select>
                                                    </template>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="px-5 py-3 bg-gray-50 rounded-b-2xl flex justify-end border-t shrink-0">
                        <button @click="showDetalleModal = false"
                            class="inline-flex items-center gap-2 text-sm font-bold px-5 py-2 rounded-lg transition shadow-sm"
                            style="background-color: #dc2626; color: white; border: 1px solid #b91c1c;"
                            onmouseover="this.style.backgroundColor='#b91c1c'"
                            onmouseout="this.style.backgroundColor='#dc2626'">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Cerrar
                        </button>
                    </div>

                </div>
            </div>
        </div>

        <div x-show="showNominaModal"
             class="fixed inset-0 z-50"
             aria-labelledby="modal-nomina" role="dialog" aria-modal="true" x-cloak>

            <div class="absolute inset-0 bg-gray-900 bg-opacity-50" @click="showNominaModal = false"></div>

            <div class="relative z-10 flex min-h-full items-center justify-center p-4 pointer-events-none">

                <div class="pointer-events-auto w-full max-w-3xl bg-white rounded-2xl shadow-2xl flex flex-col"
                     style="max-height: 85vh; border: 2px solid #7c3aed; box-shadow: 0 0 0 4px rgba(124,58,237,0.15), 0 25px 50px -12px rgba(0,0,0,0.4);"
                     @click.stop>

                    <div class="flex items-center justify-between px-5 py-3 rounded-t-2xl shrink-0"
                         :style="nominaData.nivel === 'primaria'
                             ? 'background: linear-gradient(to right, #ca8a04, #eab308);'
                             : 'background: linear-gradient(to right, #7c3aed, #8b5cf6);'">
                        <div>
                            <h3 class="text-base font-extrabold text-white" id="modal-nomina">Nómina de Estudiantes</h3>
                            <div class="mt-1 flex items-center gap-2 flex-wrap">
                                <div class="inline-block px-3 py-1 rounded-lg text-white text-xs font-bold shadow-sm"
                                     style="background-color: rgba(255,255,255,0.25); border: 1px solid rgba(255,255,255,0.4);">
                                    <span x-text="nominaData.seccion_nombre"></span>
                                </div>
                                <div class="inline-block px-3 py-1 rounded-lg text-white text-xs font-bold"
                                     style="background-color: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.3);">
                                    Año <span x-text="nominaData.anio"></span>
                                </div>
                            </div>
                        </div>
                        <button @click="showNominaModal = false"
                                class="ml-4 shrink-0 rounded-lg p-1.5 transition"
                                style="background-color: rgba(255,255,255,0.2); color: white;"
                                onmouseover="this.style.backgroundColor='rgba(255,255,255,0.35)'"
                                onmouseout="this.style.backgroundColor='rgba(255,255,255,0.2)'"
                                title="Cerrar">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div x-show="nominaLoading" class="flex items-center justify-center py-10 shrink-0">
                        <svg class="animate-spin h-6 w-6 text-violet-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                        </svg>
                        <span class="ml-2 text-gray-500 text-sm">Cargando nómina...</span>
                    </div>

                    <div x-show="!nominaLoading" class="flex-1 overflow-y-auto min-h-0 p-5">

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-5">
                            <div class="rounded-xl border border-blue-200 bg-blue-50 p-3">
                                <p class="text-xs font-extrabold text-blue-700 uppercase tracking-wide mb-1.5">Tutor Principal</p>
                                <template x-if="nominaData.tutor">
                                    <div>
                                        <p class="text-sm font-bold text-gray-800" x-text="nominaData.tutor.nombre_completo"></p>
                                        <p class="text-xs text-blue-600 mt-0.5" x-show="nominaData.tutor && nominaData.tutor.dni" x-text="'DNI: ' + (nominaData.tutor ? nominaData.tutor.dni : '')"></p>
                                    </div>
                                </template>
                                <template x-if="!nominaData.tutor">
                                    <p class="text-sm text-gray-400 italic">— Sin tutor asignado —</p>
                                </template>
                            </div>
                            <div class="rounded-xl border border-indigo-200 bg-indigo-50 p-3">
                                <p class="text-xs font-extrabold text-indigo-700 uppercase tracking-wide mb-1.5">Co-tutor</p>
                                <template x-if="nominaData.cotutor">
                                    <div>
                                        <p class="text-sm font-bold text-gray-800" x-text="nominaData.cotutor.nombre_completo"></p>
                                        <p class="text-xs text-indigo-600 mt-0.5" x-show="nominaData.cotutor && nominaData.cotutor.dni" x-text="'DNI: ' + (nominaData.cotutor ? nominaData.cotutor.dni : '')"></p>
                                    </div>
                                </template>
                                <template x-if="!nominaData.cotutor">
                                    <p class="text-sm text-gray-400 italic">— Sin co-tutor asignado —</p>
                                </template>
                            </div>
                        </div>

                        <div class="rounded-xl border border-gray-200 overflow-hidden">
                            <div class="flex items-center gap-2 px-3 py-2 bg-violet-50 border-b border-violet-100">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span class="text-xs font-extrabold text-violet-700 uppercase tracking-wide">Lista de Estudiantes</span>
                                <span class="ml-auto text-xs font-bold text-violet-600 bg-white px-2 py-0.5 rounded-full border border-violet-200"
                                      x-text="nominaData.estudiantes.length + ' alumno(s)'"></span>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full text-xs">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="text-left px-3 py-2 font-bold text-gray-600 border-b border-gray-200 w-8">#</th>
                                            <th class="text-left px-3 py-2 font-bold text-gray-600 border-b border-gray-200">DNI</th>
                                            <th class="text-left px-3 py-2 font-bold text-gray-600 border-b border-gray-200">Cód. Estudiante</th>
                                            <th class="text-left px-3 py-2 font-bold text-gray-600 border-b border-gray-200">Apellidos y Nombres</th>
                                            <th class="text-left px-3 py-2 font-bold text-gray-600 border-b border-gray-200 whitespace-nowrap">Fec. Nacimiento</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-if="nominaData.estudiantes.length === 0">
                                            <tr>
                                                <td colspan="5" class="text-center py-8 text-gray-400 italic">Sin estudiantes matriculados en esta sección.</td>
                                            </tr>
                                        </template>
                                        <template x-for="(est, idx) in nominaData.estudiantes" :key="est.id">
                                            <tr class="border-b border-gray-100 last:border-0 hover:bg-violet-50 transition-colors">
                                                <td class="px-3 py-2 text-gray-400 font-semibold" x-text="idx + 1"></td>
                                                <td class="px-3 py-2 font-mono text-gray-600" x-text="est.dni"></td>
                                                <td class="px-3 py-2 font-mono text-gray-500" x-text="est.codigo_estudiante || '—'"></td>
                                                <td class="px-3 py-2 font-semibold text-gray-800" x-text="est.apellidos + ',  ' + est.nombres"></td>
                                                <td class="px-3 py-2 text-gray-500 whitespace-nowrap" x-text="est.fecha_nacimiento"></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="px-5 py-3 bg-gray-50 rounded-b-2xl flex justify-end border-t shrink-0">
                        <button @click="showNominaModal = false"
                                class="inline-flex items-center gap-2 text-sm font-bold px-5 py-2 rounded-lg transition shadow-sm"
                                style="background-color: #dc2626; color: white; border: 1px solid #b91c1c;"
                                onmouseover="this.style.backgroundColor='#b91c1c'"
                                onmouseout="this.style.backgroundColor='#dc2626'">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="showConfirmEditDocentes" class="fixed inset-0 overflow-y-auto" style="z-index: 1050;" aria-labelledby="modal-confirm" role="dialog" aria-modal="true" x-cloak>
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showConfirmEditDocentes" 
                     x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" 
                     x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" @click="showConfirmEditDocentes = false" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div x-show="showConfirmEditDocentes" 
                     x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                     x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full"
                     style="box-shadow: 0 0 0 4px rgba(234, 179, 8, 0.25), 0 25px 50px -12px rgba(0, 0, 0, 0.5); border: 2px solid #eab308;">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-yellow-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-bold text-gray-900" id="modal-confirm">
                                    Modificar Docentes
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        ¿Está seguro de querer agregar o modificar los docentes asignados a los cursos de esta sección?
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 flex flex-col sm:flex-row-reverse gap-2 border-t border-gray-200">
                        <button type="button" @click="confirmarEdicion()" 
                                class="w-full inline-flex justify-center rounded-lg shadow-sm px-4 py-2 text-base font-bold text-white focus:outline-none sm:w-auto sm:text-sm transition-colors"
                                style="background-color: #16a34a; border: 1px solid #15803d;"
                                onmouseover="this.style.backgroundColor='#15803d'"
                                onmouseout="this.style.backgroundColor='#16a34a'">
                            Sí, editar
                        </button>
                        <button type="button" @click="showConfirmEditDocentes = false" 
                                class="w-full inline-flex justify-center rounded-lg shadow-sm px-4 py-2 text-base font-bold text-white focus:outline-none sm:w-auto sm:text-sm transition-colors"
                                style="background-color: #dc2626; border: 1px solid #b91c1c;"
                                onmouseover="this.style.backgroundColor='#b91c1c'"
                                onmouseout="this.style.backgroundColor='#dc2626'">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- ── /Modal Confirmación Edición Docentes ───────────────────────────── -->

        <!-- ── Modal Confirmar Cierre de Sección ────────────────────────────── -->
        <div x-show="showDeleteModal" class="fixed inset-0 overflow-y-auto" style="z-index: 1060;" aria-labelledby="modal-delete" role="dialog" aria-modal="true" x-cloak>
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showDeleteModal" 
                     x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" 
                     x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" @click="cerrarDeleteModal()" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div x-show="showDeleteModal" 
                     x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                     x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                     class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full"
                     style="border: 4px solid #ef4444; border-radius: 0.75rem;">
                    
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 rounded-t-md">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-bold text-gray-900" id="modal-delete">
                                    ¿Cerrar la sección <span class="text-red-600" x-text="deleteGsName"></span>?
                                </h3>
                                <div class="mt-3">
                                    <p class="text-sm text-gray-500 mb-3">
                                        Estás a punto de cerrar y eliminar esta sección del sistema.
                                    </p>
                                    <template x-if="deleteEstudiantesCount > 0">
                                        <div class="p-3 bg-red-50 border border-red-200 rounded-md text-red-800 text-sm font-semibold">
                                            ¡Atención! Hemos detectado <span x-text="deleteEstudiantesCount"></span> estudiante(s) matriculado(s) en esta sección. Sus datos y asignaciones se verán afectados.
                                        </div>
                                    </template>
                                    <template x-if="deleteEstudiantesCount === 0">
                                        <div class="p-3 bg-green-50 border border-green-200 rounded-md text-green-800 text-sm font-semibold">
                                            No hay estudiantes matriculados en esta sección, por lo que su eliminación es segura.
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 flex flex-col sm:flex-row-reverse gap-2 border-t border-gray-200">
                        <button type="button" 
                                @click="if(deleteCanConfirm) { document.getElementById('deleteForm_' + deleteGsId).submit(); }"
                                :disabled="!deleteCanConfirm"
                                :class="deleteCanConfirm ? 'bg-red-500 hover:bg-red-600 border-2 border-red-600 text-black shadow-md' : 'bg-transparent border-2 border-gray-800 text-black cursor-not-allowed opacity-75'"
                                class="w-full inline-flex justify-center rounded-lg px-4 py-2 text-base font-bold focus:outline-none focus:ring-2 focus:ring-offset-2 sm:w-auto sm:text-sm transition-all duration-300 ease-in-out">
                            <span x-show="!deleteCanConfirm" x-text="'Aceptar en ' + deleteTimer + 's'"></span>
                            <span x-show="deleteCanConfirm">Aceptar y Cerrar</span>
                        </button>
                        <button type="button" @click="cerrarDeleteModal()" 
                                class="w-full inline-flex justify-center rounded-lg shadow-md px-4 py-2 bg-red-600 text-base font-bold text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:w-auto sm:text-sm transition-colors">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>{{-- /py-12 x-data --}}

    @if($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const alpineDiv = document.querySelector('[x-data]');
                if(alpineDiv && alpineDiv._x_dataStack) {
                    alpineDiv._x_dataStack[0].showModal = true;
                }
            });
        </script>
    @endif
</x-app-layout>
