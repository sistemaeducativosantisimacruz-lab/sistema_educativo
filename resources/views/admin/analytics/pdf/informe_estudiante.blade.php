<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informe de Rendimiento - {{ $estudiante->apellidos_nombres ?? ($estudiante->apellido_paterno . ' ' . $estudiante->apellido_materno . ', ' . $estudiante->nombres) }} - I.E.P. Santísima Cruz</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 1.5cm 1.5cm 1.8cm 1.5cm;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            font-family: 'Figtree', 'Segoe UI', Arial, sans-serif;
            color: #1e293b;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
            line-height: 1.45;
            font-size: 10pt;
        }

        /* Barra de acciones flotante (solo en pantalla, oculta al imprimir) */
        .toolbar-screen {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #f8fafc;
            padding: 12px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            font-size: 12px;
        }

        .toolbar-buttons {
            display: flex;
            gap: 10px;
        }

        .btn-print {
            background-color: #1e40af;
            color: #ffffff;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 12px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: background-color 0.15s ease;
        }
        .btn-print:hover {
            background-color: #1e3a8a;
        }

        .btn-close {
            background-color: #334155;
            color: #ffffff;
            border: none;
            padding: 8px 14px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 12px;
            cursor: pointer;
        }
        .btn-close:hover {
            background-color: #475569;
        }

        /* Contenedor del documento A4 */
        .document-container {
            max-width: 210mm;
            margin: 0 auto;
            padding: 24px 20px;
            background-color: #ffffff;
        }

        /* Encabezado Institucional */
        .header {
            margin-bottom: 18px;
            border-bottom: 2.5px solid #1e40af;
            padding-bottom: 14px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .institution-title {
            font-size: 15pt;
            font-weight: 800;
            color: #0f172a;
            margin: 0 0 4px 0;
            letter-spacing: -0.3px;
            text-transform: uppercase;
        }

        .report-subtitle {
            font-size: 10.5pt;
            color: #1e40af;
            font-weight: 700;
            margin: 0 0 3px 0;
        }

        .report-meta-periodo {
            font-size: 9pt;
            color: #64748b;
            font-weight: 500;
        }

        .header-badge {
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 6px 12px;
            border-radius: 8px;
            text-align: right;
            font-size: 8.5pt;
            color: #334155;
        }

        /* Ficha del Estudiante */
        .meta-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px 14px;
            margin-bottom: 18px;
            display: grid;
            grid-template-columns: 2fr 1.2fr 1.2fr;
            gap: 10px;
            font-size: 9pt;
        }

        .meta-item {
            display: flex;
            flex-direction: column;
        }

        .meta-label {
            font-size: 7.5pt;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }

        .meta-value {
            font-size: 9.5pt;
            font-weight: 700;
            color: #0f172a;
        }

        /* Leyenda de Escalas Oficiales CNEB */
        .legend-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background-color: #ffffff;
            border: 1px dashed #cbd5e1;
            border-radius: 6px;
            padding: 6px 12px;
            margin-bottom: 18px;
            font-size: 8pt;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 5px;
            font-weight: 600;
        }

        .badge-escala {
            display: inline-block;
            font-weight: 800;
            font-size: 8.5pt;
            padding: 2px 7px;
            border-radius: 4px;
            text-align: center;
            min-width: 24px;
            line-height: 1.2;
        }

        .badge-ad { background-color: #1e40af; color: #ffffff; }
        .badge-a  { background-color: #0f766e; color: #ffffff; }
        .badge-b  { background-color: #f59e0b; color: #1e293b; }
        .badge-c  { background-color: #b91c1c; color: #ffffff; }
        .badge-none { background-color: #cbd5e1; color: #475569; }

        /* Bloque por Curso */
        .curso-block {
            margin-bottom: 20px;
            page-break-inside: avoid;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            overflow: hidden;
        }

        .curso-header {
            background-color: #f1f5f9;
            border-bottom: 1.5px solid #cbd5e1;
            padding: 8px 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .curso-title {
            margin: 0;
            font-size: 10.5pt;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.2px;
        }

        .curso-promedio {
            font-size: 8.5pt;
            font-weight: 700;
            color: #334155;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* Tabla de Competencias */
        .competencias-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
        }

        .competencias-table th {
            text-align: left;
            padding: 8px 10px;
            font-weight: 700;
            color: #475569;
            background-color: #fafbfc;
            border-bottom: 1px solid #e2e8f0;
            font-size: 8pt;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .competencias-table th.col-periodo {
            text-align: center;
            width: 70px;
        }

        .competencias-table th.col-evolucion {
            text-align: center;
            width: 110px;
        }

        .competencias-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
            vertical-align: middle;
        }

        .competencias-table tr:last-child td {
            border-bottom: none;
        }

        .competencias-table td.col-periodo {
            text-align: center;
        }

        .competencias-table td.col-evolucion {
            text-align: center;
            font-size: 8pt;
            font-weight: 700;
        }

        .tendencia-mejora { color: #0f766e; }
        .tendencia-estable { color: #475569; }
        .tendencia-descenso { color: #b91c1c; }

        /* Barra visual comparativa de nivel */
        .mini-progress-bar {
            display: inline-block;
            width: 60px;
            height: 6px;
            background-color: #e2e8f0;
            border-radius: 3px;
            overflow: hidden;
            vertical-align: middle;
            margin-left: 4px;
        }

        .mini-progress-fill {
            height: 100%;
            border-radius: 3px;
        }

        /* Recuadro de Diagnóstico Cualitativo */
        .diagnostico-box {
            background-color: #f8fafc;
            border-top: 1px dashed #cbd5e1;
            padding: 10px 12px;
            font-size: 8.5pt;
        }

        .diagnostico-title {
            font-weight: 800;
            color: #1e40af;
            text-transform: uppercase;
            font-size: 7.5pt;
            letter-spacing: 0.4px;
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .diagnostico-text {
            color: #334155;
            line-height: 1.5;
            margin: 0;
            text-align: justify;
            font-weight: 400;
        }

        /* Resumen Global */
        .global-summary-box {
            background-color: #f8fafc;
            border: 1px solid #1e40af;
            border-radius: 8px;
            padding: 12px 14px;
            margin-top: 20px;
            margin-bottom: 24px;
            page-break-inside: avoid;
        }

        .global-summary-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 6px;
        }

        .global-summary-title {
            font-size: 9.5pt;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
        }

        .global-counts {
            display: flex;
            gap: 12px;
            font-size: 8pt;
            font-weight: 700;
        }

        /* Firmas */
        .signatures-container {
            width: 100%;
            margin-top: 50px;
            page-break-inside: avoid;
        }

        .signatures-table {
            width: 100%;
            border: none;
            border-collapse: collapse;
        }

        .signatures-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding: 0 45px;
            border: none;
        }

        .signature-line {
            border-top: 1.2px solid #475569;
            margin-bottom: 6px;
            width: 100%;
        }

        .signature-label {
            font-size: 8.5pt;
            color: #334155;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .signature-sublabel {
            font-size: 7.5pt;
            color: #64748b;
        }

        /* Reglas estrictas de impresión */
        @media print {
            .toolbar-screen {
                display: none !important;
            }
            .document-container {
                padding: 0 !important;
                max-width: 100% !important;
            }
            body {
                background: none !important;
            }
        }
    </style>
</head>
<body>

    <!-- Barra de acciones en pantalla (oculta en el PDF impreso) -->
    <div class="toolbar-screen">
        <div style="display: flex; align-items: center; gap: 8px;">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
            </svg>
            <span><strong>Vista Previa A4</strong> · Informe Oficial de Rendimiento Académico</span>
        </div>
        <div class="toolbar-buttons">
            <button type="button" onclick="window.print()" class="btn-print">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                Imprimir / Guardar como PDF
            </button>
            <button type="button" onclick="window.close()" class="btn-close">Cerrar</button>
        </div>
    </div>

    <div class="document-container">

        <!-- Cabecera Institucional -->
        <div class="header">
            <div>
                <h1 class="institution-title">I.E.P. Santísima Cruz de Chulucanas</h1>
                <div class="report-subtitle">Informe de Rendimiento Académico y Diagnóstico de Logros</div>
                <div class="report-meta-periodo">
                    Año Lectivo {{ $gradoSeccion->anoLectivo->nombre ?? date('Y') }} · 
                    Periodo: {{ $bimestres->pluck('numero')->map(fn($n) => $n . '° Bim.')->join(', ') }}
                </div>
            </div>
            <div class="header-badge">
                <div><strong>Fecha de emisión:</strong></div>
                <div>{{ now()->format('d/m/Y') }}</div>
            </div>
        </div>

        <!-- Ficha del Estudiante -->
        <div class="meta-card">
            <div class="meta-item">
                <span class="meta-label">Estudiante</span>
                <span class="meta-value">{{ $estudiante->apellidos_nombres ?? ($estudiante->apellido_paterno . ' ' . $estudiante->apellido_materno . ', ' . $estudiante->nombres) }}</span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Grado y Sección</span>
                <span class="meta-value">{{ $gradoSeccion->grado->nombre ?? '' }} {{ $gradoSeccion->seccion->nombre ?? '' }} ({{ ucfirst($gradoSeccion->grado->nivel ?? 'Secundaria') }})</span>
            </div>
            <div class="meta-item">
                <span class="meta-label">DNI / Código</span>
                <span class="meta-value">{{ $estudiante->dni ?? $estudiante->codigo_estudiante ?? '-' }}</span>
            </div>
        </div>

        <!-- Leyenda de Escalas de Calificación CNEB -->
        <div class="legend-bar">
            <span style="font-weight: 700; color: #475569; text-transform: uppercase;">Escala de Logro CNEB:</span>
            <div class="legend-item">
                <span class="badge-escala badge-ad">AD</span>
                <span>Logro Destacado (18 - 20)</span>
            </div>
            <div class="legend-item">
                <span class="badge-escala badge-a">A</span>
                <span>Logro Esperado (14 - 17)</span>
            </div>
            <div class="legend-item">
                <span class="badge-escala badge-b">B</span>
                <span>En Proceso (11 - 13)</span>
            </div>
            <div class="legend-item">
                <span class="badge-escala badge-c">C</span>
                <span>En Inicio (0 - 10)</span>
            </div>
        </div>

        <!-- Bloques de Cursos Evaluados -->
        @forelse($cursosData as $curso)
            <div class="curso-block">
                
                <!-- Encabezado del Curso -->
                <div class="curso-header">
                    <h2 class="curso-title">{{ $curso['nombre'] }}</h2>
                    @if(!empty($curso['nivel_promedio']))
                        <div class="curso-promedio">
                            <span>Nivel alcanzado:</span>
                            <span class="badge-escala {{ match($curso['nivel_promedio']) { 'AD' => 'badge-ad', 'A' => 'badge-a', 'B' => 'badge-b', 'C' => 'badge-c', default => 'badge-none' } }}">
                                {{ $curso['nivel_promedio'] }}
                            </span>
                        </div>
                    @endif
                </div>

                <!-- Tabla de Competencias y Logros por Bimestre -->
                <table class="competencias-table">
                    <thead>
                        <tr>
                            <th>Competencia evaluada</th>
                            @foreach($bimestres as $b)
                                <th class="col-periodo">{{ $b->numero }}° Bim.</th>
                            @endforeach
                            @if($incluirComparativa && count($bimestres) > 1)
                                <th class="col-evolucion">Evolución</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($curso['competencias'] as $comp)
                            <tr>
                                <td>{{ $comp['nombre'] }}</td>
                                @foreach($bimestres as $b)
                                    @php $nota = $comp['notas_por_bimestre'][$b->id] ?? null; @endphp
                                    <td class="col-periodo">
                                        @if($nota)
                                            <span class="badge-escala {{ match($nota) { 'AD' => 'badge-ad', 'A' => 'badge-a', 'B' => 'badge-b', 'C' => 'badge-c', default => 'badge-none' } }}">
                                                {{ $nota }}
                                            </span>
                                        @else
                                            <span class="badge-escala badge-none">-</span>
                                        @endif
                                    </td>
                                @endforeach
                                @if($incluirComparativa && count($bimestres) > 1)
                                    <td class="col-evolucion">
                                        @if($comp['variacion'])
                                            <span class="{{ match($comp['tendencia']) { 'mejora' => 'tendencia-mejora', 'descenso' => 'tendencia-descenso', default => 'tendencia-estable' } }}">
                                                {{ $comp['variacion'] }}
                                            </span>
                                        @else
                                            <span style="color: #94a3b8;">-</span>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Diagnóstico Cualitativo del Curso -->
                <div class="diagnostico-box">
                    <div class="diagnostico-title">
                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <span>Diagnóstico Cualitativo y Recomendaciones</span>
                    </div>
                    <p class="diagnostico-text">{{ $curso['diagnostico'] }}</p>
                </div>

            </div>
        @empty
            <div style="padding: 24px; text-align: center; color: #64748b; border: 1px dashed #cbd5e1; border-radius: 8px;">
                No se registraron competencias ni calificaciones para los filtros seleccionados.
            </div>
        @endforelse

        <!-- Síntesis Global (cuando se exportan múltiples cursos) -->
        @if($diagnosticoGlobal && count($cursosData) > 1)
            <div class="global-summary-box">
                <div class="global-summary-header">
                    <h3 class="global-summary-title">Síntesis Global de Rendimiento Académico</h3>
                    <div class="global-counts">
                        <span style="color: #1e40af;">AD: {{ $conteoEscalasGlobal['AD'] }}</span>
                        <span style="color: #0f766e;">A: {{ $conteoEscalasGlobal['A'] }}</span>
                        <span style="color: #d97706;">B: {{ $conteoEscalasGlobal['B'] }}</span>
                        <span style="color: #b91c1c;">C: {{ $conteoEscalasGlobal['C'] }}</span>
                    </div>
                </div>
                <p class="diagnostico-text">{{ $diagnosticoGlobal }}</p>
            </div>
        @endif

        <!-- Firmas Institucionales Oficiales -->
        <div class="signatures-container">
            <table class="signatures-table">
                <tr>
                    <td>
                        <div class="signature-line"></div>
                        <div class="signature-label">Docente Tutor / Coordinación</div>
                        <div class="signature-sublabel">I.E.P. Santísima Cruz</div>
                    </td>
                    <td>
                        <div class="signature-line"></div>
                        <div class="signature-label">Dirección General</div>
                        <div class="signature-sublabel">I.E.P. Santísima Cruz</div>
                    </td>
                </tr>
            </table>
        </div>

    </div>

    <!-- Script de impresión automática -->
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                window.print();
            }, 450);
        });
    </script>
</body>
</html>
