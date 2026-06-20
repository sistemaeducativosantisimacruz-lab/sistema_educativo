<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Mis Cursos') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-8 py-5 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-extrabold text-gray-800">Mis Cursos</h2>
                        <p class="text-sm text-gray-500">Selecciona un curso para ver sus evaluaciones y al docente a cargo</p>
                    </div>
                    @if($matriculaActiva)
                        <span class="text-xs font-bold bg-indigo-100 text-indigo-800 px-3 py-1.5 rounded-full">
                            {{ $matriculaActiva->gradoSeccion->grado->nombre }} — Sección {{ $matriculaActiva->gradoSeccion->seccion->nombre }}
                        </span>
                    @endif
                </div>

                <div class="p-8">
                    @if(!$matriculaActiva)
                        <div class="text-center py-12 text-gray-400">
                            <svg class="mx-auto h-14 w-14 mb-3 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                            <p class="font-semibold">No tienes matrícula activa</p>
                            <p class="text-sm">Contacta al administrador del sistema.</p>
                        </div>
                    @elseif($docentes->isEmpty())
                        <div class="text-center py-12 text-gray-400">
                            <svg class="mx-auto h-14 w-14 mb-3 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/>
                            </svg>
                            <p class="font-semibold">Aún no hay cursos asignados a tu sección</p>
                        </div>
                    @else
                        @php
                            $colors = [
                                '#7c3aed', '#0284c7', '#059669', '#d97706',
                                '#e11d48', '#4338ca', '#0d9488', '#ea580c',
                                '#7e22ce', '#0f766e',
                            ];
                        @endphp

                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-5">
                            @foreach($docentes as $i => $item)
                                @if(isset($item['curso']))
                                @php
                                    $color = $colors[$i % count($colors)];
                                    $nombreCurso = $item['curso']?->nombre ?? 'Sin nombre';
                                    $partes = array_filter(explode(' ', $nombreCurso));
                                    $partes = array_values($partes);
                                    $ini = strtoupper(substr($partes[0] ?? '?', 0, 1));
                                    if (isset($partes[1])) $ini .= strtoupper(substr($partes[1], 0, 1));
                                @endphp

                                <a href="{{ route('estudiante.cursos.show', ['asignacion' => $item['asignacion_id'] ?: 0, 'curso' => $item['curso']->id ?? null]) }}"
                                   class="group flex flex-col items-center text-center p-5 rounded-2xl transition-all duration-200 hover:-translate-y-1 hover:shadow-lg"
                                   style="background: {{ $color }}12; border: 2px solid {{ $color }}25;"
                                   onmouseover="this.style.background='{{ $color }}22'; this.style.borderColor='{{ $color }}60';"
                                   onmouseout="this.style.background='{{ $color }}12'; this.style.borderColor='{{ $color }}25';">

                                    <div class="w-16 h-16 rounded-2xl flex items-center justify-center mb-3 shadow-md transition-transform duration-200 group-hover:scale-110"
                                         style="background: {{ $color }};">
                                        <span class="text-xl font-black" style="color: white;">{{ $ini }}</span>
                                    </div>

                                    <p class="text-sm font-extrabold text-gray-800 leading-snug">
                                        {{ $nombreCurso }}
                                    </p>

                                    <div class="mt-3 flex items-center gap-1 text-xs font-bold"
                                         style="color: {{ $color }};">
                                        <span>Ingresar</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </div>
                                </a>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
