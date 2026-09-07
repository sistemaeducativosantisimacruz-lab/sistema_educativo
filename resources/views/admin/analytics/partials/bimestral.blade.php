@foreach($datos as $cId => $area)
<div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-100 mb-6 area-container">
    <div class="bg-blue-600 px-6 py-4">
        <h6 class="m-0 font-bold text-white flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
            Área: {{ $area['nombre'] }}
        </h6>
    </div>
    <div class="p-6">
        @foreach($area['competencias'] as $compNombre => $compData)
            @php 
                $chartId = 'chart_' . $cId . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $compNombre); 
            @endphp
            <div class="mb-10 last:mb-0">
                <h5 class="text-gray-800 font-bold border-b pb-2 text-lg">{{ $compNombre }}</h5>
                
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mt-6 items-center">
                    <div class="lg:col-span-5">
                        <div class="overflow-x-auto rounded-lg border border-gray-200">
                            <table class="min-w-full divide-y divide-gray-200 text-center text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-3 py-3 font-semibold text-gray-700">Nivel de Logro</th>
                                        <th scope="col" class="px-3 py-3 font-semibold text-gray-700">Estudiantes</th>
                                        <th scope="col" class="px-3 py-3 font-semibold text-gray-700">Porcentaje</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr>
                                        <td class="px-3 py-2 font-bold text-green-600">Logro Destacado (AD)</td>
                                        <td class="px-3 py-2">{{ $compData['AD'] }}</td>
                                        <td class="px-3 py-2 font-medium">{{ $compData['porcentajes']['AD'] }}%</td>
                                    </tr>
                                    <tr>
                                        <td class="px-3 py-2 font-bold text-cyan-600">Logro Esperado (A)</td>
                                        <td class="px-3 py-2">{{ $compData['A'] }}</td>
                                        <td class="px-3 py-2 font-medium">{{ $compData['porcentajes']['A'] }}%</td>
                                    </tr>
                                    <tr>
                                        <td class="px-3 py-2 font-bold text-yellow-600">En Proceso (B)</td>
                                        <td class="px-3 py-2">{{ $compData['B'] }}</td>
                                        <td class="px-3 py-2 font-medium">{{ $compData['porcentajes']['B'] }}%</td>
                                    </tr>
                                    <tr>
                                        <td class="px-3 py-2 font-bold text-red-600">En Inicio (C)</td>
                                        <td class="px-3 py-2">{{ $compData['C'] }}</td>
                                        <td class="px-3 py-2 font-medium">{{ $compData['porcentajes']['C'] }}%</td>
                                    </tr>
                                    <tr class="bg-gray-100 font-bold text-gray-800">
                                        <td class="px-3 py-3">TOTAL</td>
                                        <td class="px-3 py-3">{{ $compData['total'] }}</td>
                                        <td class="px-3 py-3">100%</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Interpretación Automatizada -->
                        @php
                            $alertClass = "bg-blue-50 border-blue-500 text-blue-800";
                            $iconColor = "text-blue-500";
                            if(str_contains($compData['interpretacion'], 'Alerta')) {
                                $alertClass = "bg-red-50 border-red-500 text-red-800";
                                $iconColor = "text-red-500";
                            } elseif(str_contains($compData['interpretacion'], 'Atención')) {
                                $alertClass = "bg-yellow-50 border-yellow-500 text-yellow-800";
                                $iconColor = "text-yellow-500";
                            } elseif(str_contains($compData['interpretacion'], 'Destacado')) {
                                $alertClass = "bg-green-50 border-green-500 text-green-800";
                                $iconColor = "text-green-500";
                            }
                        @endphp
                        
                        <div class="interpretacion-box mt-4 border-l-4 p-4 rounded-r-lg {{ $alertClass }}">
                            <div class="flex items-start">
                                <div class="flex-shrink-0 mt-0.5">
                                    <svg class="h-5 w-5 {{ $iconColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-bold uppercase tracking-wide">Análisis Automático</h3>
                                    <div class="mt-1 text-sm">
                                        {{ $compData['interpretacion'] }}
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    
                    <div class="lg:col-span-7">
                        <div id="{{ $chartId }}" class="chart-container"></div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endforeach
