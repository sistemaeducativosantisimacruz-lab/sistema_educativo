<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Confirmar Cierre de Bimestre ' . $bimestre->numero) }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Advertencia Principal -->
            <div class="bg-amber-50 border-l-4 border-amber-500 p-4 mb-6 rounded shadow-sm">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-amber-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-bold text-amber-800">
                            ¡Atención! Acción Irreversible
                        </h3>
                        <div class="mt-2 text-sm text-amber-700">
                            <p>
                                Al cerrar el <strong>Bimestre {{ $bimestre->numero }}</strong> del Año Lectivo <strong>{{ $anoActivo->anio }}</strong>, se consolidarán y calcularán automáticamente los promedios bimestrales de todos los estudiantes. 
                                <br>
                                <span class="font-bold">Una vez cerrado, los docentes no podrán registrar ni modificar notas para este periodo.</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Resumen por Sección -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                        Resumen de Calificaciones Pendientes por Sección
                    </h3>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                        Grado y Sección
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">
                                        Total Matriculados
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">
                                        Estudiantes sin Notas
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">
                                        Estado
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($resumen as $item)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                            {{ $item['seccion_nombre'] }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500">
                                            {{ $item['total_estudiantes'] }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center font-bold {{ $item['estudiantes_sin_notas'] > 0 ? 'text-amber-600' : 'text-green-600' }}">
                                            {{ $item['estudiantes_sin_notas'] }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                            @if ($item['estudiantes_sin_notas'] > 0)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                                    Calificaciones Faltantes
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    Completo
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">
                                            No hay secciones registradas para este año activo.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 p-4 bg-gray-50 rounded text-xs text-gray-500">
                        * Un estudiante es considerado "sin notas" si no tiene registrada ninguna calificación en las competencias de los cursos en este bimestre escolar.
                    </div>
                </div>
            </div>

            <!-- Formulario de Acción y Cuenta Regresiva -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <div class="p-6 bg-white flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div>
                        <h4 class="text-md font-bold text-gray-900">¿Desea proceder con el cierre definitivo del Bimestre {{ $bimestre->numero }}?</h4>
                        <p class="text-sm text-gray-500 mt-1">Por favor, asegúrate de verificar el resumen de notas pendientes antes de continuar.</p>
                    </div>
                    
                    <div class="flex items-center gap-3 w-full sm:w-auto justify-end" x-data="{ secondsLeft: 5, timer: null }" x-init="timer = setInterval(() => { if (secondsLeft > 0) { secondsLeft-- } else { clearInterval(timer) } }, 1000)">
                        <a href="{{ route('admin.bimestres.index') }}" class="w-full sm:w-auto inline-flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition">
                            Cancelar
                        </a>

                        <form action="{{ route('admin.bimestres.cerrar', $bimestre) }}" method="POST" class="inline w-full sm:w-auto">
                            @csrf
                            <button type="submit" :disabled="secondsLeft > 0" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-bold rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition disabled:opacity-50 disabled:cursor-not-allowed">
                                <svg x-show="secondsLeft > 0" xmlns="http://www.w3.org/2000/svg" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span x-show="secondsLeft > 0">Espera <span x-text="secondsLeft"></span>s para confirmar</span>
                                <span x-show="secondsLeft === 0">Confirmar y Cerrar</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
