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

                        <!-- ==================== TAB: NOTAS ==================== -->
                        @if($tab === 'notas')
                            @if($resumen->isEmpty())
                                <div class="text-center py-12 text-gray-500">
                                    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                    <p class="text-lg font-medium text-gray-600">No hay datos de rendimiento disponibles aún.</p>
                                    <p class="text-sm text-gray-400 mt-1">Sube notas bimestrales desde el panel de importación.</p>
                                </div>
                            @else
                                <div class="flex items-center justify-between border-b pb-4 mb-6">
                                    <h3 class="text-lg font-bold text-gray-800">Calificaciones Consolidadas por Curso y Competencia</h3>
                                    <span class="bg-yellow-100 text-yellow-800 text-xs font-semibold px-2.5 py-0.5 rounded">Año Lectivo: {{ $anoActivo->anio }}</span>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                                    @php
                                        $totalAD = 0;
                                        $totalA = 0;
                                        $totalB = 0;
                                        $totalC = 0;
                                        foreach ($resumen as $row) {
                                            if ($row->promedio_letra == 'AD') $totalAD += $row->cantidad;
                                            if ($row->promedio_letra == 'A') $totalA += $row->cantidad;
                                            if ($row->promedio_letra == 'B') $totalB += $row->cantidad;
                                            if ($row->promedio_letra == 'C') $totalC += $row->cantidad;
                                        }
                                    @endphp
                                    
                                    <div class="bg-indigo-50/60 border border-indigo-100 rounded-xl p-5 text-center shadow-sm">
                                        <div class="text-3xl font-extrabold text-indigo-700">{{ $totalAD }}</div>
                                        <div class="text-sm font-semibold text-indigo-600 mt-1">Calificaciones AD</div>
                                    </div>
                                    <div class="bg-emerald-50/60 border border-emerald-100 rounded-xl p-5 text-center shadow-sm">
                                        <div class="text-3xl font-extrabold text-emerald-700">{{ $totalA }}</div>
                                        <div class="text-sm font-semibold text-emerald-600 mt-1">Calificaciones A</div>
                                    </div>
                                    <div class="bg-amber-50/60 border border-amber-100 rounded-xl p-5 text-center shadow-sm">
                                        <div class="text-3xl font-extrabold text-amber-700">{{ $totalB }}</div>
                                        <div class="text-sm font-semibold text-amber-600 mt-1">Calificaciones B</div>
                                    </div>
                                    <div class="bg-rose-50/60 border border-rose-100 rounded-xl p-5 text-center shadow-sm">
                                        <div class="text-3xl font-extrabold text-rose-700">{{ $totalC }}</div>
                                        <div class="text-sm font-semibold text-rose-600 mt-1">Calificaciones C</div>
                                    </div>
                                </div>

                                <div class="space-y-8">
                                    @php
                                        $agrupadoPorCurso = $resumen->groupBy('curso_nombre');
                                    @endphp

                                    @foreach($agrupadoPorCurso as $cursoNombre => $competencias)
                                        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                                            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                                                <div class="flex items-center gap-3">
                                                    <div class="p-2 bg-yellow-100 rounded-lg text-yellow-800">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                                        </svg>
                                                    </div>
                                                    <h4 class="text-lg font-bold text-gray-800">{{ $cursoNombre }}</h4>
                                                </div>
                                                <span class="bg-yellow-100 text-yellow-800 text-xs font-bold px-3 py-1 rounded-full">
                                                    {{ $competencias->sum('cantidad') }} Calificaciones
                                                </span>
                                            </div>

                                            <div class="p-6">
                                                <div class="overflow-x-auto">
                                                    <table class="min-w-full divide-y divide-gray-200">
                                                        <thead class="bg-gray-50">
                                                            <tr>
                                                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-1/2">Competencia</th>
                                                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Notas AD</th>
                                                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Notas A</th>
                                                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Notas B</th>
                                                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Notas C</th>
                                                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Total</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="bg-white divide-y divide-gray-200">
                                                            @php
                                                                $agrupadoPorCompetencia = $competencias->groupBy('competencia_nombre');
                                                            @endphp
                                                            @foreach($agrupadoPorCompetencia as $compNombre => $rows)
                                                                @php
                                                                    $cantAD = $rows->where('promedio_letra', 'AD')->sum('cantidad');
                                                                    $cantA = $rows->where('promedio_letra', 'A')->sum('cantidad');
                                                                    $cantB = $rows->where('promedio_letra', 'B')->sum('cantidad');
                                                                    $cantC = $rows->where('promedio_letra', 'C')->sum('cantidad');
                                                                    $totalComp = $rows->sum('cantidad');
                                                                @endphp
                                                                <tr class="hover:bg-gray-50 transition-colors">
                                                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                                                        {{ $compNombre }}
                                                                    </td>
                                                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-bold text-indigo-700 bg-indigo-50/20">
                                                                        {{ $cantAD }}
                                                                    </td>
                                                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-bold text-emerald-700 bg-emerald-50/20">
                                                                        {{ $cantA }}
                                                                    </td>
                                                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-bold text-amber-700 bg-amber-50/20">
                                                                        {{ $cantB }}
                                                                    </td>
                                                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-bold text-rose-700 bg-rose-50/20">
                                                                        {{ $cantC }}
                                                                    </td>
                                                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-semibold text-gray-800 bg-gray-50/40">
                                                                        {{ $totalComp }}
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        @endif


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


                        <!-- ==================== TAB: GRADOS / SECCIONES ==================== -->
                        @if($tab === 'secciones')
                            <div x-data="{
                                showVerModal: false,
                                verLoading: false,
                                verData: { grado_seccion_id: null, seccion_nombre: '', anio: '', nivel: '', tutor: null, cotutor: null, estudiantes: [] },
                                async abrirVer(gsId) {
                                    this.verLoading = true;
                                    this.showVerModal = true;
                                    this.verData = { grado_seccion_id: null, seccion_nombre: '', anio: '', nivel: '', tutor: null, cotutor: null, estudiantes: [] };
                                    try {
                                        const res = await fetch('/admin/grado-secciones/' + gsId + '/detalle', {
                                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                                        });
                                        this.verData = await res.json();
                                    } catch(e) {
                                        console.error(e);
                                    } finally {
                                        this.verLoading = false;
                                    }
                                }
                            }" class="space-y-6">
                                <div class="flex items-center justify-between border-b pb-4 mb-6">
                                    <h3 class="text-lg font-bold text-gray-800">Carga de Alumnos por Sección y Tutoría</h3>
                                    <span class="bg-purple-100 text-purple-800 text-xs font-semibold px-2.5 py-0.5 rounded">Secciones: {{ $seccionesReport->count() }}</span>
                                </div>

                                @if($seccionesReport->isEmpty())
                                    <div class="text-center py-12 text-gray-500">
                                        <p class="text-lg font-medium">No hay secciones registradas para el año lectivo activo.</p>
                                    </div>
                                @else
                                    @php
                                        $seccionesPorNivel = $seccionesReport->groupBy(fn($s) => $s->grado->nivel ?? 'primaria');
                                    @endphp

                                    @foreach(['primaria' => 'Primaria', 'secundaria' => 'Secundaria'] as $nivelKey => $nivelLabel)
                                        @if($seccionesPorNivel->has($nivelKey))
                                            @php
                                                $isPrimaria = $nivelKey === 'primaria';
                                                $bannerBg        = $isPrimaria ? '#fefce8' : '#f0f9ff';
                                                $bannerBorder    = $isPrimaria ? '#fde047' : '#7dd3fc';
                                                $bannerBorderAll = $isPrimaria ? '#fef08a' : '#bae6fd';
                                                $badgeBg         = $isPrimaria ? '#facc15' : '#38bdf8';
                                                $badgeText       = $isPrimaria ? '#713f12' : '#0c4a6e';
                                                $titleColor      = $isPrimaria ? '#854d0e' : '#075985';
                                                $lineColor       = $isPrimaria ? '#fde68a' : '#bae6fd';

                                                $seccionesOrdenadas = $seccionesPorNivel[$nivelKey]->sortBy(function($a) {
                                                    $orden = str_pad($a->grado->orden ?? 0, 5, '0', STR_PAD_LEFT);
                                                    $seccionNombre = strtolower($a->seccion->nombre ?? '');
                                                    return $orden . '-' . $seccionNombre;
                                                });
                                            @endphp

                                            {{-- Separador visible entre Primaria y Secundaria (solo para secundaria) --}}
                                            @if(!$isPrimaria && $seccionesPorNivel->has('primaria'))
                                                <div class="my-8 flex items-center gap-4">
                                                    <div class="flex-1 h-0.5 bg-gradient-to-r from-gray-200 to-transparent"></div>
                                                    <span class="text-xs font-bold tracking-wider text-gray-400 uppercase">─── Nivel Secundaria ───</span>
                                                    <div class="flex-1 h-0.5 bg-gradient-to-l from-gray-200 to-transparent"></div>
                                                </div>
                                            @endif

                                            {{-- Banner de nivel --}}
                                            <div class="mb-6 flex items-center gap-4 p-3 rounded-xl" style="background:{{ $bannerBg }}; border:1px solid {{ $bannerBorderAll }}; border-left:4px solid {{ $bannerBorder }};">
                                                <span class="text-xs font-extrabold uppercase tracking-wider px-3 py-1 rounded-full" style="background:{{ $badgeBg }}; color:{{ $badgeText }};">
                                                    Nivel {{ $nivelLabel }}
                                                </span>
                                                <span class="text-sm font-bold text-gray-700" style="color:{{ $titleColor }};">
                                                    {{ $seccionesOrdenadas->count() }} {{ $seccionesOrdenadas->count() === 1 ? 'sección' : 'secciones' }}
                                                </span>
                                                <div class="flex-1 h-px" style="background:{{ $lineColor }};"></div>
                                            </div>

                                            {{-- Grid de Tarjetas --}}
                                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">
                                                @foreach($seccionesOrdenadas as $seccion)
                                                    <div class="bg-white border border-gray-150 rounded-xl p-5 shadow-sm hover:shadow transition-shadow flex flex-col justify-between">
                                                        <div>
                                                            <div class="flex justify-between items-start mb-3">
                                                                <h4 class="text-lg font-extrabold text-gray-800">
                                                                    {{ $seccion->grado->nombre ?? '—' }} - {{ $seccion->seccion->nombre ?? '—' }}
                                                                </h4>
                                                                <span class="bg-purple-100 text-purple-800 font-bold text-sm px-2.5 py-1 rounded-lg">
                                                                    {{ $seccion->matriculas_count }} Alumnos
                                                                </span>
                                                            </div>

                                                            <div class="space-y-2 mt-4 text-sm text-gray-600">
                                                                <div class="flex items-center gap-2">
                                                                    <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                                    </svg>
                                                                    <span>
                                                                        <strong class="text-gray-700">Tutor:</strong> 
                                                                        {{ $seccion->tutor ? ($seccion->tutor->apellido_paterno . ' ' . $seccion->tutor->apellido_materno . ', ' . $seccion->tutor->nombres) : 'No asignado' }}
                                                                    </span>
                                                                </div>
                                                                <div class="flex items-center gap-2">
                                                                    <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                                                    </svg>
                                                                    <span>
                                                                        <strong class="text-gray-700">Cotutor:</strong> 
                                                                        {{ $seccion->cotutor ? ($seccion->cotutor->apellido_paterno . ' ' . $seccion->cotutor->apellido_materno . ', ' . $seccion->cotutor->nombres) : 'No asignado' }}
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="mt-4 pt-3 flex flex-col gap-3">
                                                            <button @click="abrirVer({{ $seccion->id }})" 
                                                                    class="w-full text-white font-bold py-2 px-4 rounded-lg text-center text-sm transition shadow-sm flex items-center justify-center gap-2"
                                                                    style="background-color: #6d28d9; border: 1px solid #5b21b6; box-shadow: 0 4px 6px -1px rgba(109,40,217,0.3);"
                                                                    onmouseover="this.style.backgroundColor='#5b21b6'"
                                                                    onmouseout="this.style.backgroundColor='#6d28d9'">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                                </svg>
                                                                Ver Alumnos / Tutor
                                                            </button>

                                                            <div class="border-t pt-3 flex justify-between items-center text-xs text-gray-400">
                                                                <span>Año: {{ $anoActivo->anio }}</span>
                                                                <span class="flex items-center gap-1">
                                                                    <span class="w-2 h-2 rounded-full {{ $seccion->activo ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
                                                                    {{ $seccion->activo ? 'Activo' : 'Inactivo' }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    @endforeach
                                @endif

                                <!-- Modal: Ver Detalle de Sección (Tutor + Estudiantes) -->
                                <div x-show="showVerModal"
                                     class="fixed inset-0 z-50"
                                     aria-labelledby="modal-ver" role="dialog" aria-modal="true" x-cloak>

                                    <div class="absolute inset-0 bg-gray-900 bg-opacity-50 transition-opacity" @click="showVerModal = false"></div>

                                    <div class="relative z-10 flex min-h-full items-center justify-center p-4 pointer-events-none">

                                        <div class="pointer-events-auto w-full max-w-3xl bg-white rounded-2xl shadow-2xl flex flex-col transition-all transform"
                                             style="max-height: 85vh; border: 2px solid #7c3aed; box-shadow: 0 0 0 4px rgba(124,58,237,0.15), 0 25px 50px -12px rgba(0,0,0,0.4);"
                                             @click.stop>

                                            <div class="flex items-center justify-between px-5 py-3 rounded-t-2xl shrink-0"
                                                 :style="verData.nivel === 'primaria'
                                                     ? 'background: linear-gradient(to right, #ca8a04, #eab308);'
                                                     : 'background: linear-gradient(to right, #7c3aed, #8b5cf6);'">
                                                <div>
                                                    <h3 class="text-base font-extrabold text-white" id="modal-ver">Reporte de Sección</h3>
                                                    <div class="mt-1 flex items-center gap-2 flex-wrap">
                                                        <div class="inline-block px-3 py-1 rounded-lg text-white text-xs font-bold shadow-sm"
                                                             style="background-color: rgba(255,255,255,0.25); border: 1px solid rgba(255,255,255,0.4);">
                                                            <span x-text="verData.seccion_nombre"></span>
                                                        </div>
                                                        <div class="inline-block px-3 py-1 rounded-lg text-white text-xs font-bold"
                                                             style="background-color: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.3);">
                                                            Año <span x-text="verData.anio"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <button @click="showVerModal = false"
                                                        class="ml-4 shrink-0 rounded-lg p-1.5 transition"
                                                        style="background-color: rgba(255,255,255,0.2); color: white;"
                                                        onmouseover="this.style.backgroundColor='rgba(255,255,255,0.35)'"
                                                        onmouseout="this.style.backgroundColor='rgba(255,255,255,0.2)'"
                                                        title="Cerrar">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </div>

                                            <div x-show="verLoading" class="flex items-center justify-center py-10 shrink-0">
                                                <svg class="animate-spin h-6 w-6 text-violet-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                                                </svg>
                                                <span class="ml-2 text-gray-500 text-sm">Cargando datos...</span>
                                            </div>

                                            <div x-show="!verLoading" class="flex-1 overflow-y-auto min-h-0 p-5">

                                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-5">
                                                    <div class="rounded-xl border border-blue-200 bg-blue-50 p-3">
                                                        <p class="text-xs font-extrabold text-blue-700 uppercase tracking-wide mb-1.5">Tutor Principal</p>
                                                        <template x-if="verData.tutor">
                                                            <div>
                                                                <p class="text-sm font-bold text-gray-800" x-text="verData.tutor.nombre_completo"></p>
                                                                <p class="text-xs text-blue-600 mt-0.5" x-show="verData.tutor && verData.tutor.dni" x-text="'DNI: ' + (verData.tutor ? verData.tutor.dni : '')"></p>
                                                            </div>
                                                        </template>
                                                        <template x-if="!verData.tutor">
                                                            <p class="text-sm text-gray-400 italic">— Sin tutor asignado —</p>
                                                        </template>
                                                    </div>
                                                    <div class="rounded-xl border border-indigo-200 bg-indigo-50 p-3">
                                                        <p class="text-xs font-extrabold text-indigo-700 uppercase tracking-wide mb-1.5">Co-tutor</p>
                                                        <template x-if="verData.cotutor">
                                                            <div>
                                                                <p class="text-sm font-bold text-gray-800" x-text="verData.cotutor.nombre_completo"></p>
                                                                <p class="text-xs text-indigo-600 mt-0.5" x-show="verData.cotutor && verData.cotutor.dni" x-text="'DNI: ' + (verData.cotutor ? verData.cotutor.dni : '')"></p>
                                                            </div>
                                                        </template>
                                                        <template x-if="!verData.cotutor">
                                                            <p class="text-sm text-gray-400 italic">— Sin co-tutor asignado —</p>
                                                        </template>
                                                    </div>
                                                </div>

                                                <div class="rounded-xl border border-gray-200 overflow-hidden">
                                                    <div class="flex items-center gap-2 px-3 py-2 bg-violet-50 border-b border-violet-100">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                        </svg>
                                                        <span class="text-xs font-extrabold text-violet-700 uppercase tracking-wide">Lista de Estudiantes</span>
                                                        <span class="ml-auto text-xs font-bold text-violet-600 bg-white px-2 py-0.5 rounded-full border border-violet-200"
                                                              x-text="verData.estudiantes.length + ' alumno(s)'"></span>
                                                    </div>
                                                    <div class="overflow-x-auto">
                                                        <table class="w-full text-xs">
                                                            <thead class="bg-gray-50">
                                                                <tr>
                                                                    <th class="text-left px-3 py-2 font-bold text-gray-600 border-b border-gray-200 w-8">#</th>
                                                                    <th class="text-left px-3 py-2 font-bold text-gray-600 border-b border-gray-200">DNI</th>
                                                                    <th class="text-left px-3 py-2 font-bold text-gray-600 border-b border-gray-200">Cód. Estudiante</th>
                                                                    <th class="text-left px-3 py-2 font-bold text-gray-600 border-b border-gray-200">Apellidos y Nombres</th>
                                                                    <th class="text-left px-3 py-2 font-bold text-gray-600 border-b border-gray-200 whitespace-nowrap">Fec. Nacimiento</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <template x-if="verData.estudiantes.length === 0">
                                                                    <tr>
                                                                        <td colspan="5" class="text-center py-8 text-gray-400 italic">Sin estudiantes matriculados en esta sección.</td>
                                                                    </tr>
                                                                </template>
                                                                <template x-for="(est, idx) in verData.estudiantes" :key="est.id">
                                                                    <tr class="border-b border-gray-100 last:border-0 hover:bg-violet-50 transition-colors">
                                                                        <td class="px-3 py-2 text-gray-400 font-semibold" x-text="idx + 1"></td>
                                                                        <td class="px-3 py-2 font-mono text-gray-600" x-text="est.dni"></td>
                                                                        <td class="px-3 py-2 font-mono text-gray-500" x-text="est.codigo_estudiante || '—'"></td>
                                                                        <td class="px-3 py-2 font-semibold text-gray-800" x-text="est.apellidos + ',  ' + est.nombres"></td>
                                                                        <td class="px-3 py-2 text-gray-500 whitespace-nowrap" x-text="est.fecha_nacimiento"></td>
                                                                    </tr>
                                                                </template>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="px-5 py-3 bg-gray-50 rounded-b-2xl flex justify-between items-center border-t shrink-0">
                                                <a :href="'/admin/grado-secciones/' + verData.grado_seccion_id + '/exportar'"
                                                   class="inline-flex items-center gap-2 text-sm font-bold px-5 py-2 rounded-lg transition shadow-sm cursor-pointer"
                                                   style="background-color: #059669; color: white; border: 1px solid #047857;"
                                                   onmouseover="this.style.backgroundColor='#047857'"
                                                   onmouseout="this.style.backgroundColor='#059669'">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                                    </svg>
                                                    Descargar Excel
                                                </a>
                                                <button @click="showVerModal = false"
                                                        class="inline-flex items-center gap-2 text-sm font-bold px-5 py-2 rounded-lg transition shadow-sm"
                                                        style="background-color: #dc2626; color: white; border: 1px solid #b91c1c;"
                                                        onmouseover="this.style.backgroundColor='#b91c1c'"
                                                        onmouseout="this.style.backgroundColor='#dc2626'">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                    Cerrar
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        @endif

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

                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
