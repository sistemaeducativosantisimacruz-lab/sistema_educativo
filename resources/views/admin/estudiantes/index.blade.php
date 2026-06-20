<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Gestión de Estudiantes') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="{ 
        showModal: false, 
        showMoverModal: false, 
        showEditModal: false,
        selectedMatricula: null,
        editEstudiante: null,
        editApoderado: null,
        editPadre: null,
        editMadre: null,
        editSexo: 'M'
    }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Botón y Título similar a Secciones -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                <div>
                    <h3 class="text-2xl font-bold text-gray-800">Listado de Estudiantes</h3>
                    <p class="text-gray-600">Administra la información de los estudiantes matriculados en el sistema.</p>
                </div>
                <button @click="showModal = true" class="bg-yellow-600 hover:bg-yellow-700 text-white font-bold py-3 px-6 rounded shadow transition flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    Registrar Estudiante
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

            <!-- ─── Filtros de búsqueda ────────────────────────────────────── -->

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 border border-gray-200">
                <div class="p-5" x-data="{
                    nivel: '{{ request('nivel') }}',
                    grado_id: '{{ request('grado_id') }}',
                    seccion_id: '{{ request('grado_seccion_id') }}',
                    grados: {{ $grados->toJson() }},
                    gradoSecciones: {{ $gradoSecciones->map(function($gs) {
                        return [
                            'id' => $gs->id,
                            'grado_id' => $gs->grado_id,
                            'seccion_nombre' => $gs->seccion->nombre,
                            'nivel' => $gs->grado->nivel
                        ];
                    })->toJson() }},
                    get filteredGrados() {
                        if (!this.nivel) return this.grados;
                        return this.grados.filter(g => g.nivel === this.nivel);
                    },
                    get filteredSecciones() {
                        let filtered = this.gradoSecciones;
                        if (this.nivel) {
                            filtered = filtered.filter(gs => gs.nivel === this.nivel);
                        }
                        if (this.grado_id) {
                            filtered = filtered.filter(gs => gs.grado_id == this.grado_id);
                        }
                        return filtered;
                    }
                }">
                    <form action="{{ route('admin.estudiantes.index') }}" method="GET">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                            <!-- Búsqueda por nombre o DNI -->
                            <div>
                                <label for="search" class="block text-xs font-bold text-gray-600 uppercase mb-1">Nombre o DNI</label>
                                <input type="text" name="search" id="search" value="{{ request('search') }}"
                                    placeholder="Buscar estudiante..."
                                    class="block w-full rounded-md border-gray-300 shadow-sm text-sm focus:ring-yellow-500 focus:border-yellow-500">
                            </div>
                            <!-- Filtro por nivel educativo -->
                            <div>
                                <label for="f_nivel" class="block text-xs font-bold text-gray-600 uppercase mb-1">Nivel Educativo</label>
                                <select name="nivel" id="f_nivel" x-model="nivel" @change="grado_id = ''; seccion_id = ''" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                    <option value="">Todos los niveles</option>
                                    <option value="primaria">Primaria</option>
                                    <option value="secundaria">Secundaria</option>
                                </select>
                            </div>
                            <!-- Filtro por grado -->
                            <div>
                                <label for="f_grado_id" class="block text-xs font-bold text-gray-600 uppercase mb-1">Grado</label>
                                <select name="grado_id" id="f_grado_id" x-model="grado_id" @change="seccion_id = ''" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                    <option value="">Todos los grados</option>
                                    <template x-for="grado in filteredGrados" :key="grado.id">
                                        <option :value="grado.id" x-text="grado.nombre" :selected="grado.id == grado_id"></option>
                                    </template>
                                </select>
                            </div>
                            <!-- Filtro por sección específica -->
                            <div>
                                <label for="f_grado_seccion_id" class="block text-xs font-bold text-gray-600 uppercase mb-1">Sección</label>
                                <select name="grado_seccion_id" id="f_grado_seccion_id" x-model="seccion_id" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                    <option value="">Todas las secciones</option>
                                    <template x-for="gs in filteredSecciones" :key="gs.id">
                                        <option :value="gs.id" x-text="'Sección ' + gs.seccion_nombre" :selected="gs.id == seccion_id"></option>
                                    </template>
                                </select>
                            </div>
                        </div>
                        <div class="flex gap-2 mt-4">
                            <button type="submit" class="bg-yellow-600 hover:bg-yellow-700 text-white text-sm font-bold py-2 px-5 rounded shadow transition">
                                Filtrar
                            </button>
                            <a href="{{ route('admin.estudiantes.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 text-sm font-bold py-2 px-5 rounded transition flex items-center justify-center">
                                Limpiar
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabla -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <div class="p-0 text-gray-900">
                    @if(!$anoActivo)
                        <div class="text-center py-10 bg-red-50 text-red-700 font-bold">
                            No hay un año lectivo activo.
                        </div>
                    @elseif($matriculas->isEmpty())
                        <div class="text-center py-12 text-gray-500 bg-gray-50">
                            No se encontraron estudiantes matriculados.
                        </div>
                    @else
                        <div id="students-table-container" class="overflow-x-auto overflow-y-auto max-h-[70vh]">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-100 sticky top-0 z-10">
                                    <tr>
                                        <th class="px-4 py-4 text-center text-xs font-bold text-gray-600 uppercase tracking-wider w-12">N°</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider hidden sm:table-cell">DNI</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Estudiante</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider hidden md:table-cell">Grado y Sección</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Estado</th>
                                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-600 uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($matriculas as $index => $matricula)
                                        <tr class="hover:bg-gray-50 transition">
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 font-bold text-center">
                                                {{ $matriculas->firstItem() + $index }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-mono hidden sm:table-cell">{{ $matricula->estudiante->dni }}</td>
                                            <td class="px-6 py-4">
                                                <div class="text-sm font-bold text-gray-800">
                                                    {{ $matricula->estudiante->apellido_paterno }} {{ $matricula->estudiante->apellido_materno }}, {{ $matricula->estudiante->nombres }}
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    {{ $matricula->estudiante->apoderado ? 'Apoderado: ' . $matricula->estudiante->apoderado->nombres . ' ' . $matricula->estudiante->apoderado->apellido_paterno : 'Sin apoderado asignado' }}
                                                </div>
                                                <div class="text-xs text-gray-400 md:hidden mt-0.5">
                                                    {{ $matricula->gradoSeccion->grado->nombre }} - {{ $matricula->gradoSeccion->seccion->nombre }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 hidden md:table-cell">
                                                {{ $matricula->gradoSeccion->grado->nombre }} - {{ $matricula->gradoSeccion->seccion->nombre }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full 
                                                    {{ $matricula->estado === 'matriculado' ? 'bg-blue-100 text-blue-800' : ($matricula->estado === 'promovido' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800') }}">
                                                    {{ ucfirst($matricula->estado) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                                <button @click="showEditModal = true; 
                                                    editEstudiante = {{ json_encode($matricula->estudiante) }}; 
                                                    editApoderado = {{ json_encode($matricula->estudiante->apoderado) }};
                                                    editPadre = {{ json_encode($matricula->estudiante->padre) }};
                                                    editMadre = {{ json_encode($matricula->estudiante->madre) }};
                                                    editSexo = editEstudiante.sexo;" 
                                                    class="text-indigo-600 hover:text-indigo-900 font-bold">Editar</button>
                                                
                                                @if($matricula->estado === 'matriculado')
                                                    |
                                                    <button @click="showMoverModal = true; selectedMatricula = {{ json_encode([
                                                        'estudiante_id' => $matricula->estudiante->id,
                                                        'nombre' => $matricula->estudiante->nombres . ' ' . $matricula->estudiante->apellido_paterno,
                                                        'grado_id' => $matricula->gradoSeccion->grado_id,
                                                        'seccion_actual_id' => $matricula->grado_seccion_id,
                                                    ]) }}" class="text-blue-600 hover:text-blue-900 font-bold">Mover</button>
                                                    |
                                                    <form action="{{ route('admin.estudiantes.retirar', $matricula->estudiante) }}" method="POST" class="inline" onsubmit="return confirm('¿Retirar estudiante del año lectivo?');">
                                                        @csrf
                                                        <button type="submit" class="text-red-600 hover:text-red-900 font-bold">Retirar</button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="p-4 border-t">
                            {{ $matriculas->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Ventana Modal de Registro -->
        <div x-show="showModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" x-cloak>
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showModal = false" aria-hidden="true"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                
                <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b">
                        <h3 class="text-xl leading-6 font-bold text-gray-900 mb-4">Registrar Nuevo Estudiante</h3>
                        
                        <form action="{{ route('admin.estudiantes.store') }}" method="POST" id="crearEstudianteForm" x-data="{
                            nivel: '{{ old('nivel') }}', 
                            grado_id: '{{ old('grado_id') }}',
                            grados: {{ $grados->toJson() }},
                            gradoSecciones: {{ $gradoSecciones->map(function($gs) { 
                                return [
                                    'id' => $gs->id, 
                                    'grado_id' => $gs->grado_id, 
                                    'seccion_nombre' => $gs->seccion->nombre,
                                    'nivel' => $gs->grado->nivel
                                ]; 
                            })->toJson() }},
                            get filteredGrados() {
                                return this.nivel ? this.grados.filter(g => g.nivel === this.nivel) : [];
                            },
                            get filteredSecciones() {
                                return this.grado_id ? this.gradoSecciones.filter(gs => gs.grado_id == this.grado_id) : [];
                            }
                        }">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="md:col-span-2 mb-2 pb-2 border-b">
                                    <h4 class="font-extrabold text-indigo-700 uppercase tracking-wide text-xs">1. Datos del Estudiante</h4>
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700">DNI *</label>
                                    <input type="text" name="dni" value="{{ old('dni') }}" required maxlength="8" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm @error('dni') border-red-500 @enderror">
                                    @error('dni') <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700">Cód. Estudiante (SIAGIE)</label>
                                    <input type="text" name="codigo_estudiante" value="{{ old('codigo_estudiante') }}" maxlength="20" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm @error('codigo_estudiante') border-red-500 @enderror" placeholder="Opcional">
                                    @error('codigo_estudiante') <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700">Nombres *</label>
                                    <input type="text" name="nombres" value="{{ old('nombres') }}" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm uppercase-input">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700">Apellido Paterno *</label>
                                    <input type="text" name="apellido_paterno" value="{{ old('apellido_paterno') }}" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm uppercase-input">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700">Apellido Materno *</label>
                                    <input type="text" name="apellido_materno" value="{{ old('apellido_materno') }}" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm uppercase-input">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700">Fecha de Nacimiento *</label>
                                    <input type="date" name="fecha_nacimiento" value="{{ old('fecha_nacimiento') }}" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700">Sexo *</label>
                                    <select name="sexo" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-50">
                                        <option value="M" {{ old('sexo') == 'M' ? 'selected' : '' }}>Masculino</option>
                                        <option value="F" {{ old('sexo') == 'F' ? 'selected' : '' }}>Femenino</option>
                                    </select>
                                </div>

                                <div class="md:col-span-2 mt-4 mb-2 pb-2 border-b">
                                    <h4 class="font-extrabold text-indigo-700 uppercase tracking-wide text-xs">2. Asignación Académica</h4>
                                </div>
                                
                                <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700">Nivel *</label>
                                        <select name="nivel" x-model="nivel" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-50">
                                            <option value="">-- Seleccione --</option>
                                            <option value="primaria">Primaria</option>
                                            <option value="secundaria">Secundaria</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700">Grado *</label>
                                        <select name="grado_id" x-model="grado_id" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-50" :disabled="!nivel">
                                            <option value="">-- Seleccione --</option>
                                            <template x-for="grado in filteredGrados" :key="grado.id">
                                                <option :value="grado.id" x-text="grado.nombre" :selected="grado.id == grado_id"></option>
                                            </template>
                                        </select>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-bold text-gray-700">Sección *</label>
                                        <select name="grado_seccion_id" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-indigo-50 border-indigo-300" :disabled="!grado_id">
                                            <option value="">-- Seleccione Sección --</option>
                                            <template x-for="gs in filteredSecciones" :key="gs.id">
                                                <option :value="gs.id" x-text="'Sección ' + gs.seccion_nombre" :selected="gs.id == {{ old('grado_seccion_id', 0) }}"></option>
                                            </template>
                                        </select>
                                        @error('grado_seccion_id') <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                                    </div>
                                </div>

                                <!-- Datos Primaria (Colegio Inicial, Padre, Madre) -->
                                <div class="md:col-span-2 space-y-4 my-2" x-show="nivel === 'primaria'" x-cloak x-transition>
                                    <div class="p-4 bg-yellow-50 rounded-lg border border-yellow-200">
                                        <label class="block text-sm font-bold text-yellow-800">Colegio Inicial de Procedencia</label>
                                        <input type="text" name="colegio_inicial" value="{{ old('colegio_inicial') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm uppercase-input" placeholder="Ej. PRONOEI Rayito de Sol">
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <!-- Datos del Padre -->
                                        <div class="p-4 bg-blue-50 rounded-lg border border-blue-200 space-y-3">
                                            <h5 class="font-extrabold text-blue-800 uppercase tracking-wide text-xs">Datos del Padre</h5>
                                            <div>
                                                <label class="block text-xs font-bold text-gray-700">DNI del Padre</label>
                                                <input type="text" name="padre_dni" value="{{ old('padre_dni') }}" maxlength="8" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" placeholder="8 dígitos">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-bold text-gray-700">Nombres y Apellidos del Padre</label>
                                                <input type="text" name="padre_nombres" value="{{ old('padre_nombres') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm uppercase-input" placeholder="Nombre completo">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-bold text-gray-700">Teléfono del Padre</label>
                                                <input type="text" name="padre_telefono" value="{{ old('padre_telefono') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" placeholder="Ej. 987654321">
                                            </div>
                                        </div>

                                        <!-- Datos de la Madre -->
                                        <div class="p-4 bg-pink-50 rounded-lg border border-pink-200 space-y-3">
                                            <h5 class="font-extrabold text-pink-800 uppercase tracking-wide text-xs">Datos de la Madre</h5>
                                            <div>
                                                <label class="block text-xs font-bold text-gray-700">DNI de la Madre</label>
                                                <input type="text" name="madre_dni" value="{{ old('madre_dni') }}" maxlength="8" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" placeholder="8 dígitos">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-bold text-gray-700">Nombres y Apellidos de la Madre</label>
                                                <input type="text" name="madre_nombres" value="{{ old('madre_nombres') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm uppercase-input" placeholder="Nombre completo">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-bold text-gray-700">Teléfono de la Madre</label>
                                                <input type="text" name="madre_telefono" value="{{ old('madre_telefono') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" placeholder="Ej. 987654321">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="md:col-span-2 mt-4 mb-2 pb-2 border-b flex justify-between items-center">
                                    <h4 class="font-extrabold text-gray-500 uppercase tracking-wide text-xs">3. Datos del Apoderado (Opcional)</h4>
                                </div>

                                <div>
                                    <label class="block text-sm font-bold text-gray-600">DNI del Apoderado</label>
                                    <input type="text" name="apoderado_dni" value="{{ old('apoderado_dni') }}" maxlength="8" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-600">Parentesco</label>
                                    <select name="apoderado_parentesco" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-50">
                                        <option value="APODERADO" {{ old('apoderado_parentesco') == 'APODERADO' ? 'selected' : '' }}>Apoderado</option>
                                        <option value="PADRE" {{ old('apoderado_parentesco') == 'PADRE' ? 'selected' : '' }}>Padre</option>
                                        <option value="MADRE" {{ old('apoderado_parentesco') == 'MADRE' ? 'selected' : '' }}>Madre</option>
                                        <option value="TUTOR" {{ old('apoderado_parentesco') == 'TUTOR' ? 'selected' : '' }}>Tutor Legal</option>
                                        <option value="ABUELO/A" {{ old('apoderado_parentesco') == 'ABUELO/A' ? 'selected' : '' }}>Abuelo/a</option>
                                        <option value="OTRO" {{ old('apoderado_parentesco') == 'OTRO' ? 'selected' : '' }}>Otro</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-600">Nombres del Apoderado</label>
                                    <input type="text" name="apoderado_nombres" value="{{ old('apoderado_nombres') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm uppercase-input">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-600">Apellido Paterno</label>
                                    <input type="text" name="apoderado_apellido_paterno" value="{{ old('apoderado_apellido_paterno') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm uppercase-input">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-600">Apellido Materno</label>
                                    <input type="text" name="apoderado_apellido_materno" value="{{ old('apoderado_apellido_materno') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm uppercase-input">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-600">Teléfono</label>
                                    <input type="text" name="apoderado_telefono" value="{{ old('apoderado_telefono') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-bold text-gray-600">Dirección / Domicilio</label>
                                    <input type="text" name="apoderado_direccion" value="{{ old('apoderado_direccion') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm uppercase-input">
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t">
                        <button type="submit" form="crearEstudianteForm" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-yellow-600 text-base font-medium text-white hover:bg-yellow-700 sm:ml-3 sm:w-auto sm:text-sm">
                            Guardar y Matricular
                        </button>
                        <button type="button" @click="showModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ventana Modal de Edición -->
        <div x-show="showEditModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-edit" role="dialog" aria-modal="true" x-cloak>
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showEditModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showEditModal = false" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div x-show="showEditModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b">
                        <h3 class="text-xl leading-6 font-bold text-gray-900 mb-4">Editar Estudiante</h3>
                        
                        <form :action="editEstudiante ? '/admin/estudiantes/' + editEstudiante.id : '#'" method="POST" id="editarEstudianteForm">
                            @csrf
                            @method('PUT')
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="md:col-span-2 mb-2 pb-2 border-b">
                                    <h4 class="font-extrabold text-indigo-700 uppercase tracking-wide text-xs">Datos del Estudiante</h4>
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700">DNI *</label>
                                    <input type="text" name="dni" :value="editEstudiante ? editEstudiante.dni : ''" required maxlength="8" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm @error('dni') border-red-500 @enderror font-mono">
                                    @error('dni') <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700">Cód. Estudiante (SIAGIE)</label>
                                    <input type="text" name="codigo_estudiante" :value="editEstudiante ? editEstudiante.codigo_estudiante : ''" maxlength="20" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" placeholder="Opcional">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700">Nombres *</label>
                                    <input type="text" name="nombres" :value="editEstudiante ? editEstudiante.nombres : ''" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm uppercase-input">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700">Apellido Paterno *</label>
                                    <input type="text" name="apellido_paterno" :value="editEstudiante ? editEstudiante.apellido_paterno : ''" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm uppercase-input">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700">Apellido Materno *</label>
                                    <input type="text" name="apellido_materno" :value="editEstudiante ? editEstudiante.apellido_materno : ''" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm uppercase-input">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700">Fecha de Nacimiento *</label>
                                    <input type="date" name="fecha_nacimiento" :value="editEstudiante && editEstudiante.fecha_nacimiento ? editEstudiante.fecha_nacimiento.split('T')[0] : ''" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700">Sexo *</label>
                                    <select name="sexo" x-model="editSexo" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-50" required>
                                        <option value="M">Masculino</option>
                                        <option value="F">Femenino</option>
                                    </select>
                                </div>

                                <!-- Datos Primaria (Colegio Inicial, Padre, Madre) para Edición -->
                                <div class="md:col-span-2 space-y-4 my-2" x-show="editEstudiante && editEstudiante.nivel === 'primaria'" x-cloak x-transition>
                                    <div class="p-4 bg-yellow-50 rounded-lg border border-yellow-200">
                                        <label class="block text-sm font-bold text-yellow-800">Colegio Inicial de Procedencia</label>
                                        <input type="text" name="colegio_inicial" :value="editEstudiante ? editEstudiante.colegio_inicial : ''" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm uppercase-input" placeholder="Ej. PRONOEI Rayito de Sol">
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <!-- Datos del Padre -->
                                        <div class="p-4 bg-blue-50 rounded-lg border border-blue-200 space-y-3">
                                            <h5 class="font-extrabold text-blue-800 uppercase tracking-wide text-xs">Datos del Padre</h5>
                                            <div>
                                                <label class="block text-xs font-bold text-gray-700">DNI del Padre</label>
                                                <input type="text" name="padre_dni" :value="editPadre ? editPadre.dni : ''" maxlength="8" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" placeholder="8 dígitos">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-bold text-gray-700">Nombres y Apellidos del Padre</label>
                                                <input type="text" name="padre_nombres" :value="editPadre ? (editPadre.apellido_paterno + ' ' + (editPadre.apellido_materno || '') + ', ' + editPadre.nombres) : ''" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm uppercase-input" placeholder="Nombre completo">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-bold text-gray-700">Teléfono del Padre</label>
                                                <input type="text" name="padre_telefono" :value="editPadre ? editPadre.telefono : ''" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" placeholder="Ej. 987654321">
                                            </div>
                                        </div>

                                        <!-- Datos de la Madre -->
                                        <div class="p-4 bg-pink-50 rounded-lg border border-pink-200 space-y-3">
                                            <h5 class="font-extrabold text-pink-800 uppercase tracking-wide text-xs">Datos de la Madre</h5>
                                            <div>
                                                <label class="block text-xs font-bold text-gray-700">DNI de la Madre</label>
                                                <input type="text" name="madre_dni" :value="editMadre ? editMadre.dni : ''" maxlength="8" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" placeholder="8 dígitos">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-bold text-gray-700">Nombres y Apellidos de la Madre</label>
                                                <input type="text" name="madre_nombres" :value="editMadre ? (editMadre.apellido_paterno + ' ' + (editMadre.apellido_materno || '') + ', ' + editMadre.nombres) : ''" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm uppercase-input" placeholder="Nombre completo">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-bold text-gray-700">Teléfono de la Madre</label>
                                                <input type="text" name="madre_telefono" :value="editMadre ? editMadre.telefono : ''" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" placeholder="Ej. 987654321">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="md:col-span-2 mt-4 mb-2 pb-2 border-b flex justify-between items-center">
                                    <h4 class="font-extrabold text-gray-500 uppercase tracking-wide text-xs">Datos del Apoderado (Opcional)</h4>
                                </div>

                                <div>
                                    <label class="block text-sm font-bold text-gray-600">DNI del Apoderado</label>
                                    <input type="text" name="apoderado_dni" :value="editApoderado ? editApoderado.dni : ''" maxlength="8" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-600">Parentesco</label>
                                    <select name="apoderado_parentesco" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-50">
                                        <option value="APODERADO" :selected="editApoderado && editApoderado.parentesco === 'APODERADO'">Apoderado</option>
                                        <option value="PADRE" :selected="editApoderado && editApoderado.parentesco === 'PADRE'">Padre</option>
                                        <option value="MADRE" :selected="editApoderado && editApoderado.parentesco === 'MADRE'">Madre</option>
                                        <option value="TUTOR" :selected="editApoderado && editApoderado.parentesco === 'TUTOR'">Tutor Legal</option>
                                        <option value="ABUELO/A" :selected="editApoderado && editApoderado.parentesco === 'ABUELO/A'">Abuelo/a</option>
                                        <option value="OTRO" :selected="editApoderado && editApoderado.parentesco === 'OTRO'">Otro</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-600">Nombres del Apoderado</label>
                                    <input type="text" name="apoderado_nombres" :value="editApoderado ? editApoderado.nombres : ''" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm uppercase-input">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-600">Apellido Paterno</label>
                                    <input type="text" name="apoderado_apellido_paterno" :value="editApoderado ? editApoderado.apellido_paterno : ''" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm uppercase-input">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-600">Apellido Materno</label>
                                    <input type="text" name="apoderado_apellido_materno" :value="editApoderado ? editApoderado.apellido_materno : ''" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm uppercase-input">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-600">Teléfono</label>
                                    <input type="text" name="apoderado_telefono" :value="editApoderado ? editApoderado.telefono : ''" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-bold text-gray-600">Dirección / Domicilio</label>
                                    <input type="text" name="apoderado_direccion" :value="editApoderado ? editApoderado.direccion : ''" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm uppercase-input">
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t">
                        <button type="submit" form="editarEstudianteForm" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 sm:ml-3 sm:w-auto sm:text-sm">
                            Guardar Cambios
                        </button>
                        <button type="button" @click="showEditModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ventana Modal para Mover Estudiante -->
        <div x-show="showMoverModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-mover" role="dialog" aria-modal="true" x-cloak>
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showMoverModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showMoverModal = false" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div x-show="showMoverModal" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                                <h3 class="text-xl leading-6 font-bold text-gray-900 mb-2">Mover Estudiante de Sección</h3>
                                <p class="text-sm text-gray-500 mb-4">Mueva a <span class="font-bold text-indigo-700" x-text="selectedMatricula ? selectedMatricula.nombre : ''"></span> a otra sección de su mismo grado.</p>
                                
                                <form :action="selectedMatricula ? '/admin/estudiantes/' + selectedMatricula.estudiante_id + '/mover' : '#'" method="POST" id="moverEstudianteForm">
                                    @csrf
                                    @method('PATCH')
                                    <div class="mb-4" x-data="{
                                        gradoSecciones: {{ $gradoSecciones->map(function($gs) {
                                            return [
                                                'id' => $gs->id,
                                                'grado_id' => $gs->grado_id,
                                                'seccion_nombre' => $gs->seccion->nombre
                                            ];
                                        })->toJson() }},
                                        get seccionesMismoGrado() {
                                            if (!selectedMatricula) return [];
                                            return this.gradoSecciones.filter(gs => gs.grado_id === selectedMatricula.grado_id && gs.id !== selectedMatricula.seccion_actual_id);
                                        }
                                    }">
                                        <label class="block text-sm font-bold text-gray-700">Nueva Sección</label>
                                        <select name="grado_seccion_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-yellow-500 rounded-md shadow-sm bg-gray-50" required>
                                            <option value="">-- Seleccione Sección --</option>
                                            <template x-for="gs in seccionesMismoGrado" :key="gs.id">
                                                <option :value="gs.id" x-text="'Sección ' + gs.seccion_nombre"></option>
                                            </template>
                                        </select>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t">
                        <button type="submit" form="moverEstudianteForm" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 sm:ml-3 sm:w-auto sm:text-sm">
                            Mover Estudiante
                        </button>
                        <button type="button" @click="showMoverModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Script para preservar posición de scroll tras guardar cambios y reabrir modal si hay errores -->
    <script>
        // Guardar posición de scroll al enviar formularios de tipo POST
        document.addEventListener('submit', (e) => {
            const form = e.target;
            if (form && form.method && form.method.toLowerCase() === 'post') {
                localStorage.setItem('estudiantes_scroll_y', window.scrollY);
                const tableContainer = document.getElementById('students-table-container');
                if (tableContainer) {
                    localStorage.setItem('estudiantes_table_scroll_y', tableContainer.scrollTop);
                }
            }
        });

        // Restaurar posición de scroll al cargar la página
        document.addEventListener('DOMContentLoaded', () => {
            const scrollY = localStorage.getItem('estudiantes_scroll_y');
            const tableScrollY = localStorage.getItem('estudiantes_table_scroll_y');
            
            if (scrollY !== null) {
                window.scrollTo(0, parseInt(scrollY));
                localStorage.removeItem('estudiantes_scroll_y');
            }
            
            if (tableScrollY !== null) {
                const tableContainer = document.getElementById('students-table-container');
                if (tableContainer) {
                    tableContainer.scrollTop = parseInt(tableScrollY);
                }
                localStorage.removeItem('estudiantes_table_scroll_y');
            }

            // Reabrir modal de registro si hay errores de validación
            @if($errors->has('dni') || $errors->has('nombres') || $errors->has('grado_seccion_id') || (session('error') && old('dni')))
                const alpineDiv = document.querySelector('[x-data]');
                if(alpineDiv && alpineDiv._x_dataStack) {
                    alpineDiv._x_dataStack[0].showModal = true;
                }
            @endif
        });
    </script>
</x-app-layout>
