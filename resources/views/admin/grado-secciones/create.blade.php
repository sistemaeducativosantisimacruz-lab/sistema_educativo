<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Asignar Sección a Grado') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg max-w-2xl mx-auto">
                <div class="p-6 text-gray-900">
                    
                    @if(!$anoActivo)
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                            No hay un año lectivo activo. No puedes crear asignaciones.
                        </div>
                    @else
                        <form action="{{ route('admin.grado-secciones.store') }}" method="POST">
                            @csrf

                            <div class="mb-4">
                                <label for="grado_id" class="block text-sm font-medium text-gray-700">Grado</label>
                                <select name="grado_id" id="grado_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-yellow-500 rounded-md shadow-sm" required>
                                    <option value="">Seleccione un grado</option>
                                    @foreach($grados as $grado)
                                        <option value="{{ $grado->id }}" {{ old('grado_id') == $grado->id ? 'selected' : '' }}>
                                            {{ $grado->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('grado_id')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="mb-6">
                                <label for="seccion_id" class="block text-sm font-medium text-gray-700">Sección</label>
                                @if($secciones->isEmpty())
                                    <div class="text-sm text-red-600 mt-1">No hay secciones registradas en el sistema. Debe registrar al menos una en la BD (Ej: A, B, C).</div>
                                @else
                                    <select name="seccion_id" id="seccion_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-yellow-500 rounded-md shadow-sm" required>
                                        <option value="">Seleccione una sección</option>
                                        @foreach($secciones as $seccion)
                                            <option value="{{ $seccion->id }}" {{ old('seccion_id') == $seccion->id ? 'selected' : '' }}>
                                                {{ $seccion->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                @endif
                                @error('seccion_id')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex items-center justify-end">
                                <a href="{{ route('admin.grado-secciones.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">Cancelar</a>
                                <button type="submit" class="bg-yellow-600 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded transition" {{ $secciones->isEmpty() ? 'disabled' : '' }}>
                                    Guardar Asignación
                                </button>
                            </div>
                        </form>
                    @endif

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
