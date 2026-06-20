<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Gestión de Bimestres Académicos') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="{ showModal: false, showEditModal: false, showPublishModal: false, editNumero: '', editFechaInicio: '', editFechaFin: '', editRoute: '', publishRoute: '', publishBimestre: '' }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                <div>
                    <h3 class="text-2xl font-bold text-gray-800">Bimestres del Año {{ $anoActivo ? $anoActivo->anio : '' }}</h3>
                    <p class="text-gray-600">Apertura y cierra los bimestres para habilitar el registro de calificaciones de los docentes.</p>
                </div>
                @if($anoActivo && $bimestres->count() < 4)
                <button @click="showModal = true" class="bg-yellow-600 hover:bg-yellow-700 text-white font-bold py-3 px-6 rounded shadow transition flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    Aperturar Bimestre {{ $bimestres->count() + 1 }}
                </button>
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

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <div class="p-0 text-gray-900">
                    @if(!$anoActivo)
                        <div class="text-center py-10 bg-red-50 text-red-700 font-bold border-b border-red-200">
                            No hay un año lectivo activo configurado.
                        </div>
                    @elseif($bimestres->isEmpty())
                        <div class="text-center py-16 bg-gray-50 text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <p class="text-lg font-bold text-gray-700">Ningún bimestre aperturado aún.</p>
                            <p class="text-sm">Apertura el Primer Bimestre para comenzar el ciclo de evaluación.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto overflow-y-auto max-h-[70vh]">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-100 sticky top-0 z-10">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Bimestre</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Fechas Programadas</th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Estado Actual</th>
                                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-600 uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($bimestres as $bimestre)
                                        <tr class="hover:bg-gray-50 transition">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="text-lg font-bold text-gray-800">Bimestre {{ $bimestre->numero }}</span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                                <span class="font-bold">Inicio:</span> {{ \Carbon\Carbon::parse($bimestre->fecha_inicio)->format('d/m/Y') }} <br>
                                                <span class="font-bold">Fin:</span> {{ \Carbon\Carbon::parse($bimestre->fecha_fin)->format('d/m/Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($bimestre->estado === 'abierto')
                                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-green-100 text-green-800 border border-green-200">
                                                        Abierto
                                                    </span>
                                                @else
                                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-red-100 text-red-800 border border-red-200">
                                                        Cerrado
                                                    </span>
                                                    <p class="text-xs text-gray-500 mt-1 mb-2">Cerrado el {{ \Carbon\Carbon::parse($bimestre->cerrado_en)->format('d/m/Y') }}</p>
                                                @endif
                                                <div class="mt-2 space-y-1">
                                                    <p class="text-xs font-semibold text-gray-700">Visibilidad de notas:</p>
                                                    <p class="text-xs">
                                                        <span class="font-bold">Primaria:</span> 
                                                        @if($bimestre->notas_publicadas_primaria)
                                                            <span class="text-green-600 font-bold">Públicas</span>
                                                        @else
                                                            <span class="text-gray-500 font-bold">Ocultas</span>
                                                        @endif
                                                    </p>
                                                    <p class="text-xs">
                                                        <span class="font-bold">Secundaria:</span> 
                                                        @if($bimestre->notas_publicadas_secundaria)
                                                            <span class="text-green-600 font-bold">Públicas</span>
                                                        @else
                                                            <span class="text-gray-500 font-bold">Ocultas</span>
                                                        @endif
                                                    </p>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <div class="flex items-center justify-end gap-2 flex-wrap">
                                                    <button @click="showEditModal = true; editNumero = '{{ $bimestre->numero }}'; editFechaInicio = '{{ $bimestre->fecha_inicio }}'; editFechaFin = '{{ $bimestre->fecha_fin }}'; editRoute = '{{ route('admin.bimestres.update', $bimestre) }}'" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-2 px-4 rounded shadow-sm border border-gray-300 transition flex items-center gap-1">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                                        Fechas
                                                    </button>
                                                    <button @click="showPublishModal = true; publishBimestre = '{{ $bimestre->numero }}'; publishRoute = '{{ route('admin.bimestres.toggle_publicacion', $bimestre) }}'" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded shadow-sm transition flex items-center gap-1">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                                        Notas
                                                    </button>
                                                    @if($bimestre->estado === 'abierto')
                                                        <a href="{{ route('admin.bimestres.confirmar_cierre', $bimestre) }}" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded shadow-sm transition inline-block">
                                                            Cerrar
                                                        </a>
                                                    @else
                                                        <form action="{{ route('admin.bimestres.abrir', $bimestre) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas reabrir este bimestre?');" class="inline">
                                                            @csrf
                                                            <button type="submit" class="bg-yellow-600 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded shadow-sm transition">
                                                                Reabrir
                                                            </button>
                                                        </form>
                                                    @endif
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

        <!-- Ventana Modal para Aperturar Bimestre (AlpineJS) -->
        <div x-show="showModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" x-cloak>
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                
                <div x-show="showModal" 
                     x-transition:enter="ease-out duration-300" 
                     x-transition:enter-start="opacity-0" 
                     x-transition:enter-end="opacity-100" 
                     x-transition:leave="ease-in duration-200" 
                     x-transition:leave-start="opacity-100" 
                     x-transition:leave-end="opacity-0" 
                     class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
                     @click="showModal = false" aria-hidden="true"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                
                <div x-show="showModal" 
                     x-transition:enter="ease-out duration-300" 
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                     x-transition:leave="ease-in duration-200" 
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                     class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-xl leading-6 font-bold text-gray-900" id="modal-title">
                                    Aperturar Bimestre {{ $bimestres->count() + 1 }}
                                </h3>
                                <p class="text-sm text-gray-500 mt-2 mb-4">Define la fecha de inicio y fin para el próximo bimestre escolar.</p>
                                
                                <div class="mt-4">
                                    <form action="{{ route('admin.bimestres.store') }}" method="POST" id="crearBimestreForm">
                                        @csrf
                                        <div class="mb-4">
                                            <label for="fecha_inicio" class="block text-sm font-bold text-gray-700">Fecha de Inicio *</label>
                                            <input type="date" name="fecha_inicio" id="fecha_inicio" value="{{ old('fecha_inicio') }}" class="mt-1 block w-full border-gray-300 focus:border-yellow-500 focus:ring-yellow-500 rounded-md shadow-sm" required>
                                            @error('fecha_inicio') <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                                        </div>
                                        
                                        <div class="mb-4">
                                            <label for="fecha_fin" class="block text-sm font-bold text-gray-700">Fecha de Fin *</label>
                                            <input type="date" name="fecha_fin" id="fecha_fin" value="{{ old('fecha_fin') }}" class="mt-1 block w-full border-gray-300 focus:border-yellow-500 focus:ring-yellow-500 rounded-md shadow-sm" required>
                                            @error('fecha_fin') <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t">
                        <button type="submit" form="crearBimestreForm" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-yellow-600 text-base font-medium text-white hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Guardar Bimestre
                        </button>
                        <button type="button" @click="showModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ventana Modal para Editar Bimestre (AlpineJS) -->
        <div x-show="showEditModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title-edit" role="dialog" aria-modal="true" x-cloak>
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                
                <div x-show="showEditModal" 
                     x-transition:enter="ease-out duration-300" 
                     x-transition:enter-start="opacity-0" 
                     x-transition:enter-end="opacity-100" 
                     x-transition:leave="ease-in duration-200" 
                     x-transition:leave-start="opacity-100" 
                     x-transition:leave-end="opacity-0" 
                     class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
                     @click="showEditModal = false" aria-hidden="true"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                
                <div x-show="showEditModal" 
                     x-transition:enter="ease-out duration-300" 
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                     x-transition:leave="ease-in duration-200" 
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                     class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-xl leading-6 font-bold text-gray-900" id="modal-title-edit">
                                    Editar Fechas de Bimestre <span x-text="editNumero"></span>
                                </h3>
                                <p class="text-sm text-gray-500 mt-2 mb-4">Modifica la fecha de inicio y fin para este bimestre escolar.</p>
                                
                                <div class="mt-4">
                                    <form :action="editRoute" method="POST" id="editarBimestreForm">
                                        @csrf
                                        @method('PATCH')
                                        <div class="mb-4">
                                            <label for="edit_fecha_inicio" class="block text-sm font-bold text-gray-700">Fecha de Inicio *</label>
                                            <input type="date" name="fecha_inicio" id="edit_fecha_inicio" x-model="editFechaInicio" class="mt-1 block w-full border-gray-300 focus:border-yellow-500 focus:ring-yellow-500 rounded-md shadow-sm" required>
                                        </div>
                                        
                                        <div class="mb-4">
                                            <label for="edit_fecha_fin" class="block text-sm font-bold text-gray-700">Fecha de Fin *</label>
                                            <input type="date" name="fecha_fin" id="edit_fecha_fin" x-model="editFechaFin" class="mt-1 block w-full border-gray-300 focus:border-yellow-500 focus:ring-yellow-500 rounded-md shadow-sm" required>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t">
                        <button type="submit" form="editarBimestreForm" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-yellow-600 text-base font-medium text-white hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Actualizar Fechas
                        </button>
                        <button type="button" @click="showEditModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ventana Modal para Publicar/Ocultar Notas (AlpineJS) -->
        <div x-show="showPublishModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title-publish" role="dialog" aria-modal="true" x-cloak>
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                
                <div x-show="showPublishModal" 
                     x-transition:enter="ease-out duration-300" 
                     x-transition:enter-start="opacity-0" 
                     x-transition:enter-end="opacity-100" 
                     x-transition:leave="ease-in duration-200" 
                     x-transition:leave-start="opacity-100" 
                     x-transition:leave-end="opacity-0" 
                     class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
                     @click="showPublishModal = false" aria-hidden="true"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                
                <div x-show="showPublishModal" 
                     x-transition:enter="ease-out duration-300" 
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                     x-transition:leave="ease-in duration-200" 
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                     class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-xl leading-6 font-bold text-gray-900" id="modal-title-publish">
                                    Visibilidad de Notas (Bimestre <span x-text="publishBimestre"></span>)
                                </h3>
                                <p class="text-sm text-gray-500 mt-2 mb-4">Define si los estudiantes podrán ver sus calificaciones o si seguirán ocultas.</p>
                                
                                <div class="mt-4">
                                    <form :action="publishRoute" method="POST" id="publishNotasForm">
                                        @csrf
                                        <div class="mb-4">
                                            <label for="accion" class="block text-sm font-bold text-gray-700">Acción a realizar *</label>
                                            <select name="accion" id="accion" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                                <option value="publicar">Publicar Notas (Visibles)</option>
                                                <option value="ocultar">Ocultar Notas (Invisibles)</option>
                                            </select>
                                        </div>
                                        
                                        <div class="mb-4">
                                            <label for="nivel" class="block text-sm font-bold text-gray-700">Nivel Educativo *</label>
                                            <select name="nivel" id="nivel" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                                <option value="ambos">Ambos Niveles (Primaria y Secundaria)</option>
                                                <option value="primaria">Solo Primaria</option>
                                                <option value="secundaria">Solo Secundaria</option>
                                            </select>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t">
                        <button type="submit" form="publishNotasForm" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Aplicar Cambios
                        </button>
                        <button type="button" @click="showPublishModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    @if($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const alpineDiv = document.querySelector('[x-data*="showModal"]');
                if(alpineDiv) {
                    alpineDiv._x_dataStack[0].showModal = true;
                }
            });
        </script>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const publishForm = document.getElementById('publishNotasForm');
            if (publishForm) {
                publishForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    let timerInterval;
                    Swal.fire({
                        title: '¿Estás seguro de realizar esta acción?',
                        text: 'Modificará la visibilidad de notas para los estudiantes.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#4f46e5',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Aceptar (5)',
                        cancelButtonText: 'Cancelar',
                        allowOutsideClick: false,
                        didOpen: () => {
                            const confirmButton = Swal.getConfirmButton();
                            confirmButton.disabled = true;
                            let timeLeft = 5;
                            
                            timerInterval = setInterval(() => {
                                timeLeft -= 1;
                                if (timeLeft > 0) {
                                    confirmButton.textContent = `Aceptar (${timeLeft})`;
                                } else {
                                    confirmButton.disabled = false;
                                    confirmButton.textContent = 'Aceptar';
                                    clearInterval(timerInterval);
                                }
                            }, 1000);
                        },
                        willClose: () => {
                            clearInterval(timerInterval);
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            publishForm.submit();
                        }
                    });
                });
            }
        });
    </script>
</x-app-layout>
