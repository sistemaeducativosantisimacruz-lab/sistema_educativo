@foreach($datos as $cId => $area)
<div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-100 mb-6 area-container">
    <div class="bg-emerald-600 px-6 py-4">
        <h6 class="m-0 font-bold text-white flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path></svg>
            Comparativa - Área: {{ $area['nombre'] }}
        </h6>
    </div>
    <div class="p-6">
        @foreach($area['competencias'] as $compNombre => $compData)
            @php 
                $chartId = $compData['chart_id'] ?? ('chart_' . $cId . '_' . md5($compNombre)); 
            @endphp
            <div class="mb-10 last:mb-0">
                <h5 class="text-gray-800 font-bold border-b pb-2 text-lg">{{ $compNombre }}</h5>
                
                <div class="grid grid-cols-1 md:grid-cols-12 gap-6 mt-6 items-center">
                    <div class="md:col-span-5">
                        <div class="overflow-x-auto rounded-lg border border-gray-200">
                            <table class="min-w-full divide-y divide-gray-200 text-center text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-2 py-3 font-semibold text-gray-700">Nivel</th>
                                        @php
                                            $bIds = array_keys($compData['bimestres_data']);
                                        @endphp
                                        @foreach($bIds as $bId)
                                            <th scope="col" class="px-2 py-3 font-semibold text-gray-700">
                                                Bim. {{ \App\Models\Bimestre::find($bId)->numero ?? $bId }}
                                            </th>
                                        @endforeach
                                        <th scope="col" class="px-2 py-3 font-semibold text-gray-700">Var. Neta</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach(['AD', 'A', 'B', 'C'] as $nivel)
                                        @php $var = $compData['variaciones'][$nivel]; @endphp
                                        <tr>
                                            <td class="px-2 py-2 font-bold text-gray-800">{{ $nivel }}</td>
                                            @foreach($bIds as $bId)
                                                <td class="px-2 py-2 text-gray-600">
                                                    {{ $compData['bimestres_data'][$bId]['porcentajes'][$nivel] }}%
                                                </td>
                                            @endforeach
                                            <td class="px-2 py-2 font-bold {{ $var > 0 ? 'text-green-600' : ($var < 0 ? 'text-red-600' : 'text-gray-400') }}">
                                                {{ $var > 0 ? '+' : '' }}{{ $var }}%
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Interpretación Automatizada Comparativa -->
                        <div class="interpretacion-box mt-4 border-l-4 p-4 rounded-r-lg bg-gray-50 border-gray-500 text-gray-800">
                            <div class="flex items-start">
                                <div class="flex-shrink-0 mt-0.5">
                                    <svg class="h-5 w-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-bold uppercase tracking-wide">Evolución Automática</h3>
                                    <div class="mt-1 text-sm leading-tight">
                                        {{ $compData['interpretacion_comparativa'] }}
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    
                    <div class="md:col-span-7">
                        <div id="{{ $chartId }}" class="chart-container"></div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endforeach
