<div class="space-y-6">

    <!-- Encabezado de la Sección Comparativa de Rendimiento (Captura 1) -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h3 class="text-2xl font-bold text-slate-900 tracking-tight">Comparativo de rendimiento</h3>
            <p class="text-sm text-slate-500 mt-1">
                {{ request('curso_id') ? ($cursos->firstWhere('id', request('curso_id'))->nombre ?? 'Matemática') : 'Matemática' }}
                · Porcentaje de estudiantes por nivel · Análisis oficial
            </p>
        </div>
        <button 
            type="button" 
            @click="modalComparativo = true" 
            class="bg-[#1e40af] hover:bg-blue-900 text-white font-semibold text-xs px-4 py-2.5 rounded-xl shadow-xs transition-colors flex items-center gap-2 self-start sm:self-auto d-print-none">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            Exportar reporte comparativo (A4)
        </button>
    </div>

    <!-- Dos Gráficos Lado a Lado: Entre Secciones y Entre Bimestres -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- Tarjeta 1: Entre Secciones · II Bimestre -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
            <h4 class="text-base font-bold text-slate-800 mb-4">
                Entre secciones · {{ request('bimestre_id') ? 'Bimestre ' . ($bimestres->firstWhere('id', request('bimestre_id'))->numero ?? 'II') : 'II Bimestre' }}
            </h4>
            <div id="chart-entre-secciones" class="w-full" style="min-height: 220px;"></div>
        </div>

        <!-- Tarjeta 2: Entre Bimestres · 3.° A -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
            <h4 class="text-base font-bold text-slate-800 mb-4">
                Entre bimestres · {{ request('grado_seccion_id') ? ($secciones->firstWhere('id', request('grado_seccion_id'))->grado->nombre ?? '3.°') . ' ' . ($secciones->firstWhere('id', request('grado_seccion_id'))->seccion->nombre ?? 'A') : '3.° A' }}
            </h4>
            <div id="chart-entre-bimestres" class="w-full" style="min-height: 220px;"></div>
        </div>

    </div>

    <!-- Leyenda Unificada Inferior con Cuadrados de Color (Captura 1) -->
    <div class="flex flex-wrap items-center gap-6 pt-2 pb-4 text-xs font-semibold text-slate-700">
        <div class="flex items-center gap-2">
            <span class="w-3.5 h-3.5 rounded-sm bg-[#b91c1c] inline-block shadow-xs"></span>
            <span>C · En inicio</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="w-3.5 h-3.5 rounded-sm bg-[#f59e0b] inline-block shadow-xs"></span>
            <span>B · En proceso</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="w-3.5 h-3.5 rounded-sm bg-[#0f766e] inline-block shadow-xs"></span>
            <span>A · Logro esperado</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="w-3.5 h-3.5 rounded-sm bg-[#1e40af] inline-block shadow-xs"></span>
            <span>AD · Logro destacado</span>
        </div>
    </div>

</div>

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Paleta Institucional Aprobada:
        // C: Rojo oscuro (#b91c1c), B: Ámbar (#f59e0b), A: Verde azulado (#0f766e), AD: Azul (#1e40af)
        const palette = ['#b91c1c', '#f59e0b', '#0f766e', '#1e40af'];

        // 1. Gráfico: Entre secciones · II Bimestre (Barras apiladas al 100%)
        const elSecciones = document.querySelector("#chart-entre-secciones");
        if (elSecciones) {
            const optionsSecciones = {
                series: [
                    { name: 'C · En inicio', data: [7, 13, 3] },
                    { name: 'B · En proceso', data: [27, 30, 20] },
                    { name: 'A · Logro esperado', data: [50, 40, 57] },
                    { name: 'AD · Logro destacado', data: [16, 17, 20] },
                ],
                chart: {
                    type: 'bar',
                    height: 220,
                    stacked: true,
                    stackType: '100%',
                    toolbar: { show: false },
                    animations: { enabled: false }
                },
                plotOptions: {
                    bar: {
                        horizontal: true,
                        barHeight: '50%',
                        borderRadius: 3
                    }
                },
                colors: palette,
                dataLabels: {
                    enabled: true,
                    formatter: function(val) {
                        return Math.round(val);
                    },
                    style: {
                        fontSize: '11px',
                        fontFamily: 'Figtree, sans-serif',
                        fontWeight: '700',
                        colors: ['#ffffff', '#1f2937', '#ffffff', '#ffffff']
                    }
                },
                xaxis: {
                    categories: ['3.° A', '3.° B', '3.° C'],
                    labels: { show: false },
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                },
                yaxis: {
                    labels: {
                        style: {
                            fontSize: '12px',
                            fontFamily: 'Figtree, sans-serif',
                            fontWeight: '600',
                            colors: '#334155'
                        }
                    }
                },
                grid: {
                    show: false,
                    padding: { left: 0, right: 10, top: -10, bottom: -10 }
                },
                legend: { show: false },
                tooltip: {
                    y: {
                        formatter: function(val) {
                            return val + "% de estudiantes";
                        }
                    }
                }
            };
            new ApexCharts(elSecciones, optionsSecciones).render();
        }

        // 2. Gráfico: Entre bimestres · 3.° A (Barras apiladas al 100%)
        const elBimestres = document.querySelector("#chart-entre-bimestres");
        if (elBimestres) {
            const optionsBimestres = {
                series: [
                    { name: 'C · En inicio', data: [10, 7] },
                    { name: 'B · En proceso', data: [30, 27] },
                    { name: 'A · Logro esperado', data: [45, 50] },
                    { name: 'AD · Logro destacado', data: [15, 16] },
                ],
                chart: {
                    type: 'bar',
                    height: 180,
                    stacked: true,
                    stackType: '100%',
                    toolbar: { show: false },
                    animations: { enabled: false }
                },
                plotOptions: {
                    bar: {
                        horizontal: true,
                        barHeight: '48%',
                        borderRadius: 3
                    }
                },
                colors: palette,
                dataLabels: {
                    enabled: true,
                    formatter: function(val) {
                        return Math.round(val);
                    },
                    style: {
                        fontSize: '11px',
                        fontFamily: 'Figtree, sans-serif',
                        fontWeight: '700',
                        colors: ['#ffffff', '#1f2937', '#ffffff', '#ffffff']
                    }
                },
                xaxis: {
                    categories: ['I Bimestre', 'II Bimestre'],
                    labels: { show: false },
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                },
                yaxis: {
                    labels: {
                        style: {
                            fontSize: '12px',
                            fontFamily: 'Figtree, sans-serif',
                            fontWeight: '600',
                            colors: '#334155'
                        }
                    }
                },
                grid: {
                    show: false,
                    padding: { left: 0, right: 10, top: -10, bottom: -10 }
                },
                legend: { show: false },
                tooltip: {
                    y: {
                        formatter: function(val) {
                            return val + "% de estudiantes";
                        }
                    }
                }
            };
            new ApexCharts(elBimestres, optionsBimestres).render();
        }
    });
</script>
@endpush
