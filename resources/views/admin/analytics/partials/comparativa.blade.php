@foreach($datos as $cId => $area)
<div class="card shadow mb-4 area-container" style="page-break-after: always;">
    <div class="card-header py-3 bg-success">
        <h6 class="m-0 font-weight-bold text-white"><i class="fas fa-balance-scale mr-2"></i> Comparativa - Área: {{ $area['nombre'] }}</h6>
    </div>
    <div class="card-body">
        @foreach($area['competencias'] as $compNombre => $compData)
            @php 
                $chartId = 'chart_' . $cId . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $compNombre); 
            @endphp
            <div class="mb-5">
                <h5 class="text-dark font-weight-bold border-bottom pb-2">{{ $compNombre }}</h5>
                
                <div class="row mt-3">
                    <div class="col-lg-6">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm text-center">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nivel</th>
                                        <th>Bimestre {{ request('bimestres_comp')[0] ?? 'A' }}</th>
                                        <th>Bimestre {{ request('bimestres_comp')[1] ?? 'B' }}</th>
                                        <th>Variación</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach(['AD', 'A', 'B', 'C'] as $nivel)
                                        @php $var = $compData['variaciones'][$nivel]; @endphp
                                        <tr>
                                            <td class="font-weight-bold">{{ $nivel }}</td>
                                            <td>{{ $compData['b1']['porcentajes'][$nivel] }}%</td>
                                            <td>{{ $compData['b2']['porcentajes'][$nivel] }}%</td>
                                            <td class="font-weight-bold {{ $var > 0 ? 'text-success' : ($var < 0 ? 'text-danger' : 'text-muted') }}">
                                                {{ $var > 0 ? '+' : '' }}{{ $var }}%
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Interpretación Automatizada Comparativa -->
                        <div class="interpretacion-box alert alert-dark mt-3">
                            <strong><i class="fas fa-robot mr-1"></i> Evolución Automática:</strong><br>
                            {{ $compData['interpretacion_comparativa'] }}
                        </div>
                    </div>
                    
                    <div class="col-lg-6">
                        <div id="{{ $chartId }}" class="chart-container"></div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endforeach
