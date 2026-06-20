<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Gestión de Docentes y Asignaciones') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="{
        showModal: false,
        nivel: '{{ old('nivel') }}',
        tipo: '{{ old('tipo', 'especialista') }}',
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
                    <h3 class="text-2xl font-bold text-gray-800">Directorio Docente</h3>
                    <p class="text-gray-600">Administra a los profesores y asígnales los grados/secciones que tienen a cargo.</p>
                </div>
                <button @click="showModal = true" class="bg-yellow-600 hover:bg-yellow-700 text-white font-bold py-3 px-6 rounded shadow transition flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    Registrar Nuevo Docente
                </button>
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

            {{-- ─── Filtros ─────────────────────────────────────────────── --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 border border-gray-200">
                <div class="p-5">
                    <form action="{{ route('admin.docentes.index') }}" method="GET">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
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
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider hidden sm:table-cell">DNI</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Apellidos y Nombres</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider hidden md:table-cell">Nivel / Tipo</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider hidden lg:table-cell">Curso(s)</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider hidden lg:table-cell">Contacto</th>
                                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-600 uppercase tracking-wider">Gestión</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($docentes as $docente)
                                        <tr class="hover:bg-gray-50 transition">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-700 hidden sm:table-cell">{{ $docente->dni }}</td>
                                            <td class="px-6 py-4">
                                                <div class="text-sm font-bold text-gray-900">
                                                    {{ $docente->apellido_paterno }} {{ $docente->apellido_materno }}, {{ $docente->nombres }}
                                                </div>
                                                <div class="text-xs text-gray-400 lg:hidden mt-0.5">
                                                    {{ $docente->user->email ?? 'Sin correo' }} | {{ $docente->celular ?? 'Sin celular' }}
                                                </div>
                                                <div class="text-xs mt-0.5 md:hidden flex items-center gap-1">
                                                    <span class="px-1.5 py-0.5 rounded text-xs font-bold {{ $docente->nivel === 'primaria' ? 'bg-green-100 text-green-800' : 'bg-purple-100 text-purple-800' }}">
                                                        {{ ucfirst($docente->nivel) }}
                                                    </span>
                                                    <span class="px-1.5 py-0.5 rounded text-xs font-bold {{ $docente->tipo === 'polidocente' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800' }}">
                                                        {{ ucfirst($docente->tipo) }}
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm hidden md:table-cell">
                                                <div class="flex flex-col gap-1">
                                                    <span class="px-2 py-0.5 inline-flex text-xs font-bold rounded-full w-fit
                                                        {{ $docente->nivel === 'primaria' ? 'bg-green-100 text-green-800' : 'bg-purple-100 text-purple-800' }}">
                                                        {{ ucfirst($docente->nivel) }}
                                                    </span>
                                                    <span class="px-2 py-0.5 inline-flex text-xs font-bold rounded-full w-fit
                                                        {{ $docente->tipo === 'polidocente' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800' }}">
                                                        {{ ucfirst($docente->tipo) }}
                                                    </span>
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
                                                            curso_ids: {{ json_encode($docente->cursos->pluck('id')->map(fn($id) => (string)$id)) }}
                                                        })"
                                                        class="inline-block bg-emerald-50 text-emerald-700 hover:bg-emerald-100 font-bold px-3 py-2 rounded border border-emerald-200 transition shadow-sm text-xs sm:text-sm">
                                                        Editar Datos
                                                    </button>
                                                    <a href="{{ route('admin.docentes.show', $docente) }}"
                                                       class="inline-block bg-blue-50 text-blue-700 hover:bg-blue-100 font-bold px-3 py-2 rounded border border-blue-200 transition shadow-sm text-xs sm:text-sm">
                                                        Asignar / Ver
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

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
                                    <label for="modal_nivel" class="block text-sm font-bold text-gray-700">Nivel Educativo *</label>
                                    <select name="nivel" id="modal_nivel" x-model="nivel" required
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-50">
                                        <option value="">-- Seleccione Nivel --</option>
                                        <option value="primaria">Primaria</option>
                                        <option value="secundaria">Secundaria</option>
                                    </select>
                                    @error('nivel') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                {{-- Tipo de docencia --}}
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Tipo de Docencia *</label>
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
                                    </div>
                                    @error('tipo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                {{-- Cursos: solo si es especialista --}}
                                <div class="md:col-span-2" x-show="esEspecialista" x-transition>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">
                                        Curso(s) que imparte *
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
                docente: { id: '', dni: '', nombres: '', apellido_paterno: '', apellido_materno: '', email: '', celular: '', tipo: '', nivel: '', curso_ids: [] },
                openEdit(data) {
                    this.docente = data;
                    this.showEditModal = true;
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
                            <h3 class="text-lg font-bold leading-6 text-gray-900 mb-4" id="modal-title">
                                Editar Datos del Docente
                            </h3>

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

                                {{-- Cursos (Solo si es Especialista) --}}
                                <div class="md:col-span-2 border-t pt-3 mt-1" x-show="docente.tipo === 'especialista'">
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Cursos a Impartir *</label>
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
                        <div class="px-4 py-3 bg-gray-50 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-200">
                            <button type="submit"
                                class="inline-flex justify-center w-full px-4 py-2 text-base font-bold text-white border border-transparent rounded-md shadow-sm bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 sm:ml-3 sm:w-auto sm:text-sm transition">
                                Guardar Cambios
                            </button>
                            <button type="button" @click="showEditModal = false"
                                class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-bold text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition">
                                Cancelar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

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
                    alpineDiv._x_dataStack[0].showModal = true;
                }
            @endif
        });
    </script>
</x-app-layout>
