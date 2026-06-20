<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Admin Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Bienvenida -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100">
                <div class="p-6 text-gray-900 flex flex-col md:flex-row items-center justify-between gap-4">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-800">{{ __("¡Bienvenido al panel de administración!") }}</h3>
                        <p class="text-gray-500 mt-1 text-sm">Resumen general del estado actual de la institución educativa.</p>
                    </div>
                </div>
            </div>

            <!-- Tarjetas de Resumen Principales (3 Columnas) -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                <!-- Tarjeta 1: Estudiantes -->
                <div class="relative overflow-hidden bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl shadow-lg p-6 group hover:-translate-y-1 transition-all duration-300">
                    <div class="absolute -right-6 -top-6 text-white/20 group-hover:scale-110 group-hover:rotate-12 transition-transform duration-500">
                        <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12.5a4 4 0 100-8 4 4 0 000 8zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                    </div>
                    <div class="relative z-10">
                        <div class="flex items-center space-x-3 text-white/80 mb-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            <span class="font-medium text-sm uppercase tracking-wider">Estudiantes Activos</span>
                        </div>
                        <h4 class="text-4xl font-extrabold text-white">{{ $totalEstudiantesActivos }}</h4>
                        <p class="text-white/70 text-xs mt-2">Matriculados en el año lectivo</p>
                    </div>
                </div>

                <!-- Tarjeta 2: Docentes -->
                <div class="relative overflow-hidden bg-gradient-to-br from-emerald-400 to-teal-500 rounded-2xl shadow-lg p-6 group hover:-translate-y-1 transition-all duration-300">
                    <div class="absolute -right-6 -top-6 text-white/20 group-hover:scale-110 group-hover:rotate-12 transition-transform duration-500">
                        <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 24 24"><path d="M21.5 9h-2v-1a1 1 0 00-1-1h-3.5V5.5c0-.83-.67-1.5-1.5-1.5H10.5C9.67 4 9 4.67 9 5.5V7H5.5a1 1 0 00-1 1V9h-2c-.55 0-1 .45-1 1v4c0 .55.45 1 1 1h2v4a1 1 0 001 1h13a1 1 0 001-1v-4h2c.55 0 1-.45 1-1v-4c0-.55-.45-1-1-1zm-10-3.5h1v1.5h-1V5.5zm4 11.5h-7v-6h7v6zm-3.5-5c.83 0 1.5.67 1.5 1.5s-.67 1.5-1.5 1.5-1.5-.67-1.5-1.5.67-1.5 1.5-1.5z"/></svg>
                    </div>
                    <div class="relative z-10">
                        <div class="flex items-center space-x-3 text-white/80 mb-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                            <span class="font-medium text-sm uppercase tracking-wider">Total de Docentes</span>
                        </div>
                        <h4 class="text-4xl font-extrabold text-white">{{ $totalDocentes }}</h4>
                        <p class="text-white/70 text-xs mt-2">Registrados en plataforma</p>
                    </div>
                </div>

                <!-- Tarjeta 3: Secciones -->
                <div class="relative overflow-hidden bg-gradient-to-br from-violet-500 to-fuchsia-600 rounded-2xl shadow-lg p-6 group hover:-translate-y-1 transition-all duration-300">
                    <div class="absolute -right-6 -top-6 text-white/20 group-hover:scale-110 group-hover:rotate-12 transition-transform duration-500">
                        <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 24 24"><path d="M4 10v7h3v-7H4zm6 0v7h3v-7h-3zM2 22h19v-3H2v3zm14-12v7h3v-7h-3zm-4.5-9L2 6v2h19V6l-9.5-5z"/></svg>
                    </div>
                    <div class="relative z-10">
                        <div class="flex items-center space-x-3 text-white/80 mb-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                            <span class="font-medium text-sm uppercase tracking-wider">Aulas Activas</span>
                        </div>
                        <h4 class="text-4xl font-extrabold text-white">{{ $totalSecciones }}</h4>
                        <p class="text-white/70 text-xs mt-2">Secciones configuradas</p>
                    </div>
                </div>

            </div>

            <!-- Bloque 2: Recaudación y Avance de Notas -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                
                <!-- Recaudación -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col justify-between">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                        <h4 class="text-lg font-bold text-gray-800">Recaudación (Mes)</h4>
                        <form method="GET" action="{{ route('admin.dashboard') }}" class="flex items-center gap-2">
                            <select name="mes" onchange="this.form.submit()" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm py-1 pl-3 pr-8">
                                @foreach ($meses as $num => $nombre)
                                    <option value="{{ $num }}" {{ $currentMonth == $num ? 'selected' : '' }}>
                                        {{ $nombre }}
                                    </option>
                                @endforeach
                            </select>
                            <select name="anio" onchange="this.form.submit()" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm py-1 pl-3 pr-8">
                                @for ($i = date('Y') - 2; $i <= date('Y') + 1; $i++)
                                    <option value="{{ $i }}" {{ $currentYear == $i ? 'selected' : '' }}>
                                        {{ $i }}
                                    </option>
                                @endfor
                            </select>
                        </form>
                    </div>

                    <div class="relative overflow-hidden bg-gradient-to-br from-orange-400 to-red-500 rounded-xl p-6 group">
                        <div class="absolute -right-4 -top-4 text-white/20 group-hover:scale-110 group-hover:rotate-12 transition-transform duration-500">
                            <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 24 24"><path d="M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78 0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61 0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41 0 .69-.49 1.79-2.7 1.79-2.06 0-2.87-.92-2.98-2.1h-2.2c.12 2.19 1.76 3.42 3.68 3.83V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55 0-2.84-2.43-3.81-4.7-4.4z"/></svg>
                        </div>
                        <div class="relative z-10">
                            <div class="flex items-center space-x-3 text-white/80 mb-2">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span class="font-medium text-sm uppercase tracking-wider">Estado al día</span>
                            </div>
                            <div class="flex items-end space-x-1">
                                <h4 class="text-4xl font-extrabold text-white">{{ $porcentajeAlDia }}</h4>
                                <span class="text-2xl font-bold text-white/80 mb-1">%</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Consolidación de Notas -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col justify-between">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                        <h4 class="text-lg font-bold text-gray-800">Consolidación de Notas</h4>
                        @if($currentBimestre)
                            <span class="px-3 py-1 bg-blue-100 text-blue-800 text-xs font-bold rounded-full">
                                Bimestre {{ $currentBimestre->numero }} - Faltan {{ $diasFaltantesBimestre }} días
                            </span>
                        @else
                            <span class="px-3 py-1 bg-gray-100 text-gray-800 text-xs font-bold rounded-full">
                                Sin Bimestre Activo
                            </span>
                        @endif
                    </div>

                    <div class="bg-gray-50 rounded-xl p-6 border border-gray-100 flex-grow flex flex-col justify-center">
                        <div class="flex justify-between text-sm font-medium text-gray-600 mb-2">
                            <span>Avance de Docentes</span>
                            <span>{{ $docentesConNotas }} de {{ $totalDocentesAsignados }} ({{ $porcentajeNotas }}%)</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-4 mb-4">
                            <div class="bg-indigo-600 h-4 rounded-full transition-all duration-1000 ease-out" style="width: {{ $porcentajeNotas }}%"></div>
                        </div>
                        <p class="text-sm text-gray-500">
                            Muestra la proporción de docentes que ya han registrado calificaciones para el periodo en curso.
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
