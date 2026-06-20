<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Registrar Nuevo Docente') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg max-w-3xl mx-auto">
                <div class="p-6 text-gray-900">
                    
                    <form action="{{ route('admin.docentes.store') }}" method="POST">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- DNI -->
                            <div>
                                <label for="dni" class="block text-sm font-medium text-gray-700">DNI</label>
                                <input type="text" name="dni" id="dni" value="{{ old('dni') }}" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-yellow-500 rounded-md shadow-sm" required maxlength="8" pattern="\d{8}" title="Debe contener 8 dígitos numéricos">
                                @error('dni') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <!-- Curso -->
                            <div>
                                <label for="curso_id" class="block text-sm font-medium text-gray-700">Curso que dictará</label>
                                <select name="curso_id" id="curso_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-yellow-500 rounded-md shadow-sm" required>
                                    <option value="">Seleccione un curso</option>
                                    @foreach($cursos as $curso)
                                        <option value="{{ $curso->id }}" {{ old('curso_id') == $curso->id ? 'selected' : '' }}>
                                            {{ $curso->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('curso_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <!-- Nombres -->
                            <div class="md:col-span-2">
                                <label for="nombres" class="block text-sm font-medium text-gray-700">Nombres</label>
                                <input type="text" name="nombres" id="nombres" value="{{ old('nombres') }}" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-yellow-500 rounded-md shadow-sm" required>
                                @error('nombres') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <!-- Apellido Paterno -->
                            <div>
                                <label for="apellido_paterno" class="block text-sm font-medium text-gray-700">Apellido Paterno</label>
                                <input type="text" name="apellido_paterno" id="apellido_paterno" value="{{ old('apellido_paterno') }}" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-yellow-500 rounded-md shadow-sm" required>
                                @error('apellido_paterno') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <!-- Apellido Materno -->
                            <div>
                                <label for="apellido_materno" class="block text-sm font-medium text-gray-700">Apellido Materno</label>
                                <input type="text" name="apellido_materno" id="apellido_materno" value="{{ old('apellido_materno') }}" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-yellow-500 rounded-md shadow-sm" required>
                                @error('apellido_materno') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <!-- Email -->
                            <div class="md:col-span-2">
                                <label for="email" class="block text-sm font-medium text-gray-700">Correo Electrónico (Acceso al sistema)</label>
                                <input type="email" name="email" id="email" value="{{ old('email') }}" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-yellow-500 rounded-md shadow-sm" required>
                                @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="mt-6 flex items-center justify-end">
                            <a href="{{ route('admin.docentes.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">Cancelar</a>
                            <button type="submit" class="bg-yellow-600 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded transition">
                                Registrar Docente
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
