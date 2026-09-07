<!-- ==================== TAB: DEUDAS ==================== -->
                        @if($tab === 'deudas')
                            <div class="flex items-center justify-between border-b pb-4 mb-6">
                                <h3 class="text-lg font-bold text-gray-800">Reporte de Mensualidades Pendientes (Deudores)</h3>
                                <span class="bg-red-100 text-red-800 text-xs font-semibold px-2.5 py-0.5 rounded">Pendientes: {{ $deudas->total() }}</span>
                            </div>

                            @if($deudas->isEmpty())
                                <div class="text-center py-12 text-gray-500">
                                    <svg class="w-16 h-16 mx-auto text-emerald-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <p class="text-lg font-medium text-gray-600">¡Al día! No se registran deudas pendientes para este filtro.</p>
                                </div>
                            @else
                                <div class="overflow-x-auto border border-gray-100 rounded-xl">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Cód. Estudiante</th>
                                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Estudiante</th>
                                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Grado y Sección</th>
                                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Mes Pendiente</th>
                                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Estado</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($deudas as $deuda)
                                                <tr class="hover:bg-gray-50 transition-colors">
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-600">
                                                        {{ $deuda->matricula->estudiante->codigo_estudiante ?? '—' }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-800">
                                                        {{ $deuda->matricula->estudiante->nombre_completo }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                                        {{ $deuda->matricula->gradoSeccion->grado->nombre ?? '—' }} - {{ $deuda->matricula->gradoSeccion->seccion->nombre ?? '—' }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 font-medium">
                                                        {{ $deuda->nombre_mes }} ({{ $deuda->anio }})
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-800">
                                                            {{ $deuda->estado }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @if($deudas->hasPages())
                                    <div class="mt-4">
                                        {{ $deudas->links() }}
                                    </div>
                                @endif
                            @endif
                        @endif
