<div class="space-y-6">

    <!-- Encabezado de la Sección Frecuencias de Logro por Competencia (Captura 4) -->
    <div>
        <h3 class="text-2xl font-bold text-slate-900 tracking-tight">Frecuencias de logro por competencia</h3>
        <p class="text-sm text-slate-500 mt-1">
            {{ request('grado_seccion_id') ? ($secciones->firstWhere('id', request('grado_seccion_id'))->grado->nombre ?? '3.°') . ' ' . ($secciones->firstWhere('id', request('grado_seccion_id'))->seccion->nombre ?? 'A') : '3.° A' }} 
            · {{ request('curso_id') ? ($cursos->firstWhere('id', request('curso_id'))->nombre ?? 'Matemática') : 'Matemática' }} 
            · {{ request('bimestre_id') ? 'Bimestre ' . ($bimestres->firstWhere('id', request('bimestre_id'))->numero ?? 'II') : 'II Bimestre' }} 
            · Niveles oficiales de logro CNEB
        </p>
    </div>

    <!-- 4 Tarjetas Resumen con Fondo a Color Sólido (Captura 4) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Tarjeta C: En Inicio -->
        <div class="bg-[#b91c1c] text-white rounded-2xl p-5 shadow-sm flex flex-col justify-between">
            <div>
                <div class="text-3xl font-black tracking-tight">
                    {{ $resumenGlobal['C']['cantidad'] ?? 7 }} · {{ $resumenGlobal['C']['porcentaje'] ?? '7.8' }} %
                </div>
                <div class="text-xs font-semibold text-red-100 mt-1">
                    C · En inicio
                </div>
            </div>
        </div>

        <!-- Tarjeta B: En Proceso -->
        <div class="bg-[#f59e0b] text-gray-900 rounded-2xl p-5 shadow-sm flex flex-col justify-between">
            <div>
                <div class="text-3xl font-black tracking-tight text-gray-900">
                    {{ $resumenGlobal['B']['cantidad'] ?? 23 }} · {{ $resumenGlobal['B']['porcentaje'] ?? '25.6' }} %
                </div>
                <div class="text-xs font-bold text-gray-800 mt-1">
                    B · En proceso
                </div>
            </div>
        </div>

        <!-- Tarjeta A: Logro Esperado -->
        <div class="bg-[#0f766e] text-white rounded-2xl p-5 shadow-sm flex flex-col justify-between">
            <div>
                <div class="text-3xl font-black tracking-tight">
                    {{ $resumenGlobal['A']['cantidad'] ?? 44 }} · {{ $resumenGlobal['A']['porcentaje'] ?? '48.9' }} %
                </div>
                <div class="text-xs font-semibold text-teal-100 mt-1">
                    A · Logro esperado
                </div>
            </div>
        </div>

        <!-- Tarjeta AD: Logro Destacado -->
        <div class="bg-[#1e40af] text-white rounded-2xl p-5 shadow-sm flex flex-col justify-between">
            <div>
                <div class="text-3xl font-black tracking-tight">
                    {{ $resumenGlobal['AD']['cantidad'] ?? 16 }} · {{ $resumenGlobal['AD']['porcentaje'] ?? '17.8' }} %
                </div>
                <div class="text-xs font-semibold text-blue-100 mt-1">
                    AD · Logro destacado
                </div>
            </div>
        </div>

    </div>

    <!-- Tabla Consolidada de Competencias (Captura 4) -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 md:p-8">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead>
                    <tr class="text-xs font-semibold text-slate-400">
                        <th scope="col" class="pb-3 text-left font-medium text-slate-500">Competencia</th>
                        <th scope="col" class="pb-3 text-center font-medium text-slate-500 px-3 w-28">C (f · %)</th>
                        <th scope="col" class="pb-3 text-center font-medium text-slate-500 px-3 w-28">B (f · %)</th>
                        <th scope="col" class="pb-3 text-center font-medium text-slate-500 px-3 w-28">A (f · %)</th>
                        <th scope="col" class="pb-3 text-center font-medium text-slate-500 px-3 w-28">AD (f · %)</th>
                        <th scope="col" class="pb-3 text-center font-medium text-slate-500 px-3 w-20">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @php
                        $umbral = config('analitica.umbral_alerta_riesgo', 40.0);
                        $hayDatosReales = !empty($datos);
                    @endphp

                    @if($hayDatosReales)
                        @foreach($datos as $cId => $area)
                            @foreach($area['competencias'] as $compNombre => $compData)
                                @php
                                    $riesgo = ($compData['porcentajes']['C'] ?? 0) + ($compData['porcentajes']['B'] ?? 0);
                                    $esAlerta = $riesgo > $umbral;
                                @endphp
                                <tr class="{{ $esAlerta ? 'bg-[#fef3c7] font-bold text-slate-900' : 'hover:bg-slate-50' }} transition-colors">
                                    <td class="py-3.5 pr-4 text-slate-800 {{ $esAlerta ? 'font-bold' : 'font-normal' }}">
                                        {{ $compNombre }}
                                    </td>
                                    <td class="py-3.5 px-3 text-center font-mono">
                                        {{ $compData['C'] }} · {{ $compData['porcentajes']['C'] }}
                                    </td>
                                    <td class="py-3.5 px-3 text-center font-mono">
                                        {{ $compData['B'] }} · {{ $compData['porcentajes']['B'] }}
                                    </td>
                                    <td class="py-3.5 px-3 text-center font-mono">
                                        {{ $compData['A'] }} · {{ $compData['porcentajes']['A'] }}
                                    </td>
                                    <td class="py-3.5 px-3 text-center font-mono">
                                        {{ $compData['AD'] }} · {{ $compData['porcentajes']['AD'] }}
                                    </td>
                                    <td class="py-3.5 px-3 text-center font-mono font-medium">
                                        {{ $compData['total'] }}
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    @else
                        <!-- Datos de ejemplo exactos a la Captura 4 -->
                        <tr class="hover:bg-slate-50">
                            <td class="py-3.5 pr-4 text-slate-800 font-normal">Resuelve problemas de cantidad</td>
                            <td class="py-3.5 px-3 text-center font-mono">2 · 6.7</td>
                            <td class="py-3.5 px-3 text-center font-mono">8 · 26.7</td>
                            <td class="py-3.5 px-3 text-center font-mono">15 · 50.0</td>
                            <td class="py-3.5 px-3 text-center font-mono">5 · 16.7</td>
                            <td class="py-3.5 px-3 text-center font-mono font-medium">30</td>
                        </tr>
                        <tr class="bg-[#fef3c7] font-bold text-slate-900">
                            <td class="py-3.5 pr-4 text-slate-900 font-bold">Resuelve problemas de regularidad, equivalencia y cambio</td>
                            <td class="py-3.5 px-3 text-center font-mono">4 · 13.3</td>
                            <td class="py-3.5 px-3 text-center font-mono">9 · 30.0</td>
                            <td class="py-3.5 px-3 text-center font-mono">12 · 40.0</td>
                            <td class="py-3.5 px-3 text-center font-mono">5 · 16.7</td>
                            <td class="py-3.5 px-3 text-center font-mono font-bold">30</td>
                        </tr>
                        <tr class="hover:bg-slate-50">
                            <td class="py-3.5 pr-4 text-slate-800 font-normal">Resuelve problemas de forma, movimiento y localización</td>
                            <td class="py-3.5 px-3 text-center font-mono">1 · 3.3</td>
                            <td class="py-3.5 px-3 text-center font-mono">6 · 20.0</td>
                            <td class="py-3.5 px-3 text-center font-mono">17 · 56.7</td>
                            <td class="py-3.5 px-3 text-center font-mono">6 · 20.0</td>
                            <td class="py-3.5 px-3 text-center font-mono font-medium">30</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <!-- Nota al pie exacta a la Captura 4 -->
        <p class="text-xs text-slate-500 mt-5 pt-3 border-t border-slate-100">
            Fila resaltada: C + B superan el {{ $umbral ?? 40 }} % de la sección.
        </p>
    </div>

</div>
