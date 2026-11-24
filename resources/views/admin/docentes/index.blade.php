<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Gestión de Docentes y Asignaciones') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="{
        activeTab: '{{ request('tab', session('tab', 'docentes')) }}',
        showModal: false,
        showDirectivoModal: false,
        nivel: '{{ old('nivel') }}',
        tipo: '{{ old('tipo') }}',
        cursos: {{ $cursos->toJson() }},
        get filteredCursos() {
            if (!this.nivel) return [];
            return this.cursos.filter(c => c.nivel === this.nivel || c.nivel === 'ambos');
        },
        get esEspecialista() { return this.tipo === 'especialista'; }
    }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                <div>
                    <h3 class="text-2xl font-bold text-gray-800">Directorio de Personal</h3>
                    <p class="text-gray-600">Administra a los docentes y al equipo directivo de la institución educativa.</p>
                </div>
                @if(auth()->user()->puedeEditar())
                <div class="flex flex-wrap items-center gap-3">
                    <button x-show="activeTab === 'docentes'" @click="showModal = true" class="bg-yellow-600 hover:bg-yellow-700 text-white font-bold py-3 px-6 rounded shadow transition flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                        Registrar Nuevo Docente
                    </button>
                    <button x-show="activeTab === 'directivos'" @click="showDirectivoModal = true" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded shadow transition flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                        Registrar Directivo
                    </button>
                </div>
                @endif
            </div>

            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative shadow-sm" role="alert">
                    <span class="block sm:inline font-medium">{{ session('success') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative shadow-sm" role="alert">
                    <span class="block sm:inline font-medium">{{ session('error') }}</span>
                </div>
            @endif

            {{-- ─── Pestañas de Navegación ─────────────────────────────── --}}
            <div class="flex border-b border-gray-200 mb-6 bg-white rounded-t-lg shadow-sm px-4 pt-2">
                <button type="button"
                    @click="activeTab = 'docentes'"
                    :class="activeTab === 'docentes' ? 'border-yellow-600 text-yellow-700 font-bold bg-yellow-50/50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium'"
                    class="flex items-center gap-2 py-3 px-6 border-b-2 text-sm transition rounded-t-md">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                    <span>Plana Docente</span>
                    <span class="ml-1 px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-700 font-semibold">{{ $docentes->total() }}</span>
                </button>
                <button type="button"
                    @click="activeTab = 'directivos'"
                    :class="activeTab === 'directivos' ? 'border-indigo-600 text-indigo-700 font-bold bg-indigo-50/50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium'"
                    class="flex items-center gap-2 py-3 px-6 border-b-2 text-sm transition rounded-t-md">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                    <span>Equipo Directivo</span>
                    <span class="ml-1 px-2 py-0.5 text-xs rounded-full bg-indigo-100 text-indigo-800 font-semibold">{{ $directivos->count() }}</span>
                </button>
            </div>

            {{-- ─── Sección: Plana Docente ───────────────────────────────── --}}
            <div x-show="activeTab === 'docentes'">
            {{-- ─── Filtros ─────────────────────────────────────────────── --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 border border-gray-200">
                <div class="p-5">
                    <form action="{{ route('admin.docentes.index') }}" method="GET">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
                            <div>
                                <label for="search" class="block text-xs font-bold text-gray-600 uppercase mb-1">DNI o Nombre</label>
                                <input type="text" name="search" id="search" value="{{ request('search') }}"
                                    placeholder="Buscar docente..."
                                    class="block w-full rounded-md border-gray-300 shadow-sm text-sm focus:ring-yellow-500 focus:border-yellow-500">
                            </div>
                            <div>
                                <label for="f_nivel" class="block text-xs font-bold text-gray-600 uppercase mb-1">Nivel</label>
                                <select name="nivel" id="f_nivel" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                    <option value="">Todos los niveles</option>
                                    <option value="primaria"   {{ request('nivel') == 'primaria'   ? 'selected' : '' }}>Primaria</option>
                                    <option value="secundaria" {{ request('nivel') == 'secundaria' ? 'selected' : '' }}>Secundaria</option>
                                </select>
                            </div>
                            <div>
                                <label for="f_tipo" class="block text-xs font-bold text-gray-600 uppercase mb-1">Tipo</label>
                                <select name="tipo" id="f_tipo" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                    <option value="">Todos</option>
                                    <option value="especialista" {{ request('tipo') == 'especialista' ? 'selected' : '' }}>Especialista</option>
                                    <option value="polidocente"  {{ request('tipo') == 'polidocente'  ? 'selected' : '' }}>Polidocente</option>
                                </select>
                            </div>
                            <div>
                                <label for="f_curso_id" class="block text-xs font-bold text-gray-600 uppercase mb-1">Curso</label>
                                <select name="curso_id" id="f_curso_id" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                    <option value="">Todos los cursos</option>
                                    @foreach($cursos as $curso)
                                        <option value="{{ $curso->id }}" {{ request('curso_id') == $curso->id ? 'selected' : '' }}>
                                            {{ $curso->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="f_estado" class="block text-xs font-bold text-gray-600 uppercase mb-1">Estado</label>
                                <select name="estado" id="f_estado" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                    <option value="">Todos los estados</option>
                                    <option value="activo"   {{ request('estado') === 'activo'   ? 'selected' : '' }}>Activo</option>
                                    <option value="retirado" {{ request('estado') === 'retirado' ? 'selected' : '' }}>Retirado</option>
                                </select>
                            </div>
                        </div>
                        <div class="flex gap-2 mt-4">
                            <button type="submit" class="bg-yellow-600 hover:bg-yellow-700 text-white text-sm font-bold py-2 px-5 rounded shadow transition">
                                Filtrar
                            </button>
                            <a href="{{ route('admin.docentes.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 text-sm font-bold py-2 px-5 rounded transition">
                                Limpiar
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            {{-- ─── Tabla de Docentes ───────────────────────────────────── --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <div class="p-0 text-gray-900">
                    @if($docentes->isEmpty())
                        <div class="text-center py-12 text-gray-500 bg-gray-50">
                            No se encontraron docentes con los filtros aplicados.
                        </div>
                    @else
                        <div id="teachers-table-container" class="overflow-x-auto overflow-y-auto max-h-[70vh]">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-100 sticky top-0 z-10">
                                    <tr>
                                        <th class="px-4 py-4 text-center text-xs font-bold text-gray-600 uppercase tracking-wider w-12">N°</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider hidden sm:table-cell">DNI</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Apellidos y Nombres</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider hidden md:table-cell">Nivel / Tipo</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider hidden lg:table-cell">Curso(s)</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider hidden lg:table-cell">Contacto</th>
                                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-600 uppercase tracking-wider">Gestión</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($docentes as $index => $docente)
                                        <tr class="hover:bg-gray-50 transition">
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 font-bold text-center">
                                                {{ $docentes->firstItem() + $index }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-700 hidden sm:table-cell">{{ $docente->dni }}</td>
                                            <td class="px-6 py-4">
                                                <div class="flex items-center gap-2">
                                                    <div class="text-sm font-bold {{ $docente->activo ? 'text-gray-900' : 'text-gray-500' }}">
                                                        {{ $docente->apellido_paterno }} {{ $docente->apellido_materno }}, {{ $docente->nombres }}
                                                    </div>
                                                    @if(!$docente->activo)
                                                        <span class="px-2 py-0.5 inline-flex text-xs font-bold rounded-full bg-rose-100 text-rose-800 border border-rose-300">
                                                            Retirado
                                                        </span>
                                                    @endif
                                                </div>
                                                <div class="text-xs text-gray-400 lg:hidden mt-0.5">
                                                    {{ $docente->user->email ?? 'Sin correo' }} | {{ $docente->celular ?? 'Sin celular' }}
                                                </div>
                                                <div class="text-xs mt-0.5 md:hidden flex items-center gap-1">
                                                    @if($docente->nivel || $docente->tipo)
                                                        @if($docente->nivel)
                                                            <span class="px-1.5 py-0.5 rounded text-xs font-bold {{ $docente->nivel === 'primaria' ? 'bg-green-100 text-green-800' : 'bg-purple-100 text-purple-800' }}">
                                                                {{ ucfirst($docente->nivel) }}
                                                            </span>
                                                        @endif
                                                        @if($docente->tipo)
                                                            <span class="px-1.5 py-0.5 rounded text-xs font-bold {{ $docente->tipo === 'polidocente' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800' }}">
                                                                {{ ucfirst($docente->tipo) }}
                                                            </span>
                                                        @endif
                                                    @else
                                                        <span class="text-red-500 font-bold text-xs">(falta asignar datos)</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm hidden md:table-cell">
                                                <div class="flex flex-col gap-1">
                                                    @if($docente->nivel || $docente->tipo)
                                                        @if($docente->nivel)
                                                            <span class="px-2 py-0.5 inline-flex text-xs font-bold rounded-full w-fit
                                                                {{ $docente->nivel === 'primaria' ? 'bg-green-100 text-green-800' : 'bg-purple-100 text-purple-800' }}">
                                                                {{ ucfirst($docente->nivel) }}
                                                            </span>
                                                        @endif
                                                        @if($docente->tipo)
                                                            <span class="px-2 py-0.5 inline-flex text-xs font-bold rounded-full w-fit
                                                                {{ $docente->tipo === 'polidocente' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800' }}">
                                                                {{ ucfirst($docente->tipo) }}
                                                            </span>
                                                        @endif
                                                    @else
                                                        <span class="text-red-500 font-bold text-xs">(falta asignar datos)</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 text-sm hidden lg:table-cell">
                                                @if($docente->tipo === 'polidocente')
                                                    <span class="text-xs italic text-gray-500">Todos los cursos</span>
                                                @else
                                                    <div class="flex flex-wrap gap-1">
                                                        @forelse($docente->cursos as $curso)
                                                            <span class="px-2 py-0.5 text-xs font-bold rounded-full bg-indigo-100 text-indigo-800">
                                                                {{ $curso->nombre }}
                                                            </span>
                                                        @empty
                                                            <span class="text-xs text-red-500 font-semibold">Sin cursos asignados</span>
                                                        @endforelse
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 hidden lg:table-cell">
                                                <div>{{ $docente->user->email ?? '—' }}</div>
                                                <div class="text-xs text-gray-400 mt-0.5">{{ $docente->celular ?? 'Sin celular' }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <div class="flex items-center justify-end gap-2">
                                                    @if(auth()->user()->puedeEditar())
                                                        <button type="button" @click="$dispatch('open-edit-docente', {
                                                                id: {{ $docente->id }},
                                                                dni: '{{ $docente->dni }}',
                                                                nombres: '{{ addslashes($docente->nombres) }}',
                                                                apellido_paterno: '{{ addslashes($docente->apellido_paterno) }}',
                                                                apellido_materno: '{{ addslashes($docente->apellido_materno) }}',
                                                                email: '{{ $docente->user->email ?? '' }}',
                                                                celular: '{{ $docente->celular ?? '' }}',
                                                                tipo: '{{ $docente->tipo }}',
                                                                nivel: '{{ $docente->nivel }}',
                                                                activo: {{ $docente->activo ? 'true' : 'false' }},
                                                                curso_ids: {{ json_encode($docente->cursos->pluck('id')->map(fn($id) => (string)$id)) }}
                                                            })"
                                                            class="inline-block bg-emerald-50 text-emerald-700 hover:bg-emerald-100 font-bold px-3 py-2 rounded border border-emerald-200 transition shadow-sm text-xs sm:text-sm">
                                                            Editar Datos
                                                        </button>
                                                        <a href="{{ route('admin.docentes.show', $docente) }}"
                                                           class="inline-block {{ $docente->activo ? 'bg-blue-50 text-blue-700 hover:bg-blue-100 border-blue-200' : 'bg-gray-100 text-gray-500 hover:bg-gray-200 border-gray-300' }} font-bold px-3 py-2 rounded border transition shadow-sm text-xs sm:text-sm">
                                                            {{ $docente->activo ? 'Asignar / Ver' : 'Ver Asignaciones' }}
                                                        </a>
                                                    @else
                                                        <a href="{{ route('admin.docentes.show', $docente) }}"
                                                           class="inline-block bg-blue-50 text-blue-700 hover:bg-blue-100 border-blue-200 font-bold px-3 py-2 rounded border transition shadow-sm text-xs sm:text-sm">
                                                            Ver Asignaciones
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="p-4 border-t">
                            {{ $docentes->links() }}
                        </div>
                    @endif
                </div>
            </div>
            </div> {{-- Fin Sección: Plana Docente --}}

            {{-- ─── Sección: Equipo Directivo ─────────────────────────────── --}}
            <div x-show="activeTab === 'directivos'" style="display: none;">
                {{-- Banner informativo --}}
                <div class="bg-gradient-to-r from-indigo-50 to-blue-50 border border-indigo-200 rounded-lg p-4 mb-6 shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="p-2.5 bg-indigo-100 text-indigo-700 rounded-lg shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-800">Cargos Directivos de la Institución</h4>
                            <p class="text-xs text-gray-600">Registro oficial de las autoridades del colegio: Director(a), Subdirector(a) de Secundaria y Subdirector(a) de Primaria.</p>
                        </div>
                    </div>
                    @if(auth()->user()->puedeEditar())
                        <button @click="showDirectivoModal = true" class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg shadow transition text-xs shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                            + Nuevo Directivo
                        </button>
                    @endif
                </div>

                {{-- Tabla de Directivos --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                    <div class="p-0 text-gray-900">
                        @if($directivos->isEmpty())
                            <div class="text-center py-12 text-gray-500 bg-gray-50">
                                <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <p class="font-bold text-gray-700 text-base mb-1">No hay directivos registrados actualmente</p>
                                @if(auth()->user()->puedeEditar())
                                    <p class="text-xs text-gray-500 mb-4">Haga clic en el botón para agregar al Director(a) o Subdirectores de la institución.</p>
                                    <button @click="showDirectivoModal = true" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-5 rounded-lg shadow transition text-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                        Registrar Directivo
                                    </button>
                                @endif
                            </div>
                        @else
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-100">
                                        <tr>
                                            <th class="px-4 py-4 text-center text-xs font-bold text-gray-600 uppercase tracking-wider w-12">N°</th>
                                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">DNI</th>
                                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Apellidos y Nombres</th>
                                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Cargo</th>
                                            <th class="px-6 py-4 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">Estado</th>
                                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Teléfono</th>
                                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Dirección</th>
                                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-600 uppercase tracking-wider">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($directivos as $index => $directivo)
                                            <tr class="hover:bg-gray-50 transition">
                                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 font-bold text-center">
                                                    {{ $index + 1 }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-mono font-bold text-gray-700">
                                                    {{ $directivo->dni }}
                                                </td>
                                                <td class="px-6 py-4">
                                                    <div class="text-sm font-bold text-gray-900">
                                                        {{ $directivo->apellidos }}, {{ $directivo->nombres }}
                                                    </div>
                                                    @if($directivo->email)
                                                        <div class="text-xs text-gray-400 mt-0.5">
                                                            {{ $directivo->email }}
                                                        </div>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    @if($directivo->cargo === 'Director(a)')
                                                        <span class="px-3 py-1 inline-flex items-center gap-1.5 text-xs font-extrabold rounded-full bg-amber-100 text-amber-900 border border-amber-300 shadow-xs">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-amber-600" viewBox="0 0 20 20" fill="currentColor">
                                                                <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1.323l3.954 1.582 1.599-.8a1 1 0 01.894 1.79l-1.233.616 1.738 5.42a1 1 0 01-.285 1.05A3.989 3.989 0 0115 15a3.989 3.989 0 01-2.667-1.019 1 1 0 01-.285-1.05l1.715-5.349L11 6.477V16h2a1 1 0 110 2H7a1 1 0 110-2h2V6.477L6.237 7.582l1.715 5.349a1 1 0 01-.285 1.05A3.989 3.989 0 015 15a3.989 3.989 0 01-2.667-1.019 1 1 0 01-.285-1.05l1.738-5.42-1.233-.617a1 1 0 01.894-1.788l1.599.799L9 4.323V3a1 1 0 011-1z" clip-rule="evenodd" />
                                                            </svg>
                                                            {{ $directivo->cargo }}
                                                        </span>
                                                    @elseif($directivo->cargo === 'Subdirector(a) de Secundaria')
                                                        <span class="px-3 py-1 inline-flex items-center gap-1.5 text-xs font-bold rounded-full bg-purple-100 text-purple-900 border border-purple-300 shadow-xs">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-purple-600" viewBox="0 0 20 20" fill="currentColor">
                                                                <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a10.912 10.912 0 01.352 1.487l-.427.171a1 1 0 00-.575.922v2.5a1 1 0 00.575.923l1 .4a1 1 0 001.3-.538l.428-1.071c.883-.162 1.796-.39 2.722-.686a11.025 11.025 0 002.375-.989v4.24a1 1 0 00.553.894l2 1A1 1 0 0017 17V8.5a1 1 0 00-.553-.894l-6.053-3.026z" />
                                                            </svg>
                                                            {{ $directivo->cargo }}
                                                        </span>
                                                    @elseif($directivo->cargo === 'Subdirector(a) de Primaria')
                                                        <span class="px-3 py-1 inline-flex items-center gap-1.5 text-xs font-bold rounded-full bg-emerald-100 text-emerald-900 border border-emerald-300 shadow-xs">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-emerald-600" viewBox="0 0 20 20" fill="currentColor">
                                                                <path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z" />
                                                            </svg>
                                                            {{ $directivo->cargo }}
                                                        </span>
                                                    @else
                                                        <span class="px-3 py-1 inline-flex text-xs font-bold rounded-full bg-gray-100 text-gray-800 border border-gray-300">
                                                            {{ $directivo->cargo }}
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                                    @if($directivo->activo)
                                                        <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-emerald-100 text-emerald-800 border border-emerald-300">
                                                            En Ejercicio
                                                        </span>
                                                    @else
                                                        <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-rose-100 text-rose-800 border border-rose-300">
                                                            Cesado / Retirado
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                                    {{ $directivo->telefono ?? '—' }}
                                                </td>
                                                <td class="px-6 py-4 text-sm text-gray-600 max-w-xs truncate" title="{{ $directivo->direccion }}">
                                                    {{ $directivo->direccion ?? '—' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                    @if(auth()->user()->puedeEditar())
                                                        <div class="flex items-center justify-end gap-1.5 flex-wrap">
                                                            @if($directivo->activo)
                                                                <button type="button"
                                                                    @click="$dispatch('open-edit-directivo', {
                                                                        id: {{ $directivo->id }},
                                                                        dni: '{{ $directivo->dni }}',
                                                                        nombres: '{{ addslashes($directivo->nombres) }}',
                                                                        apellidos: '{{ addslashes($directivo->apellidos) }}',
                                                                        cargo: '{{ addslashes($directivo->cargo) }}',
                                                                        telefono: '{{ addslashes($directivo->telefono ?? '') }}',
                                                                        direccion: '{{ addslashes($directivo->direccion ?? '') }}',
                                                                        email: '{{ addslashes($directivo->email ?? '') }}'
                                                                    })"
                                                                    class="inline-block bg-emerald-50 text-emerald-700 hover:bg-emerald-100 font-bold px-2.5 py-1.5 rounded border border-emerald-200 transition shadow-sm text-xs">
                                                                    Editar
                                                                </button>
                                                                <button type="button"
                                                                    @click="$dispatch('open-reemplazar-directivo', {
                                                                        id: {{ $directivo->id }},
                                                                        cargo: '{{ addslashes($directivo->cargo) }}',
                                                                        actual: '{{ addslashes($directivo->apellidos . ' ' . $directivo->nombres) }}'
                                                                    })"
                                                                    class="inline-block bg-blue-50 text-blue-700 hover:bg-blue-100 font-bold px-2.5 py-1.5 rounded border border-blue-200 transition shadow-sm text-xs" title="Designar a un nuevo titular para este cargo">
                                                                    Reemplazar
                                                                </button>
                                                                <form action="{{ route('admin.directivos.quitar', $directivo) }}" method="POST" class="inline" onsubmit="return confirm('¿Está seguro de remover a este directivo del cargo? Perderá acceso de consulta al sistema escolar.');">
                                                                    @csrf
                                                                    <button type="submit" class="inline-block bg-amber-50 text-amber-700 hover:bg-amber-100 font-bold px-2.5 py-1.5 rounded border border-amber-200 transition shadow-sm text-xs" title="Cesar del cargo activo">
                                                                        Quitar
                                                                    </button>
                                                                </form>
                                                            @else
                                                                <form action="{{ route('admin.directivos.reincorporar', $directivo) }}" method="POST" class="inline" onsubmit="return confirm('¿Desea reincorporar a este directivo en su cargo? Se reactivará su acceso de consulta.');">
                                                                    @csrf
                                                                    <button type="submit" class="inline-block bg-green-50 text-green-700 hover:bg-green-100 font-bold px-2.5 py-1.5 rounded border border-green-200 transition shadow-sm text-xs">
                                                                        Reincorporar
                                                                    </button>
                                                                </form>
                                                            @endif
                                                            <form action="{{ route('admin.directivos.destroy', $directivo) }}" method="POST" class="inline" onsubmit="return confirm('¿Está seguro de que desea eliminar definitivamente a este directivo del registro?');">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="inline-block bg-rose-50 text-rose-700 hover:bg-rose-100 font-bold px-2.5 py-1.5 rounded border border-rose-200 transition shadow-sm text-xs">
                                                                    Eliminar
                                                                </button>
                                                            </form>
                                                        </div>
                                                    @else
                                                        <span class="text-xs text-gray-400 italic">Solo lectura</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div> {{-- Fin Sección: Equipo Directivo --}}
        </div>

        @if(auth()->user()->puedeEditar())
        {{-- ─── Modal: Registrar Docente ───────────────────────────────── --}}
        <div x-show="showModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-docente" role="dialog" aria-modal="true" x-cloak>
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showModal"
                     x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showModal = false" aria-hidden="true"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="showModal"
                     x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">

                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b">
                        <h3 class="text-xl leading-6 font-bold text-gray-900 mb-1" id="modal-docente">Registrar Nuevo Docente</h3>
                        <p class="text-xs text-gray-500 mb-4">La contraseña inicial será el DNI del docente.</p>

                        <form action="{{ route('admin.docentes.store') }}" method="POST" id="crearDocenteForm">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                                {{-- Datos personales --}}
                                <div>
                                    <label for="dni" class="block text-sm font-bold text-gray-700">DNI *</label>
                                    <input type="text" name="dni" id="dni" value="{{ old('dni') }}" required maxlength="8" pattern="\d{8}"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-50 @error('dni') border-red-500 @enderror">
                                    @error('dni') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="nombres" class="block text-sm font-bold text-gray-700">Nombres *</label>
                                    <input type="text" name="nombres" id="nombres" value="{{ old('nombres') }}" required
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm uppercase-input">
                                    @error('nombres') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="apellido_paterno" class="block text-sm font-bold text-gray-700">Apellido Paterno *</label>
                                    <input type="text" name="apellido_paterno" id="apellido_paterno" value="{{ old('apellido_paterno') }}" required
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm uppercase-input">
                                    @error('apellido_paterno') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="apellido_materno" class="block text-sm font-bold text-gray-700">Apellido Materno *</label>
                                    <input type="text" name="apellido_materno" id="apellido_materno" value="{{ old('apellido_materno') }}" required
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm uppercase-input">
                                    @error('apellido_materno') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div class="md:col-span-1">
                                    <label for="email" class="block text-sm font-bold text-gray-700">Correo Electrónico <span class="text-xs text-gray-400 font-normal">(Opcional)</span></label>
                                    <input type="email" name="email" id="email" value="{{ old('email') }}"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                    @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div class="md:col-span-1">
                                    <label for="celular" class="block text-sm font-bold text-gray-700">Celular <span class="text-xs text-gray-400 font-normal">(Opcional)</span></label>
                                    <input type="text" name="celular" id="celular" value="{{ old('celular') }}" maxlength="15"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                    @error('celular') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                {{-- Separador --}}
                                <div class="md:col-span-2 border-t pt-3 mt-1">
                                    <h4 class="font-bold text-gray-700 text-sm mb-3">Asignación Académica</h4>
                                </div>

                                {{-- Nivel --}}
                                <div>
                                    <label for="modal_nivel" class="block text-sm font-bold text-gray-700">Nivel Educativo</label>
                                    <select name="nivel" id="modal_nivel" x-model="nivel"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-50">
                                        <option value="">-- Sin asignar --</option>
                                        <option value="primaria">Primaria</option>
                                        <option value="secundaria">Secundaria</option>
                                    </select>
                                    @error('nivel') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                {{-- Tipo de docencia --}}
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Tipo de Docencia</label>
                                    <div class="flex gap-3">
                                        <label class="flex-1 cursor-pointer">
                                            <input type="radio" name="tipo" value="especialista" x-model="tipo" class="sr-only">
                                            <div :class="tipo === 'especialista'
                                                    ? 'border-blue-500 bg-blue-50 text-blue-700'
                                                    : 'border-gray-300 bg-white text-gray-600'"
                                                class="border-2 rounded-lg p-3 text-center transition">
                                                <div class="font-bold text-sm">Especialista</div>
                                                <div class="text-xs mt-0.5 opacity-75">Enseña curso(s) específico(s)</div>
                                            </div>
                                        </label>
                                        <label class="flex-1 cursor-pointer">
                                            <input type="radio" name="tipo" value="polidocente" x-model="tipo" class="sr-only">
                                            <div :class="tipo === 'polidocente'
                                                    ? 'border-amber-500 bg-amber-50 text-amber-700'
                                                    : 'border-gray-300 bg-white text-gray-600'"
                                                class="border-2 rounded-lg p-3 text-center transition">
                                                <div class="font-bold text-sm">Polidocente</div>
                                                <div class="text-xs mt-0.5 opacity-75">Enseña todos los cursos al grado</div>
                                            </div>
                                        </label>
                                        <label class="flex-1 cursor-pointer">
                                            <input type="radio" name="tipo" value="" x-model="tipo" class="sr-only">
                                            <div :class="!tipo || tipo === ''
                                                    ? 'border-gray-500 bg-gray-100 text-gray-700'
                                                    : 'border-gray-300 bg-white text-gray-600'"
                                                class="border-2 rounded-lg p-3 text-center transition">
                                                <div class="font-bold text-sm">Sin asignar</div>
                                                <div class="text-xs mt-0.5 opacity-75">Datos pendientes</div>
                                            </div>
                                        </label>
                                    </div>
                                    @error('tipo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                {{-- Cursos: solo si es especialista --}}
                                <div class="md:col-span-2" x-show="esEspecialista" x-transition>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">
                                        Curso(s) que imparte
                                        <span class="text-xs font-normal text-gray-500">(puede seleccionar más de uno)</span>
                                    </label>
                                    @error('curso_ids') <p class="text-red-500 text-xs mb-2 font-bold">{{ $message }}</p> @enderror

                                    <div x-show="!nivel" class="text-xs text-gray-500 italic p-3 bg-gray-50 rounded-md border border-dashed">
                                        Selecciona primero el nivel educativo para ver los cursos disponibles.
                                    </div>

                                    <div x-show="nivel" class="grid grid-cols-2 sm:grid-cols-3 gap-2 max-h-48 overflow-y-auto p-1">
                                        <template x-for="curso in filteredCursos" :key="curso.id">
                                            <label class="flex items-center gap-2 p-2 rounded-lg border border-gray-200 hover:bg-indigo-50 hover:border-indigo-300 cursor-pointer transition">
                                                <input type="checkbox" name="curso_ids[]"
                                                    :value="curso.id"
                                                    :checked="{{ json_encode(old('curso_ids', [])) }}.includes(String(curso.id))"
                                                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                                <span class="text-sm text-gray-700 font-medium" x-text="curso.nombre"></span>
                                            </label>
                                        </template>
                                    </div>
                                </div>

                                {{-- Mensaje polidocente --}}
                                <div class="md:col-span-2" x-show="tipo === 'polidocente'" x-transition>
                                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 text-sm text-amber-800">
                                        <strong>Nota:</strong> El docente polidocente no requiere asignación de cursos específicos.
                                        Al asignarle un grado/sección, se entenderá que cubre todas las áreas de ese grado.
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t">
                        <button type="submit" form="crearDocenteForm"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-yellow-600 text-base font-medium text-white hover:bg-yellow-700 sm:ml-3 sm:w-auto sm:text-sm">
                            Guardar Docente
                        </button>
                        <button type="button" @click="showModal = false"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
        {{-- Modal Editar Docente --}}
        <div x-data="{
                showEditModal: false,
                docente: { id: '', dni: '', nombres: '', apellido_paterno: '', apellido_materno: '', email: '', celular: '', tipo: '', nivel: '', activo: true, curso_ids: [] },
                openEdit(data) {
                    this.docente = data;
                    this.showEditModal = true;
                },
                retirarDocente() {
                    if (confirm('¿Está seguro de retirar a este docente? El docente permanecerá en el sistema pero no podrá ser asignado a cursos, grados ni secciones.')) {
                        const f = document.getElementById('formRetirarDocente');
                        f.action = '{{ url('admin/docentes') }}/' + this.docente.id + '/retirar';
                        f.submit();
                    }
                },
                reincorporarDocente() {
                    if (confirm('¿Desea ingresar nuevamente a este docente como activo? Podrá volver a ser asignado a cursos y secciones.')) {
                        const f = document.getElementById('formReincorporarDocente');
                        f.action = '{{ url('admin/docentes') }}/' + this.docente.id + '/reincorporar';
                        f.submit();
                    }
                }
            }"
            @open-edit-docente.window="openEdit($event.detail)"
            x-show="showEditModal"
            class="fixed inset-0 z-50 overflow-y-auto"
            style="display: none;"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0">

            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" aria-hidden="true" @click="showEditModal = false"></div>

                <div class="relative inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-2xl w-full"
                    x-show="showEditModal"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

                    <form :action="'{{ url('admin/docentes') }}/' + docente.id" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="px-4 pt-5 pb-4 bg-white sm:p-6 sm:pb-4 border-t-4 border-emerald-500">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-bold leading-6 text-gray-900" id="modal-title">
                                    Editar Datos del Docente
                                </h3>
                                <template x-if="docente.activo">
                                    <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-green-100 text-green-800 border border-green-300 inline-flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-green-500"></span> Activo
                                    </span>
                                </template>
                                <template x-if="!docente.activo">
                                    <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-rose-100 text-rose-800 border border-rose-300 inline-flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-rose-500"></span> Retirado
                                    </span>
                                </template>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-left">
                                {{-- DNI --}}
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-bold text-gray-700">DNI *</label>
                                    <input type="text" name="dni" x-model="docente.dni" required maxlength="8" pattern="\d{8}"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                </div>

                                {{-- Nombres --}}
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-bold text-gray-700">Nombres *</label>
                                    <input type="text" name="nombres" x-model="docente.nombres" required
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm uppercase-input">
                                </div>

                                {{-- Apellidos --}}
                                <div>
                                    <label class="block text-sm font-bold text-gray-700">Apellido Paterno *</label>
                                    <input type="text" name="apellido_paterno" x-model="docente.apellido_paterno" required
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm uppercase-input">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700">Apellido Materno *</label>
                                    <input type="text" name="apellido_materno" x-model="docente.apellido_materno" required
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm uppercase-input">
                                </div>

                                {{-- Contacto --}}
                                <div>
                                    <label class="block text-sm font-bold text-gray-700">Correo <span class="text-xs text-gray-400 font-normal">(Opcional)</span></label>
                                    <input type="email" name="email" x-model="docente.email"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700">Celular <span class="text-xs text-gray-400 font-normal">(Opcional)</span></label>
                                    <input type="text" name="celular" x-model="docente.celular" maxlength="15"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                </div>

                                <div class="md:col-span-2 border-t pt-3 mt-1">
                                    <h4 class="font-bold text-gray-700 text-sm mb-3">Asignación Académica</h4>
                                </div>

                                {{-- Nivel --}}
                                <div>
                                    <label class="block text-sm font-bold text-gray-700">Nivel Educativo</label>
                                    <select name="nivel" x-model="docente.nivel"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-50">
                                        <option value="">-- Sin asignar --</option>
                                        <option value="primaria">Primaria</option>
                                        <option value="secundaria">Secundaria</option>
                                    </select>
                                </div>

                                {{-- Tipo de docencia --}}
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Tipo de Docencia</label>
                                    <div class="flex gap-3">
                                        <label class="flex-1 cursor-pointer">
                                            <input type="radio" name="tipo" value="especialista" x-model="docente.tipo" class="sr-only">
                                            <div :class="docente.tipo === 'especialista'
                                                    ? 'border-blue-500 bg-blue-50 text-blue-700'
                                                    : 'border-gray-300 bg-white text-gray-600'"
                                                class="border-2 rounded-lg p-3 text-center transition">
                                                <div class="font-bold text-sm">Especialista</div>
                                            </div>
                                        </label>
                                        <label class="flex-1 cursor-pointer">
                                            <input type="radio" name="tipo" value="polidocente" x-model="docente.tipo" class="sr-only">
                                            <div :class="docente.tipo === 'polidocente'
                                                    ? 'border-amber-500 bg-amber-50 text-amber-700'
                                                    : 'border-gray-300 bg-white text-gray-600'"
                                                class="border-2 rounded-lg p-3 text-center transition">
                                                <div class="font-bold text-sm">Polidocente</div>
                                            </div>
                                        </label>
                                        <label class="flex-1 cursor-pointer">
                                            <input type="radio" name="tipo" value="" x-model="docente.tipo" class="sr-only">
                                            <div :class="!docente.tipo || docente.tipo === ''
                                                    ? 'border-gray-500 bg-gray-100 text-gray-700'
                                                    : 'border-gray-300 bg-white text-gray-600'"
                                                class="border-2 rounded-lg p-3 text-center transition">
                                                <div class="font-bold text-sm">Sin asignar</div>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                {{-- Cursos (Solo si es Especialista) --}}
                                <div class="md:col-span-2 border-t pt-3 mt-1" x-show="docente.tipo === 'especialista'">
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Cursos a Impartir</label>
                                    <div class="grid grid-cols-2 gap-2">
                                        @foreach($cursos as $curso)
                                            <label class="flex items-center space-x-2 text-sm bg-gray-50 p-2 rounded border border-gray-200 hover:bg-indigo-50 cursor-pointer"
                                                x-show="docente.nivel === '{{ $curso->nivel }}' || '{{ $curso->nivel }}' === 'ambos'">
                                                <input type="checkbox" name="curso_ids[]" value="{{ $curso->id }}" x-model="docente.curso_ids"
                                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                                <span class="font-medium text-gray-700">{{ $curso->nombre }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                    <p class="text-xs text-gray-500 mt-2 italic">Selecciona los cursos que este docente podrá dictar.</p>
                                </div>
                            </div>
                        </div>
                        <div class="px-4 py-3 bg-gray-50 sm:px-6 flex flex-col sm:flex-row items-center justify-between gap-3 border-t border-gray-200">
                            <div>
                                <button type="button"
                                    x-show="docente.activo"
                                    @click="retirarDocente()"
                                    class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-4 py-2 text-sm font-bold text-rose-700 bg-rose-50 border border-rose-300 rounded-md hover:bg-rose-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-rose-500 transition shadow-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7a4 4 0 11-8 0 4 4 0 018 0zM9 14a6 6 0 00-6 6v1h12v-1a6 6 0 00-6-6zM21 12h-6" />
                                    </svg>
                                    Retirar docente
                                </button>
                                <button type="button"
                                    x-show="!docente.activo"
                                    @click="reincorporarDocente()"
                                    class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-4 py-2 text-sm font-bold text-emerald-700 bg-emerald-50 border border-emerald-300 rounded-md hover:bg-emerald-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition shadow-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                    </svg>
                                    Ingresar docente
                                </button>
                            </div>
                            <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                                <button type="button" @click="showEditModal = false"
                                    class="w-full sm:w-auto inline-flex justify-center px-4 py-2 text-base sm:text-sm font-bold text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition">
                                    Cancelar
                                </button>
                                <button type="submit"
                                    class="w-full sm:w-auto inline-flex justify-center px-4 py-2 text-base sm:text-sm font-bold text-white border border-transparent rounded-md shadow-sm bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition">
                                    Guardar Cambios
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Formularios ocultos para Retirar / Reincorporar Docente --}}
        <form id="formRetirarDocente" method="POST" style="display: none;">
            @csrf
        </form>
        <form id="formReincorporarDocente" method="POST" style="display: none;">
            @csrf
        </form>

        {{-- ─── Modal: Registrar Directivo ─────────────────────────────── --}}
        <div x-show="showDirectivoModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-directivo-title" role="dialog" aria-modal="true" x-cloak>
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showDirectivoModal"
                     x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showDirectivoModal = false" aria-hidden="true"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="showDirectivoModal"
                     x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full">

                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="p-2 bg-indigo-100 text-indigo-700 rounded-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl leading-6 font-bold text-gray-900" id="modal-directivo-title">Registrar Directivo</h3>
                                <p class="text-xs text-gray-500">Ingrese los datos correspondientes a la autoridad educativa.</p>
                            </div>
                        </div>

                        <form action="{{ route('admin.directivos.store') }}" method="POST" id="crearDirectivoForm" class="mt-4">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {{-- Cargo --}}
                                <div class="md:col-span-2">
                                    <label for="directivo_cargo" class="block text-sm font-bold text-gray-700">Cargo Directivo *</label>
                                    <select name="cargo" id="directivo_cargo" required
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('cargo') border-red-500 @enderror">
                                        <option value="">Seleccione el cargo...</option>
                                        <option value="Director(a)" {{ old('cargo') == 'Director(a)' ? 'selected' : '' }}>Director(a)</option>
                                        <option value="Subdirector(a) de Secundaria" {{ old('cargo') == 'Subdirector(a) de Secundaria' ? 'selected' : '' }}>Subdirector(a) de Secundaria</option>
                                        <option value="Subdirector(a) de Primaria" {{ old('cargo') == 'Subdirector(a) de Primaria' ? 'selected' : '' }}>Subdirector(a) de Primaria</option>
                                    </select>
                                    @error('cargo')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- DNI --}}
                                <div>
                                    <label for="directivo_dni" class="block text-sm font-bold text-gray-700">DNI *</label>
                                    <input type="text" name="dni" id="directivo_dni" value="{{ old('dni') }}" required maxlength="8" pattern="\d{8}"
                                        placeholder="8 dígitos"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('dni') border-red-500 @enderror">
                                    @error('dni')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Teléfono --}}
                                <div>
                                    <label for="directivo_telefono" class="block text-sm font-bold text-gray-700">Teléfono / Celular</label>
                                    <input type="text" name="telefono" id="directivo_telefono" value="{{ old('telefono') }}"
                                        placeholder="Ej: 987654321"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('telefono') border-red-500 @enderror">
                                    @error('telefono')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Nombres --}}
                                <div>
                                    <label for="directivo_nombres" class="block text-sm font-bold text-gray-700">Nombres *</label>
                                    <input type="text" name="nombres" id="directivo_nombres" value="{{ old('nombres') }}" required
                                        placeholder="Nombres completos"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('nombres') border-red-500 @enderror">
                                    @error('nombres')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Apellidos --}}
                                <div>
                                    <label for="directivo_apellidos" class="block text-sm font-bold text-gray-700">Apellidos *</label>
                                    <input type="text" name="apellidos" id="directivo_apellidos" value="{{ old('apellidos') }}" required
                                        placeholder="Apellidos completos"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('apellidos') border-red-500 @enderror">
                                    @error('apellidos')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Dirección --}}
                                <div class="md:col-span-2">
                                    <label for="directivo_direccion" class="block text-sm font-bold text-gray-700">Dirección</label>
                                    <input type="text" name="direccion" id="directivo_direccion" value="{{ old('direccion') }}"
                                        placeholder="Dirección domiciliaria"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('direccion') border-red-500 @enderror">
                                    @error('direccion')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Correo electrónico (opcional) --}}
                                <div class="md:col-span-2">
                                    <label for="directivo_email" class="block text-sm font-bold text-gray-700">Correo Electrónico</label>
                                    <input type="email" name="email" id="directivo_email" value="{{ old('email') }}"
                                        placeholder="directivo@colegio.edu.pe"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('email') border-red-500 @enderror">
                                    @error('email')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t">
                        <button type="submit" form="crearDirectivoForm"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-bold text-white hover:bg-indigo-700 sm:ml-3 sm:w-auto sm:text-sm transition">
                            Guardar Directivo
                        </button>
                        <button type="button" @click="showDirectivoModal = false"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ─── Modal: Editar Directivo ───────────────────────────────── --}}
        <div x-data="{
                showEditDirectivoModal: false,
                directivo: { id: '', dni: '', nombres: '', apellidos: '', cargo: '', telefono: '', direccion: '', email: '' },
                openEditDirectivo(data) {
                    this.directivo = data;
                    this.showEditDirectivoModal = true;
                }
            }"
            @open-edit-directivo.window="openEditDirectivo($event.detail)"
            x-show="showEditDirectivoModal"
            class="fixed inset-0 z-50 overflow-y-auto"
            style="display: none;"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            x-cloak>

            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" aria-hidden="true" @click="showEditDirectivoModal = false"></div>

                <div class="relative inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-xl w-full"
                    x-show="showEditDirectivoModal"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

                    <form :action="'{{ url('admin/directivos') }}/' + directivo.id" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="px-4 pt-5 pb-4 bg-white sm:p-6 sm:pb-4 border-t-4 border-indigo-600">
                            <h3 class="text-lg font-bold leading-6 text-gray-900 mb-4">
                                Editar Datos del Directivo
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-left">
                                {{-- Cargo --}}
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-bold text-gray-700">Cargo Directivo *</label>
                                    <select name="cargo" x-model="directivo.cargo" required
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                        <option value="Director(a)">Director(a)</option>
                                        <option value="Subdirector(a) de Secundaria">Subdirector(a) de Secundaria</option>
                                        <option value="Subdirector(a) de Primaria">Subdirector(a) de Primaria</option>
                                    </select>
                                </div>

                                {{-- DNI --}}
                                <div>
                                    <label class="block text-sm font-bold text-gray-700">DNI *</label>
                                    <input type="text" name="dni" x-model="directivo.dni" required maxlength="8" pattern="\d{8}"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                </div>

                                {{-- Teléfono --}}
                                <div>
                                    <label class="block text-sm font-bold text-gray-700">Teléfono / Celular</label>
                                    <input type="text" name="telefono" x-model="directivo.telefono"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                </div>

                                {{-- Nombres --}}
                                <div>
                                    <label class="block text-sm font-bold text-gray-700">Nombres *</label>
                                    <input type="text" name="nombres" x-model="directivo.nombres" required
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                </div>

                                {{-- Apellidos --}}
                                <div>
                                    <label class="block text-sm font-bold text-gray-700">Apellidos *</label>
                                    <input type="text" name="apellidos" x-model="directivo.apellidos" required
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                </div>

                                {{-- Dirección --}}
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-bold text-gray-700">Dirección</label>
                                    <input type="text" name="direccion" x-model="directivo.direccion"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                </div>

                                {{-- Correo electrónico --}}
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-bold text-gray-700">Correo Electrónico</label>
                                    <input type="email" name="email" x-model="directivo.email"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                            </div>
                        </div>
                        <div class="px-4 py-3 bg-gray-50 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-200">
                            <button type="submit"
                                class="inline-flex justify-center w-full px-4 py-2 text-base font-bold text-white border border-transparent rounded-md shadow-sm bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm transition">
                                Guardar Cambios
                            </button>
                            <button type="button" @click="showEditDirectivoModal = false"
                                class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-bold text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition">
                                Cancelar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ─── Modal: Reemplazar Directivo ───────────────────────────────── --}}
        <div x-data="{
                showReemplazarModal: false,
                target: { id: '', cargo: '', actual: '' },
                openReemplazar(data) {
                    this.target = data;
                    this.showReemplazarModal = true;
                }
            }"
            @open-reemplazar-directivo.window="openReemplazar($event.detail)"
            x-show="showReemplazarModal"
            class="fixed inset-0 z-50 overflow-y-auto"
            style="display: none;"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            x-cloak>

            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" aria-hidden="true" @click="showReemplazarModal = false"></div>

                <div class="relative inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-xl w-full"
                    x-show="showReemplazarModal"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

                    <form :action="'{{ url('admin/directivos') }}/' + target.id + '/reemplazar'" method="POST">
                        @csrf
                        <div class="px-4 pt-5 pb-4 bg-white sm:p-6 sm:pb-4 border-t-4 border-blue-600">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="p-2.5 bg-blue-100 text-blue-700 rounded-lg shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold leading-6 text-gray-900">
                                        Reemplazar Titular del Cargo
                                    </h3>
                                    <p class="text-xs text-gray-500">Designar un nuevo profesional en el cargo directivo.</p>
                                </div>
                            </div>

                            <div class="mb-4 p-3 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-800">
                                Cargo: <strong class="font-bold" x-text="target.cargo"></strong><br>
                                Titular saliente: <strong class="font-semibold" x-text="target.actual"></strong> (quedará en estado cesado/inactivo).
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-left">
                                {{-- DNI --}}
                                <div>
                                    <label class="block text-sm font-bold text-gray-700">DNI del Nuevo Titular *</label>
                                    <input type="text" name="dni" required maxlength="8" pattern="\d{8}" placeholder="8 dígitos"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                </div>

                                {{-- Teléfono --}}
                                <div>
                                    <label class="block text-sm font-bold text-gray-700">Teléfono / Celular</label>
                                    <input type="text" name="telefono" placeholder="Ej: 987654321"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                </div>

                                {{-- Nombres --}}
                                <div>
                                    <label class="block text-sm font-bold text-gray-700">Nombres *</label>
                                    <input type="text" name="nombres" required placeholder="Nombres del nuevo titular"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                </div>

                                {{-- Apellidos --}}
                                <div>
                                    <label class="block text-sm font-bold text-gray-700">Apellidos *</label>
                                    <input type="text" name="apellidos" required placeholder="Apellidos completos"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                </div>

                                {{-- Dirección --}}
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-bold text-gray-700">Dirección</label>
                                    <input type="text" name="direccion" placeholder="Dirección domiciliaria"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                </div>

                                {{-- Correo electrónico --}}
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-bold text-gray-700">Correo Electrónico (Opcional)</label>
                                    <input type="email" name="email" placeholder="directivo@colegio.edu.pe"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>
                        </div>
                        <div class="px-4 py-3 bg-gray-50 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-200">
                            <button type="submit"
                                class="inline-flex justify-center w-full px-4 py-2 text-base font-bold text-white border border-transparent rounded-md shadow-sm bg-blue-600 hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm transition">
                                Confirmar Reemplazo
                            </button>
                            <button type="button" @click="showReemplazarModal = false"
                                class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-bold text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition">
                                Cancelar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif

    <!-- Script para preservar posición de scroll tras guardar cambios y reabrir modal si hay errores -->
    <script>
        // Guardar posición de scroll al enviar formularios de tipo POST
        document.addEventListener('submit', (e) => {
            const form = e.target;
            if (form && form.method && form.method.toLowerCase() === 'post') {
                localStorage.setItem('docentes_scroll_y', window.scrollY);
                const tableContainer = document.getElementById('teachers-table-container');
                if (tableContainer) {
                    localStorage.setItem('docentes_table_scroll_y', tableContainer.scrollTop);
                }
            }
        });

        // Restaurar posición de scroll al cargar la página
        document.addEventListener('DOMContentLoaded', () => {
            const scrollY = localStorage.getItem('docentes_scroll_y');
            const tableScrollY = localStorage.getItem('docentes_table_scroll_y');
            
            if (scrollY !== null) {
                window.scrollTo(0, parseInt(scrollY));
                localStorage.removeItem('docentes_scroll_y');
            }
            
            if (tableScrollY !== null) {
                const tableContainer = document.getElementById('teachers-table-container');
                if (tableContainer) {
                    tableContainer.scrollTop = parseInt(tableScrollY);
                }
                localStorage.removeItem('docentes_table_scroll_y');
            }

            // Reabrir modal si hay errores de validación
            @if($errors->any())
                const alpineDiv = document.querySelector('[x-data]');
                if (alpineDiv && alpineDiv._x_dataStack) {
                    @if(old('cargo') || $errors->has('cargo'))
                        alpineDiv._x_dataStack[0].activeTab = 'directivos';
                        alpineDiv._x_dataStack[0].showDirectivoModal = true;
                    @else
                        alpineDiv._x_dataStack[0].activeTab = 'docentes';
                        alpineDiv._x_dataStack[0].showModal = true;
                    @endif
                }
            @endif
        });
    </script>
</x-app-layout>
