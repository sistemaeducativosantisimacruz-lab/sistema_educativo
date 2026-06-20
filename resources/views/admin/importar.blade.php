<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Nueva Importación desde Excel') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8" x-data="importWizard()">
            <!-- Wizard Container -->
            <div class="max-w-5xl mx-auto bg-white rounded-lg shadow-sm border border-gray-200">
    <!-- Header -->
    <div class="bg-[#f0f4f8] p-4 border-b border-gray-200 rounded-t-lg" x-show="!showMapping">
        <h2 class="text-lg font-semibold text-[#3b3b8c]">Nueva Importación desde Excel</h2>
    </div>

    <form method="POST" action="{{ route('admin.importaciones.confirmar') }}" enctype="multipart/form-data">
        @csrf
        
        <div x-show="!showMapping" class="p-6 md:p-8 space-y-8">
        <div>
            <label class="block text-gray-700 font-semibold text-sm mb-2">Paso 1: Selecciona el Nivel Educativo</label>
            <select name="nivel_id" x-model="form.nivel_id" @change="resetDestino" class="w-full md:w-1/3 border border-gray-300 rounded-md p-2 focus:outline-none focus:ring-1 focus:ring-indigo-500 bg-white text-sm" required>
                <option value="" disabled>-- Seleccione Nivel --</option>
                @foreach($niveles as $nivel)
                    <option value="{{ $nivel }}">{{ ucfirst($nivel) }}</option>
                @endforeach
            </select>
        </div>

        <!-- Paso 2 -->
        <div>
            <label class="block text-gray-700 font-semibold text-sm mb-2">Paso 2: Selecciona Grado y Sección Destino</label>
            <select x-model="selectedDestino" @change="updateDestino" class="w-full md:w-1/3 border border-gray-300 rounded-md p-2 focus:outline-none focus:ring-1 focus:ring-indigo-500 bg-white text-sm" required>
                <option value="" disabled>-- Seleccione destino --</option>
                <template x-for="destino in filteredDestinos" :key="destino.id">
                    <option :value="destino.id" x-text="destino.nombre"></option>
                </template>
            </select>
            <input type="hidden" name="grado_id" x-model="form.grado_id">
            <input type="hidden" name="seccion_id" x-model="form.seccion_id">
        </div>

        <!-- Seleccionar Bimestre (Solo para Notas) -->
        <div x-show="form.tipo === 'notas'" x-cloak class="mt-4">
            <label class="block text-gray-700 font-semibold text-sm mb-2">Selecciona el Bimestre</label>
            <select name="bimestre_id" x-model="form.bimestre_id" class="w-full md:w-1/3 border border-gray-300 rounded-md p-2 focus:outline-none focus:ring-1 focus:ring-indigo-500 bg-white text-gray-900 text-sm" :required="form.tipo === 'notas'">
                <option value="" disabled>-- Seleccione Bimestre --</option>
                @foreach($bimestres as $bimestre)
                    <option value="{{ $bimestre->id }}" @if($bimestre->estado === 'cerrado') disabled @endif>
                        Bimestre {{ $bimestre->numero }} @if($bimestre->estado === 'cerrado') (Cerrado) @endif
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Paso 3 -->
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

        <!-- Paso 4 -->
        <div>
            <label class="block text-gray-700 font-semibold text-sm mb-3">Paso 4: Tipo de Importación</label>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                
                <label class="relative cursor-pointer rounded-md border p-4 flex flex-col items-center text-center transition-all shadow-sm"
                       :class="form.tipo === 'directorio' ? 'border-indigo-500 bg-[#fafafa]' : 'border-gray-200 bg-white hover:border-indigo-300'">
                    <input type="radio" name="tipo" value="directorio" x-model="form.tipo" class="sr-only">
                    <svg class="h-7 w-7 text-gray-700 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <span class="font-semibold text-gray-800 block text-sm">Directorio Completo</span>
                    <span class="text-xs text-gray-500 mt-1">Estudiante + Madre en una sola hoja</span>
                </label>

                <label class="relative cursor-pointer rounded-md border p-4 flex flex-col items-center text-center transition-all shadow-sm"
                       :class="form.tipo === 'estudiantes' ? 'border-indigo-500 bg-[#fafafa]' : 'border-gray-200 bg-white hover:border-indigo-300'">
                    <input type="radio" name="tipo" value="estudiantes" x-model="form.tipo" class="sr-only">
                    <svg class="h-7 w-7 text-gray-700 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="font-semibold text-gray-800 block text-sm">Solo Estudiantes</span>
                    <span class="text-xs text-gray-500 mt-1">Solo datos del alumno</span>
                </label>

                <label class="relative cursor-pointer rounded-md border p-4 flex flex-col items-center text-center transition-all shadow-sm"
                       :class="form.tipo === 'padres' ? 'border-indigo-500 bg-[#fafafa]' : 'border-gray-200 bg-white hover:border-indigo-300'">
                    <input type="radio" name="tipo" value="padres" x-model="form.tipo" class="sr-only">
                    <svg class="h-7 w-7 text-gray-700 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span class="font-semibold text-gray-800 block text-sm">Solo Apoderado</span>
                    <span class="text-xs text-gray-500 mt-1">Vincular madre/padre a alumno existente</span>
                </label>

                <label class="relative cursor-pointer rounded-md border p-4 flex flex-col items-center text-center transition-all shadow-sm"
                       :class="form.tipo === 'siagie' ? 'border-indigo-500 bg-[#fafafa]' : 'border-gray-200 bg-white hover:border-indigo-300'">
                    <input type="radio" name="tipo" value="siagie" x-model="form.tipo" class="sr-only">
                    <svg class="h-7 w-7 text-gray-700 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                    </svg>
                    <span class="font-semibold text-gray-800 block text-sm">Cód. SIAGIE</span>
                    <span class="text-xs text-gray-500 mt-1">Actualizar códigos desde SIAGIE</span>
                </label>

                <label class="relative cursor-pointer rounded-md border p-4 flex flex-col items-center text-center transition-all shadow-sm"
                       :class="form.tipo === 'notas' ? 'border-indigo-500 bg-[#fafafa]' : 'border-gray-200 bg-white hover:border-indigo-300'">
                    <input type="radio" name="tipo" value="notas" x-model="form.tipo" class="sr-only">
                    <svg class="h-7 w-7 text-gray-700 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    <span class="font-semibold text-gray-800 block text-sm">Notas Bimestrales</span>
                    <span class="text-xs text-gray-500 mt-1">Notas por Curso en pestañas</span>
                </label>
            </div>
        </div>

        <!-- Botones de Acción Principal -->
        <div class="pt-6 border-t border-gray-100 mt-6 flex justify-start" x-show="!showMapping">
            <!-- Para todos los demás tipos: Previsualizar -->
            <button type="button" x-show="form.tipo !== 'notas'" @click="previewData" class="text-white font-bold py-3.5 px-8 rounded-lg shadow-md transition-all text-base flex items-center justify-center min-w-[280px]" style="background-color: #ca8a04;" onmouseover="if(!this.disabled) this.style.backgroundColor='#a16207'" onmouseout="if(!this.disabled) this.style.backgroundColor='#ca8a04'" :disabled="!isFormValid || loading" :class="{ 'opacity-50 cursor-not-allowed': !isFormValid || loading }">
                <svg x-show="loading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" x-cloak><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                <span x-text="loading ? 'Procesando archivo...' : 'Previsualizar y Asignar Datos'"></span>
            </button>
            
            <!-- Para Notas: Importar directamente (sin preview ni mapeo porque la estructura SIAGIE es estándar y multi-pestaña) -->
            <button type="button" x-cloak x-show="form.tipo === 'notas'" @click="submitImport" class="text-white font-bold py-3.5 px-8 rounded-lg shadow-md transition-all text-base flex items-center justify-center min-w-[280px]" style="background-color: #5c4cfc;" onmouseover="if(!this.disabled) this.style.backgroundColor='#4a3bcc'" onmouseout="if(!this.disabled) this.style.backgroundColor='#5c4cfc'" :disabled="!isFormValid || loading" :class="{ 'opacity-50 cursor-not-allowed': !isFormValid || loading }">
                <svg x-show="loading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" x-cloak><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                <span x-text="loading ? 'Importando Notas de todas las pestañas...' : 'Importar Notas Oficiales SIAGIE'"></span>
            </button>
        </div>

        </div>

        <!-- Paso 5: Mapeo de Columnas -->
        <div x-show="showMapping" x-cloak class="p-6 md:p-8 space-y-6" style="display: none;">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 border-b pb-4 gap-4">
                <h3 class="text-2xl font-bold text-gray-800">Revisión y Mapeo de Columnas</h3>
                <button type="button" @click="showMapping = false" class="text-sm text-gray-500 hover:text-gray-800 transition-colors flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Cambiar Archivo
                </button>
            </div>

            <div class="bg-[#5c4cfc] rounded-t-lg p-4 text-white shadow-sm">
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <h4 class="font-semibold text-lg">
                        Mapeo para <span class="capitalize" x-text="form.tipo === 'siagie' ? 'Cód. SIAGIE' : form.tipo"></span>
                    </h4>
                </div>
                <p class="text-indigo-100 text-sm mt-1" x-show="form.tipo === 'directorio'">El nombre completo va en una sola columna (ej: CASTILLO NIMA SHIRLEY). El sistema separará automáticamente apellidos y nombres.</p>
                <p class="text-indigo-100 text-sm mt-1" x-show="form.tipo !== 'directorio'">Asigna las columnas de tu archivo a los campos requeridos por el sistema.</p>
            </div>
            
            <div class="bg-[#f8fafc] border-x border-b border-indigo-100 rounded-b-lg p-6 md:p-8 space-y-8 shadow-sm">
                <!-- Selección de Apoderado para Primaria -->
                <template x-if="form.tipo === 'directorio' && form.nivel_id && form.nivel_id.toLowerCase() === 'primaria'">
                    <div class="mb-6 p-4 bg-indigo-50 border border-indigo-200 rounded-lg">
                        <label class="block text-sm font-semibold text-indigo-800 mb-2">¿Quién es el Apoderado principal?</label>
                        <div class="flex items-center space-x-6">
                            <label class="flex items-center space-x-2 cursor-pointer">
                                <input type="radio" x-model="form.apoderado_tipo" value="padre" class="text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm text-gray-700">El Padre</span>
                            </label>
                            <label class="flex items-center space-x-2 cursor-pointer">
                                <input type="radio" x-model="form.apoderado_tipo" value="madre" class="text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm text-gray-700">La Madre</span>
                            </label>
                            <label class="flex items-center space-x-2 cursor-pointer">
                                <input type="radio" x-model="form.apoderado_tipo" value="otro" class="text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm text-gray-700">Otro familiar</span>
                            </label>
                        </div>
                        <p class="text-xs text-indigo-600 mt-2">Si seleccionas Padre o Madre, los campos adicionales de Apoderado se ocultarán automáticamente y se asumirá su información.</p>
                    </div>
                </template>

                <!-- Layout Dinámico Unificado para Mapeo -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-6">
                    <template x-for="field in currentExpectedFields" :key="field.key">
                        <div class="flex flex-col p-4 bg-white border border-gray-200 rounded-lg shadow-sm hover:border-indigo-300 transition-colors">
                            <label class="text-[11px] font-bold text-gray-700 uppercase mb-2 flex items-center" x-text="field.label"></label>
                            <select x-model="mapping[field.key]" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm py-2 bg-gray-50">
                                <option value="">Seleccionar columna..</option>
                                <template x-for="col in excelColumns" :key="col">
                                    <option :value="col" x-text="col"></option>
                                </template>
                            </select>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Alerta Automática -->
            <div class="bg-yellow-50/80 border border-yellow-200/80 rounded-md p-4 flex items-start space-x-3 mt-6">
                <div class="text-yellow-600 mt-0.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <h5 class="text-sm font-semibold text-yellow-800">Mapeo Automático Aplicado</h5>
                    <p class="text-xs text-yellow-700 mt-1">El sistema ha detectado automáticamente las columnas según el tipo seleccionado.</p>
                </div>
            </div>

            <!-- Tabla de Previsualización -->
            <div class="border border-gray-200 rounded-lg overflow-x-auto mt-6 shadow-sm bg-white max-h-96 overflow-y-auto">
                <table class="w-full text-sm text-left whitespace-nowrap">
                    <thead class="text-xs text-gray-600 uppercase bg-gray-50 sticky top-0 z-10 shadow-sm">
                        <tr>
                            <th class="px-4 py-3 border-b border-gray-200 text-center">
                                <input type="checkbox" class="rounded border-gray-300 text-[#5c4cfc] shadow-sm" x-on:change="toggleAllRows($event.target.checked)" checked>
                            </th>
                            <th class="px-4 py-3 border-b border-gray-200 text-gray-400 font-medium">#</th>
                            <template x-for="col in excelColumns">
                                <th class="px-4 py-3 border-b border-gray-200 font-semibold" x-text="col"></th>
                            </template>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <template x-for="(row, index) in excelPreviewRows" :key="index">
                            <tr class="hover:bg-gray-50 transition-colors" :class="{'opacity-50': !row._selected}">
                                <td class="px-4 py-3 text-center">
                                    <input type="checkbox" x-model="row._selected" class="rounded border-gray-300 text-[#5c4cfc] shadow-sm cursor-pointer">
                                </td>
                                <td class="px-4 py-3 text-gray-400" x-text="index + 1"></td>
                                <template x-for="col in excelColumns">
                                    <td class="px-4 py-3 text-gray-700" x-text="row[col] || ''"></td>
                                </template>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <div class="flex justify-end gap-4 mt-8 pt-6 border-t border-gray-200">
                <button type="button" @click="submitImport" class="text-white font-bold py-3.5 px-10 rounded-lg shadow-md transition-colors text-base flex items-center justify-center w-auto" style="background-color: #5c4cfc;" onmouseover="if(!this.disabled) this.style.backgroundColor='#4a3bcc'" onmouseout="if(!this.disabled) this.style.backgroundColor='#5c4cfc'" :disabled="loading" :class="{ 'opacity-50 cursor-not-allowed': loading }">
                    <svg x-show="loading" class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span x-text="loading ? 'Importando...' : 'Confirmar e Importar'"></span>
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
            <p class="text-gray-500 text-sm">No hay importaciones registradas en este año lectivo.</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 border border-gray-200 rounded-lg overflow-hidden">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Fecha</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Archivo</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Tipo</th>
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
                                <td class="px-6 py-4 whitespace-nowrap text-gray-500 capitalize">{{ $h->tipo }}</td>
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
        <!-- Background overlay -->
        <div x-show="showErrorsModal" class="fixed inset-0 transition-opacity" aria-hidden="true" @click="showErrorsModal = false">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <!-- Modal panel -->
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

