<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Importar Notas - Tutor') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8" x-data="importWizard()">
            <!-- Wizard Container -->
            <div class="max-w-5xl mx-auto bg-white rounded-lg shadow-sm border border-gray-200">
                <!-- Header -->
                <div class="bg-[#f0f4f8] p-4 border-b border-gray-200 rounded-t-lg">
                    <h2 class="text-lg font-semibold text-[#3b3b8c]">Nueva Importación desde Excel</h2>
                </div>

                <form method="POST" action="{{ route('docente.importar.confirmar') }}" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="p-6 md:p-8 space-y-8">
                        
                        <!-- Paso 1: Seleccionar Sección a Cargo -->
                        <div>
                            <label class="block text-gray-700 font-semibold text-sm mb-2">Paso 1: Sección a Cargo</label>
                            @if($secciones->count() > 1)
                                <select name="grado_seccion_id" x-model="form.grado_seccion_id" class="w-full md:w-1/3 border border-gray-300 rounded-md p-2 focus:outline-none focus:ring-1 focus:ring-indigo-500 bg-white text-sm" required>
                                    <option value="" disabled>-- Seleccione su sección --</option>
                                    @foreach($secciones as $seccion)
                                        <option value="{{ $seccion->id }}">{{ $seccion->grado->nombre }} - {{ $seccion->seccion->nombre }}</option>
                                    @endforeach
                                </select>
                            @else
                                <!-- Si solo tiene una sección, la seleccionamos por defecto y la mostramos bloqueada -->
                                <select name="grado_seccion_id" x-model="form.grado_seccion_id" class="w-full md:w-1/3 border border-gray-300 rounded-md p-2 focus:outline-none focus:ring-1 focus:ring-indigo-500 bg-gray-100 text-sm cursor-not-allowed" required>
                                    @foreach($secciones as $seccion)
                                        <option value="{{ $seccion->id }}">{{ $seccion->grado->nombre }} - {{ $seccion->seccion->nombre }}</option>
                                    @endforeach
                                </select>
                            @endif
                        </div>

                        <!-- Paso 2: Seleccionar Bimestre -->
                        <div>
                            <label class="block text-gray-700 font-semibold text-sm mb-2">Paso 2: Selecciona el Bimestre</label>
                            <select name="bimestre_id" x-model="form.bimestre_id" class="w-full md:w-1/3 border border-gray-300 rounded-md p-2 focus:outline-none focus:ring-1 focus:ring-indigo-500 bg-white text-gray-900 text-sm" required>
                                <option value="" disabled>-- Seleccione Bimestre --</option>
                                @foreach($bimestres as $bimestre)
                                    <option value="{{ $bimestre->id }}" @if($bimestre->estado === 'cerrado') disabled @endif>
                                        Bimestre {{ $bimestre->numero }} @if($bimestre->estado === 'cerrado') (Cerrado) @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Paso 3: Subir Archivo Excel -->
                        <div>
                            <label class="block text-gray-700 font-semibold text-sm mb-2">Paso 3: Sube el archivo Excel del SIAGIE</label>
                            <div class="mt-1 flex justify-center px-6 pt-8 pb-8 border-2 border-gray-300 border-dashed rounded-md bg-white relative hover:bg-gray-50 transition-colors cursor-pointer"
                                 @click="$refs.fileInput.click()"
                                 @dragover.prevent="dragover = true"
                                 @dragleave.prevent="dragover = false"
                                 @drop.prevent="handleDrop"
                                 :class="{ 'border-indigo-500 bg-indigo-50': dragover }">
                                
                                <input type="file" name="file" x-ref="fileInput" class="sr-only" @change="handleFile" accept=".xlsx,.xls">
                                
                                <div class="space-y-2 text-center pointer-events-none">
                                    <svg class="mx-auto h-10 w-10 text-gray-400 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <div class="flex text-sm text-gray-600 justify-center items-center space-x-1">
                                        <span class="font-medium text-[#4f46e5]">Seleccionar un archivo</span>
                                        <p>o arrastra y suelta aquí</p>
                                    </div>
                                    <p class="text-xs text-gray-400">Solo archivos .xlsx o .xls</p>
                                    <p x-show="file" class="text-sm font-semibold text-green-600 mt-2" x-text="file ? file.name : ''"></p>
                                </div>
                            </div>
                        </div>

                        <!-- Botón Importar -->
                        <div class="pt-6 border-t border-gray-100 mt-6 flex justify-start">
                            <button type="button" @click="submitImport" class="text-white font-bold py-3.5 px-8 rounded-lg shadow-md transition-all text-base flex items-center justify-center min-w-[280px]" style="background-color: #5c4cfc;" onmouseover="if(!this.disabled) this.style.backgroundColor='#4a3bcc'" onmouseout="if(!this.disabled) this.style.backgroundColor='#5c4cfc'" :disabled="!isFormValid || loading" :class="{ 'opacity-50 cursor-not-allowed': !isFormValid || loading }">
                                <svg x-show="loading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" x-cloak><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                <span x-text="loading ? 'Importando Notas de todas las pestañas...' : 'Importar Notas Oficiales SIAGIE'"></span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Historial de Importaciones -->
            <div class="max-w-5xl mx-auto mt-8 bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="bg-[#f0f4f8] p-4 border-b border-gray-200 rounded-t-lg">
                    <h2 class="text-lg font-semibold text-[#3b3b8c]">Historial de Importaciones</h2>
                </div>
                <div class="p-6">
                    @if($historial->isEmpty())
                        <p class="text-gray-500 text-sm">No has realizado importaciones en este año lectivo.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 border border-gray-200 rounded-lg overflow-hidden">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Fecha</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Archivo</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Destino</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Estado</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Importados</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Acción</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200 text-sm">
                                    @foreach($historial as $h)
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap text-gray-700">{{ $h->created_at->format('d/m/Y H:i') }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-gray-900 font-medium">{{ $h->nombre_archivo }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-gray-500">
                                                {{ $h->gradoSeccion->grado->nombre ?? '-' }} - {{ $h->gradoSeccion->seccion->nombre ?? '-' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if(in_array(strtolower($h->estado), ['completado', 'exitoso']))
                                                    <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Completado</span>
                                                @elseif(in_array(strtolower($h->estado), ['con_errores', 'errores']))
                                                    <div class="flex items-center space-x-2">
                                                        <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Con Errores</span>
                                                        <button type="button" @click='openErrorsModal(@json($h->errores))' class="text-xs font-medium text-indigo-600 hover:text-indigo-900 underline bg-transparent border-none p-0 cursor-pointer">Ver detalles</button>
                                                    </div>
                                                @elseif(strtolower($h->estado) === 'revertido')
                                                    <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Revertido</span>
                                                @else
                                                    <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">{{ ucfirst($h->estado) }}</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-gray-600 font-medium">{{ $h->estudiantes_importados ?? 0 }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                @if(strtolower($h->estado) !== 'revertido')
                                                    <button type="button" @click="confirmRevert({{ $h->id }})" class="text-red-500 hover:text-red-700 transition-colors" title="Revertir Importación">
                                                        <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                                                    </button>
                                                @else
                                                    <button type="button" disabled class="text-gray-300 cursor-not-allowed" title="Ya fue revertido">
                                                        <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">
                            {{ $historial->links() }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Modal para ver errores -->
            <div x-show="showErrorsModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-cloak>
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div x-show="showErrorsModal" class="fixed inset-0 transition-opacity" aria-hidden="true" @click="showErrorsModal = false">
                        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                    </div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                    <div x-show="showErrorsModal" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                    <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                </div>
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Detalles de Errores</h3>
                                    <div class="mt-4 max-h-60 overflow-y-auto w-full pr-2">
                                        <ul class="list-disc pl-5 text-sm text-gray-600 space-y-1">
                                            <template x-for="(error, index) in currentErrors" :key="index">
                                                <li x-text="error" class="break-words"></li>
                                            </template>
                                        </ul>
                                        <p x-show="!currentErrors || currentErrors.length === 0" class="text-sm text-gray-500">No se proporcionaron detalles específicos.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm" @click="showErrorsModal = false">
                                Cerrar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Cargar SweetAlert2 directamente en la vista por precaución -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
    function importWizard() {
        return {
            form: { grado_seccion_id: '{{ $secciones->count() === 1 ? $secciones->first()->id : "" }}', bimestre_id: '' },
            file: null,
            dragover: false,
            loading: false,
            showErrorsModal: false,
            currentErrors: [],
            
            get isFormValid() {
                return this.form.grado_seccion_id && this.form.bimestre_id && this.file;
            },

            handleFile(event) {
                const file = event.target.files[0];
                if (!file) return;
                this.file = file;
            },

            handleDrop(event) {
                this.dragover = false;
                if (event.dataTransfer.files.length > 0) {
                    const file = event.dataTransfer.files[0];
                    if (file.name.endsWith('.xlsx') || file.name.endsWith('.xls')) {
                        this.file = file;
                    } else {
                        Swal.fire('Atención', 'Por favor suba un archivo Excel (.xlsx o .xls)', 'warning');
                    }
                }
            },

            submitImport() {
                if (!this.isFormValid) {
                    Swal.fire('Faltan datos', 'Por favor complete todos los campos requeridos y seleccione un archivo.', 'warning');
                    return;
                }
                
                try {
                    this.loading = true;
                    const payload = new FormData();
                    payload.append('grado_seccion_id', this.form.grado_seccion_id);
                    payload.append('bimestre_id', this.form.bimestre_id);
                    payload.append('file', this.file);
                    
                    fetch('{{ route('docente.importar.confirmar') }}', {
                        method: 'POST',
                        headers: { 
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: payload,
                    })
                    .then(async response => {
                        this.loading = false;
                        const data = await response.json().catch(() => ({}));
                        if (response.ok) {
                            if (data.success) {
                                Swal.fire('¡Éxito!', data.message || 'Importación procesada correctamente', 'success').then(() => {
                                    window.location.reload();
                                });
                            } else {
                                Swal.fire('Error', data.message || 'Hubo un error al procesar la importación', 'error');
                            }
                        } else {
                            let msg = data.message || 'Hubo un error al procesar la importación';
                            if (response.status === 422 && data.errors) {
                                msg = Object.values(data.errors).flat().join('\n');
                            }
                            Swal.fire('Error', msg, 'error');
                        }
                    })
                    .catch(err => {
                        this.loading = false;
                        console.error(err);
                        Swal.fire('Error de Red', 'Ocurrió un error al enviar los datos: ' + err.message, 'error');
                    });
                } catch (error) {
                    this.loading = false;
                    console.error(error);
                    Swal.fire('Error Inesperado', 'Ocurrió un error inesperado al preparar los datos: ' + error.message, 'error');
                }
            },

            openErrorsModal(errores) {
                let errorsArray = [];
                if (typeof errores === 'string') {
                    try {
                        errorsArray = JSON.parse(errores);
                    } catch(e) {
                        errorsArray = [errores];
                    }
                } else if (Array.isArray(errores)) {
                    errorsArray = errores;
                } else if (errores && typeof errores === 'object') {
                    errorsArray = Object.values(errores).flat();
                }
                
                this.currentErrors = errorsArray;
                this.showErrorsModal = true;
            },

            confirmRevert(id) {
                Swal.fire({
                    title: '¿Revertir importación?',
                    text: "Esto intentará eliminar las notas que se hayan creado durante esta importación. Esta acción no se puede deshacer.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sí, revertir',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Revirtiendo...',
                            text: 'Por favor espere.',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        fetch('{{ url("docente/importar") }}/' + id, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire('¡Revertido!', data.message, 'success').then(() => {
                                    window.location.reload();
                                });
                            } else {
                                Swal.fire('Error', data.message, 'error');
                            }
                        })
                        .catch(err => {
                            console.error(err);
                            Swal.fire('Error', 'Ocurrió un error de red al intentar revertir la importación.', 'error');
                        });
                    }
                });
            }
        }
    }
    </script>
</x-app-layout>
