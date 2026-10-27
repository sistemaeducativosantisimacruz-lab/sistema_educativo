<x-app-layout>
    <div x-data="{ 
        tab: new URLSearchParams(window.location.search).get('tab') || '{{ $tipoVista === 'comparativa' ? 'comparativo' : 'frecuencias' }}' 
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

                <!-- Botón de impresión A4 -->
                <button onclick="window.print()" class="bg-white hover:bg-slate-50 text-slate-700 font-semibold py-2 px-3.5 rounded-lg text-xs shadow-sm border border-slate-200 transition-colors flex items-center gap-2">
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
