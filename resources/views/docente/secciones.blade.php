<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Secciones a Cargo') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 text-center md:text-left md:flex items-center justify-between">
                <div>
                    <h3 class="text-2xl font-extrabold text-gray-900">Secciones Asignadas</h3>
                    <p class="text-gray-500 mt-2 text-lg">Aquí están tus secciones asignadas para el <b>Año Lectivo {{ $anoActivo ? $anoActivo->anio : 'Actual' }}</b>.</p>
                </div>
                <div class="mt-4 md:mt-0">
                    <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-bold bg-blue-100 text-blue-800">
                        <svg class="-ml-1 mr-2 h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                        Tus Secciones
                    </span>
                </div>
            </div>

            @if(!$anoActivo)
                <div class="bg-red-50 text-red-800 p-6 rounded-xl border border-red-200 text-center font-medium shadow-sm">
                    No hay un año lectivo activo configurado actualmente.
                </div>
            @elseif($seccionesAsignadas->isEmpty())
                <div class="bg-white text-gray-500 p-12 rounded-xl border border-gray-100 text-center shadow-sm">
                    <svg class="w-20 h-20 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="text-xl font-medium">Aún no tienes grados o secciones asignadas.</p>
                    <p class="text-sm mt-2 text-gray-400">Por favor, contacta con administración.</p>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    @foreach($seccionesAsignadas as $gs)
                        <a href="{{ route('docente.seccion.estudiantes', $gs->id) }}" 
                           class="group bg-white rounded-2xl shadow-sm border {{ $gs->tutor_id === Auth::user()->docente->id ? 'border-amber-300 ring-4 ring-amber-50 pt-10' : 'border-gray-100' }} p-6 flex flex-col items-center justify-center text-center transition-all duration-300 hover:shadow-lg hover:-translate-y-1 hover:border-indigo-300 relative overflow-hidden">
                            
                            <!-- Decoración de fondo -->
                            <div class="absolute -right-6 -top-6 w-24 h-24 bg-gradient-to-br from-indigo-100 to-indigo-50 rounded-full opacity-50 group-hover:scale-150 transition-transform duration-500"></div>
                            
                            @if($gs->tutor_id === Auth::user()->docente->id)
                                <div class="absolute top-0 left-0 w-full bg-amber-400 text-amber-900 text-[10px] sm:text-xs font-extrabold py-1.5 uppercase tracking-wider z-20 shadow-sm flex items-center justify-center gap-1">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                    Sección a cargo (Tutor)
                                </div>
                            @endif

                            <div class="relative z-10">
                                <div class="w-16 h-16 bg-indigo-100 text-indigo-600 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:bg-indigo-600 group-hover:text-white transition-colors duration-300 shadow-sm">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                </div>
                                
                                <h4 class="text-xl font-extrabold text-gray-800 mb-1">{{ $gs->grado->nombre }}</h4>
                                <p class="text-indigo-600 font-bold text-lg mb-2">Sección {{ $gs->seccion->nombre }}</p>
                                
                                <div class="mt-4 px-4 py-2 bg-gray-50 rounded-lg text-sm font-semibold text-gray-600 group-hover:bg-indigo-50 group-hover:text-indigo-700 transition-colors">
                                    {{ $gs->grado->nivel === 'secundaria' ? 'Secundaria' : 'Primaria' }}
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
