<!-- ==================== TAB: NOTAS ==================== -->
                        @if($tab === 'notas')
                            @if($resumen->isEmpty())
                                <div class="text-center py-12 text-gray-500">
                                    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                    <p class="text-lg font-medium text-gray-600">No hay datos de rendimiento disponibles aún.</p>
                                    <p class="text-sm text-gray-400 mt-1">Sube notas bimestrales desde el panel de importación.</p>
                                </div>
                            @else
                                <div class="flex items-center justify-between border-b pb-4 mb-6">
                                    <h3 class="text-lg font-bold text-gray-800">Calificaciones Consolidadas por Curso y Competencia</h3>
                                    <span class="bg-yellow-100 text-yellow-800 text-xs font-semibold px-2.5 py-0.5 rounded">Año Lectivo: {{ $anoActivo->anio }}</span>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                                    @php
                                        $totalAD = 0;
                                        $totalA = 0;
                                        $totalB = 0;
                                        $totalC = 0;
                                        foreach ($resumen as $row) {
                                            if ($row->promedio_letra == 'AD') $totalAD += $row->cantidad;
                                            if ($row->promedio_letra == 'A') $totalA += $row->cantidad;
                                            if ($row->promedio_letra == 'B') $totalB += $row->cantidad;
                                            if ($row->promedio_letra == 'C') $totalC += $row->cantidad;
                                        }
                                    @endphp
                                    
                                    <div class="bg-indigo-50/60 border border-indigo-100 rounded-xl p-5 text-center shadow-sm">
                                        <div class="text-3xl font-extrabold text-indigo-700">{{ $totalAD }}</div>
                                        <div class="text-sm font-semibold text-indigo-600 mt-1">Calificaciones AD</div>
                                    </div>
                                    <div class="bg-emerald-50/60 border border-emerald-100 rounded-xl p-5 text-center shadow-sm">
                                        <div class="text-3xl font-extrabold text-emerald-700">{{ $totalA }}</div>
                                        <div class="text-sm font-semibold text-emerald-600 mt-1">Calificaciones A</div>
                                    </div>
                                    <div class="bg-amber-50/60 border border-amber-100 rounded-xl p-5 text-center shadow-sm">
                                        <div class="text-3xl font-extrabold text-amber-700">{{ $totalB }}</div>
                                        <div class="text-sm font-semibold text-amber-600 mt-1">Calificaciones B</div>
                                    </div>
                                    <div class="bg-rose-50/60 border border-rose-100 rounded-xl p-5 text-center shadow-sm">
                                        <div class="text-3xl font-extrabold text-rose-700">{{ $totalC }}</div>
                                        <div class="text-sm font-semibold text-rose-600 mt-1">Calificaciones C</div>
                                    </div>
                                </div>

                                <div class="space-y-8">
                                    @php
                                        $agrupadoPorCurso = $resumen->groupBy('curso_nombre');
                                    @endphp

                                    @foreach($agrupadoPorCurso as $cursoNombre => $competencias)
                                        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                                            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                                                <div class="flex items-center gap-3">
                                                    <div class="p-2 bg-yellow-100 rounded-lg text-yellow-800">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                                        </svg>
                                                    </div>
                                                    <h4 class="text-lg font-bold text-gray-800">{{ $cursoNombre }}</h4>
                                                </div>
                                                <span class="bg-yellow-100 text-yellow-800 text-xs font-bold px-3 py-1 rounded-full">
                                                    {{ $competencias->sum('cantidad') }} Calificaciones
                                                </span>
                                            </div>

                                            <div class="p-6">
                                                <div class="overflow-x-auto">
                                                    <table class="min-w-full divide-y divide-gray-200">
                                                        <thead class="bg-gray-50">
                                                            <tr>
                                                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-1/2">Competencia</th>
                                                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Notas AD</th>
                                                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Notas A</th>
                                                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Notas B</th>
                                                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Notas C</th>
                                                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Total</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="bg-white divide-y divide-gray-200">
                                                            @php
                                                                $agrupadoPorCompetencia = $competencias->groupBy('competencia_nombre');
                                                            @endphp
                                                            @foreach($agrupadoPorCompetencia as $compNombre => $rows)
                                                                @php
                                                                    $cantAD = $rows->where('promedio_letra', 'AD')->sum('cantidad');
                                                                    $cantA = $rows->where('promedio_letra', 'A')->sum('cantidad');
                                                                    $cantB = $rows->where('promedio_letra', 'B')->sum('cantidad');
                                                                    $cantC = $rows->where('promedio_letra', 'C')->sum('cantidad');
                                                                    $totalComp = $rows->sum('cantidad');
                                                                @endphp
                                                                <tr class="hover:bg-gray-50 transition-colors">
                                                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                                                        {{ $compNombre }}
                                                                    </td>
                                                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-bold text-indigo-700 bg-indigo-50/20">
                                                                        {{ $cantAD }}
                                                                    </td>
                                                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-bold text-emerald-700 bg-emerald-50/20">
                                                                        {{ $cantA }}
                                                                    </td>
                                                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-bold text-amber-700 bg-amber-50/20">
                                                                        {{ $cantB }}
                                                                    </td>
                                                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-bold text-rose-700 bg-rose-50/20">
                                                                        {{ $cantC }}
                                                                    </td>
                                                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-semibold text-gray-800 bg-gray-50/40">
                                                                        {{ $totalComp }}
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        @endif
