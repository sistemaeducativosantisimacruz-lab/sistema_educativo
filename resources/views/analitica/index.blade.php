<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Analítica Académica') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium">Módulo de Analítica Académica</h3>
                    <p class="mt-4 text-sm text-gray-600">
                        Aquí se visualizarán los datos y gráficos sobre el desempeño académico (Escalas C, B, A, AD).
                    </p>
                    
                    <!-- Contenedor para futuros gráficos o tablas -->
                    <div class="mt-6 border border-gray-200 rounded p-4 bg-gray-50">
                        <p class="text-center text-gray-500 italic">Área de visualización en desarrollo...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
