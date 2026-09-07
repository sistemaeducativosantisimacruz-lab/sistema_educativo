<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Reportes del Sistema Académico') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Botones de Navegación de Reportes (Tabs) -->
            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
                <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                    <!-- Notas -->
                    <a href="{{ route('admin.rendimiento.index', ['tab' => 'notas', 'grado_seccion_id' => request('grado_seccion_id')]) }}" 
                       class="flex items-center justify-center gap-3 p-4 rounded-lg border-2 transition-all duration-200 {{ $tab === 'notas' ? 'border-yellow-500 bg-yellow-50/50 text-yellow-800 shadow-sm' : 'border-gray-100 hover:border-gray-200 hover:bg-gray-50 text-gray-600' }}">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <div class="text-left">
                            <span class="block text-xs font-semibold uppercase tracking-wider text-gray-400">Reporte</span>
                            <span class="font-bold text-sm md:text-base">Notas</span>
                        </div>
                    </a>

                    <!-- Deudas -->
                    <a href="{{ route('admin.rendimiento.index', ['tab' => 'deudas', 'grado_seccion_id' => request('grado_seccion_id')]) }}" 
                       class="flex items-center justify-center gap-3 p-4 rounded-lg border-2 transition-all duration-200 {{ $tab === 'deudas' ? 'border-red-500 bg-red-50/50 text-red-800 shadow-sm' : 'border-gray-100 hover:border-gray-200 hover:bg-gray-50 text-gray-600' }}">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div class="text-left">
                            <span class="block text-xs font-semibold uppercase tracking-wider text-gray-400">Reporte</span>
                            <span class="font-bold text-sm md:text-base">Deudas</span>
                        </div>
                    </a>

                    <!-- Docentes -->
                    <a href="{{ route('admin.rendimiento.index', ['tab' => 'docentes']) }}" 
                       class="flex items-center justify-center gap-3 p-4 rounded-lg border-2 transition-all duration-200 {{ $tab === 'docentes' ? 'border-blue-500 bg-blue-50/50 text-blue-800 shadow-sm' : 'border-gray-100 hover:border-gray-200 hover:bg-gray-50 text-gray-600' }}">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222 4 2.222V20"></path>
                        </svg>
                        <div class="text-left">
                            <span class="block text-xs font-semibold uppercase tracking-wider text-gray-400">Reporte</span>
                            <span class="font-bold text-sm md:text-base">Docentes</span>
                        </div>
                    </a>

                    <!-- Grados/Secciones -->
                    <a href="{{ route('admin.rendimiento.index', ['tab' => 'secciones']) }}" 
                       class="flex items-center justify-center gap-3 p-4 rounded-lg border-2 transition-all duration-200 {{ $tab === 'secciones' ? 'border-purple-500 bg-purple-50/50 text-purple-800 shadow-sm' : 'border-gray-100 hover:border-gray-200 hover:bg-gray-50 text-gray-600' }}">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                        <div class="text-left">
                            <span class="block text-xs font-semibold uppercase tracking-wider text-gray-400">Reporte</span>
                            <span class="font-bold text-sm md:text-base">Grados/Secc.</span>
                        </div>
                    </a>
                    
                    <!-- Estudiantes -->
                    <a href="{{ route('admin.rendimiento.index', ['tab' => 'estudiantes']) }}" 
                       class="flex items-center justify-center gap-3 p-4 rounded-lg border-2 transition-all duration-200 {{ $tab === 'estudiantes' ? 'border-emerald-500 bg-emerald-50/50 text-emerald-800 shadow-sm' : 'border-gray-100 hover:border-gray-200 hover:bg-gray-50 text-gray-600' }}">
                        <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <div class="text-left">
                            <span class="block text-xs font-semibold uppercase tracking-wider text-gray-400">Reporte</span>
                            <span class="font-bold text-sm md:text-base">Estudiante/Notas</span>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Filtros -->
            @if(in_array($tab, ['notas', 'deudas', 'docentes', 'estudiantes', 'secciones']))
                <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-100">
                    <div class="p-6">
                        <form action="{{ route('admin.rendimiento.index') }}" method="GET" class="grid grid-cols-1 {{ $tab === 'deudas' ? 'lg:grid-cols-5 md:grid-cols-3' : (in_array($tab, ['notas', 'docentes', 'estudiantes', 'secciones']) ? 'md:grid-cols-4' : 'md:grid-cols-2') }} gap-4 items-end">
                            <input type="hidden" name="tab" value="{{ $tab }}">
                            
                            @if(in_array($tab, ['notas', 'deudas']))
                                <div class="w-full">
                                    <label for="nivel" class="block text-sm font-semibold text-gray-700">Filtrar por Nivel</label>
                                    <select name="nivel" id="nivel" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-yellow-500 focus:ring focus:ring-yellow-200 sm:text-sm">
                                        <option value="">Todos los niveles</option>
                                        @foreach($niveles as $n)
                                            <option value="{{ $n }}" {{ request('nivel') == $n ? 'selected' : '' }}>
                                                {{ ucfirst($n) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            @if(in_array($tab, ['notas', 'deudas', 'estudiantes']))
                                <div class="w-full">
                                    <label for="grado_seccion_id" class="block text-sm font-semibold text-gray-700">Filtrar por Grado/Sección</label>
                                    <select name="grado_seccion_id" id="grado_seccion_id" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-yellow-500 focus:ring focus:ring-yellow-200 sm:text-sm">
                                        <option value="">Todas las secciones</option>
                                        @php
                                            $gsPorNivel = $gradoSecciones->groupBy(function($gs) {
                                                return $gs->grado->nivel;
                                            });
                                        @endphp
                                        @foreach($gsPorNivel as $nivelGroup => $sections)
                                            <optgroup label="{{ ucfirst($nivelGroup) }}">
                                                @foreach($sections as $gs)
                                                    <option value="{{ $gs->id }}" {{ request('grado_seccion_id') == $gs->id ? 'selected' : '' }} data-nivel="{{ $gs->grado->nivel }}">
                                                        {{ $gs->grado->nombre }} - {{ $gs->seccion->nombre }}
                                                    </option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            @if($tab === 'deudas')
                                <div class="w-full">
                                    <label for="mes" class="block text-sm font-semibold text-gray-700">Filtrar por Mes</label>
                                    <select name="mes" id="mes" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring focus:ring-red-200 sm:text-sm">
                                        <option value="">Todos los meses</option>
                                        @foreach(\App\Models\Mensualidad::meses() as $num => $nombre)
                                            <option value="{{ $num }}" {{ request('mes') == $num ? 'selected' : '' }}>{{ $nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="w-full">
                                    <label for="estado" class="block text-sm font-semibold text-gray-700">Filtrar por Estado</label>
                                    <select name="estado" id="estado" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring focus:ring-red-200 sm:text-sm">
                                        <option value="DEBE" {{ request('estado', 'DEBE') == 'DEBE' ? 'selected' : '' }}>Solo Deudores (DEBE)</option>
                                        <option value="PAGÓ" {{ request('estado') == 'PAGÓ' ? 'selected' : '' }}>Pagaron (PAGÓ)</option>
                                        <option value="EXONERADO" {{ request('estado') == 'EXONERADO' ? 'selected' : '' }}>Exonerados</option>
                                        <option value="BENEFICIADO" {{ request('estado') == 'BENEFICIADO' ? 'selected' : '' }}>Beneficiados</option>
                                        <option value="TODOS" {{ request('estado') == 'TODOS' ? 'selected' : '' }}>Todos los estados</option>
                                    </select>
                                </div>
                            @endif

                            @if($tab === 'docentes')
                                <div class="w-full">
                                    <label for="docente_nivel" class="block text-sm font-semibold text-gray-700">Filtrar por Nivel</label>
                                    <select name="docente_nivel" id="docente_nivel" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-yellow-500 focus:ring focus:ring-yellow-200 sm:text-sm">
                                        <option value="">Todos los niveles</option>
                                        <option value="primaria" {{ request('docente_nivel') == 'primaria' ? 'selected' : '' }}>Primaria</option>
                                        <option value="secundaria" {{ request('docente_nivel') == 'secundaria' ? 'selected' : '' }}>Secundaria</option>
                                    </select>
                                </div>

                                <div class="w-full">
                                    <label for="curso_id" class="block text-sm font-semibold text-gray-700">Filtrar por Curso</label>
                                    <select name="curso_id" id="curso_id" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-yellow-500 focus:ring focus:ring-yellow-200 sm:text-sm">
                                        <option value="">Todos los cursos</option>
                                        @foreach($cursos as $c)
                                            <option value="{{ $c->id }}" {{ request('curso_id') == $c->id ? 'selected' : '' }} data-nivel="{{ $c->nivel }}">
                                                {{ $c->nombre }} ({{ ucfirst($c->nivel) }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="w-full flex items-center h-10 pb-1">
                                    <label class="inline-flex items-center cursor-pointer text-sm font-semibold text-gray-700">
                                        <input type="checkbox" name="solo_tutores" value="1" {{ request('solo_tutores') ? 'checked' : '' }} class="rounded border-gray-300 text-yellow-600 shadow-sm focus:border-yellow-500 focus:ring focus:ring-yellow-200 focus:ring-opacity-50 mr-2 h-4 w-4">
                                        Solo Tutores
                                    </label>
                                </div>
                            @endif
                            
                            @if($tab === 'estudiantes')
                                <div class="w-full col-span-1 md:col-span-2">
                                    <label for="search" class="block text-sm font-semibold text-gray-700">Buscar Estudiante</label>
                                    <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Nombre, Apellido, DNI, Código..." class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-yellow-500 focus:ring focus:ring-yellow-200 sm:text-sm">
                                </div>
                            @endif
                            
                            @if($tab === 'notas')
                                <div class="w-full">
                                    <label for="bimestre_id" class="block text-sm font-semibold text-gray-700">Filtrar por Bimestre</label>
                                    <select name="bimestre_id" id="bimestre_id" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-yellow-500 focus:ring focus:ring-yellow-200 sm:text-sm">
                                        <option value="">Todos los bimestres</option>
                                        @foreach($bimestres as $b)
                                            <option value="{{ $b->id }}" {{ request('bimestre_id') == $b->id ? 'selected' : '' }}>
                                                Bimestre {{ $b->numero }} ({{ $b->nombre }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            @if($tab === 'secciones')
                                <div class="w-full">
                                    <label for="nivel_sec" class="block text-sm font-semibold text-gray-700">Filtrar por Nivel</label>
                                    <select name="nivel" id="nivel_sec" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-yellow-500 focus:ring focus:ring-yellow-200 sm:text-sm">
                                        <option value="">Todos los niveles</option>
                                        <option value="primaria" {{ request('nivel') == 'primaria' ? 'selected' : '' }}>Primaria</option>
                                        <option value="secundaria" {{ request('nivel') == 'secundaria' ? 'selected' : '' }}>Secundaria</option>
                                    </select>
                                </div>
                                <div class="w-full">
                                    <label for="grado_id_sec" class="block text-sm font-semibold text-gray-700">Filtrar por Grado</label>
                                    <select name="grado_id" id="grado_id_sec" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-yellow-500 focus:ring focus:ring-yellow-200 sm:text-sm">
                                        <option value="">Todos los grados</option>
                                        @foreach($gradoSecciones->unique('grado_id') as $gs)
                                            <option value="{{ $gs->grado_id }}" {{ request('grado_id') == $gs->grado_id ? 'selected' : '' }} data-nivel="{{ $gs->grado->nivel }}">
                                                {{ $gs->grado->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="w-full">
                                    <label for="seccion_id_sec" class="block text-sm font-semibold text-gray-700">Filtrar por Sección</label>
                                    <select name="seccion_id" id="seccion_id_sec" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-yellow-500 focus:ring focus:ring-yellow-200 sm:text-sm">
                                        <option value="">Todas las secciones</option>
                                        @foreach($gradoSecciones->unique('seccion_id') as $gs)
                                            <option value="{{ $gs->seccion_id }}" {{ request('seccion_id') == $gs->seccion_id ? 'selected' : '' }} data-grado="{{ $gs->grado_id }}">
                                                {{ $gs->seccion->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            <div class="w-full grid grid-cols-2 gap-2 {{ $tab === 'deudas' ? 'md:col-start-4' : '' }}">
                                <button type="submit" class="bg-yellow-600 hover:bg-yellow-700 text-white font-bold py-2 px-2 rounded-md text-xs sm:text-sm transition-colors text-center">Filtrar</button>
                                <a href="{{ route('admin.rendimiento.index', ['tab' => $tab]) }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-2 px-2 rounded-md text-xs sm:text-sm text-center transition-colors border border-gray-300">Limpiar</a>
                                @if($tab === 'notas')
                                    <button type="button" x-data @click="$dispatch('open-modal', 'modal-consolidado')" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-2 rounded-md text-xs sm:text-sm text-center transition-colors" title="Exportar reporte consolidado por nivel">
                                        Consolidado
                                    </button>
                                    <button type="button" x-data @click="$dispatch('open-modal', 'modal-criticos')" class="bg-rose-600 hover:bg-rose-700 text-white font-bold py-2 px-2 rounded-md text-xs sm:text-sm text-center transition-colors" title="Exportar reporte de estudiantes según nivel de logro (AD, A, B, C)">
                                        Generar por Notas
                                    </button>
                                @endif
                                @if($tab === 'deudas')
                                    <button type="button" x-data @click="$dispatch('open-modal', 'modal-deudas')" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-2 rounded-md text-xs sm:text-sm text-center transition-colors col-span-2" title="Exportar reporte de estudiantes según estado de pago">
                                        Generar por Estado
                                    </button>
                                @endif
                                @if($tab === 'docentes')
                                    <button type="submit" formaction="{{ route('admin.rendimiento.exportar_docentes') }}" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-2 rounded-md text-xs sm:text-sm text-center transition-colors col-span-2">
                                        Generar Excel
                                    </button>
                                @endif
                                @if($tab === 'secciones')
                                    <button type="submit" formaction="{{ route('admin.rendimiento.exportar_secciones') }}" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-2 rounded-md text-xs sm:text-sm text-center transition-colors col-span-2">
                                        Generar Excel
                                    </button>
                                @endif
                            </div>
                        </form>

                        @if($tab === 'notas')
                            <x-modal name="modal-criticos" focusable>
                                <form method="GET" action="{{ route('admin.rendimiento.exportar_criticos') }}" class="p-6" x-data="{ todosCursos: true }">
                                    <h2 class="text-lg font-medium text-gray-900">
                                        {{ __('Reporte de Estudiantes por Notas') }}
                                    </h2>
                                    <p class="mt-1 text-sm text-gray-600 mb-4">
                                        {{ __('Selecciona los cursos y el nivel de logro que deseas incluir en el reporte.') }}
                                    </p>

                                    <div class="mt-4">
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Seleccionar Notas a Exportar</label>
                                        <div class="flex gap-4">
                                            <label class="inline-flex items-center cursor-pointer">
                                                <input type="checkbox" name="notas_seleccionadas[]" value="AD" class="rounded border-gray-300 text-yellow-600 shadow-sm focus:border-yellow-500 focus:ring focus:ring-yellow-200">
                                                <span class="ml-2 text-sm text-gray-700">AD</span>
                                            </label>
                                            <label class="inline-flex items-center cursor-pointer">
                                                <input type="checkbox" name="notas_seleccionadas[]" value="A" class="rounded border-gray-300 text-yellow-600 shadow-sm focus:border-yellow-500 focus:ring focus:ring-yellow-200">
                                                <span class="ml-2 text-sm text-gray-700">A</span>
                                            </label>
                                            <label class="inline-flex items-center cursor-pointer">
                                                <input type="checkbox" name="notas_seleccionadas[]" value="B" class="rounded border-gray-300 text-yellow-600 shadow-sm focus:border-yellow-500 focus:ring focus:ring-yellow-200">
                                                <span class="ml-2 text-sm text-gray-700">B</span>
                                            </label>
                                            <label class="inline-flex items-center cursor-pointer">
                                                <input type="checkbox" name="notas_seleccionadas[]" value="C" class="rounded border-gray-300 text-yellow-600 shadow-sm focus:border-yellow-500 focus:ring focus:ring-yellow-200" checked>
                                                <span class="ml-2 text-sm text-gray-700">C</span>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="mt-4">
                                        <label for="bimestre_criticos" class="block text-sm font-semibold text-gray-700">Filtrar por Bimestre (Opcional)</label>
                                        <select name="bimestre_id" id="bimestre_criticos" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-yellow-500 focus:ring focus:ring-yellow-200 sm:text-sm">
                                            <option value="">Todos los Bimestres</option>
                                            @foreach($bimestres as $b)
                                                <option value="{{ $b->id }}">Bimestre {{ $b->numero }} ({{ $b->nombre }})</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="mt-4">
                                        <label for="nivel_criticos" class="block text-sm font-semibold text-gray-700">Filtrar por Nivel (Opcional)</label>
                                        <select name="nivel" id="nivel_criticos" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-yellow-500 focus:ring focus:ring-yellow-200 sm:text-sm">
                                            <option value="">Ambos Niveles</option>
                                            <option value="primaria">Primaria</option>
                                            <option value="secundaria">Secundaria</option>
                                        </select>
                                    </div>

                                    <div class="mt-4">
                                        <label class="inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="todos_cursos" value="1" x-model="todosCursos" class="rounded border-gray-300 text-yellow-600 shadow-sm focus:border-yellow-500 focus:ring focus:ring-yellow-200">
                                            <span class="ml-2 text-sm font-semibold text-gray-700">Seleccionar todos los cursos</span>
                                        </label>
                                    </div>

                                    <div class="mt-4">
                                        <label class="inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="dividir_secciones" value="1" class="rounded border-gray-300 text-yellow-600 shadow-sm focus:border-yellow-500 focus:ring focus:ring-yellow-200">
                                            <span class="ml-2 text-sm font-semibold text-gray-700">Dividir en grados y secciones</span>
                                        </label>
                                    </div>

                                    <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-60 overflow-y-auto p-2 border border-gray-100 rounded bg-gray-50" x-show="!todosCursos" style="display: none;">
                                        @foreach($cursos as $curso)
                                            <label class="inline-flex items-center cursor-pointer">
                                                <input type="checkbox" name="cursos_seleccionados[]" value="{{ $curso->id }}" class="rounded border-gray-300 text-yellow-600 shadow-sm focus:border-yellow-500 focus:ring focus:ring-yellow-200" :disabled="todosCursos">
                                                <span class="ml-2 text-sm text-gray-600">{{ $curso->nombre }} ({{ ucfirst($curso->nivel) }})</span>
                                            </label>
                                        @endforeach
                                    </div>

                                    <div class="mt-6 flex justify-end">
                                        <button type="button" x-on:click="$dispatch('close')" class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 font-bold py-2 px-4 rounded-lg text-sm transition-colors mr-2">
                                            {{ __('Cancelar') }}
                                        </button>
                                        <button type="submit" class="bg-rose-600 hover:bg-rose-700 text-white font-bold py-2 px-4 rounded-lg text-sm transition-colors" x-on:click="$dispatch('close')">
                                            {{ __('Generar Reporte Excel') }}
                                        </button>
                                    </div>
                                </form>
                            </x-modal>

                            <x-modal name="modal-consolidado" focusable>
                                <form method="GET" action="{{ route('admin.rendimiento.exportar_consolidado') }}" class="p-6">
                                    <h2 class="text-lg font-medium text-gray-900">
                                        {{ __('Generar Reporte Consolidado') }}
                                    </h2>
                                    <p class="mt-1 text-sm text-gray-600 mb-4">
                                        {{ __('Selecciona el nivel educativo para generar el reporte consolidado por curso y grado.') }}
                                    </p>

                                    <div class="mt-4">
                                        <label for="nivel_consolidado" class="block text-sm font-semibold text-gray-700">Nivel Educativo</label>
                                        <select name="nivel" id="nivel_consolidado" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-yellow-500 focus:ring focus:ring-yellow-200 sm:text-sm" required>
                                            <option value="" disabled selected>Selecciona un nivel</option>
                                            <option value="primaria">Primaria</option>
                                            <option value="secundaria">Secundaria</option>
                                        </select>
                                    </div>

                                    <div class="mt-4">
                                        <label for="bimestre_consolidado" class="block text-sm font-semibold text-gray-700">Bimestre</label>
                                        <select name="bimestre_id" id="bimestre_consolidado" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-yellow-500 focus:ring focus:ring-yellow-200 sm:text-sm" required>
                                            <option value="" disabled selected>Selecciona un bimestre</option>
                                            @foreach($bimestres as $b)
                                                <option value="{{ $b->id }}">Bimestre {{ $b->numero }} ({{ $b->nombre }})</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="mt-4">
                                        <label class="inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="generar_grafico" value="1" class="rounded border-gray-300 text-emerald-600 shadow-sm focus:border-emerald-500 focus:ring focus:ring-emerald-200">
                                            <span class="ml-2 text-sm font-semibold text-gray-700">Generar archivo para gráfico</span>
                                        </label>
                                    </div>

                                    <div class="mt-4">
                                        <label class="inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="estudiantes_faltan_notas" value="1" class="rounded border-gray-300 text-rose-600 shadow-sm focus:border-rose-500 focus:ring focus:ring-rose-200">
                                            <span class="ml-2 text-sm font-semibold text-gray-700">Solo estudiantes que faltan notas</span>
                                        </label>
                                    </div>

                                    <div class="mt-6 flex justify-end">
                                        <button type="button" x-on:click="$dispatch('close')" class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 font-bold py-2 px-4 rounded-lg text-sm transition-colors mr-2">
                                            {{ __('Cancelar') }}
                                        </button>
                                        <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-4 rounded-lg text-sm transition-colors" x-on:click="$dispatch('close')">
                                            {{ __('Generar Reporte') }}
                                        </button>
                                    </div>
                                </form>
                            </x-modal>
                        @endif

                        @if($tab === 'deudas')
                            <x-modal name="modal-deudas" focusable>
                                <form method="GET" action="{{ route('admin.rendimiento.exportar_deudas') }}" class="p-6">
                                    <h2 class="text-lg font-medium text-gray-900">
                                        {{ __('Reporte de Mensualidades') }}
                                    </h2>
                                    <p class="mt-1 text-sm text-gray-600 mb-4">
                                        {{ __('Selecciona los filtros para generar el reporte de estudiantes y sus estados de pago.') }}
                                    </p>

                                    <div class="mt-4">
                                        <label for="nivel_deudas" class="block text-sm font-semibold text-gray-700">Filtrar por Nivel</label>
                                        <select name="nivel" id="nivel_deudas" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring focus:ring-red-200 sm:text-sm">
                                            <option value="">Ambos Niveles</option>
                                            <option value="primaria">Primaria</option>
                                            <option value="secundaria">Secundaria</option>
                                        </select>
                                    </div>

                                    <div class="mt-4">
                                        <label for="grado_seccion_deudas" class="block text-sm font-semibold text-gray-700">Filtrar por Grado/Sección (Opcional)</label>
                                        <select name="grado_seccion_id" id="grado_seccion_deudas" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring focus:ring-red-200 sm:text-sm">
                                            <option value="">Todas las secciones</option>
                                            @foreach($gsPorNivel as $nivelGroup => $sections)
                                                <optgroup label="{{ ucfirst($nivelGroup) }}">
                                                    @foreach($sections as $gs)
                                                        <option value="{{ $gs->id }}">{{ $gs->grado->nombre }} - {{ $gs->seccion->nombre }}</option>
                                                    @endforeach
                                                </optgroup>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="mt-4">
                                        <label for="mes_deudas" class="block text-sm font-semibold text-gray-700">Filtrar por Mes (Opcional)</label>
                                        <select name="mes" id="mes_deudas" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring focus:ring-red-200 sm:text-sm">
                                            <option value="">Todos los meses</option>
                                            @foreach(\App\Models\Mensualidad::meses() as $num => $nombre)
                                                <option value="{{ $num }}">{{ $nombre }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="mt-4">
                                        <label for="estado_pago" class="block text-sm font-semibold text-gray-700">Estado de Pago</label>
                                        <select name="estado_pago" id="estado_pago" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring focus:ring-red-200 sm:text-sm">
                                            <option value="AMBOS">Todos los estados</option>
                                            <option value="DEUDORES">Solo Deudores (DEBE)</option>
                                            <option value="PAGARON">Solo Pagaron (PAGÓ)</option>
                                        </select>
                                    </div>

                                    <div class="mt-4">
                                        <label class="inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="dividir_secciones" value="1" class="rounded border-gray-300 text-red-600 shadow-sm focus:border-red-500 focus:ring focus:ring-red-200">
                                            <span class="ml-2 text-sm font-semibold text-gray-700">Dividir en grados y secciones (Hojas separadas)</span>
                                        </label>
                                    </div>

                                    <div class="mt-6 flex justify-end">
                                        <button type="button" x-on:click="$dispatch('close')" class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 font-bold py-2 px-4 rounded-lg text-sm transition-colors mr-2">
                                            {{ __('Cancelar') }}
                                        </button>
                                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg text-sm transition-colors" x-on:click="$dispatch('close')">
                                            {{ __('Generar Reporte Excel') }}
                                        </button>
                                    </div>
                                </form>
                            </x-modal>
                        @endif


                        @if(in_array($tab, ['notas', 'deudas']))
                            <script>
                            (function () {
                                function initFilterSections() {
                                    const nivelSelect = document.getElementById('nivel');
                                    const gsSelect = document.getElementById('grado_seccion_id');
                                    if (nivelSelect && gsSelect) {
                                        const originalOptions = Array.from(gsSelect.querySelectorAll('option')).filter(o => o.value !== '');
                                        
                                        function filterSections() {
                                            const nivel = nivelSelect.value;
                                            const currentVal = gsSelect.value;
                                            
                                            gsSelect.querySelectorAll('optgroup').forEach(el => el.remove());
                                            
                                            const groups = {};
                                            originalOptions.forEach(opt => {
                                                const optNivel = opt.getAttribute('data-nivel');
                                                if (!nivel || optNivel === nivel) {
                                                    if (!groups[optNivel]) {
                                                        groups[optNivel] = [];
                                                    }
                                                    groups[optNivel].push(opt);
                                                }
                                            });
                                            
                                            Object.keys(groups).forEach(grpNivel => {
                                                const optGroup = document.createElement('optgroup');
                                                optGroup.label = grpNivel.charAt(0).toUpperCase() + grpNivel.slice(1);
                                                groups[grpNivel].forEach(opt => {
                                                    optGroup.appendChild(opt.cloneNode(true));
                                                });
                                                gsSelect.appendChild(optGroup);
                                            });
                                            
                                            const availableVal = Array.from(gsSelect.options).some(o => o.value === currentVal);
                                            if (availableVal) {
                                                gsSelect.value = currentVal;
                                            } else {
                                                gsSelect.value = '';
                                            }
                                        }
                                        
                                        nivelSelect.addEventListener('change', filterSections);
                                        if (nivelSelect.value) {
                                            filterSections();
                                        }
                                    }
                                }
                                if (document.readyState === 'loading') {
                                    document.addEventListener('DOMContentLoaded', initFilterSections);
                                } else {
                                    initFilterSections();
                                }
                            })();
                            </script>
                        @endif

                        @if($tab === 'docentes')
                            <script>
                            (function () {
                                function initFilterCursos() {
                                    const nivelSelect = document.getElementById('docente_nivel');
                                    const cursoSelect = document.getElementById('curso_id');
                                    if (nivelSelect && cursoSelect) {
                                        const originalOptions = Array.from(cursoSelect.querySelectorAll('option')).filter(o => o.value !== '');
                                        
                                        function filterCursos() {
                                            const nivel = nivelSelect.value;
                                            const currentVal = cursoSelect.value;
                                            
                                            // Remove all options except the empty one ("Todos los cursos")
                                            cursoSelect.querySelectorAll('option').forEach(opt => {
                                                if (opt.value !== '') opt.remove();
                                            });
                                            
                                            // Append matching options
                                            originalOptions.forEach(opt => {
                                                const optNivel = opt.getAttribute('data-nivel');
                                                // Show if matching selected level OR if course is for "ambos" OR if no level is selected
                                                if (!nivel || optNivel === 'ambos' || optNivel === nivel) {
                                                    cursoSelect.appendChild(opt.cloneNode(true));
                                                }
                                            });
                                            
                                            // Restore selected value if still available
                                            const availableVal = Array.from(cursoSelect.options).some(o => o.value === currentVal);
                                            if (availableVal) {
                                                cursoSelect.value = currentVal;
                                            } else {
                                                cursoSelect.value = '';
                                            }
                                        }
                                        
                                        nivelSelect.addEventListener('change', filterCursos);
                                        if (nivelSelect.value) {
                                            filterCursos();
                                        }
                                    }
                                }
                                if (document.readyState === 'loading') {
                                    document.addEventListener('DOMContentLoaded', initFilterCursos);
                                } else {
                                    initFilterCursos();
                                }
                            })();
                            </script>
                        @endif

                        @if($tab === 'secciones')
                            <script>
                            (function () {
                                function initFilterSecciones() {
                                    const nivelSelect = document.getElementById('nivel_sec');
                                    const gradoSelect = document.getElementById('grado_id_sec');
                                    const seccionSelect = document.getElementById('seccion_id_sec');
                                    
                                    if (nivelSelect && gradoSelect && seccionSelect) {
                                        const originalGrados = Array.from(gradoSelect.querySelectorAll('option')).filter(o => o.value !== '');
                                        const originalSecciones = Array.from(seccionSelect.querySelectorAll('option')).filter(o => o.value !== '');
                                        
                                        function filterGrados() {
                                            const nivel = nivelSelect.value;
                                            const currentGrado = gradoSelect.value;
                                            
                                            gradoSelect.querySelectorAll('option').forEach(opt => {
                                                if (opt.value !== '') opt.remove();
                                            });
                                            
                                            originalGrados.forEach(opt => {
                                                const optNivel = opt.getAttribute('data-nivel');
                                                if (!nivel || optNivel === nivel) {
                                                    gradoSelect.appendChild(opt.cloneNode(true));
                                                }
                                            });
                                            
                                            const availableVal = Array.from(gradoSelect.options).some(o => o.value === currentGrado);
                                            gradoSelect.value = availableVal ? currentGrado : '';
                                            filterSecciones();
                                        }

                                        function filterSecciones() {
                                            const gradoId = gradoSelect.value;
                                            const currentSeccion = seccionSelect.value;
                                            
                                            seccionSelect.querySelectorAll('option').forEach(opt => {
                                                if (opt.value !== '') opt.remove();
                                            });
                                            
                                            originalSecciones.forEach(opt => {
                                                const optGrado = opt.getAttribute('data-grado');
                                                if (!gradoId || optGrado === gradoId) {
                                                    seccionSelect.appendChild(opt.cloneNode(true));
                                                }
                                            });
                                            
                                            const availableVal = Array.from(seccionSelect.options).some(o => o.value === currentSeccion);
                                            seccionSelect.value = availableVal ? currentSeccion : '';
                                        }
                                        
                                        nivelSelect.addEventListener('change', filterGrados);
                                        gradoSelect.addEventListener('change', filterSecciones);
                                        
                                        if (nivelSelect.value) filterGrados();
                                        if (gradoSelect.value) filterSecciones();
                                    }
                                }
                                if (document.readyState === 'loading') {
                                    document.addEventListener('DOMContentLoaded', initFilterSecciones);
                                } else {
                                    initFilterSecciones();
                                }
                            })();
                            </script>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Contenedor Principal de Reporte -->
            <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-100">
                <div class="p-6">
                    @if(!$anoActivo)
                        <div class="text-center py-8 text-red-500 font-semibold text-lg">No hay un año lectivo activo configurado.</div>
                    @else

                                                @if($tab === 'notas')
                            @include('admin.rendimiento.partials.tab_notas')
                        @endif
                        @if($tab === 'deudas')
                            @include('admin.rendimiento.partials.tab_deudas')
                        @endif
                        @if($tab === 'docentes')
                            @include('admin.rendimiento.partials.tab_docentes')
                        @endif
                        @if($tab === 'secciones')
                            @include('admin.rendimiento.partials.tab_secciones')
                        @endif
                        @if($tab === 'estudiantes')
                            @include('admin.rendimiento.partials.tab_estudiantes')
                        @endif

                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
