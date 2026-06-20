<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Importación de Estudiantes SIAGIE') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="importacionApp()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- SECCIÓN: NUEVA IMPORTACIÓN (REACTIVE) -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8 border border-indigo-200">
                <div class="bg-indigo-50 px-6 py-4 border-b border-indigo-100 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-indigo-800">Nueva Importación desde Excel</h3>
                </div>
                <div class="p-6 text-gray-900">
                    @if(!$anoActivo)
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                            No hay un año lectivo activo.
                        </div>
                    @else
                        <!-- PASO 1: Subir Archivo -->
                        <div x-show="step === 1" id="step-1">
                            <form @submit.prevent="previewExcel" id="uploadForm" class="flex flex-col gap-6">
                                <div>
                                    <label for="grado_seccion_id" class="block text-sm font-bold text-gray-700 mb-2">Paso 1: Selecciona Grado y Sección Destino</label>
                                    <select x-model="gradoSeccionId" id="grado_seccion_id" class="block w-full max-w-md border-gray-300 focus:border-indigo-500 focus:ring-yellow-500 rounded-md shadow-sm" required>
                                        <option value="">-- Seleccione destino --</option>
                                        @foreach($gradoSecciones as $gs)
                                            <option value="{{ $gs->id }}">{{ $gs->grado->nombre }} - {{ $gs->seccion->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Paso 2: Sube el archivo Excel del SIAGIE</label>
                                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md bg-gray-50 hover:bg-gray-100 transition cursor-pointer"
                                         @dragover.prevent="dragover = true"
                                         @dragleave.prevent="dragover = false"
                                         @drop.prevent="dragover = false; handleDrop($event)"
                                         :class="{'border-indigo-500 bg-indigo-50': dragover}"
                                         @click="$refs.fileInput.click()">
                                        <div class="space-y-1 text-center">
                                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                            <div class="flex text-sm text-gray-600 justify-center">
                                                <span class="relative cursor-pointer bg-transparent rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                                    <span x-text="fileName ? fileName : 'Seleccionar un archivo'"></span>
                                                    <input type="file" x-ref="fileInput" @change="handleFileChange($event)" accept=".xlsx, .xls" class="sr-only">
                                                </span>
                                                <p class="pl-1" x-show="!fileName">o arrastra y suelta aquí</p>
                                            </div>
                                            <p class="text-xs text-gray-500">
                                                Solo archivos .xlsx o .xls
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div>
                                    <button type="submit" class="bg-yellow-600 hover:bg-yellow-700 text-white font-bold py-3 px-8 rounded shadow transition" :disabled="loading || !fileName">
                                        <span x-show="!loading">Previsualizar y Editar Datos</span>
                                        <span x-show="loading">Procesando archivo...</span>
                                    </button>
                                </div>
                            </form>
                            <p x-show="errorMessage" x-text="errorMessage" class="text-red-500 mt-2 text-sm font-bold"></p>
                        </div>

                        <!-- PASO 2: Confirmar y Mapear -->
                        <div x-show="step === 2" x-cloak id="step-2">
                            <div class="flex justify-between items-center mb-4">
                                <h4 class="text-md font-bold text-gray-800">Revisión y Mapeo de Columnas</h4>
                                <button type="button" @click="step = 1" class="text-sm text-gray-600 hover:underline">&larr; Cambiar Archivo</button>
                            </div>
                            
                            <div class="mb-4 bg-yellow-50 p-4 rounded border border-yellow-200">
                                <p class="text-sm font-bold text-yellow-800">Mapeo Automático Aplicado</p>
                                <p class="text-xs text-yellow-700 mt-1">El sistema ha detectado automáticamente las columnas de DNI, Nombres y Apellidos. Puedes editar los datos en la tabla si lo deseas.</p>
                            </div>

                            <div class="overflow-x-auto max-h-80 overflow-y-auto mb-4 border rounded shadow-inner">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-200 sticky top-0 z-10">
                                        <tr>
                                            <th class="px-4 py-2 w-10"><input type="checkbox" @change="toggleAll" x-model="allSelected"></th>
                                            <th class="px-4 py-2 text-xs text-gray-600 font-bold w-12">#</th>
                                            <template x-for="(header, index) in headers" :key="index">
                                                <th class="px-4 py-2 text-left text-xs font-bold text-gray-600 whitespace-nowrap" x-text="header || 'Col '+ (index+1)"></th>
                                            </template>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <template x-for="(row, rowIndex) in rows" :key="rowIndex">
                                            <tr :class="{'bg-gray-100 opacity-60': !selectedRows.includes(rowIndex)}">
                                                <td class="px-4 py-2 border-r bg-gray-50 text-center">
                                                    <input type="checkbox" :value="rowIndex" x-model="selectedRows" class="w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-yellow-500">
                                                </td>
                                                <td class="px-4 py-2 text-xs text-gray-500 border-r bg-gray-50 font-mono text-center" x-text="rowIndex + 1"></td>
                                                <template x-for="(cell, colIndex) in row" :key="colIndex">
                                                    <td class="px-2 py-1 text-sm text-gray-800 whitespace-nowrap">
                                                        <input type="text" x-model="rows[rowIndex][colIndex]" :disabled="!selectedRows.includes(rowIndex)" class="w-full border-transparent focus:border-indigo-500 focus:ring-yellow-500 bg-transparent px-2 py-1 text-sm rounded hover:border-gray-300 transition" :class="!selectedRows.includes(rowIndex) ? 'text-gray-400' : 'text-gray-900'">
                                                    </td>
                                                </template>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>

                            <div class="flex justify-between items-center bg-gray-50 p-4 rounded border">
                                <p x-show="errorMessage" x-text="errorMessage" class="text-red-600 font-bold text-sm"></p>
                                <div class="ml-auto flex items-center">
                                    <span class="text-sm font-bold mr-4 text-indigo-700 bg-indigo-100 px-3 py-1 rounded-full"><span x-text="selectedRows.length"></span> filas listas para importar</span>
                                    <button @click="confirmarImportacion" class="bg-yellow-600 hover:bg-yellow-700 text-white font-bold py-2 px-6 rounded shadow" :disabled="loading || selectedRows.length === 0">
                                        <span x-show="!loading">Confirmar e Importar</span>
                                        <span x-show="loading">Guardando Estudiantes...</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- PASO 3: Resultado -->
                        <div x-show="step === 3" x-cloak id="step-3" class="text-center py-6">
                            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
                                <svg class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                </svg>
                            </div>
                            <h3 class="text-2xl font-bold mb-2 text-gray-800">¡Importación Finalizada!</h3>
                            <p class="text-gray-600 mb-4">Se importaron/actualizaron <strong x-text="result.importados" class="text-xl text-green-600"></strong> estudiantes exitosamente.</p>
                            
                            <template x-if="result.errores && result.errores.length > 0">
                                <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 rounded p-4 max-w-2xl mx-auto mb-6 text-left text-sm max-h-40 overflow-y-auto">
                                    <p class="font-bold mb-2">Se encontraron algunos errores/advertencias:</p>
                                    <ul class="list-disc pl-5">
                                        <template x-for="err in result.errores">
                                            <li x-text="err" class="mb-1"></li>
                                        </template>
                                    </ul>
                                </div>
                            </template>

                            <div class="space-x-4">
                                <button @click="window.location.reload()" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-6 rounded">Hacer otra importación</button>
                                <a href="{{ route('admin.estudiantes.index') }}" class="bg-yellow-600 hover:bg-yellow-700 text-white font-bold py-2 px-6 rounded inline-block">Ver todos los Estudiantes</a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- SECCIÓN: HISTORIAL (TABLA) -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-bold mb-4 text-gray-800">Historial de Importaciones</h3>
                    @if($importaciones->isEmpty())
                        <div class="text-center py-6 text-gray-500 border rounded bg-gray-50">
                            No se han realizado importaciones anteriores.
                        </div>
                    @else
                        <div class="overflow-x-auto border rounded">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha / Usuario</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Archivo</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Destino</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Importados</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($importaciones as $imp)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <span class="font-bold text-gray-700">{{ $imp->created_at->format('d/m/Y H:i') }}</span><br>
                                                <small>{{ $imp->admin->name }}</small>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $imp->nombre_archivo }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                                {{ $imp->gradoSeccion->grado->nombre }} - {{ $imp->gradoSeccion->seccion->nombre }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-bold">
                                                {{ $imp->estudiantes_importados }}
                                            </td>
                                            <td class="px-6 py-4 text-sm">
                                                @if($imp->estado === 'exitoso')
                                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Exitoso</span>
                                                @elseif($imp->estado === 'con_errores')
                                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Con Errores</span>
                                                    <button onclick="alert('Errores: \n{{ addslashes(implode('\n', json_decode($imp->errores, true) ?? [])) }}')" class="text-xs text-blue-600 hover:text-blue-800 underline ml-2">Detalles</button>
                                                @else
                                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Fallido</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">
                            {{ $importaciones->links() }}
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>

    @push('scripts')
    <script>
        function importacionApp() {
            return {
                step: 1,
                loading: false,
                gradoSeccionId: '',
                errorMessage: '',
                dragover: false,
                fileName: '',
                fileObj: null,
                
                // Data from preview
                headers: [],
                rows: [],
                tempFile: '',
                originalName: '',
                
                // Selections
                selectedRows: [],
                allSelected: true,
                mapping: {
                    dni: '',
                    nombres: '',
                    apellido_paterno: '',
                    apellido_materno: ''
                },
                result: {},

                handleFileChange(event) {
                    const file = event.target.files[0];
                    if (file) {
                        this.fileName = file.name;
                        this.fileObj = file;
                    }
                },

                handleDrop(event) {
                    const file = event.dataTransfer.files[0];
                    if (file && (file.name.endsWith('.xlsx') || file.name.endsWith('.xls'))) {
                        this.fileName = file.name;
                        this.fileObj = file;
                        this.$refs.fileInput.files = event.dataTransfer.files; // Sincroniza con el input
                    } else {
                        this.errorMessage = 'Por favor, suelta un archivo Excel válido (.xlsx, .xls)';
                    }
                },

                async previewExcel() {
                    this.errorMessage = '';
                    
                    if (!this.gradoSeccionId) {
                        this.errorMessage = 'Seleccione un grado y sección destino.';
                        return;
                    }
                    if (!this.fileObj) {
                        this.errorMessage = 'Seleccione o arrastre un archivo Excel válido.';
                        return;
                    }

                    this.loading = true;
                    
                    const formData = new FormData();
                    formData.append('file', this.fileObj);
                    formData.append('_token', '{{ csrf_token() }}');

                    try {
                        const response = await fetch('{{ route('admin.importaciones.preview') }}', {
                            method: 'POST',
                            body: formData,
                            headers: { 'Accept': 'application/json' }
                        });

                        const data = await response.json();
                        if (!response.ok || !data.success) throw new Error(data.message || 'Error en el servidor');

                        this.headers = data.headers;
                        this.rows = data.rows;
                        this.tempFile = data.temp_file;
                        this.originalName = data.original_name;
                        this.selectedRows = this.rows.map((_, index) => index);
                        this.autoMapHeaders();
                        this.step = 2;
                    } catch (error) {
                        this.errorMessage = error.message;
                    } finally {
                        this.loading = false;
                    }
                },

                autoMapHeaders() {
                    const lowerHeaders = this.headers.map(h => h ? h.toLowerCase() : '');
                    const dniIndex = lowerHeaders.findIndex(h => h.includes('dni') || h.includes('documento'));
                    this.mapping.dni = dniIndex >= 0 ? dniIndex : 0; // fallback to column 0
                    
                    const nombresIndex = lowerHeaders.findIndex(h => h === 'nombres' || h === 'nombre');
                    this.mapping.nombres = nombresIndex >= 0 ? nombresIndex : 1; // fallback to col 1
                    
                    const paternoIndex = lowerHeaders.findIndex(h => h.includes('paterno') || h.includes('apellido p'));
                    this.mapping.apellido_paterno = paternoIndex >= 0 ? paternoIndex : 2; // fallback to col 2
                    
                    const maternoIndex = lowerHeaders.findIndex(h => h.includes('materno') || h.includes('apellido m'));
                    this.mapping.apellido_materno = maternoIndex >= 0 ? maternoIndex : null;
                },

                toggleAll() {
                    this.selectedRows = this.allSelected ? this.rows.map((_, index) => index) : [];
                },

                async confirmarImportacion() {
                    // Quitamos la validación estricta de mapping ya que ahora es automático con fallback.
                    this.errorMessage = '';
                    this.loading = true;

                    // Enviar solo las filas seleccionadas y con los datos EDITADOS en el frontend
                    const rowsToImport = this.selectedRows.map(index => this.rows[index]);

                    try {
                        const response = await fetch('{{ route('admin.importaciones.confirmar') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                grado_seccion_id: this.gradoSeccionId,
                                temp_file: this.tempFile,
                                original_name: this.originalName,
                                mapping: this.mapping,
                                rows_to_import: rowsToImport
                            })
                        });

                        const data = await response.json();
                        if (!response.ok || !data.success) throw new Error(data.message || 'Error durante la importación');

                        this.result = data;
                        this.step = 3;
                    } catch (error) {
                        this.errorMessage = error.message;
                    } finally {
                        this.loading = false;
                    }
                }
            }
        }
    </script>
    @endpush
</x-app-layout>
