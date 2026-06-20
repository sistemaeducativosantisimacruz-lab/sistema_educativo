<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('estudiante.cursos.index') }}" class="text-gray-500 hover:text-indigo-700 transition" title="Volver a Mis Cursos">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $curso ? $curso->nombre : ($asignacion->curso->nombre ?? 'Detalle del Curso') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-8 py-6 flex flex-col md:flex-row items-start md:items-center justify-between gap-5" style="background: linear-gradient(135deg, #4338ca, #3b82f6);">
                    <div class="flex items-center gap-5">
                        <div class="w-16 h-16 rounded-full flex items-center justify-center shadow-lg flex-shrink-0" style="background: rgba(255,255,255,0.2); border: 2px solid rgba(255,255,255,0.3);">
                            <span class="text-2xl font-extrabold text-white">
                                {{ $asignacion && $asignacion->docente ? strtoupper(substr($asignacion->docente->nombres, 0, 1)) : '?' }}
                            </span>
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-widest mb-1" style="color: rgba(255,255,255,0.8);">Docente a cargo</p>
                            <h1 class="text-xl font-extrabold text-white leading-tight">
                                @if($asignacion && $asignacion->docente)
                                    {{ $asignacion->docente->apellido_paterno }} {{ $asignacion->docente->apellido_materno }}, {{ $asignacion->docente->nombres }}
                                @else
                                    Docente sin asignar
                                @endif
                            </h1>
                            <div class="mt-2 flex flex-wrap gap-2">
                                @if($curso)
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold px-3 py-1 rounded-full shadow-sm" style="background: rgba(255,255,255,0.2); color: white;">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                        {{ $curso->nombre }}
                                    </span>
                                @endif
                                <span class="text-xs font-semibold px-3 py-1 rounded-full shadow-sm" style="background: rgba(0,0,0,0.2); color: white;">
                                    {{ ucfirst($matriculaActiva->gradoSeccion->grado->nivel) }}
                                </span>
                                <span class="text-xs font-semibold px-3 py-1 rounded-full shadow-sm" style="background: rgba(0,0,0,0.2); color: white;">
                                    {{ $matriculaActiva->gradoSeccion->grado->nombre }} — {{ $matriculaActiva->gradoSeccion->seccion->nombre }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="hidden md:block">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 opacity-20 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path d="M12 14l9-5-9-5-9 5 9 5z" />
                            <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 px-6 py-4 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-800">Evaluaciones del Curso</h3>
                        <p class="text-xs text-gray-500">Revisa tus calificaciones por periodo</p>
                    </div>
                </div>
                <form method="GET" action="{{ route('estudiante.cursos.show', ['asignacion' => $asignacion ? $asignacion->id : 0, 'curso' => $curso ? $curso->id : null]) }}" class="flex items-center gap-2 w-full sm:w-auto">
                    <select name="bimestre" class="border-gray-300 rounded-lg text-sm shadow-sm focus:ring-indigo-500 focus:border-indigo-500 flex-1 sm:w-48" onchange="this.form.submit()">
                        <option value="">Todos los bimestres</option>
                        @foreach($bimestres as $b)
                            <option value="{{ $b->numero }}" {{ $bimestreFiltro == $b->numero ? 'selected' : '' }}>
                                {{ $b->numero }}° Bimestre
                            </option>
                        @endforeach
                    </select>
                    @if($bimestreFiltro)
                        <a href="{{ route('estudiante.cursos.show', ['asignacion' => $asignacion ? $asignacion->id : 0, 'curso' => $curso ? $curso->id : null]) }}" class="text-gray-400 hover:text-red-500 bg-gray-50 hover:bg-red-50 p-2 rounded-lg transition" title="Quitar filtro">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </a>
                    @endif
                </form>
            </div>

            @php
                $bimestresVisibles = $bimestreFiltro
                    ? $bimestres->where('numero', $bimestreFiltro)
                    : $bimestres;
            @endphp

            @if($bimestresVisibles->isEmpty())
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center text-gray-400">
                    <svg class="mx-auto h-14 w-14 mb-3 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-3-3v6m-9 1V7a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M15 20H5a2 2 0 01-2-2V6a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2"/>
                    </svg>
                    <p class="font-semibold">No hay bimestres ni sesiones registradas</p>
                </div>
            @else
                @foreach($bimestresVisibles as $bimestre)
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 bg-gray-50">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white font-extrabold text-sm shadow-sm" style="background: #4f46e5;">
                                    {{ $bimestre->numero }}°
                                </div>
                                <div>
                                    <h3 class="font-extrabold text-gray-800">{{ $bimestre->numero }}° Bimestre</h3>
                                    <p class="text-xs text-gray-500 font-medium">
                                        {{ $bimestre->fecha_inicio->format('d/m/Y') }} — {{ $bimestre->fecha_fin->format('d/m/Y') }}
                                    </p>
                                </div>
                            </div>
                            @if($bimestre->estado === 'cerrado')
                                <span class="text-xs font-bold bg-gray-200 text-gray-600 px-3 py-1 rounded-full">Cerrado</span>
                            @elseif($bimestre->estado === 'activo')
                                <span class="text-xs font-bold bg-green-100 text-green-800 px-3 py-1 rounded-full border border-green-200">En curso</span>
                            @else
                                <span class="text-xs font-bold bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full border border-yellow-200">Pendiente</span>
                            @endif
                        </div>

                        <div class="p-6">
                            @php
                                $notasDelBimestre = $estudiante->notasBimestrales->where('bimestre_id', $bimestre->id);
                                $nivelEscolar = strtolower($matriculaActiva->gradoSeccion->grado->nivel ?? 'secundaria');
                                $notasVisibles = false;
                                if ($nivelEscolar === 'primaria' && $bimestre->notas_publicadas_primaria) {
                                    $notasVisibles = true;
                                } elseif ($nivelEscolar === 'secundaria' && $bimestre->notas_publicadas_secundaria) {
                                    $notasVisibles = true;
                                }
                            @endphp

                            @if(!$notasVisibles)
                                <div class="text-center py-8 bg-gray-50 rounded-xl border border-gray-100">
                                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                    <p class="text-sm font-bold text-gray-500">Las calificaciones de este bimestre aún no han sido publicadas o se encuentran en proceso de revisión.</p>
                                </div>
                            @elseif($notasDelBimestre->isEmpty())
                                <div class="text-center py-6 text-gray-400">
                                    <p class="text-sm">Aún no hay notas registradas en este bimestre.</p>
                                </div>
                            @else
                                <div class="overflow-x-auto border border-gray-200 rounded-xl">
                                    <table class="min-w-full text-sm">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="text-left text-xs font-bold text-gray-500 uppercase tracking-wider py-3 px-4 border-b border-gray-200">Competencia</th>
                                                <th class="text-center text-xs font-bold text-gray-500 uppercase tracking-wider py-3 px-4 border-b border-gray-200">Calificación</th>
                                                <th class="text-left text-xs font-bold text-gray-500 uppercase tracking-wider py-3 px-4 border-b border-gray-200">Conclusión Descriptiva</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100 bg-white">
                                            @foreach($notasDelBimestre as $nota)
                                                <tr>
                                                    <td class="py-3 px-4 text-gray-700 font-medium">
                                                        {{ $nota->competencia->nombre ?? 'Competencia' }}
                                                    </td>
                                                    <td class="py-3 px-4 text-center">
                                                        @php
                                                            $letra = $nota->nota;
                                                            $colorBg = match($letra) {
                                                                'AD' => '#dcfce7',
                                                                'A'  => '#dbeafe',
                                                                'B'  => '#fef9c3',
                                                                'C'  => '#fee2e2',
                                                                default => '#f3f4f6',
                                                            };
                                                            $colorText = match($letra) {
                                                                'AD' => '#166534',
                                                                'A'  => '#1e40af',
                                                                'B'  => '#854d0e',
                                                                'C'  => '#991b1b',
                                                                default => '#374151',
                                                            };
                                                        @endphp
                                                        @if($letra)
                                                            <span class="inline-block font-extrabold text-sm px-3 py-1 rounded-md" style="background-color: {{ $colorBg }}; color: {{ $colorText }}; min-width: 2.5rem;">
                                                                {{ $letra }}
                                                            </span>
                                                        @else
                                                            <span class="text-gray-400">—</span>
                                                        @endif
                                                    </td>
                                                    <td class="py-3 px-4 text-gray-500 text-xs">
                                                        {{ $nota->conclusion_descriptiva ?? '—' }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            @endif

        </div>
    </div>
</x-app-layout>
