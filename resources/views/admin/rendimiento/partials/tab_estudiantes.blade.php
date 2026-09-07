<!-- ==================== TAB: ESTUDIANTES ==================== -->
                        @if($tab === 'estudiantes')
                            <div class="flex items-center justify-between border-b pb-4 mb-6">
                                <h3 class="text-lg font-bold text-gray-800">Directorio de Estudiantes para Reporte</h3>
                                <span class="bg-emerald-100 text-emerald-800 text-xs font-semibold px-2.5 py-0.5 rounded">Total Encontrados: {{ $estudiantesList->total() }}</span>
                            </div>

                            @if($estudiantesList->isEmpty())
                                <div class="text-center py-12 text-gray-500">
                                    <p class="text-lg font-medium">No hay estudiantes que coincidan con la búsqueda.</p>
                                </div>
                            @else
                                <div class="overflow-x-auto border border-gray-100 rounded-xl mb-4">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Cód. / DNI</th>
                                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Estudiante</th>
                                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Grado y Sección</th>
                                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($estudiantesList as $estudiante)
                                                @php
                                                    $matricula = $estudiante->matriculas->first();
                                                @endphp
                                                <tr class="hover:bg-gray-50 transition-colors">
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 font-mono">
                                                        <div class="font-medium text-gray-900">{{ $estudiante->codigo_estudiante ?? 'S/C' }}</div>
                                                        <div class="text-xs text-gray-500">DNI: {{ $estudiante->dni }}</div>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <div class="text-sm font-bold text-gray-900">
                                                            {{ $estudiante->apellido_paterno }} {{ $estudiante->apellido_materno }}, {{ $estudiante->nombres }}
                                                        </div>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                                        {{ $matricula ? $matricula->gradoSeccion->grado->nombre . ' - ' . $matricula->gradoSeccion->seccion->nombre : 'Sin Matrícula' }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                                        <a href="{{ route('admin.rendimiento.exportar_estudiante', ['estudiante_id' => $estudiante->id]) }}" 
                                                           class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-bold rounded-lg shadow-sm text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-colors">
                                                            <svg class="-ml-0.5 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                            </svg>
                                                            Descargar Récord Excel
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="px-4">
                                    {{ $estudiantesList->appends(request()->query())->links() }}
                                </div>
                            @endif
                        @endif
