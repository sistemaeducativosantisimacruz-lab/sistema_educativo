<div x-data="diagnosticoEstudiante()" x-init="init()" class="space-y-6">

    <!-- Encabezado de la Sección Diagnóstico por Estudiante -->
    <div>
        <h3 class="text-2xl font-bold text-slate-900 tracking-tight">Diagnóstico por estudiante</h3>
        <p class="text-sm text-slate-500 mt-1">
            {{ request('grado_seccion_id') && $secciones->firstWhere('id', request('grado_seccion_id')) ? ($secciones->firstWhere('id', request('grado_seccion_id'))->grado->nombre ?? '') . ' ' . ($secciones->firstWhere('id', request('grado_seccion_id'))->seccion->nombre ?? '') : 'Seleccione grado y sección' }} 
            · {{ request('bimestre_id') && $bimestres->firstWhere('id', request('bimestre_id')) ? 'Bimestre ' . ($bimestres->firstWhere('id', request('bimestre_id'))->numero ?? '') : 'Seleccione bimestre' }} 
            · Generado automáticamente a partir de los niveles de logro
        </p>
    </div>

    <!-- Layout de Dos Columnas: Lista de Estudiantes (Izq) y Detalle de Diagnóstico (Der) -->
    <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-start">
        
        <!-- Columna Izquierda: Lista de Estudiantes con Búsqueda -->
        <div class="md:col-span-4 lg:col-span-3 space-y-2.5">
            
            <!-- Barra de Búsqueda de Estudiante -->
            <div class="relative">
                <input 
                    type="text" 
                    x-model="searchQuery" 
                    placeholder="Buscar estudiante..." 
                    class="w-full text-xs rounded-xl border-slate-200 pl-8 pr-3 py-2 text-slate-700 placeholder-slate-400 focus:border-slate-800 focus:ring-1 focus:ring-slate-800 shadow-2xs transition-colors"
                >
                <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>

            <!-- Estado Vacío: Sin Estudiantes Matriculados o Filtros No Aplicados -->
            <template x-if="estudiantes.length === 0">
                <div class="p-4 bg-white rounded-xl border border-slate-200 text-center text-xs text-slate-500">
                    <svg class="w-8 h-8 text-slate-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    <span>Seleccione una sección y bimestre en los filtros superiores para cargar la lista.</span>
                </div>
            </template>

            <!-- Estado Vacío: Búsqueda sin Resultados -->
            <template x-if="estudiantes.length > 0 && estudiantesFiltrados.length === 0">
                <div class="p-4 bg-white rounded-xl border border-slate-200 text-center text-xs text-slate-500">
                    No se encontraron coincidencias para "<span class="font-medium text-slate-700" x-text="searchQuery"></span>".
                </div>
            </template>

            <!-- Lista Reactiva de Estudiantes -->
            <div class="space-y-2 max-h-[580px] overflow-y-auto pr-1">
                <template x-for="est in estudiantesFiltrados" :key="est.id">
                    <button 
                        type="button"
                        @click="seleccionarEstudiante(est)"
                        :class="estudianteSeleccionado && estudianteSeleccionado.id === est.id ? 'border-slate-800 ring-2 ring-slate-800/20 bg-slate-50 shadow-xs' : 'border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50/60 shadow-2xs'"
                        class="w-full flex items-center justify-between p-3 rounded-xl border text-left transition-all duration-150">
                        <span class="font-medium text-slate-800 text-xs sm:text-sm line-clamp-1" x-text="est.nombre"></span>
                        <span 
                            class="px-2 py-0.5 rounded text-[11px] font-bold leading-4 whitespace-nowrap ml-2 shadow-2xs"
                            :class="getBadgeClass(est.nivel_global)"
                            x-text="est.nivel_global ? est.nivel_global : 'Sin notas'">
                        </span>
                    </button>
                </template>
            </div>
        </div>

        <!-- Columna Derecha: Panel de Resumen del Estudiante Seleccionado -->
        <div class="md:col-span-8 lg:col-span-9">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 md:p-8 min-h-[420px]">
                
                <!-- Estado de Carga -->
                <div x-show="cargando" class="py-16 text-center space-y-3">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-slate-200 border-t-[#1e40af]"></div>
                    <p class="text-sm font-medium text-slate-600">Cargando diagnóstico del estudiante...</p>
                </div>

                <!-- Estado de Error -->
                <div x-show="error && !cargando" class="p-6 bg-red-50 border border-red-200 rounded-xl text-center space-y-3">
                    <p class="text-sm font-semibold text-red-700" x-text="error"></p>
                    <button 
                        type="button" 
                        @click="seleccionarEstudiante(estudianteSeleccionado)" 
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-semibold shadow-xs transition-colors">
                        Reintentar
                    </button>
                </div>

                <!-- Estado Vacío: Sin Estudiante Seleccionado -->
                <div x-show="!estudianteSeleccionado && !cargando && !error" class="py-20 text-center space-y-2">
                    <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h5 class="text-base font-bold text-slate-700">Sin estudiante seleccionado</h5>
                    <p class="text-xs text-slate-500 max-w-sm mx-auto">Seleccione un estudiante de la lista izquierda para consultar su matriz de logros y su diagnóstico cualitativo.</p>
                </div>

                <!-- Contenido del Diagnóstico: Estudiante Seleccionado -->
                <div x-show="estudianteSeleccionado && !cargando && !error">
                    
                    <!-- Título del Panel de Resumen -->
                    <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-6">
                        <h4 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                            <span x-text="estudianteSeleccionado ? estudianteSeleccionado.nombre : ''"></span>
                            <span class="text-slate-400 font-normal">·</span>
                            <span class="text-slate-600 font-semibold text-base">Resumen</span>
                        </h4>
                        <div>
                            <span 
                                class="px-3 py-1 rounded-md text-xs font-bold leading-5 shadow-2xs"
                                :class="getBadgeClass(estudianteSeleccionado ? estudianteSeleccionado.nivel_global : null)"
                                x-text="estudianteSeleccionado && estudianteSeleccionado.nivel_global ? 'Nivel ' + estudianteSeleccionado.nivel_global : 'Sin notas'">
                            </span>
                        </div>
                    </div>

                    <!-- Tabla de Niveles por Competencia / Área (Bimestre Anterior vs. Actual) -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-100 text-sm">
                            <thead>
                                <tr class="text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">
                                    <th scope="col" class="pb-3 pr-4 font-medium text-slate-500" x-text="filtroCursoId ? 'Competencia' : 'Área curricular'"></th>
                                    <th scope="col" class="pb-3 px-4 text-center font-medium text-slate-500 w-28" x-text="columnas.anterior ? columnas.anterior : '—'"></th>
                                    <th scope="col" class="pb-3 pl-4 text-center font-medium text-slate-500 w-28" x-text="columnas.actual ? columnas.actual : 'Bimestre Actual'"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-700">
                                <template x-for="comp in competencias" :key="comp.nombre">
                                    <tr class="hover:bg-slate-50/50 transition-colors">
                                        <td class="py-3.5 pr-4 text-slate-800 font-normal text-xs sm:text-sm" x-text="comp.nombre"></td>
                                        <td class="py-3.5 px-4 text-center font-semibold text-slate-600 text-xs sm:text-sm" x-text="comp.anterior ? comp.anterior : '—'"></td>
                                        <td class="py-3.5 pl-4 text-center font-semibold text-slate-900 text-xs sm:text-sm" x-text="comp.actual ? comp.actual : '—'"></td>
                                    </tr>
                                </template>
                                <template x-if="competencias.length === 0">
                                    <tr>
                                        <td colspan="3" class="py-8 text-center text-xs text-slate-500">
                                            No se encontraron calificaciones registradas para los periodos seleccionados.
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <!-- Recuadro del Diagnóstico Cualitativo Generado -->
                    <div class="mt-8 bg-slate-50/80 rounded-xl p-5 border border-slate-100 shadow-2xs">
                        <h5 class="text-xs font-bold text-slate-900 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-indigo-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                            Diagnóstico generado
                        </h5>
                        <p class="text-sm text-slate-600 leading-relaxed font-normal" x-text="diagnosticoTexto"></p>
                    </div>

                    <!-- Botón de Exportación PDF A4 -->
                    <div class="mt-8 flex justify-start">
                        <button 
                            type="button" 
                            @click="exportarPdfEstudiante()"
                            class="bg-[#1e40af] hover:bg-blue-900 text-white font-semibold text-sm px-6 py-2.5 rounded-xl shadow-xs transition-colors flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Exportar informe PDF (A4)
                        </button>
                    </div>

                </div>

            </div>
        </div>

    </div>

</div>

<script>
    function diagnosticoEstudiante() {
        return {
            estudiantes: @json($estudiantes ?? []),
            searchQuery: '',
            estudianteSeleccionado: null,
            filtroBimestreId: {{ (int) request('bimestre_id', 0) }},
            filtroGradoSeccionId: {{ (int) request('grado_seccion_id', 0) }},
            filtroCursoId: {{ (int) request('curso_id', 0) }},
            columnas: { anterior: null, actual: null },
            competencias: [],
            diagnosticoTexto: '',
            cargando: false,
            error: null,

            init() {
                if (Array.isArray(this.estudiantes) && this.estudiantes.length > 0) {
                    this.seleccionarEstudiante(this.estudiantes[0]);
                }
            },

            get estudiantesFiltrados() {
                if (!this.searchQuery || !this.searchQuery.trim()) {
                    return this.estudiantes;
                }
                const q = this.searchQuery.toLowerCase();
                return this.estudiantes.filter(e => e.nombre && e.nombre.toLowerCase().includes(q));
            },

            async seleccionarEstudiante(est) {
                if (!est) return;
                this.estudianteSeleccionado = est;

                if (!this.filtroBimestreId || !this.filtroGradoSeccionId) {
                    this.diagnosticoTexto = 'Aplique los filtros de sección y bimestre para generar el diagnóstico.';
                    return;
                }

                this.cargando = true;
                this.error = null;

                try {
                    const params = new URLSearchParams({
                        bimestre_id: this.filtroBimestreId,
                        grado_seccion_id: this.filtroGradoSeccionId,
                    });
                    if (this.filtroCursoId) {
                        params.append('curso_id', this.filtroCursoId);
                    }

                    const url = `/admin/analytics/estudiantes/${est.id}/diagnostico?${params.toString()}`;
                    const res = await fetch(url, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (!res.ok) {
                        throw new Error(`Error ${res.status}: No se pudo obtener el diagnóstico`);
                    }

                    const data = await res.json();
                    this.columnas = data.columnas || { anterior: null, actual: null };
                    this.competencias = data.competencias || [];
                    this.diagnosticoTexto = data.diagnostico || '';
                } catch (err) {
                    console.error('Error fetching diagnosis:', err);
                    this.error = 'No se pudo cargar el diagnóstico del estudiante. Por favor, intente nuevamente.';
                } finally {
                    this.cargando = false;
                }
            },

            getBadgeClass(nivel) {
                switch(nivel) {
                    case 'AD': return 'bg-[#1e40af] text-white'; // Azul oscuro institucional
                    case 'A':  return 'bg-[#0f766e] text-white'; // Teal institucional
                    case 'B':  return 'bg-[#f59e0b] text-gray-900'; // Ámbar institucional
                    case 'C':  return 'bg-[#b91c1c] text-white'; // Rojo oscuro institucional
                    default:   return 'bg-slate-400 text-white'; // Sin notas
                }
            },

            exportarPdfEstudiante() {
                window.print();
            }
        };
    }
</script>
