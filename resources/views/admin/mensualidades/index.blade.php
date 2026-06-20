<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Mensualidades') }}
        </h2>
    </x-slot>

    @php
        $mesesNombres = \App\Models\Mensualidad::meses();
        $anioActual   = now()->year;
        $mesActual    = now()->month;
    @endphp

    <div class="py-6 px-4 sm:px-0" x-data="{
        showGenerar: false,
        selectedIds: [],
        bulkEstado: 'PAGÓ',
        toggleAll(event) {
            const checkboxes = document.querySelectorAll('.row-check');
            this.selectedIds = event.target.checked
                ? Array.from(checkboxes).map(cb => parseInt(cb.value))
                : [];
            checkboxes.forEach(cb => cb.checked = event.target.checked);
        },
        toggleOne(id, checked) {
            if (checked) { this.selectedIds.push(id); }
            else { this.selectedIds = this.selectedIds.filter(i => i !== id); }
        }
    }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-5">

            {{-- ─── Filtros compactos ──────────────────────────────────────── --}}
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden"
                x-data="{
                    nivel: '{{ request('nivel') }}',
                    grado_id: '{{ request('grado_id') }}',
                    seccion_id: '{{ request('grado_seccion_id') }}',
                    grados: {{ $grados->toJson() }},
                    gradoSecciones: {{ $gradoSecciones->map(fn($gs) => [
                        'id'             => $gs->id,
                        'grado_id'       => $gs->grado_id,
                        'seccion_nombre' => $gs->seccion->nombre,
                        'nivel'          => $gs->grado->nivel,
                    ])->toJson() }},
                    get filteredGrados() {
                        return this.nivel ? this.grados.filter(g => g.nivel === this.nivel) : this.grados;
                    },
                    get filteredSecciones() {
                        let r = this.gradoSecciones;
                        if (this.nivel)    r = r.filter(gs => gs.nivel === this.nivel);
                        if (this.grado_id) r = r.filter(gs => gs.grado_id == this.grado_id);
                        return r;
                    }
                }">
                <form action="{{ route('admin.mensualidades.index') }}" method="GET">
                    <div class="grid grid-cols-2 sm:flex sm:flex-wrap items-center gap-2 px-3 py-2.5">

                        {{-- Icono embudo --}}
                        <svg class="w-4 h-4 text-gray-400 shrink-0 hidden sm:block" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                        </svg>

                        {{-- Grupo Mes / Año en píldora --}}
                        <div class="col-span-2 sm:col-auto flex items-center gap-1 bg-gray-50 border border-gray-200 rounded-lg px-2 py-1 shrink-0 w-full sm:w-auto">
                            <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <select name="mes" title="Mes"
                                class="bg-transparent border-none text-xs font-semibold text-gray-700 focus:ring-0 focus:outline-none py-0 pl-0 pr-5 cursor-pointer">
                                @foreach($mesesNombres as $num => $nombre)
                                    <option value="{{ $num }}" {{ $mes == $num ? 'selected' : '' }}>{{ $nombre }}</option>
                                @endforeach
                            </select>
                            <span class="text-gray-300 text-xs select-none">/</span>
                            <select name="anio" title="Año"
                                class="w-full bg-transparent border-none text-xs font-semibold text-gray-700 focus:ring-0 focus:outline-none py-0 pl-0 pr-5 cursor-pointer">
                                @foreach($aniosDisponibles as $y)
                                    <option value="{{ $y }}" {{ $anio == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Divisor vertical --}}
                        <div class="hidden sm:block w-px h-5 bg-gray-200 shrink-0"></div>

                        {{-- Nivel --}}
                        <select name="nivel" x-model="nivel" @change="grado_id = ''; seccion_id = ''" title="Nivel educativo"
                            class="col-span-2 sm:col-auto w-full sm:w-auto bg-gray-50 border border-gray-200 rounded-lg text-xs font-semibold text-gray-700
                                   focus:ring-1 focus:ring-emerald-400 focus:border-emerald-400
                                   py-1.5 pl-2.5 pr-7 cursor-pointer shrink-0 transition">
                            <option value="">Nivel</option>
                            <option value="primaria">Primaria</option>
                            <option value="secundaria">Secundaria</option>
                        </select>

                        {{-- Grado --}}
                        <select name="grado_id" x-model="grado_id" @change="seccion_id = ''" title="Grado"
                            class="col-span-1 sm:col-auto w-full sm:w-auto bg-gray-50 border border-gray-200 rounded-lg text-xs font-semibold text-gray-700
                                   focus:ring-1 focus:ring-emerald-400 focus:border-emerald-400
                                   py-1.5 pl-2.5 pr-7 cursor-pointer shrink-0 transition">
                            <option value="">Grado</option>
                            <template x-for="g in filteredGrados" :key="g.id">
                                <option :value="g.id" x-text="g.nombre" :selected="g.id == grado_id"></option>
                            </template>
                        </select>

                        {{-- Sección --}}
                        <select name="grado_seccion_id" x-model="seccion_id" title="Sección"
                            class="col-span-1 sm:col-auto w-full sm:w-auto bg-gray-50 border border-gray-200 rounded-lg text-xs font-semibold text-gray-700
                                   focus:ring-1 focus:ring-emerald-400 focus:border-emerald-400
                                   py-1.5 pl-2.5 pr-7 cursor-pointer shrink-0 transition">
                            <option value="">Sección</option>
                            <template x-for="gs in filteredSecciones" :key="gs.id">
                                <option :value="gs.id" x-text="'Sec. ' + gs.seccion_nombre" :selected="gs.id == seccion_id"></option>
                            </template>
                        </select>

                        {{-- Divisor vertical --}}
                        <div class="hidden sm:block w-px h-5 bg-gray-200 shrink-0"></div>

                        {{-- Búsqueda con icono interno --}}
                        <div class="col-span-2 sm:col-auto flex items-center gap-1.5 bg-gray-50 border border-gray-200 rounded-lg
                                    px-2.5 py-1.5 w-full sm:flex-1 min-w-[130px]
                                    focus-within:border-emerald-400 focus-within:ring-1 focus-within:ring-emerald-400 transition">
                            <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
                            </svg>
                            <input type="text" name="search" value="{{ request('search') }}"
                                placeholder="Nombre o DNI..."
                                class="bg-transparent border-none text-xs text-gray-700 focus:ring-0 focus:outline-none w-full placeholder-gray-400 min-w-0">
                        </div>

                        {{-- Botón Filtrar --}}
                        <button type="submit"
                            class="col-span-1 sm:col-auto w-full sm:w-auto justify-center bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white text-xs font-bold
                                   py-1.5 px-4 rounded-lg shadow-sm transition-all flex items-center gap-1.5 whitespace-nowrap">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                            Filtrar
                        </button>

                        {{-- Botón Limpiar --}}
                        <a href="{{ route('admin.mensualidades.index') }}" title="Limpiar filtros"
                            class="col-span-1 sm:col-auto w-full sm:w-auto justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100
                                   text-xs font-medium py-1.5 px-2.5 rounded-lg transition flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            <span class="sm:hidden xl:inline">Limpiar</span>
                        </a>

                    </div>
                </form>
            </div>

            {{-- ─── Cabecera título + botón generar ─────────────────────── --}}
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
                <div>
                    <h3 class="text-xl font-bold text-gray-800">
                        {{ $mesesNombres[$mes] ?? '' }} {{ $anio }}
                    </h3>
                    <p class="text-sm text-gray-500">Estado de pago mensual de los estudiantes</p>
                </div>
                <button @click="showGenerar = true"
                    class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-5 rounded-lg shadow transition text-sm shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Generar Lista de Mes
                </button>
            </div>

            {{-- ─── Alertas ──────────────────────────────────────────────── --}}
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-800 px-4 py-2.5 rounded-lg shadow-sm flex items-center gap-2 text-sm">
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-800 px-4 py-2.5 rounded-lg text-sm">
                    {{ session('error') }}
                </div>
            @endif

            @if(!$anoActivo)
                <div class="bg-red-50 border border-red-300 text-red-700 px-5 py-4 rounded-xl font-bold text-center">
                    No hay un ano lectivo activo. No se pueden gestionar mensualidades.
                </div>
            @else

            {{-- ─── Resumen: toggle cards / gráfico ─────────────────────── --}}
            @php
                $debe        = $stats['DEBE']        ?? 0;
                $pago        = $stats['PAGÓ']        ?? 0;
                $exonerado   = $stats['EXONERADO']   ?? 0;
                $beneficiado = $stats['BENEFICIADO'] ?? 0;
                $total       = $debe + $pago + $exonerado + $beneficiado;
            @endphp
            <div x-data="{ vistaGrafico: false }">
                {{-- Botón toggle --}}
                <div class="flex justify-end mb-3">
                    <div class="inline-flex rounded-lg border border-gray-200 bg-white shadow-sm overflow-hidden">
                        <button @click="vistaGrafico = false"
                            :class="!vistaGrafico ? 'bg-emerald-600 text-white' : 'text-gray-600 hover:bg-gray-50'"
                            class="px-4 py-1.5 text-xs font-bold transition flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                            Resumen
                        </button>
                        <button @click="vistaGrafico = true; $nextTick(() => renderChart())"
                            :class="vistaGrafico ? 'bg-emerald-600 text-white' : 'text-gray-600 hover:bg-gray-50'"
                            class="px-4 py-1.5 text-xs font-bold transition flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/></svg>
                            Gráfico
                        </button>
                    </div>
                </div>

                {{-- Vista: Cards --}}
                <div x-show="!vistaGrafico" class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-4 text-center shadow-sm">
                        <div class="text-3xl font-extrabold text-red-700">{{ $debe }}</div>
                        <div class="text-xs font-bold text-red-600 uppercase tracking-wider mt-1">Deben</div>
                    </div>
                    <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-4 text-center shadow-sm">
                        <div class="text-3xl font-extrabold text-green-700">{{ $pago }}</div>
                        <div class="text-xs font-bold text-green-600 uppercase tracking-wider mt-1">Pagaron</div>
                    </div>
                    <div class="rounded-xl border border-blue-200 bg-blue-50 px-4 py-4 text-center shadow-sm">
                        <div class="text-3xl font-extrabold text-blue-700">{{ $exonerado }}</div>
                        <div class="text-xs font-bold text-blue-600 uppercase tracking-wider mt-1">Exonerados</div>
                    </div>
                    <div class="rounded-xl border border-purple-200 bg-purple-50 px-4 py-4 text-center shadow-sm">
                        <div class="text-3xl font-extrabold text-purple-700">{{ $beneficiado }}</div>
                        <div class="text-xs font-bold text-purple-600 uppercase tracking-wider mt-1">Beneficiados</div>
                    </div>
                </div>

                {{-- Vista: Gráfico --}}
                <div x-show="vistaGrafico" x-cloak
                    class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 flex flex-col sm:flex-row items-center gap-6">
                    <div class="relative" style="width:200px;height:200px">
                        <canvas id="mensualidadChart"></canvas>
                        <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                            <span class="text-2xl font-extrabold text-gray-800">{{ $total }}</span>
                            <span class="text-xs text-gray-500 font-medium">Total</span>
                        </div>
                    </div>
                    <div class="flex flex-col gap-3 text-sm">
                        <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-red-400 inline-block"></span><span class="font-semibold text-gray-700">Deben:</span> <span class="font-bold text-red-700">{{ $debe }}</span> @if($total > 0)<span class="text-gray-400 text-xs">({{ round($debe/$total*100,1) }}%)</span>@endif</div>
                        <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-green-400 inline-block"></span><span class="font-semibold text-gray-700">Pagaron:</span> <span class="font-bold text-green-700">{{ $pago }}</span> @if($total > 0)<span class="text-gray-400 text-xs">({{ round($pago/$total*100,1) }}%)</span>@endif</div>
                        <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-blue-400 inline-block"></span><span class="font-semibold text-gray-700">Exonerados:</span> <span class="font-bold text-blue-700">{{ $exonerado }}</span> @if($total > 0)<span class="text-gray-400 text-xs">({{ round($exonerado/$total*100,1) }}%)</span>@endif</div>
                        <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-purple-400 inline-block"></span><span class="font-semibold text-gray-700">Beneficiados:</span> <span class="font-bold text-purple-700">{{ $beneficiado }}</span> @if($total > 0)<span class="text-gray-400 text-xs">({{ round($beneficiado/$total*100,1) }}%)</span>@endif</div>
                    </div>
                </div>
            </div>

            <script>
            function renderChart() {
                const ctx = document.getElementById('mensualidadChart');
                if (!ctx) return;
                if (ctx._chart) { ctx._chart.destroy(); }
                ctx._chart = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['DEBE','PAGÓ','EXONERADO','BENEFICIADO'],
                        datasets: [{
                            data: [{{ $debe }}, {{ $pago }}, {{ $exonerado }}, {{ $beneficiado }}],
                            backgroundColor: ['#f87171','#4ade80','#60a5fa','#c084fc'],
                            borderWidth: 2,
                            borderColor: '#fff',
                        }]
                    },
                    options: {
                        cutout: '68%',
                        plugins: { legend: { display: false }, tooltip: { callbacks: {
                            label: ctx => ` ${ctx.label}: ${ctx.parsed}`
                        }}},
                        animation: { duration: 500 }
                    }
                });
            }
            </script>

            {{-- ─── Acciones masivas ────────────────────────────────────── --}}
            <div x-show="selectedIds.length > 0" x-cloak
                class="bg-amber-50 border border-amber-300 rounded-xl px-4 py-2.5">
                <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                    <span class="text-sm font-bold text-amber-800">
                        <span x-text="selectedIds.length"></span> seleccionado(s) —
                    </span>
                    <form action="{{ route('admin.mensualidades.masivo') }}" method="POST"
                        class="flex flex-wrap items-center gap-2">
                        @csrf
                        <template x-for="id in selectedIds" :key="id">
                            <input type="hidden" name="ids[]" :value="id">
                        </template>
                        <span class="text-sm text-amber-700">Cambiar a:</span>
                        <select name="estado" x-model="bulkEstado"
                            class="rounded-md border-amber-300 text-sm shadow-sm focus:ring-amber-400 py-1">
                            <option value="DEBE">DEBE</option>
                            <option value="PAGÓ">PAGÓ</option>
                            <option value="EXONERADO">EXONERADO</option>
                            <option value="BENEFICIADO">BENEFICIADO</option>
                        </select>
                        <button type="submit"
                            class="bg-green-600 hover:bg-green-700 text-white text-sm font-bold py-1 px-4 rounded-lg shadow transition"
                            onclick="return confirm('¿Actualizar el estado de todos los seleccionados?')">
                            Aplicar
                        </button>
                    </form>
                </div>
            </div>

            {{-- ─── Tabla ────────────────────────────────────────────────── --}}
            <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
                @if($mensualidades->isEmpty())
                    <div class="py-16 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                        </svg>
                        <p class="mt-3 text-gray-500 font-medium">
                            No hay registros para <strong>{{ $mesesNombres[$mes] ?? '' }} {{ $anio }}</strong>.
                        </p>
                        <p class="text-sm text-gray-400 mt-1">Usa el botón "Generar Lista de Mes" para crearlos.</p>
                    </div>
                @else
                    <div class="overflow-x-auto overflow-y-auto max-h-[65vh]">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50 sticky top-0 z-10">
                                <tr>
                                    <th class="px-4 py-3 text-left w-10">
                                        <input type="checkbox" @change="toggleAll($event)"
                                            class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider hidden sm:table-cell">DNI</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Estudiante</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider hidden md:table-cell">Grado / Sección</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider hidden lg:table-cell">Nivel</th>
                                    <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Estado</th>
                                    <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($mensualidades as $m)
                                    @php
                                        $est = $m->matricula->estudiante;
                                        $gs  = $m->matricula->gradoSeccion;
                                    @endphp
                                    <tr class="hover:bg-gray-50 transition-colors"
                                        x-data="{ estado: '{{ $m->estado }}' }">

                                        {{-- Checkbox --}}
                                        <td class="px-4 py-3">
                                            <input type="checkbox"
                                                class="row-check rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                                                value="{{ $m->id }}"
                                                @change="toggleOne({{ $m->id }}, $event.target.checked)">
                                        </td>

                                        {{-- DNI --}}
                                        <td class="px-4 py-3 font-mono text-gray-600 whitespace-nowrap text-xs hidden sm:table-cell">
                                            {{ $est->dni }}
                                        </td>

                                        {{-- Nombre --}}
                                        <td class="px-4 py-3">
                                            <span class="font-semibold text-gray-800 block">
                                                {{ $est->apellido_paterno }} {{ $est->apellido_materno }}, {{ $est->nombres }}
                                            </span>
                                            <span class="text-xs text-gray-400 md:hidden">
                                                {{ $gs->grado->nombre }} – Sec. {{ $gs->seccion->nombre }}
                                            </span>
                                        </td>

                                        {{-- Grado/Sección --}}
                                        <td class="px-4 py-3 whitespace-nowrap text-gray-600 hidden md:table-cell">
                                            {{ $gs->grado->nombre }} – Sec. {{ $gs->seccion->nombre }}
                                        </td>

                                        {{-- Nivel --}}
                                        <td class="px-4 py-3 whitespace-nowrap hidden lg:table-cell">
                                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold
                                                {{ $gs->grado->nivel === 'primaria' ? 'bg-sky-100 text-sky-700' : 'bg-orange-100 text-orange-700' }}">
                                                {{ ucfirst($gs->grado->nivel) }}
                                            </span>
                                        </td>

                                        {{-- Estado badge --}}
                                        <td class="px-4 py-3 text-center whitespace-nowrap">
                                            <span :class="{
                                                'bg-red-100 text-red-800':       estado === 'DEBE',
                                                'bg-green-100 text-green-800':   estado === 'PAGÓ',
                                                'bg-blue-100 text-blue-800':     estado === 'EXONERADO',
                                                'bg-purple-100 text-purple-800': estado === 'BENEFICIADO',
                                            }" class="px-2.5 py-1 rounded-full text-xs font-bold inline-block"
                                                x-text="estado">
                                            </span>
                                        </td>

                                        {{-- Acción inline: select + guardar directo --}}
                                        <td class="px-4 py-3 text-center whitespace-nowrap">
                                            <form action="{{ route('admin.mensualidades.update', $m) }}" method="POST"
                                                class="flex items-center justify-center gap-2">
                                                @csrf
                                                @method('PATCH')
                                                <select name="estado" x-model="estado"
                                                    class="rounded-md border-gray-300 text-xs shadow-sm focus:ring-emerald-500 focus:border-emerald-500 py-1 pr-7">
                                                    <option value="DEBE">DEBE</option>
                                                    <option value="PAGÓ">PAGÓ</option>
                                                    <option value="EXONERADO">EXONERADO</option>
                                                    <option value="BENEFICIADO">BENEFICIADO</option>
                                                </select>
                                                <button type="submit"
                                                    class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold py-1 px-3 rounded-lg shadow transition">
                                                    Guardar
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Paginación --}}
                    <div class="p-4 border-t border-gray-100">
                        {{ $mensualidades->links() }}
                    </div>
                @endif
            </div>

            @endif {{-- fin @if $anoActivo --}}
        </div>

        {{-- ─── Modal: Generar Lista ────────────────────────────────────── --}}
        <div x-show="showGenerar" x-cloak
            class="fixed inset-0 z-50 overflow-y-auto"
            role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div x-show="showGenerar"
                    x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-150"
                    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                    class="fixed inset-0 bg-gray-700 bg-opacity-60 backdrop-blur-sm"
                    @click="showGenerar = false">
                </div>

                <div x-show="showGenerar"
                    x-transition:enter="ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 z-10">

                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-gray-900">Generar Lista de Mensualidades</h3>
                            <p class="text-xs text-gray-500">Crea registros "DEBE" para todos los estudiantes activos del mes.</p>
                        </div>
                    </div>

                    <form action="{{ route('admin.mensualidades.generar') }}" method="POST" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Mes *</label>
                                <select name="mes" required
                                    class="block w-full rounded-lg border-gray-300 shadow-sm text-sm focus:ring-emerald-500 focus:border-emerald-500">
                                    @foreach($mesesNombres as $num => $nombre)
                                        <option value="{{ $num }}" {{ $mes == $num ? 'selected' : '' }}>{{ $nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Año *</label>
                                <select name="anio" required
                                    class="block w-full rounded-lg border-gray-300 shadow-sm text-sm focus:ring-emerald-500 focus:border-emerald-500">
                                    @foreach($aniosDisponibles as $y)
                                        <option value="{{ $y }}" {{ $anio == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="bg-amber-50 border border-amber-200 rounded-lg px-4 py-3 text-xs text-amber-800">
                            <strong>ℹ️ Nota:</strong> Solo se crean registros <u>nuevos</u>. Si un estudiante ya tiene registro para ese mes/año, no se modifica.
                        </div>

                        <div class="flex justify-end gap-3 pt-1">
                            <button type="button" @click="showGenerar = false"
                                class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-2 px-4 rounded-lg transition text-sm">
                                Cancelar
                            </button>
                            <button type="submit"
                                class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-6 rounded-lg shadow transition text-sm">
                                Generar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