<script>
function importWizard() {
    return {
        form: { nivel_id: '', grado_id: '', seccion_id: '', tipo: '', apoderado_tipo: 'otro', bimestre_id: '' },
        selectedDestino: '',
        file: null,
        dragover: false,
        loading: false,
        showMapping: false,
        showErrorsModal: false,
        currentErrors: [],
        excelColumns: [],
        excelPreviewRows: [],
        mapping: {},
        expectedFields: {
            directorio_primaria: [
                { key: 'estudiante_nombres_completos', label: 'Estudiante: Nombre y Apellidos Completos' },
                { key: 'estudiante_dni', label: 'Estudiante: DNI' },
                { key: 'estudiante_fecha_nacimiento', label: 'Estudiante: Fecha Nacimiento (Opcional)' },
                { key: 'padre_nombres', label: 'Padre: Nombres Completos (Opcional)' },
                { key: 'padre_dni', label: 'Padre: DNI (Opcional)' },
                { key: 'padre_telefono', label: 'Padre: Teléfono (Opcional)' },
                { key: 'madre_nombres', label: 'Madre: Nombres Completos (Opcional)' },
                { key: 'madre_dni', label: 'Madre: DNI (Opcional)' },
                { key: 'madre_telefono', label: 'Madre: Teléfono (Opcional)' },
                { key: 'apoderado_nombres', label: 'Apoderado: Nombres Completos (Si no es P/M)' },
                { key: 'apoderado_dni', label: 'Apoderado: DNI (Opcional)' },
                { key: 'apoderado_parentesco', label: 'Apoderado: Parentesco' },
                { key: 'telefono', label: 'Teléfono Domicilio (Opcional)' },
                { key: 'direccion', label: 'Dirección Domicilio (Opcional)' }
            ],
            directorio_secundaria: [
                { key: 'estudiante_nombres_completos', label: 'Estudiante: Nombre y Apellidos Completos' },
                { key: 'estudiante_dni', label: 'Estudiante: DNI' },
                { key: 'estudiante_fecha_nacimiento', label: 'Estudiante: Fecha Nacimiento (Opcional)' },
                { key: 'apoderado_nombres_completos', label: 'Apoderado: Nombre y Apellidos de la Madre/Apod (Opcional)' },
                { key: 'apoderado_dni', label: 'Apoderado: DNI (Opcional)' },
                { key: 'telefono', label: 'Teléfono (Opcional)' },
                { key: 'direccion', label: 'Dirección (Opcional)' }
            ],
            estudiantes: [
                { key: 'estudiante_grado', label: 'Grado (ej: PRIMERO)' },
                { key: 'estudiante_seccion', label: 'Sección (ej: A)' },
                { key: 'estudiante_dni', label: 'DNI' },
                { key: 'estudiante_codigo', label: 'Código Estudiante (Opcional)' },
                { key: 'estudiante_apellido_paterno', label: 'Apellido Paterno' },
                { key: 'estudiante_apellido_materno', label: 'Apellido Materno' },
                { key: 'estudiante_nombres', label: 'Nombres' },
                { key: 'estudiante_sexo', label: 'Sexo (Hombre/Mujer)' },
                { key: 'estudiante_fecha_nacimiento', label: 'Fecha de Nacimiento' }
            ],
            padres: [
                { key: 'estudiante_dni', label: 'Estudiante: DNI (*)' },
                { key: 'estudiante_nombres_apellidos', label: 'Estudiante: Apellidos y Nombres' },
                { key: 'estudiante_fecha_nacimiento', label: 'Estudiante: Fecha de Nacimiento' },
                { key: 'colegio_inicial', label: 'Colegio Inicial de Procedencia' },
                { key: 'padre_nombres', label: 'Padre: Apellidos y Nombres' },
                { key: 'padre_dni', label: 'Padre: DNI' },
                { key: 'padre_telefono', label: 'Padre: Teléfono' },
                { key: 'madre_nombres', label: 'Madre: Apellidos y Nombres' },
                { key: 'madre_dni', label: 'Madre: DNI' },
                { key: 'madre_telefono', label: 'Madre: Teléfono' },
                { key: 'apoderado_nombres', label: 'Apoderado: Apellidos y Nombres' },
                { key: 'apoderado_dni', label: 'Apoderado: DNI' },
                { key: 'apoderado_telefono', label: 'Apoderado: Teléfono' },
                { key: 'apoderado_direccion', label: 'Apoderado: Dirección' },
                { key: 'apoderado_parentesco', label: 'Apoderado: Parentesco (ej: PADRE, MADRE, TIO)' }
            ],
            siagie: [
                { key: 'codigo_siagie', label: 'Código SIAGIE (*)' },
                { key: 'nombres_apellidos', label: 'Nombres y Apellidos (Estudiante)' }
            ],
            notas: [
                { key: 'dni_estudiante', label: 'DNI Estudiante (*)' },
                { key: 'nombres_apellidos', label: 'Nombres y Apellidos' }
            ]
        },
        get currentExpectedFields() {
            if (this.form.tipo === 'directorio') {
                if (this.form.nivel_id && this.form.nivel_id.toLowerCase() === 'primaria') {
                    if (this.form.apoderado_tipo === 'padre' || this.form.apoderado_tipo === 'madre') {
                        return this.expectedFields.directorio_primaria.filter(f => !f.key.startsWith('apoderado_'));
                    }
                    return this.expectedFields.directorio_primaria;
                }
                return this.expectedFields.directorio_secundaria;
            }
            return this.expectedFields[this.form.tipo] || [];
        },
        
        gradoSecciones: @json($gradoSecciones),
        
        get filteredDestinos() {
            if (!this.form.nivel_id) return [];
            
            if (this.form.tipo === 'estudiantes') {
                return [{ id: 'todos', grado_id: 'todos', seccion_id: 'todos', nombre: 'Todos los grados y secciones (Asignación Automática por Excel)' }];
            }

            let destinos = this.gradoSecciones
                .filter(gs => gs.grado.nivel === this.form.nivel_id)
                .map(gs => ({ 
                    id: gs.id, 
                    grado_id: gs.grado.id, 
                    seccion_id: gs.seccion.id, 
                    nombre: `${gs.grado.nombre} - ${gs.seccion.nombre}` 
                }));
            
            return destinos;
        },

        get isFormValid() {
            let valid = this.form.nivel_id && this.form.grado_id && this.form.seccion_id && this.form.tipo && this.file;
            if (this.form.tipo === 'notas') {
                valid = valid && this.form.bimestre_id;
            }
            return valid;
        },

        resetDestino() {
            this.selectedDestino = '';
            this.form.grado_id = '';
            this.form.seccion_id = '';
        },

        updateDestino() {
            if (this.selectedDestino === 'todos') {
                this.form.grado_id = 'todos';
                this.form.seccion_id = 'todos';
                return;
            }
            const destino = this.filteredDestinos.find(d => d.id == this.selectedDestino);
            if (destino) {
                this.form.grado_id = destino.grado_id;
                this.form.seccion_id = destino.seccion_id;
            } else {
                this.form.grado_id = '';
                this.form.seccion_id = '';
            }
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

        previewData() {
            if (!this.isFormValid) {
                Swal.fire('Faltan datos', 'Por favor complete todos los campos requeridos y seleccione un archivo.', 'warning');
                return;
            }
            this.loading = true;
            
            const payload = new FormData();
            payload.append('file', this.file);
            
            fetch('{{ route('admin.importaciones.preview') }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: payload,
            })
            .then(res => res.json())
            .then(data => {
                this.loading = false;
                if (data.success) {
                    this.excelColumns = data.columns;
                    this.excelPreviewRows = (data.rows || []).map(r => ({ ...r, _selected: true }));
                    this.mapping = {};
                    const fields = this.expectedFields[this.form.tipo] || [];
                    fields.forEach(f => {
                        const colIndex = this.excelColumns.findIndex(c => c.toLowerCase().includes(f.key.toLowerCase().replace(/_/g, ' ')));
                        if (colIndex !== -1) {
                            this.mapping[f.key] = this.excelColumns[colIndex];
                        }
                    });
                    this.showMapping = true;
                } else {
                    Swal.fire('Error', 'Error al leer el archivo: ' + (data.message || 'Error desconocido'), 'error');
                }
            })
            .catch(err => {
                this.loading = false;
                console.error(err);
                Swal.fire('Error', 'Ocurrió un error al previsualizar el archivo.', 'error');
            });
        },

        submitImport() {
            if (!this.isFormValid) {
                Swal.fire('Faltan datos', 'Por favor complete todos los campos requeridos y seleccione un archivo.', 'warning');
                return;
            }
            
            try {
                this.loading = true;
                const payload = new FormData();
                payload.append('nivel_id', this.form.nivel_id);
                payload.append('grado_id', this.form.grado_id);
                payload.append('seccion_id', this.form.seccion_id);
                payload.append('tipo', this.form.tipo);
                if (this.form.tipo === 'directorio' && this.form.nivel_id && this.form.nivel_id.toLowerCase() === 'primaria') {
                    payload.append('apoderado_tipo', this.form.apoderado_tipo);
                }
                if (this.form.tipo === 'notas') {
                    payload.append('bimestre_id', this.form.bimestre_id);
                }
                payload.append('file', this.file);
                payload.append('mapping', JSON.stringify(this.mapping));
                
                const unselectedIndices = this.excelPreviewRows
                    .map((row, idx) => (!row._selected ? idx : -1))
                    .filter(idx => idx !== -1);
                payload.append('unselected_indices', JSON.stringify(unselectedIndices));
                
                fetch('{{ route('admin.importaciones.confirmar') }}', {
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
            // Asegurar que errores sea un array (en caso de que venga como un string json o null)
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

        toggleAllRows(checked) {
            this.excelPreviewRows.forEach(row => row._selected = checked);
        },

        confirmRevert(id) {
            Swal.fire({
                title: '¿Revertir importación?',
                text: "Esto intentará eliminar los estudiantes, padres o notas que se hayan creado durante esta importación. Esta acción no se puede deshacer.",
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

                    fetch('{{ url("admin/importaciones") }}/' + id + '/revertir', {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
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
    };
}
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        </div>
    </div>
</x-app-layout>
