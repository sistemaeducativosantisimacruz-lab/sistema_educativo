<!-- ==================== TAB: DOCENTES ==================== -->
                        @if($tab === 'docentes')
                            <div class="flex items-center justify-between border-b pb-4 mb-6">
                                <h3 class="text-lg font-bold text-gray-800">Directorio y Asignaciones de Docentes</h3>
                                <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded">Total: {{ $docentesReport->count() }}</span>
                            </div>

                            @if($docentesReport->isEmpty())
                                <div class="text-center py-12 text-gray-500">
                                    <p class="text-lg font-medium">No hay docentes registrados en el sistema.</p>
                                </div>
                            @else
                                <div class="overflow-x-auto border border-gray-100 rounded-xl">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider w-16">N°</th>
                                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Docente</th>
                                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">DNI / Celular</th>
                                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Correo Electrónico</th>
                                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tipo Docente</th>
                                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Cursos & Carga Horaria</th>
                                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Secciones Asignadas</th>
                                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Sección Tutorada</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($docentesReport as $docente)
                                                <tr class="hover:bg-gray-50 transition-colors">
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-semibold text-center w-16">
                                                        {{ $loop->iteration }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <div class="text-sm font-bold text-gray-900">
                                                            {{ $docente->apellido_paterno }} {{ $docente->apellido_materno }}, {{ $docente->nombres }}
                                                        </div>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                                        <div class="font-medium">DNI: {{ $docente->dni ?? '—' }}</div>
                                                        <div class="text-xs text-gray-500">Cel: {{ $docente->celular ?? '—' }}</div>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 font-mono">
                                                        {{ $docente->user->email ?? '—' }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold 
                                                            {{ $docente->esPolidocente() ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                                                            {{ ucfirst($docente->tipo) }}
                                                        </span>
                                                    </td>
                                                    <td class="px-6 py-4 text-sm text-gray-600 max-w-xs">
                                                        <div class="font-medium truncate" title="{{ $docente->nombresCursos() }}">
                                                            {{ $docente->nombresCursos() }}
                                                        </div>
                                                    </td>
                                                    <td class="px-6 py-4 text-sm text-gray-600 font-medium">
                                                        @php
                                                            $seccionesDocente = $docente->asignaciones->pluck('gradoSeccion')->unique('id');
                                                        @endphp
                                                        @if($seccionesDocente->isEmpty())
                                                            <span class="text-gray-400 italic text-xs">Sin carga</span>
                                                        @else
                                                            <div class="flex flex-wrap gap-1">
                                                                @foreach($seccionesDocente as $gs)
                                                                    <span class="bg-gray-150 text-gray-800 text-xs px-2 py-0.5 rounded font-semibold border border-gray-200 bg-gray-50">
                                                                        {{ $gs->grado->nombre ?? '—' }} - {{ $gs->seccion->nombre ?? '—' }}
                                                                    </span>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    </td>
                                                    <td class="px-6 py-4 text-sm text-gray-600 font-medium font-semibold">
                                                         @php
                                                             $tutorSecc = $docente->tutoriaSecciones->map(function($ts) {
                                                                 return ($ts->grado->nombre ?? '') . ' - ' . ($ts->seccion->nombre ?? '');
                                                             })->filter()->join(', ');
                                                         @endphp
                                                         @if($tutorSecc)
                                                             <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-800 border border-green-200 shadow-sm">
                                                                 {{ $tutorSecc }}
                                                             </span>
                                                         @else
                                                             <span class="text-gray-400 italic text-xs">—</span>
                                                         @endif
                                                     </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        @endif
