@php
    $gradosUnicos    = $gradoSeccionesDisponibles->map(fn($gs) => $gs->grado)->unique('id')->sortBy('orden')->values();
    $seccionesUnicas = $gradoSeccionesDisponibles->map(fn($gs) => $gs->seccion)->unique('id')->sortBy('nombre')->values();
    $gsLookup = [];
    foreach ($gradoSeccionesDisponibles as $gs) {
        $gsLookup[$gs->grado_id][$gs->seccion_id] = $gs;
    }
    $cursosDocente = $docente->esEspecialista() ? $docente->cursos : collect();
    $cursoUnico    = $cursosDocente->count() === 1 ? $cursosDocente->first() : null;
    $multiCurso    = $cursosDocente->count() > 1;
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Gestión Académica: <span class="text-indigo-600">{{ $docente->apellido_paterno }} {{ $docente->apellido_materno }}, {{ $docente->nombres }}</span>
        </h2>
    </x-slot>

    {{-- x-data wraps EVERYTHING including the modal --}}
    <div x-data="asignacionDocente()" class="py-12">

        {{-- Modal de conflictos (DENTRO del x-data) --}}
        <div x-show="modalVisible"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             class="fixed inset-0 z-50 flex items-center justify-center px-4 bg-black/50"
             style="display:none">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden border-2 border-black"
                 @click.stop>
                <div class="bg-red-600 px-5 py-4 flex items-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    </svg>
                    <h3 class="text-base font-extrabold text-white">Conflicto de Asignación Detectado</h3>
                </div>
                <div class="p-5">
                    <p class="text-sm text-gray-600 mb-3">
                        Las siguientes secciones ya tienen otro docente asignado para el mismo curso:
                    </p>
                    <ul class="space-y-2 max-h-56 overflow-y-auto">
                        <template x-for="c in conflictos" :key="c.seccion">
                            <li class="bg-red-50 border border-red-200 rounded-lg px-3 py-2.5">
                                <p class="font-bold text-red-800 text-sm" x-text="c.seccion"></p>
                                <p class="text-xs text-red-600 mt-0.5" x-text="c.mensaje"></p>
                            </li>
                        </template>
                    </ul>
                    <div class="mt-8 flex gap-2 justify-end">
                        <button @click="modalVisible = false"
                            class="px-4 py-2 text-sm font-bold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
                            Cancelar
                        </button>
                        <button @click="forzarEnvio()"
                            class="px-4 py-2 text-sm font-bold text-white bg-yellow-600 hover:bg-yellow-700 rounded-lg transition">
                            Continuar de todas formas
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-3 gap-6">

            {{-- Panel lateral perfil --}}
            <div class="bg-white shadow-sm sm:rounded-lg md:col-span-1 h-fit border border-gray-200">
                <div class="{{ $docente->esPolidocente() ? 'bg-amber-50 border-b border-amber-100' : 'bg-indigo-50 border-b border-indigo-100' }} px-6 py-4">
                    <h3 class="text-lg font-bold {{ $docente->esPolidocente() ? 'text-amber-800' : 'text-indigo-800' }}">
                        Perfil del Docente
                    </h3>
                </div>
                <div class="p-6">
                    <p class="text-sm text-gray-600 mb-1"><strong>DNI:</strong> <span class="font-mono">{{ $docente->dni }}</span></p>
                    <p class="text-sm text-gray-600 mb-1"><strong>Nombres:</strong> {{ $docente->apellido_paterno }} {{ $docente->apellido_materno }}, {{ $docente->nombres }}</p>
                    <p class="text-sm text-gray-600 mb-4"><strong>Correo:</strong> {{ $docente->user->email }}</p>
                    <div class="flex gap-2 flex-wrap mb-4">
                        <span class="px-3 py-1 rounded-full text-xs font-bold {{ $docente->nivel === 'primaria' ? 'bg-green-100 text-green-800' : 'bg-purple-100 text-purple-800' }}">
                            {{ ucfirst($docente->nivel) }}
                        </span>
                        <span class="px-3 py-1 rounded-full text-xs font-bold {{ $docente->esPolidocente() ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800' }}">
                            {{ ucfirst($docente->tipo) }}
                        </span>
                    </div>
                    <div class="border-t pt-4">
                        <p class="text-sm font-bold text-gray-700 mb-2">{{ $docente->esPolidocente() ? 'Modalidad:' : 'Curso(s) que imparte:' }}</p>
                        @if($docente->esPolidocente())
                            <p class="text-sm text-amber-700 bg-amber-50 px-3 py-2 rounded-lg border border-amber-200 font-semibold">Todos los cursos del grado</p>
                        @else
                            <div class="flex flex-wrap gap-1.5">
                                @forelse($docente->cursos as $curso)
                                    <span class="bg-indigo-100 text-indigo-800 font-bold px-3 py-1 rounded-full text-xs border border-indigo-200">{{ $curso->nombre }}</span>
                                @empty
                                    <p class="text-xs text-red-500 font-semibold">Sin cursos asignados</p>
                                @endforelse
                            </div>
                        @endif
                    </div>
                    <div class="mt-6 text-center">
                        <a href="{{ route('admin.docentes.index') }}" class="text-sm text-indigo-600 hover:underline font-semibold">&larr; Volver al Directorio</a>
                    </div>
                </div>
            </div>

            {{-- Panel principal --}}
            <div class="md:col-span-2 space-y-6">

                @if(session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded shadow-sm font-medium">
                        {{ session('success') }}
                    </div>
                @endif
                @if(session('conflictos'))
                    <div class="bg-red-50 border border-red-300 text-red-700 px-4 py-4 rounded shadow-sm">
                        <p class="font-bold mb-2">⚠ Algunas secciones no pudieron asignarse:</p>
                        <ul class="list-disc pl-5 text-sm space-y-1">
                            @foreach(session('conflictos') as $c)<li>{{ $c }}</li>@endforeach
                        </ul>
                    </div>
                @endif

                <div class="bg-white shadow-sm sm:rounded-lg border border-gray-200">
                    <div class="bg-gray-50 px-6 py-4 border-b">
                        <h3 class="text-lg font-bold text-gray-800">Añadir Secciones a Cargo</h3>
                        <p class="text-xs text-gray-500 mt-1">
                            @if($docente->esPolidocente())
                                Selecciona el grado/sección que atenderá como único responsable.
                            @elseif($multiCurso)
                                Selecciona el curso y luego las secciones donde lo impartirá.
                            @else
                                Selecciona las secciones donde impartirá <strong>{{ $cursoUnico?->nombre }}</strong>.
                            @endif
                        </p>
                    </div>
                    <div class="p-6">
                        @if(!$anoActivo)
                            <p class="text-red-500 text-sm font-bold bg-red-50 p-3 rounded">No hay un año lectivo activo.</p>
                        @elseif($gradoSeccionesDisponibles->isEmpty())
                            <p class="text-yellow-600 text-sm font-bold bg-yellow-50 p-3 rounded border border-yellow-200">No hay secciones disponibles para este docente.</p>
                        @else
                            <form id="formAsignacion"
                                  action="{{ route('admin.docentes.asignar', $docente) }}"
                                  method="POST"
                                  @submit.prevent="submitConValidacion()">
                                @csrf

                                {{-- Selector de curso para especialistas con múltiples cursos --}}
                                @if($multiCurso)
                                    <div class="mb-5 p-4 bg-indigo-50 rounded-lg border border-indigo-200">
                                        <label class="block text-sm font-bold text-indigo-800 mb-2">
                                            1. Selecciona el curso a asignar
                                        </label>
                                        <div class="flex flex-wrap gap-2">
                                            @foreach($cursosDocente as $curso)
                                                <button type="button"
                                                    @click="cursoId = {{ $curso->id }}; cursoNombre = '{{ addslashes($curso->nombre) }}'"
                                                    :class="cursoId === {{ $curso->id }}
                                                        ? 'bg-indigo-600 text-white border-indigo-600 ring-2 ring-indigo-300'
                                                        : 'bg-white text-indigo-700 border-indigo-300 hover:bg-indigo-50'"
                                                    class="px-4 py-2 text-sm font-bold rounded-lg border transition">
                                                    {{ $curso->nombre }}
                                                </button>
                                            @endforeach
                                        </div>
                                        <p x-show="cursoId" class="text-xs text-indigo-700 mt-2 font-semibold">
                                            Curso seleccionado: <span x-text="cursoNombre" class="font-extrabold"></span>
                                        </p>
                                        <p x-show="!cursoId" class="text-xs text-red-500 mt-2 font-semibold">
                                            ⚠ Debes seleccionar un curso primero.
                                        </p>
                                    </div>
                                @endif

                                {{-- Tabla grados × secciones --}}
                                @if($gradosUnicos->isNotEmpty() && $seccionesUnicas->isNotEmpty())
                                    <div class="mb-4">
                                        <div class="flex items-center justify-between mb-3">
                                            <p class="text-sm font-bold text-gray-700">
                                                @if($multiCurso) 2. @endif Selecciona las secciones
                                            </p>
                                            <div class="flex items-center gap-3">
                                                <span class="text-xs text-gray-500">
                                                    <span x-text="seleccionados.length" class="font-bold text-indigo-600"></span> seleccionada(s)
                                                </span>
                                                <button type="button" @click="seleccionarTodas()"
                                                    class="text-xs text-indigo-600 hover:text-indigo-800 font-semibold underline">
                                                    Seleccionar todas
                                                </button>
                                                <button type="button" @click="limpiarSeleccion()"
                                                    class="text-xs text-gray-400 hover:text-gray-600 font-semibold underline">
                                                    Limpiar
                                                </button>
                                            </div>
                                        </div>

                                        <div class="overflow-x-auto rounded-lg border border-gray-200">
                                            <table class="min-w-full text-sm">
                                                <thead>
                                                    <tr class="bg-blue-600">
                                                        <th class="px-4 py-3 text-left text-xs font-bold text-white uppercase w-24 border-r border-blue-500">
                                                            Sección
                                                        </th>
                                                        @foreach($gradosUnicos as $grado)
                                                            <th class="px-3 py-3 text-center text-xs font-bold text-white uppercase">
                                                                {{ $grado->nombre }}
                                                            </th>
                                                        @endforeach
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-gray-100">
                                                    @foreach($seccionesUnicas as $seccion)
                                                        <tr class="hover:bg-indigo-50 transition">
                                                            <td class="px-4 py-3 font-bold text-gray-700 bg-gray-50 border-r border-gray-200 text-sm">
                                                                {{ $seccion->nombre }}
                                                            </td>
                                                            @foreach($gradosUnicos as $grado)
                                                                <td class="px-3 py-3 text-center">
                                                                    @if(isset($gsLookup[$grado->id][$seccion->id]))
                                                                        @php $gs = $gsLookup[$grado->id][$seccion->id] @endphp
                                                                        <label class="inline-flex items-center justify-center cursor-pointer">
                                                                            <input type="checkbox"
                                                                                class="w-5 h-5 rounded border-2 border-gray-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer transition"
                                                                                :checked="seleccionados.includes({{ $gs->id }})"
                                                                                @change="toggleSeccion({{ $gs->id }}, $event.target.checked)">
                                                                        </label>
                                                                    @else
                                                                        <span class="text-gray-200 select-none">—</span>
                                                                    @endif
                                                                </td>
                                                            @endforeach
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @endif

                                <div class="mt-4 pt-4 border-t border-gray-100 flex items-center gap-3">
                                    <button type="submit"
                                        class="bg-yellow-600 hover:bg-yellow-700 text-white font-bold py-2.5 px-6 rounded shadow transition flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                        </svg>
                                        Asignar Secciones Seleccionadas
                                    </button>
                                    <span x-show="cargando" class="text-sm text-gray-500 italic animate-pulse">Verificando...</span>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>

                {{-- Asignaciones actuales --}}
                <div class="bg-white shadow-sm sm:rounded-lg border border-gray-200">
                    <div class="bg-gray-50 px-6 py-4 border-b">
                        <h3 class="text-lg font-bold text-gray-800">Secciones a Cargo — Año {{ $anoActivo?->anio ?? 'Sin año activo' }}</h3>
                        <p class="text-xs text-gray-500 mt-1">Para cambiar, remueve la asignación y vuelve a crearla.</p>
                    </div>
                    <div class="p-6">
                        @if($asignaciones->isEmpty())
                            <div class="text-center py-8 text-gray-500 bg-gray-50 rounded border border-dashed">
                                El docente no tiene secciones a cargo este año.
                            </div>
                        @else
                            <div class="overflow-hidden border border-gray-200 rounded-md">
                                <ul class="divide-y divide-gray-200">
                                    @foreach($asignaciones as $asignacion)
                                        <li class="p-4 flex justify-between items-center hover:bg-gray-50 transition">
                                            <div class="flex items-center gap-3">
                                                <div class="bg-indigo-100 text-indigo-700 px-3 py-2 rounded-lg font-bold text-sm shrink-0">
                                                    {{ $asignacion->gradoSeccion->grado->nombre }}
                                                </div>
                                                <div>
                                                    <span class="font-bold text-gray-900 block text-sm">Sección "{{ $asignacion->gradoSeccion->seccion->nombre }}"</span>
                                                    <span class="text-xs text-gray-500">
                                                        @if($docente->esPolidocente()) Todos los cursos (Polidocente)
                                                        @else Curso: <strong>{{ $asignacion->curso?->nombre ?? '—' }}</strong>
                                                        @endif
                                                    </span>
                                                </div>
                                            </div>
                                            <form action="{{ route('admin.docentes.desasignar', [$docente->id, $asignacion->id]) }}"
                                                  method="POST" onsubmit="return confirm('¿Remover esta asignación?');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="bg-red-50 text-red-600 hover:bg-red-100 border border-red-200 text-xs font-bold py-1.5 px-3 rounded transition">
                                                    Remover
                                                </button>
                                            </form>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    function asignacionDocente() {
        return {
            esEspecialista: {{ $docente->esEspecialista() ? 'true' : 'false' }},
            cursoId:        {{ $cursoUnico ? $cursoUnico->id : 'null' }},
            cursoNombre:    '{{ addslashes($cursoUnico?->nombre ?? '') }}',
            urlVerificar:   '{{ route('admin.docentes.verificarConflictos', $docente) }}',
            csrfToken:      document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            seleccionados:  [],
            conflictos:     [],
            modalVisible:   false,
            cargando:       false,
            forzar:         false,

            toggleSeccion(gsId, checked) {
                if (checked) {
                    if (!this.seleccionados.includes(gsId)) this.seleccionados = [...this.seleccionados, gsId];
                } else {
                    this.seleccionados = this.seleccionados.filter(id => id !== gsId);
                }
            },

            seleccionarTodas() {
                const ids = [{{ $gradoSeccionesDisponibles->pluck('id')->join(', ') }}];
                this.seleccionados = [...ids];
                document.querySelectorAll('#formAsignacion input[type=checkbox]').forEach(cb => cb.checked = true);
            },

            limpiarSeleccion() {
                this.seleccionados = [];
                document.querySelectorAll('#formAsignacion input[type=checkbox]').forEach(cb => cb.checked = false);
            },

            buildPayload() {
                return this.seleccionados.map(gsId => ({
                    grado_seccion_id: gsId,
                    curso_id: this.esEspecialista ? this.cursoId : null,
                }));
            },

            async submitConValidacion() {
                if (this.seleccionados.length === 0) {
                    alert('Debes seleccionar al menos una sección.');
                    return;
                }
                if (this.esEspecialista && !this.cursoId) {
                    alert('Debes seleccionar el curso antes de asignar.');
                    return;
                }

                if (this.forzar) {
                    this.enviar();
                    return;
                }

                if (this.esEspecialista) {
                    this.cargando = true;
                    try {
                        const resp = await fetch(this.urlVerificar, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken,
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({ asignaciones: this.buildPayload() }),
                        });
                        if (!resp.ok) throw new Error('HTTP ' + resp.status);
                        const data = await resp.json();
                        if (data.conflictos && data.conflictos.length > 0) {
                            this.conflictos = data.conflictos;
                            this.modalVisible = true;
                            return;
                        }
                    } catch (e) {
                        console.error('Error verificando conflictos:', e);
                    } finally {
                        this.cargando = false;
                    }
                }

                this.enviar();
            },

            forzarEnvio() {
                this.modalVisible = false;
                this.forzar = true;
                this.enviar();
            },

            enviar() {
                const form = document.getElementById('formAsignacion');
                form.querySelectorAll('input[name^="asignaciones"]').forEach(el => el.remove());

                this.buildPayload().forEach((a, i) => {
                    const addInput = (name, value) => {
                        const inp = document.createElement('input');
                        inp.type = 'hidden';
                        inp.name = name;
                        inp.value = value;
                        form.appendChild(inp);
                    };
                    addInput(`asignaciones[${i}][grado_seccion_id]`, a.grado_seccion_id);
                    if (a.curso_id) addInput(`asignaciones[${i}][curso_id]`, a.curso_id);
                });

                form.submit();
            },
        };
    }
    </script>
    @endpush
</x-app-layout>
