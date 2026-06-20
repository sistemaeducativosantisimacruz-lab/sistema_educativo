<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('docente.seccion.estudiantes', $gradoSeccion->id) }}" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Notas del Estudiante') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="flex flex-col md:flex-row items-start md:items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center font-bold text-2xl shadow-sm">
                            {{ substr($estudiante->nombres, 0, 1) }}{{ substr($estudiante->apellido_paterno, 0, 1) }}
                        </div>
                        <div>
                            <h3 class="text-2xl font-extrabold text-gray-900">{{ $estudiante->nombre_completo }}</h3>
                            <div class="flex flex-wrap gap-2 mt-2">
                                <span class="bg-gray-100 text-gray-600 px-3 py-1 rounded-full text-xs font-semibold">DNI: {{ $estudiante->dni }}</span>
                                <span class="bg-indigo-50 text-indigo-700 px-3 py-1 rounded-full text-xs font-bold">{{ $gradoSeccion->grado->nombre }} - {{ $gradoSeccion->seccion->nombre }}</span>
                                <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-xs font-bold">Año Lectivo: {{ $anoActivo->anio }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if($cursos->isEmpty())
                <div class="bg-white text-gray-500 p-12 rounded-xl border border-gray-100 text-center shadow-sm">
                    <p class="text-lg font-medium">No tienes cursos asignados para visualizar las notas en esta sección.</p>
                </div>
            @else
                <div class="space-y-6">
                    @foreach($cursos as $curso)
                        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex items-center gap-3">
                                <div class="p-2 bg-indigo-100 rounded-lg text-indigo-800">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                    </svg>
                                </div>
                                <h4 class="text-lg font-bold text-gray-800 uppercase">{{ $curso->nombre }}</h4>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-1/2">Competencias</th>
                                            @foreach($bimestres as $bimestre)
                                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                                    B{{ $bimestre->numero }}
                                                </th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($curso->competencias as $competencia)
                                            <tr class="hover:bg-gray-50 transition-colors">
                                                <td class="px-6 py-4 text-sm font-medium text-gray-900 leading-tight">
                                                    {{ $competencia->nombre }}
                                                </td>
                                                @foreach($bimestres as $bimestre)
                                                    @php
                                                        $notaObj = $notas[$curso->id][$competencia->id][$bimestre->id] ?? null;
                                                        $letra = $notaObj ? $notaObj->nota : '-';
                                                        
                                                        // Styles for different grades
                                                        $bgColor = 'bg-gray-50';
                                                        $textColor = 'text-gray-400';
                                                        $fontWeight = 'font-normal';
                                                        
                                                        if ($letra === 'AD') {
                                                            $bgColor = 'bg-indigo-100';
                                                            $textColor = 'text-indigo-800';
                                                            $fontWeight = 'font-extrabold';
                                                        } elseif ($letra === 'A') {
                                                            $bgColor = 'bg-emerald-100';
                                                            $textColor = 'text-emerald-800';
                                                            $fontWeight = 'font-bold';
                                                        } elseif ($letra === 'B') {
                                                            $bgColor = 'bg-amber-100';
                                                            $textColor = 'text-amber-800';
                                                            $fontWeight = 'font-bold';
                                                        } elseif ($letra === 'C') {
                                                            $bgColor = 'bg-rose-100';
                                                            $textColor = 'text-rose-800';
                                                            $fontWeight = 'font-bold';
                                                        }
                                                    @endphp
                                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                                        <span class="inline-flex items-center justify-center w-10 h-10 rounded-lg {{ $bgColor }} {{ $textColor }} {{ $fontWeight }} shadow-sm">
                                                            {{ $letra }}
                                                        </span>
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
