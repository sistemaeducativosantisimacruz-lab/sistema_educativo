<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Mi Panel Docente') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(!$docente)
                <div class="bg-yellow-50 border border-yellow-300 text-yellow-800 px-6 py-4 rounded-lg shadow-sm">
                    <p class="font-bold">⚠ Perfil no encontrado</p>
                    <p class="text-sm mt-1">Tu cuenta aún no tiene un perfil de docente vinculado. Contacta al administrador.</p>
                </div>
            @else

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-8 py-6 flex items-center gap-6" style="background: linear-gradient(135deg, #0f766e, #047857);">
                    <div class="w-20 h-20 rounded-full flex items-center justify-center shadow-lg flex-shrink-0"
                         style="background: rgba(255,255,255,0.2); border: 2px solid rgba(255,255,255,0.35);">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white" class="w-12 h-12">
                            <path fill-rule="evenodd" d="M7.5 6a4.5 4.5 0 1 1 9 0 4.5 4.5 0 0 1-9 0ZM3.751 20.105a8.25 8.25 0 0 1 16.498 0 .75.75 0 0 1-.437.695A18.683 18.683 0 0 1 12 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 0 1-.437-.695Z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-widest mb-1" style="color: rgba(255,255,255,0.7);">Docente</p>
                        <h1 class="text-2xl font-extrabold leading-tight" style="color: white;">
                            {{ $docente->apellido_paterno }} {{ $docente->apellido_materno }}, {{ $docente->nombres }}
                        </h1>
                        <span class="mt-2 inline-block text-xs font-semibold px-3 py-1 rounded-full"
                              style="background: rgba(0,0,0,0.25); color: rgba(255,255,255,0.9);">
                            {{ $docente->tipo === 'polidocente' ? 'Polidocente' : 'Especialista' }}
                        </span>
                    </div>
                </div>

                <div class="px-8 py-6 grid grid-cols-2 md:grid-cols-4 gap-6">
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-1">DNI</p>
                        <p class="text-lg font-bold text-gray-800 font-mono">{{ $docente->dni }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-1">Nivel</p>
                        <span class="inline-block text-xs font-bold px-3 py-1 rounded-full {{ $docente->nivel === 'primaria' ? 'bg-green-100 text-green-800' : ($docente->nivel === 'secundaria' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') }}">
                            {{ ucfirst($docente->nivel) }}
                        </span>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-1">Celular</p>
                        <p class="text-sm font-semibold text-gray-700">
                            {{ $docente->celular ?? '—' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-1">Tipo</p>
                        <p class="text-sm font-semibold text-gray-700">
                            {{ ucfirst($docente->tipo) }}
                        </p>
                    </div>
                </div>

                @if($docente->esEspecialista())
                <div class="border-t border-gray-100 px-8 py-5 bg-gray-50">
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-3">Cursos Especialidad</p>
                    <p class="text-sm font-semibold text-gray-700">{{ $docente->nombresCursos() }}</p>
                </div>
                @endif
            </div>

            @endif
        </div>
    </div>
</x-app-layout>
