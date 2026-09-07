<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight flex justify-between items-center">
            <span>
                <svg class="inline-block w-6 h-6 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                {{ __('Analítica e Interpretación de Resultados') }}
            </span>
            <button onclick="window.print()" class="d-print-none bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg text-sm shadow-sm transition-colors flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Imprimir Reporte
            </button>
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Panel de Filtros (Oculto en Impresión) -->
            <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-100 d-print-none">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">Configurar Reporte Analítico</h3>
                    
                    <form method="GET" action="{{ route('admin.analytics.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                        
                        <div class="w-full">
                            <label class="block text-sm font-semibold text-gray-700">Modo de Análisis</label>
                            <select name="tipo_vista" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 sm:text-sm" onchange="toggleFiltrosBimestre(this.value)">
                                <option value="bimestral" {{ $tipoVista == 'bimestral' ? 'selected' : '' }}>Distribución de un Bimestre</option>
                                <option value="comparativa" {{ $tipoVista == 'comparativa' ? 'selected' : '' }}>Comparativa Interbimestral</option>
                            </select>
                        </div>

                        <div class="w-full" id="filtro-bimestre-unico" style="{{ $tipoVista == 'bimestral' ? '' : 'display: none;' }}">
                            <label class="block text-sm font-semibold text-gray-700">Bimestre</label>
                            <select name="bimestre_id" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 sm:text-sm">
                                <option value="">Seleccione un bimestre...</option>
                                @foreach($bimestres as $b)
                                    <option value="{{ $b->id }}" {{ request('bimestre_id') == $b->id ? 'selected' : '' }}>
                                        Bimestre {{ $b->numero }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="w-full md:col-span-2" id="filtro-bimestre-doble" style="{{ $tipoVista == 'comparativa' ? '' : 'display: none;' }}">
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Bimestres a Comparar</label>
                            <div class="flex flex-wrap items-center gap-4 bg-white p-2 rounded-lg border border-gray-300 shadow-sm">
                                @foreach($bimestres as $b)
                                    <label class="inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="bimestres_comp[]" value="{{ $b->id }}" 
                                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                                            {{ in_array($b->id, (array)request('bimestres_comp', [])) ? 'checked' : '' }}>
                                        <span class="ml-2 text-sm text-gray-700">Bim. {{ $b->numero }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="w-full" style="{{ $tipoVista == 'comparativa' ? 'display: none;' : '' }}" id="spacer-div"></div>

                        <div class="w-full">
                            <label class="block text-sm font-semibold text-gray-700">Nivel</label>
                            <select name="nivel" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 sm:text-sm" onchange="this.form.submit()">
                                <option value="">Todos los niveles</option>
                                <option value="primaria" {{ request('nivel') == 'primaria' ? 'selected' : '' }}>Primaria</option>
                                <option value="secundaria" {{ request('nivel') == 'secundaria' ? 'selected' : '' }}>Secundaria</option>
                            </select>
                        </div>

                        <div class="w-full">
                            <label class="block text-sm font-semibold text-gray-700">Grado y Sección (Opcional)</label>
                            <select name="grado_seccion_id" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 sm:text-sm">
                                <option value="">Toda la escuela / nivel</option>
                                @foreach($secciones as $sec)
                                    <option value="{{ $sec->id }}" {{ request('grado_seccion_id') == $sec->id ? 'selected' : '' }}>
                                        {{ $sec->grado->nombre }} - {{ $sec->seccion->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="w-full md:col-span-2 flex justify-end gap-2">
                            <a href="{{ route('admin.analytics.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-2 px-4 rounded-md text-sm transition-colors border border-gray-300">
                                Limpiar
                            </a>
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md text-sm transition-colors">
                                Generar Reporte
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Resultados -->
            @if(!empty($datos))
                @if($tipoVista == 'bimestral')
                    @include('admin.analytics.partials.bimestral')
                @else
                    @include('admin.analytics.partials.comparativa')
                @endif
            @else
                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700 font-medium">
                                Seleccione los filtros y haga clic en "Generar Reporte" para ver el análisis de resultados.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>

<style>
    @media print {
        @page { size: A4 portrait; margin: 1cm; }
        body { background-color: white !important; }
        .d-print-none { display: none !important; }
        nav { display: none !important; }
        header { display: none !important; }
        .min-h-screen { min-height: auto !important; background-color: white !important; padding: 0 !important; }
        .max-w-7xl { max-width: 100% !important; padding: 0 !important; margin: 0 !important; }
        .shadow-sm { box-shadow: none !important; }
        .border { border: 1px solid #ccc !important; }
        
        .area-container { page-break-after: always; margin-bottom: 2cm; }
        .chart-container { page-break-inside: avoid; height: 320px !important; width: 100% !important; margin-top: 15px; }
        .interpretacion-box { border: 2px solid #555 !important; background-color: #f8f9fa !important; color: black !important; }
        
        table { page-break-inside: avoid; border-collapse: collapse !important; width: 100%; font-size: 11pt; }
        th, td { border: 1px solid #999 !important; padding: 6px !important; }
        th { background-color: #eee !important; font-weight: bold !important; -webkit-print-color-adjust: exact; }
        
        .apexcharts-toolbar { display: none !important; }
        
        /* Forzar colores de fondo de las alertas al imprimir */
        .bg-red-50 { background-color: #fee2e2 !important; -webkit-print-color-adjust: exact; }
        .bg-yellow-50 { background-color: #fef3c7 !important; -webkit-print-color-adjust: exact; }
        .bg-green-50 { background-color: #dcfce3 !important; -webkit-print-color-adjust: exact; }
        .text-red-800 { color: #991b1b !important; }
        .text-yellow-800 { color: #92400e !important; }
        .text-green-800 { color: #166534 !important; }
    }
</style>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    function toggleFiltrosBimestre(val) {
        const unico = document.getElementById('filtro-bimestre-unico');
        const doble = document.getElementById('filtro-bimestre-doble');
        const spacer = document.getElementById('spacer-div');
        
        if(val === 'bimestral') {
            unico.style.display = 'block';
            doble.style.display = 'none';
            if(spacer) spacer.style.display = 'block';
        } else {
            unico.style.display = 'none';
            doble.style.display = 'block';
            if(spacer) spacer.style.display = 'none';
        }
    }

    // Inicializar gráficos
    document.addEventListener("DOMContentLoaded", function() {
        const graficosData = @json($graficosData ?? []);
        console.log("Inicializando " + graficosData.length + " gráficos", graficosData);
        
        for (const config of graficosData) {
            let el = document.querySelector(`#${config.chart_id}`);
            if (el) {
                let isComparativa = !!config.categories;
                
                let options = {
                    series: config.series,
                    chart: {
                        type: 'bar',
                        height: 320,
                        animations: { enabled: false }, 
                        toolbar: { show: false }
                    },
                    colors: isComparativa ? ['#3b82f6', '#10b981', '#6366f1', '#f59e0b', '#ec4899'] : ['#10b981', '#06b6d4', '#f59e0b', '#ef4444'],
                    plotOptions: {
                        bar: {
                            borderRadius: 4,
                            horizontal: false,
                            dataLabels: { position: 'top' },
                            distributed: !isComparativa
                        }
                    },
                    dataLabels: {
                        enabled: true,
                        formatter: function (val) { return val + "%"; },
                        style: { colors: ["#1f2937"] }
                    },
                    xaxis: {
                        categories: isComparativa ? config.categories : config.labels,
                    },
                    legend: { show: isComparativa }
                };

                try {
                    new ApexCharts(el, options).render();
                } catch (e) {
                    console.error("Error renderizando grafico", config.chart_id, e);
                    el.innerHTML = '<div class="text-red-500 text-sm p-4">Error cargando gráfico. Consulte la consola.</div>';
                }
            } else {
                console.warn("No se encontró el contenedor para", config.chart_id);
            }
        }
    });
</script>
@endpush
</x-app-layout>
