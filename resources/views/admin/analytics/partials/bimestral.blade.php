@foreach($datos as $cId => $area)
<div class="card shadow mb-4 area-container" style="page-break-after: always;">
    <div class="card-header py-3 bg-primary">
        <h6 class="m-0 font-weight-bold text-white"><i class="fas fa-book-open mr-2"></i> Área: {{ $area['nombre'] }}</h6>
    </div>
    <div class="card-body">
        @foreach($area['competencias'] as $compNombre => $compData)
            @php 
                $chartId = 'chart_' . $cId . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $compNombre); 
            @endphp
            <div class="mb-5">
                <h5 class="text-dark font-weight-bold border-bottom pb-2">{{ $compNombre }}</h5>
                
                <div class="row align-items-center mt-3">
                    <div class="col-lg-5">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm text-center">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nivel de Logro</th>
                                        <th>Estudiantes</th>
                                        <th>Porcentaje</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="font-weight-bold text-success">Logro Destacado (AD)</td>
                                        <td>{{ $compData['AD'] }}</td>
                                        <td>{{ $compData['porcentajes']['AD'] }}%</td>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold text-info">Logro Esperado (A)</td>
                                        <td>{{ $compData['A'] }}</td>
                                        <td>{{ $compData['porcentajes']['A'] }}%</td>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold text-warning">En Proceso (B)</td>
                                        <td>{{ $compData['B'] }}</td>
                                        <td>{{ $compData['porcentajes']['B'] }}%</td>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold text-danger">En Inicio (C)</td>
                                        <td>{{ $compData['C'] }}</td>
                                        <td>{{ $compData['porcentajes']['C'] }}%</td>
                                    </tr>
                                    <tr class="table-secondary font-weight-bold">
                                        <td>TOTAL</td>
                                        <td>{{ $compData['total'] }}</td>
                                        <td>100%</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Interpretación Automatizada -->
                        <div class="interpretacion-box alert 
                            @if(str_contains($compData['interpretacion'], 'Alerta')) alert-danger 
                            @elseif(str_contains($compData['interpretacion'], 'Atención')) alert-warning 
                            @elseif(str_contains($compData['interpretacion'], 'Destacado')) alert-success 
                            @else alert-info @endif mt-3">
                            <strong><i class="fas fa-robot mr-1"></i> Análisis Automático:</strong><br>
                            {{ $compData['interpretacion'] }}
                        </div>

                    </div>
                    
                    <div class="col-lg-7">
                        <div id="{{ $chartId }}" class="chart-container"></div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endforeach
