<!-- ==================== TAB: GRADOS / SECCIONES ==================== -->
                        @if($tab === 'secciones')
                            <div x-data="{
                                showVerModal: false,
                                verLoading: false,
                                verData: { grado_seccion_id: null, seccion_nombre: '', anio: '', nivel: '', tutor: null, cotutor: null, estudiantes: [] },
                                async abrirVer(gsId) {
                                    this.verLoading = true;
                                    this.showVerModal = true;
                                    this.verData = { grado_seccion_id: null, seccion_nombre: '', anio: '', nivel: '', tutor: null, cotutor: null, estudiantes: [] };
                                    try {
                                        const res = await fetch('/admin/grado-secciones/' + gsId + '/detalle', {
                                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                                        });
                                        this.verData = await res.json();
                                    } catch(e) {
                                        console.error(e);
                                    } finally {
                                        this.verLoading = false;
                                    }
                                }
                            }" class="space-y-6">
                                <div class="flex items-center justify-between border-b pb-4 mb-6">
                                    <h3 class="text-lg font-bold text-gray-800">Carga de Alumnos por Sección y Tutoría</h3>
                                    <span class="bg-purple-100 text-purple-800 text-xs font-semibold px-2.5 py-0.5 rounded">Secciones: {{ $seccionesReport->count() }}</span>
                                </div>

                                @if($seccionesReport->isEmpty())
                                    <div class="text-center py-12 text-gray-500">
                                        <p class="text-lg font-medium">No hay secciones registradas para el año lectivo activo.</p>
                                    </div>
                                @else
                                    @php
                                        $seccionesPorNivel = $seccionesReport->groupBy(fn($s) => $s->grado->nivel ?? 'primaria');
                                    @endphp

                                    @foreach(['primaria' => 'Primaria', 'secundaria' => 'Secundaria'] as $nivelKey => $nivelLabel)
                                        @if($seccionesPorNivel->has($nivelKey))
                                            @php
                                                $isPrimaria = $nivelKey === 'primaria';
                                                $bannerBg        = $isPrimaria ? '#fefce8' : '#f0f9ff';
                                                $bannerBorder    = $isPrimaria ? '#fde047' : '#7dd3fc';
                                                $bannerBorderAll = $isPrimaria ? '#fef08a' : '#bae6fd';
                                                $badgeBg         = $isPrimaria ? '#facc15' : '#38bdf8';
                                                $badgeText       = $isPrimaria ? '#713f12' : '#0c4a6e';
                                                $titleColor      = $isPrimaria ? '#854d0e' : '#075985';
                                                $lineColor       = $isPrimaria ? '#fde68a' : '#bae6fd';

                                                $seccionesOrdenadas = $seccionesPorNivel[$nivelKey]->sortBy(function($a) {
                                                    $orden = str_pad($a->grado->orden ?? 0, 5, '0', STR_PAD_LEFT);
                                                    $seccionNombre = strtolower($a->seccion->nombre ?? '');
                                                    return $orden . '-' . $seccionNombre;
                                                });
                                            @endphp

                                            {{-- Separador visible entre Primaria y Secundaria (solo para secundaria) --}}
                                            @if(!$isPrimaria && $seccionesPorNivel->has('primaria'))
                                                <div class="my-8 flex items-center gap-4">
                                                    <div class="flex-1 h-0.5 bg-gradient-to-r from-gray-200 to-transparent"></div>
                                                    <span class="text-xs font-bold tracking-wider text-gray-400 uppercase">─── Nivel Secundaria ───</span>
                                                    <div class="flex-1 h-0.5 bg-gradient-to-l from-gray-200 to-transparent"></div>
                                                </div>
                                            @endif

                                            {{-- Banner de nivel --}}
                                            <div class="mb-6 flex items-center gap-4 p-3 rounded-xl" style="background:{{ $bannerBg }}; border:1px solid {{ $bannerBorderAll }}; border-left:4px solid {{ $bannerBorder }};">
                                                <span class="text-xs font-extrabold uppercase tracking-wider px-3 py-1 rounded-full" style="background:{{ $badgeBg }}; color:{{ $badgeText }};">
                                                    Nivel {{ $nivelLabel }}
                                                </span>
                                                <span class="text-sm font-bold text-gray-700" style="color:{{ $titleColor }};">
                                                    {{ $seccionesOrdenadas->count() }} {{ $seccionesOrdenadas->count() === 1 ? 'sección' : 'secciones' }}
                                                </span>
                                                <div class="flex-1 h-px" style="background:{{ $lineColor }};"></div>
                                            </div>

                                            {{-- Grid de Tarjetas --}}
                                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">
                                                @foreach($seccionesOrdenadas as $seccion)
                                                    <div class="bg-white border border-gray-150 rounded-xl p-5 shadow-sm hover:shadow transition-shadow flex flex-col justify-between">
                                                        <div>
                                                            <div class="flex justify-between items-start mb-3">
                                                                <h4 class="text-lg font-extrabold text-gray-800">
                                                                    {{ $seccion->grado->nombre ?? '—' }} - {{ $seccion->seccion->nombre ?? '—' }}
                                                                </h4>
                                                                <span class="bg-purple-100 text-purple-800 font-bold text-sm px-2.5 py-1 rounded-lg">
                                                                    {{ $seccion->matriculas_count }} Alumnos
                                                                </span>
                                                            </div>

                                                            <div class="space-y-2 mt-4 text-sm text-gray-600">
                                                                <div class="flex items-center gap-2">
                                                                    <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                                    </svg>
                                                                    <span>
                                                                        <strong class="text-gray-700">Tutor:</strong> 
                                                                        {{ $seccion->tutor ? ($seccion->tutor->apellido_paterno . ' ' . $seccion->tutor->apellido_materno . ', ' . $seccion->tutor->nombres) : 'No asignado' }}
                                                                    </span>
                                                                </div>
                                                                <div class="flex items-center gap-2">
                                                                    <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                                                    </svg>
                                                                    <span>
                                                                        <strong class="text-gray-700">Cotutor:</strong> 
                                                                        {{ $seccion->cotutor ? ($seccion->cotutor->apellido_paterno . ' ' . $seccion->cotutor->apellido_materno . ', ' . $seccion->cotutor->nombres) : 'No asignado' }}
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="mt-4 pt-3 flex flex-col gap-3">
                                                            <button @click="abrirVer({{ $seccion->id }})" 
                                                                    class="w-full text-white font-bold py-2 px-4 rounded-lg text-center text-sm transition shadow-sm flex items-center justify-center gap-2"
                                                                    style="background-color: #6d28d9; border: 1px solid #5b21b6; box-shadow: 0 4px 6px -1px rgba(109,40,217,0.3);"
                                                                    onmouseover="this.style.backgroundColor='#5b21b6'"
                                                                    onmouseout="this.style.backgroundColor='#6d28d9'">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                                </svg>
                                                                Ver Alumnos / Tutor
                                                            </button>

                                                            <div class="border-t pt-3 flex justify-between items-center text-xs text-gray-400">
                                                                <span>Año: {{ $anoActivo->anio }}</span>
                                                                <span class="flex items-center gap-1">
                                                                    <span class="w-2 h-2 rounded-full {{ $seccion->activo ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
                                                                    {{ $seccion->activo ? 'Activo' : 'Inactivo' }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    @endforeach
                                @endif

                                <!-- Modal: Ver Detalle de Sección (Tutor + Estudiantes) -->
                                <div x-show="showVerModal"
                                     class="fixed inset-0 z-50"
                                     aria-labelledby="modal-ver" role="dialog" aria-modal="true" x-cloak>

                                    <div class="absolute inset-0 bg-gray-900 bg-opacity-50 transition-opacity" @click="showVerModal = false"></div>

                                    <div class="relative z-10 flex min-h-full items-center justify-center p-4 pointer-events-none">

                                        <div class="pointer-events-auto w-full max-w-3xl bg-white rounded-2xl shadow-2xl flex flex-col transition-all transform"
                                             style="max-height: 85vh; border: 2px solid #7c3aed; box-shadow: 0 0 0 4px rgba(124,58,237,0.15), 0 25px 50px -12px rgba(0,0,0,0.4);"
                                             @click.stop>

                                            <div class="flex items-center justify-between px-5 py-3 rounded-t-2xl shrink-0"
                                                 :style="verData.nivel === 'primaria'
                                                     ? 'background: linear-gradient(to right, #ca8a04, #eab308);'
                                                     : 'background: linear-gradient(to right, #7c3aed, #8b5cf6);'">
                                                <div>
                                                    <h3 class="text-base font-extrabold text-white" id="modal-ver">Reporte de Sección</h3>
                                                    <div class="mt-1 flex items-center gap-2 flex-wrap">
                                                        <div class="inline-block px-3 py-1 rounded-lg text-white text-xs font-bold shadow-sm"
                                                             style="background-color: rgba(255,255,255,0.25); border: 1px solid rgba(255,255,255,0.4);">
                                                            <span x-text="verData.seccion_nombre"></span>
                                                        </div>
                                                        <div class="inline-block px-3 py-1 rounded-lg text-white text-xs font-bold"
                                                             style="background-color: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.3);">
                                                            Año <span x-text="verData.anio"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <button @click="showVerModal = false"
                                                        class="ml-4 shrink-0 rounded-lg p-1.5 transition"
                                                        style="background-color: rgba(255,255,255,0.2); color: white;"
                                                        onmouseover="this.style.backgroundColor='rgba(255,255,255,0.35)'"
                                                        onmouseout="this.style.backgroundColor='rgba(255,255,255,0.2)'"
                                                        title="Cerrar">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </div>

                                            <div x-show="verLoading" class="flex items-center justify-center py-10 shrink-0">
                                                <svg class="animate-spin h-6 w-6 text-violet-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                                                </svg>
                                                <span class="ml-2 text-gray-500 text-sm">Cargando datos...</span>
                                            </div>

                                            <div x-show="!verLoading" class="flex-1 overflow-y-auto min-h-0 p-5">

                                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-5">
                                                    <div class="rounded-xl border border-blue-200 bg-blue-50 p-3">
                                                        <p class="text-xs font-extrabold text-blue-700 uppercase tracking-wide mb-1.5">Tutor Principal</p>
                                                        <template x-if="verData.tutor">
                                                            <div>
                                                                <p class="text-sm font-bold text-gray-800" x-text="verData.tutor.nombre_completo"></p>
                                                                <p class="text-xs text-blue-600 mt-0.5" x-show="verData.tutor && verData.tutor.dni" x-text="'DNI: ' + (verData.tutor ? verData.tutor.dni : '')"></p>
                                                            </div>
                                                        </template>
                                                        <template x-if="!verData.tutor">
                                                            <p class="text-sm text-gray-400 italic">— Sin tutor asignado —</p>
                                                        </template>
                                                    </div>
                                                    <div class="rounded-xl border border-indigo-200 bg-indigo-50 p-3">
                                                        <p class="text-xs font-extrabold text-indigo-700 uppercase tracking-wide mb-1.5">Co-tutor</p>
                                                        <template x-if="verData.cotutor">
                                                            <div>
                                                                <p class="text-sm font-bold text-gray-800" x-text="verData.cotutor.nombre_completo"></p>
                                                                <p class="text-xs text-indigo-600 mt-0.5" x-show="verData.cotutor && verData.cotutor.dni" x-text="'DNI: ' + (verData.cotutor ? verData.cotutor.dni : '')"></p>
                                                            </div>
                                                        </template>
                                                        <template x-if="!verData.cotutor">
                                                            <p class="text-sm text-gray-400 italic">— Sin co-tutor asignado —</p>
                                                        </template>
                                                    </div>
                                                </div>

                                                <div class="rounded-xl border border-gray-200 overflow-hidden">
                                                    <div class="flex items-center gap-2 px-3 py-2 bg-violet-50 border-b border-violet-100">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                        </svg>
                                                        <span class="text-xs font-extrabold text-violet-700 uppercase tracking-wide">Lista de Estudiantes</span>
                                                        <span class="ml-auto text-xs font-bold text-violet-600 bg-white px-2 py-0.5 rounded-full border border-violet-200"
                                                              x-text="verData.estudiantes.length + ' alumno(s)'"></span>
                                                    </div>
                                                    <div class="overflow-x-auto">
                                                        <table class="w-full text-xs">
                                                            <thead class="bg-gray-50">
                                                                <tr>
                                                                    <th class="text-left px-3 py-2 font-bold text-gray-600 border-b border-gray-200 w-8">#</th>
                                                                    <th class="text-left px-3 py-2 font-bold text-gray-600 border-b border-gray-200">DNI</th>
                                                                    <th class="text-left px-3 py-2 font-bold text-gray-600 border-b border-gray-200">Cód. Estudiante</th>
                                                                    <th class="text-left px-3 py-2 font-bold text-gray-600 border-b border-gray-200">Apellidos y Nombres</th>
                                                                    <th class="text-left px-3 py-2 font-bold text-gray-600 border-b border-gray-200 whitespace-nowrap">Fec. Nacimiento</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <template x-if="verData.estudiantes.length === 0">
                                                                    <tr>
                                                                        <td colspan="5" class="text-center py-8 text-gray-400 italic">Sin estudiantes matriculados en esta sección.</td>
                                                                    </tr>
                                                                </template>
                                                                <template x-for="(est, idx) in verData.estudiantes" :key="est.id">
                                                                    <tr class="border-b border-gray-100 last:border-0 hover:bg-violet-50 transition-colors">
                                                                        <td class="px-3 py-2 text-gray-400 font-semibold" x-text="idx + 1"></td>
                                                                        <td class="px-3 py-2 font-mono text-gray-600" x-text="est.dni"></td>
                                                                        <td class="px-3 py-2 font-mono text-gray-500" x-text="est.codigo_estudiante || '—'"></td>
                                                                        <td class="px-3 py-2 font-semibold text-gray-800" x-text="est.apellidos + ',  ' + est.nombres"></td>
                                                                        <td class="px-3 py-2 text-gray-500 whitespace-nowrap" x-text="est.fecha_nacimiento"></td>
                                                                    </tr>
                                                                </template>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="px-5 py-3 bg-gray-50 rounded-b-2xl flex justify-between items-center border-t shrink-0">
                                                <a :href="'/admin/grado-secciones/' + verData.grado_seccion_id + '/exportar'"
                                                   class="inline-flex items-center gap-2 text-sm font-bold px-5 py-2 rounded-lg transition shadow-sm cursor-pointer"
                                                   style="background-color: #059669; color: white; border: 1px solid #047857;"
                                                   onmouseover="this.style.backgroundColor='#047857'"
                                                   onmouseout="this.style.backgroundColor='#059669'">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                                    </svg>
                                                    Descargar Excel
                                                </a>
                                                <button @click="showVerModal = false"
                                                        class="inline-flex items-center gap-2 text-sm font-bold px-5 py-2 rounded-lg transition shadow-sm"
                                                        style="background-color: #dc2626; color: white; border: 1px solid #b91c1c;"
                                                        onmouseover="this.style.backgroundColor='#b91c1c'"
                                                        onmouseout="this.style.backgroundColor='#dc2626'">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                    Cerrar
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        @endif
