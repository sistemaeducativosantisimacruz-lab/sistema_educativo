<div x-data="diagnosticoEstudiante()" class="space-y-6">

    <!-- Encabezado de la Sección Diagnóstico por Estudiante (Captura 3) -->
    <div>
        <h3 class="text-2xl font-bold text-slate-900 tracking-tight">Diagnóstico por estudiante</h3>
        <p class="text-sm text-slate-500 mt-1">
            {{ request('grado_seccion_id') ? ($secciones->firstWhere('id', request('grado_seccion_id'))->grado->nombre ?? '3.°') . ' ' . ($secciones->firstWhere('id', request('grado_seccion_id'))->seccion->nombre ?? 'A') : '3.° A' }} 
            · {{ request('bimestre_id') ? 'Bimestre ' . ($bimestres->firstWhere('id', request('bimestre_id'))->numero ?? 'II') : 'II Bimestre' }} 
            · Generado automáticamente a partir de los niveles de logro
        </p>
    </div>

    <!-- Layout de Dos Columnas: Lista de Estudiantes (Izq) y Detalle de Diagnóstico (Der) -->
    <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-start">
        
        <!-- Columna Izquierda: Lista de Estudiantes -->
        <div class="md:col-span-4 lg:col-span-3 space-y-2.5">
            <template x-for="est in estudiantes" :key="est.id">
                <button 
                    type="button"
                    @click="seleccionarEstudiante(est)"
                    :class="estudianteSeleccionado.id === est.id ? 'border-slate-800 ring-2 ring-slate-800/20 bg-slate-50 shadow' : 'border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50/60 shadow-sm'"
                    class="w-full flex items-center justify-between p-3.5 rounded-xl border text-left transition-all duration-150">
                    <span class="font-medium text-slate-800 text-sm" x-text="est.nombre"></span>
                    <span 
                        class="px-2.5 py-0.5 rounded text-xs font-bold leading-5 text-white"
                        :class="getBadgeClass(est.nivel_global)"
                        x-text="est.nivel_global">
                    </span>
                </button>
            </template>
        </div>

        <!-- Columna Derecha: Panel de Resumen del Estudiante Seleccionado -->
        <div class="md:col-span-8 lg:col-span-9">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 md:p-8">
                
                <!-- Título del Panel de Resumen -->
                <h4 class="text-lg font-bold text-slate-900 mb-6 flex items-center gap-2">
                    <span x-text="estudianteSeleccionado.nombre"></span>
                    <span class="text-slate-400 font-normal">·</span>
                    <span class="text-slate-600 font-semibold text-base">Resumen</span>
                </h4>

                <!-- Tabla de Niveles por Competencia (Bimestre Anterior vs. Actual) -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100 text-sm">
                        <thead>
                            <tr class="text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">
                                <th scope="col" class="pb-3 pr-4 font-medium text-slate-500">Competencia</th>
                                <th scope="col" class="pb-3 px-4 text-center font-medium text-slate-500 w-24">I Bim.</th>
                                <th scope="col" class="pb-3 pl-4 text-center font-medium text-slate-500 w-24">II Bim.</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            <template x-for="comp in competencias" :key="comp.nombre">
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="py-3.5 pr-4 text-slate-800 font-normal" x-text="comp.nombre"></td>
                                    <td class="py-3.5 px-4 text-center font-semibold text-slate-600" x-text="comp.bimestre_ant"></td>
                                    <td class="py-3.5 pl-4 text-center font-semibold text-slate-900" x-text="comp.bimestre_act"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <!-- Recuadro del Diagnóstico Cualitativo Generado -->
                <div class="mt-8 bg-slate-50/80 rounded-xl p-5 border border-slate-100">
                    <h5 class="text-sm font-bold text-slate-900 mb-2">Diagnóstico generado</h5>
                    <p class="text-sm text-slate-600 leading-relaxed font-normal" x-text="diagnosticoTexto"></p>
                </div>

                <!-- Botón de Exportación PDF A4 -->
                <div class="mt-8 flex justify-start">
                    <button 
                        type="button" 
                        @click="exportarPdfEstudiante()"
                        class="bg-[#1e40af] hover:bg-blue-900 text-white font-semibold text-sm px-6 py-2.5 rounded-lg shadow-sm transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Exportar informe PDF (A4)
                    </button>
                </div>

            </div>
        </div>

    </div>

</div>

<script>
    function diagnosticoEstudiante() {
        return {
            // Datos de ejemplo claramente rotulados conforme a la Captura 3
            estudiantes: [
                { id: 1, nombre: 'Estudiante 01', nivel_global: 'A' },
                { id: 2, nombre: 'Estudiante 02', nivel_global: 'B' },
                { id: 3, nombre: 'Estudiante 03', nivel_global: 'C' },
                { id: 4, nombre: 'Estudiante 04', nivel_global: 'AD' },
                { id: 5, nombre: 'Estudiante 05', nivel_global: 'A' },
            ],
            estudianteSeleccionado: { id: 2, nombre: 'Estudiante 02', nivel_global: 'B' },
            competencias: [
                { nombre: 'Resuelve problemas de cantidad', bimestre_ant: 'B', bimestre_act: 'A' },
                { nombre: 'Resuelve problemas de regularidad, equivalencia y cambio', bimestre_ant: 'B', bimestre_act: 'B' },
                { nombre: 'Resuelve problemas de forma, movimiento y localización', bimestre_ant: 'C', bimestre_act: 'B' },
            ],
            diagnosticoTexto: 'El estudiante alcanza el nivel A en una de tres competencias y se encuentra en proceso (B) en las otras dos. Respecto al bimestre anterior muestra mejora en dos competencias. Se recomienda reforzar la competencia de forma, movimiento y localización.',

            seleccionarEstudiante(est) {
                this.estudianteSeleccionado = est;

                /**
                 * TODO: Conectar con endpoint JSON real en la Iteración 3:
                 * fetch(`/admin/analytics/estudiantes/${est.id}/diagnostico?bimestre_id=${bimestreId}&curso_id=${cursoId}`)
                 *   .then(res => res.json())
                 *   .then(data => {
                 *       this.competencias = data.competencias;
                 *       this.diagnosticoTexto = data.diagnostico;
                 *   });
                 */
                console.log("Cargando diagnóstico reactivo para estudiante ID:", est.id);
            },

            getBadgeClass(nivel) {
                switch(nivel) {
                    case 'AD': return 'bg-[#1e40af] text-white'; // Azul oscuro institucional
                    case 'A':  return 'bg-[#0f766e] text-white'; // Verde azulado institucional
                    case 'B':  return 'bg-[#f59e0b] text-gray-900'; // Ámbar institucional
                    case 'C':  return 'bg-[#b91c1c] text-white'; // Rojo oscuro institucional
                    default:   return 'bg-slate-400 text-white';
                }
            },

            exportarPdfEstudiante() {
                // Abre vista de impresión estándar o disparador de informe
                window.print();
            }
        }
    }
</script>
