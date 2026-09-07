@extends('layouts.admin')

@section('title', 'Analítica e Interpretación de Resultados')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-chart-bar text-primary mr-2"></i> Analítica de Rendimiento
        </h1>
        <button onclick="window.print()" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-print fa-sm text-white-50 mr-2"></i> Imprimir Reporte
        </button>
    </div>

    <!-- Panel de Filtros (Oculto en Impresión) -->
    <div class="card shadow mb-4 d-print-none">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Configurar Reporte Analítico</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.analytics.index') }}" class="row g-3">
                
                <div class="col-md-3">
                    <label class="form-label font-weight-bold">Modo de Análisis</label>
                    <select name="tipo_vista" class="form-select" onchange="toggleFiltrosBimestre(this.value)">
                        <option value="bimestral" {{ $tipoVista == 'bimestral' ? 'selected' : '' }}>Distribución de un Bimestre</option>
                        <option value="comparativa" {{ $tipoVista == 'comparativa' ? 'selected' : '' }}>Comparativa Interbimestral (2 periodos)</option>
                    </select>
                </div>

                <div class="col-md-3" id="filtro-bimestre-unico" style="{{ $tipoVista == 'bimestral' ? '' : 'display: none;' }}">
                    <label class="form-label font-weight-bold">Bimestre</label>
                    <select name="bimestre_id" class="form-select">
                        <option value="">Seleccione un bimestre...</option>
                        @foreach($bimestres as $b)
                            <option value="{{ $b->id }}" {{ request('bimestre_id') == $b->id ? 'selected' : '' }}>
                                Bimestre {{ $b->numero }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3" id="filtro-bimestre-doble" style="{{ $tipoVista == 'comparativa' ? '' : 'display: none;' }}">
                    <label class="form-label font-weight-bold">Bimestres a Comparar</label>
                    <div class="d-flex align-items-center">
                        <select name="bimestres_comp[]" class="form-select me-2">
                            <option value="">Bimestre A...</option>
                            @foreach($bimestres as $b)
                                <option value="{{ $b->id }}" {{ (request('bimestres_comp') && isset(request('bimestres_comp')[0]) && request('bimestres_comp')[0] == $b->id) ? 'selected' : '' }}>
                                    Bimestre {{ $b->numero }}
                                </option>
                            @endforeach
                        </select>
                        <span class="mx-2">vs</span>
                        <select name="bimestres_comp[]" class="form-select ms-2">
                            <option value="">Bimestre B...</option>
                            @foreach($bimestres as $b)
                                <option value="{{ $b->id }}" {{ (request('bimestres_comp') && isset(request('bimestres_comp')[1]) && request('bimestres_comp')[1] == $b->id) ? 'selected' : '' }}>
                                    Bimestre {{ $b->numero }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-3">
                    <label class="form-label font-weight-bold">Nivel</label>
                    <select name="nivel" class="form-select" onchange="this.form.submit()">
                        <option value="">Todos los niveles</option>
                        <option value="primaria" {{ request('nivel') == 'primaria' ? 'selected' : '' }}>Primaria</option>
                        <option value="secundaria" {{ request('nivel') == 'secundaria' ? 'selected' : '' }}>Secundaria</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label font-weight-bold">Grado y Sección (Opcional)</label>
                    <select name="grado_seccion_id" class="form-select">
                        <option value="">Toda la escuela / nivel</option>
                        @foreach($secciones as $sec)
                            <option value="{{ $sec->id }}" {{ request('grado_seccion_id') == $sec->id ? 'selected' : '' }}>
                                {{ ucfirst($sec->grado->nivel) }} - {{ $sec->grado->orden }}° "{{ $sec->seccion->nombre }}"
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 mt-3 text-end">
                    <a href="{{ route('admin.analytics.index') }}" class="btn btn-secondary">Limpiar</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Generar Reporte
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
        <div class="alert alert-info text-center">
            <i class="fas fa-info-circle fa-2x mb-3 d-block"></i>
            Seleccione los filtros y haga clic en "Generar Reporte" para ver el análisis de resultados.
        </div>
    @endif
</div>
@endsection

@push('styles')
<style>
    @media print {
        @page { size: A4 portrait; margin: 1.5cm; }
        body { font-size: 12pt; background-color: white !important; }
        .d-print-none { display: none !important; }
        .card { border: none !important; box-shadow: none !important; margin-bottom: 20px !important; }
        .card-header { background-color: transparent !important; border-bottom: 2px solid #333 !important; }
        .chart-container { page-break-inside: avoid; height: 350px !important; width: 100% !important; }
        .interpretacion-box { border: 1px solid #ccc !important; background-color: #f8f9fa !important; color: black !important; }
        table { page-break-inside: avoid; border-collapse: collapse !important; }
        th, td { border: 1px solid #999 !important; padding: 4px !important; }
        .apexcharts-toolbar { display: none !important; }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    function toggleFiltrosBimestre(val) {
        if(val === 'bimestral') {
            document.getElementById('filtro-bimestre-unico').style.display = 'block';
            document.getElementById('filtro-bimestre-doble').style.display = 'none';
        } else {
            document.getElementById('filtro-bimestre-unico').style.display = 'none';
            document.getElementById('filtro-bimestre-doble').style.display = 'block';
        }
    }

    // Inicializar gráficos si hay datos
    document.addEventListener("DOMContentLoaded", function() {
        const graficosData = @json($graficosData ?? []);
        
        for (const [cId, competencias] of Object.entries(graficosData)) {
            for (const [compNombre, config] of Object.entries(competencias)) {
                let chartId = `chart_${cId}_${compNombre.replace(/[^a-zA-Z0-9]/g, '_')}`;
                let el = document.querySelector(`#${chartId}`);
                if (el) {
                    let isComparativa = !!config.categories;
                    
                    let options = {
                        series: config.series,
                        chart: {
                            type: 'bar',
                            height: 350,
                            animations: { enabled: false }, // Importante para impresión
                            toolbar: { show: false }
                        },
                        colors: isComparativa ? ['#4e73df', '#1cc88a'] : ['#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'],
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
                            style: { colors: ["#304758"] }
                        },
                        xaxis: {
                            categories: isComparativa ? config.categories : config.labels,
                        },
                        legend: { show: isComparativa }
                    };

                    new ApexCharts(el, options).render();
                }
            }
        }
    });
</script>
@endpush
