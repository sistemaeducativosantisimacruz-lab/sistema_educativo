<x-app-layout>
    <div x-data="{ 
        tab: new URLSearchParams(window.location.search).get('tab') || '{{ $tipoVista === 'comparativa' ? 'comparativo' : 'frecuencias' }}',
        modalComparativo: false,
        compBimestres: {{ json_encode($bimestres->take(2)->pluck('id')->values()->toArray()) }},
        compTodosCursos: true,
        compCursos: {{ json_encode($cursos->pluck('id')->values()->toArray()) }},
        compSeccionId: {{ (int) request('grado_seccion_id', $secciones->first()?->id ?? 0) }},
        bimestresList: {{ json_encode($bimestres) }},
        cursosList: {{ json_encode($cursos) }},

        imprimirReporte() {
            if (this.tab === 'diagnostico') {
                window.dispatchEvent(new CustomEvent('abrir-modal-diagnostico-event'));
            } else {
                this.modalComparativo = true;
            }
        },

        toggleCompTodosCursos() {
            if (this.compTodosCursos) {
                this.compCursos = this.cursosList.map(c => c.id);
            } else {
                this.compCursos = [];
            }
        },

        ejecutarExportacionComparativa() {
            if (!this.compSeccionId) {
                alert('Por favor seleccione una sección en los filtros superiores.');
                return;
            }
            if (!this.compBimestres || this.compBimestres.length < 2) {
                alert('Debe seleccionar al menos dos bimestres para la comparativa.');
                return;
            }
            if (!this.compTodosCursos && (!this.compCursos || this.compCursos.length === 0)) {
                alert('Debe seleccionar al menos un curso o marcar \'Todos los cursos\'.');
                return;
            }

            const params = new URLSearchParams({
                grado_seccion_id: this.compSeccionId,
            });

            this.compBimestres.forEach(bId => params.append('bimestres[]', bId));

            if (!this.compTodosCursos) {
                this.compCursos.forEach(cId => params.append('cursos[]', cId));
            }

            const url = '{{ route('admin.analytics.reporte_comparativo_pdf') }}?' + params.toString();
            window.open(url, '_blank');
            this.modalComparativo = false;
        }
    }" class="py-8 bg-[#f8fafc] min-h-screen">
        
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Pestañas de Navegación entre Secciones del Prototipo (Solo UI) -->
            <div class="flex items-center justify-between border-b border-slate-200 pb-3 d-print-none">
                <div class="flex items-center gap-2">
                    <button 
                        type="button" 
                        @click="tab = 'frecuencias'" 
                        :class="tab === 'frecuencias' ? 'bg-[#1e40af] text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                        class="px-4 py-2 rounded-lg text-xs font-semibold transition-all">
                        Frecuencias de logro
                    </button>
                    <button 
                        type="button" 
                        @click="tab = 'comparativo'" 
                        :class="tab === 'comparativo' ? 'bg-[#1e40af] text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                        class="px-4 py-2 rounded-lg text-xs font-semibold transition-all">
                        Comparativo de rendimiento
                    </button>
                    <button 
                        type="button" 
                        @click="tab = 'diagnostico'" 
                        :class="tab === 'diagnostico' ? 'bg-[#1e40af] text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                        class="px-4 py-2 rounded-lg text-xs font-semibold transition-all">
                        Diagnóstico por estudiante
                    </button>
                </div>

                <!-- Botón de impresión A4 Inteligente -->
                <button type="button" @click="imprimirReporte()" class="bg-white hover:bg-slate-50 text-slate-700 font-semibold py-2 px-3.5 rounded-lg text-xs shadow-sm border border-slate-200 transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                    </svg>
                    Imprimir reporte (A4)
                </button>
            </div>

            <!-- Fila de Filtros Unificada (Exacta a las Capturas 1 y 4) -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 d-print-none">
                <form method="GET" action="{{ route('admin.analytics.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4 items-end">
                    
                    <!-- Campo oculto para preservar la pestaña actual -->
                    <input type="hidden" name="tab" :value="tab">
                    
                    <!-- Nivel -->
                    <div class="w-full">
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Nivel</label>
                        <select name="nivel" class="block w-full rounded-xl border-slate-200 shadow-xs focus:border-[#1e40af] focus:ring focus:ring-blue-100 text-xs py-2.5 px-3 text-slate-700 font-medium" onchange="this.form.submit()">
                            <option value="">Todos los niveles</option>
                            <option value="primaria" {{ request('nivel') == 'primaria' ? 'selected' : '' }}>Primaria</option>
                            <option value="secundaria" {{ request('nivel', 'secundaria') == 'secundaria' ? 'selected' : '' }}>Secundaria</option>
                        </select>
                    </div>

                    <!-- Grado-sección -->
                    <div class="w-full">
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Grado-sección</label>
                        <select name="grado_seccion_id" class="block w-full rounded-xl border-slate-200 shadow-xs focus:border-[#1e40af] focus:ring focus:ring-blue-100 text-xs py-2.5 px-3 text-slate-700 font-medium">
                            @forelse($secciones as $sec)
                                <option value="{{ $sec->id }}" {{ request('grado_seccion_id') == $sec->id ? 'selected' : '' }}>
                                    {{ $sec->grado->nombre }} {{ $sec->seccion->nombre }}
                                </option>
                            @empty
                                <option value="">3.° A</option>
                                <option value="">3.° B</option>
                                <option value="">3.° C</option>
                            @endforelse
                        </select>
                    </div>

                    <!-- Curso -->
                    <div class="w-full">
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Curso</label>
                        <select name="curso_id" class="block w-full rounded-xl border-slate-200 shadow-xs focus:border-[#1e40af] focus:ring focus:ring-blue-100 text-xs py-2.5 px-3 text-slate-700 font-medium">
                            @forelse($cursos as $c)
                                <option value="{{ $c->id }}" {{ request('curso_id') == $c->id ? 'selected' : '' }}>
                                    {{ $c->nombre }}
                                </option>
                            @empty
                                <option value="">Matemática</option>
                                <option value="">Comunicación</option>
                            @endforelse
                        </select>
                    </div>

                    <!-- Bimestre -->
                    <div class="w-full">
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Bimestre</label>
                        <select name="bimestre_id" class="block w-full rounded-xl border-slate-200 shadow-xs focus:border-[#1e40af] focus:ring focus:ring-blue-100 text-xs py-2.5 px-3 text-slate-700 font-medium">
                            @forelse($bimestres as $b)
                                <option value="{{ $b->id }}" {{ request('bimestre_id') == $b->id ? 'selected' : '' }}>
                                    {{ $b->numero }}° Bimestre
                                </option>
                            @empty
                                <option value="1">I Bimestre</option>
                                <option value="2" selected>II Bimestre</option>
                                <option value="3">III Bimestre</option>
                                <option value="4">IV Bimestre</option>
                            @endforelse
                        </select>
                    </div>

                    <!-- Botón Aplicar filtros (Exacto a las Capturas) -->
                    <div class="w-full">
                        <button type="submit" class="w-full bg-[#1e40af] hover:bg-blue-900 text-white font-semibold py-2.5 px-4 rounded-xl text-xs transition-colors shadow-xs text-center flex items-center justify-center">
                            Aplicar filtros
                        </button>
                    </div>

                </form>
            </div>

            <!-- Sección 1: Frecuencias de Logro por Competencia (Captura 4) -->
            <div x-show="tab === 'frecuencias'" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                @include('admin.analytics.partials.bimestral')
            </div>

            <!-- Sección 2: Comparativo de Rendimiento (Captura 1) -->
            <div x-show="tab === 'comparativo'" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                @include('admin.analytics.partials.comparativa')
            </div>

            <!-- Sección 3: Diagnóstico por Estudiante (Captura 3) -->
            <div x-show="tab === 'diagnostico'" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                @include('admin.analytics.partials.diagnostico')
            </div>

        </div>

        <!-- Modal de Selección de Exportación PDF A4 (Reporte Comparativo) -->
        <div 
            x-show="modalComparativo" 
            x-cloak 
            class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 d-print-none"
            style="display: none;"
        >
            <div 
                @click.away="modalComparativo = false" 
                class="bg-white rounded-2xl shadow-xl border border-slate-100 max-w-lg w-full p-6 space-y-5 transform transition-all"
            >
                <!-- Cabecera del Modal -->
                <div class="flex items-start justify-between border-b border-slate-100 pb-3">
                    <div>
                        <h4 class="text-base font-bold text-slate-900 flex items-center gap-2">
                            <svg class="w-5 h-5 text-[#1e40af]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            Exportar reporte comparativo (A4)
                        </h4>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Sección: <strong class="text-slate-800">{{ request('grado_seccion_id') && $secciones->firstWhere('id', request('grado_seccion_id')) ? ($secciones->firstWhere('id', request('grado_seccion_id'))->grado->nombre . ' ' . $secciones->firstWhere('id', request('grado_seccion_id'))->seccion->nombre) : ($secciones->first() ? ($secciones->first()->grado->nombre . ' ' . $secciones->first()->seccion->nombre) : 'Sección activa') }}</strong>
                        </p>
                    </div>
                    <button type="button" @click="modalComparativo = false" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <!-- Cuerpo del Modal -->
                <div class="space-y-4 text-xs">
                    
                    <!-- 1. Bimestres a comparar -->
                    <div>
                        <label class="block font-bold text-slate-700 mb-1.5 uppercase text-[11px] tracking-wider">
                            1. Bimestres a comparar (mínimo 2)
                        </label>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                            <template x-for="b in bimestresList" :key="b.id">
                                <label class="flex items-center gap-2 p-2.5 rounded-xl border cursor-pointer transition-colors"
                                    :class="compBimestres.includes(b.id) ? 'bg-blue-50/70 border-blue-300 text-blue-900 font-semibold' : 'border-slate-200 text-slate-700 hover:bg-slate-50'">
                                    <input 
                                        type="checkbox" 
                                        :value="b.id" 
                                        x-model="compBimestres" 
                                        class="rounded border-slate-300 text-[#1e40af] focus:ring-[#1e40af]"
                                    >
                                    <span x-text="b.numero + '° Bimestre'"></span>
                                </label>
                            </template>
                        </div>
                    </div>

                    <!-- 2. Cursos a exportar -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block font-bold text-slate-700 uppercase text-[11px] tracking-wider">
                                2. Cursos a incluir en la comparativa
                            </label>
                            <label class="flex items-center gap-1.5 text-xs text-[#1e40af] font-semibold cursor-pointer">
                                <input 
                                    type="checkbox" 
                                    x-model="compTodosCursos" 
                                    @change="toggleCompTodosCursos()" 
                                    class="rounded border-slate-300 text-[#1e40af] focus:ring-[#1e40af]"
                                >
                                <span>Todos los cursos</span>
                            </label>
                        </div>

                        <div x-show="!compTodosCursos" class="max-h-40 overflow-y-auto border border-slate-200 rounded-xl p-2 space-y-1 bg-slate-50/50">
                            <template x-for="c in cursosList" :key="c.id">
                                <label class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-white text-slate-700 cursor-pointer transition-colors text-xs">
                                    <input 
                                        type="checkbox" 
                                        :value="c.id" 
                                        x-model="compCursos" 
                                        class="rounded border-slate-300 text-[#1e40af] focus:ring-[#1e40af]"
                                    >
                                    <span x-text="c.nombre"></span>
                                </label>
                            </template>
                        </div>
                        <p x-show="compTodosCursos" class="text-[11px] text-slate-500 italic mt-1">
                            Se incluirán todos los cursos del nivel con sus competencias, gráficos de distribución 100% y análisis de evolución.
                        </p>
                    </div>

                </div>

                <!-- Botones -->
                <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
                    <button 
                        type="button" 
                        @click="modalComparativo = false" 
                        class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 font-semibold text-xs transition-colors"
                    >
                        Cancelar
                    </button>
                    <button 
                        type="button" 
                        @click="ejecutarExportacionComparativa()" 
                        class="bg-[#1e40af] hover:bg-blue-900 text-white font-semibold text-xs px-5 py-2.5 rounded-xl shadow-xs transition-colors flex items-center gap-2"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Generar Reporte Comparativo (A4)
                    </button>
                </div>
            </div>
        </div>
    </div>

<style>
    @media print {
        @page { size: A4 portrait; margin: 1cm; }
        body { background-color: white !important; font-size: 10pt; }
        .d-print-none { display: none !important; }
        nav, header { display: none !important; }
        .min-h-screen { min-height: auto !important; background-color: white !important; padding: 0 !important; }
        .max-w-7xl { max-width: 100% !important; padding: 0 !important; margin: 0 !important; }
        .shadow-sm, .shadow { box-shadow: none !important; }
        .border { border: 1px solid #e2e8f0 !important; }
        table { page-break-inside: avoid; border-collapse: collapse !important; width: 100%; font-size: 9pt; }
        th, td { border: 1px solid #cbd5e1 !important; padding: 6px !important; }
        th { background-color: #f1f5f9 !important; font-weight: bold !important; -webkit-print-color-adjust: exact; }
    }
</style>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
@endpush
</x-app-layout>
