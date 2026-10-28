<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte Comparativo de Rendimiento - {{ $gradoSeccion->grado->nombre ?? '' }} {{ $gradoSeccion->seccion->nombre ?? '' }} - I.E.P. Santísima Cruz</title>
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

        .document-container {
            max-width: 210mm;
            margin: 0 auto;
            padding: 24px 20px;
            background-color: #ffffff;
        }

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
            text-transform: uppercase;
            letter-spacing: -0.3px;
        }

        .report-subtitle {
            font-size: 11pt;
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

        .meta-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px 14px;
            margin-bottom: 18px;
            display: grid;
            grid-template-columns: 1.5fr 1.5fr 1fr;
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

        .legend-box {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 3px;
        }

        .curso-block {
            margin-bottom: 24px;
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

        .comp-block {
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
        }
        .comp-block:last-child {
            border-bottom: none;
        }

        .comp-title {
            font-size: 9.5pt;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 8px;
        }

        /* Tabla comparativa de la competencia */
        .comp-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
            margin-bottom: 10px;
        }

        .comp-table th {
            background-color: #f8fafc;
            padding: 6px 8px;
            border-bottom: 1px solid #cbd5e1;
            font-size: 7.5pt;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            text-align: center;
        }
        .comp-table th:first-child {
            text-align: left;
        }

        .comp-table td {
            padding: 6px 8px;
            border-bottom: 1px solid #f1f5f9;
            text-align: center;
            vertical-align: middle;
        }
        .comp-table td:first-child {
            text-align: left;
            font-weight: 600;
        }

        /* Barras apiladas al 100% en CSS puro */
        .stacked-bar-container {
            display: flex;
            width: 100%;
            height: 18px;
            border-radius: 4px;
            overflow: hidden;
            background-color: #e2e8f0;
            margin-top: 4px;
            margin-bottom: 4px;
        }

        .stacked-bar-seg {
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 7.5pt;
            font-weight: 700;
            color: #ffffff;
            height: 100%;
            overflow: hidden;
            white-space: nowrap;
        }

        .seg-c  { background-color: #b91c1c; }
        .seg-b  { background-color: #f59e0b; color: #1e293b; }
        .seg-a  { background-color: #0f766e; }
        .seg-ad { background-color: #1e40af; }

        .comp-interpretation {
            background-color: #f8fafc;
            border-left: 3px solid #1e40af;
            padding: 6px 10px;
            margin-top: 8px;
            font-size: 8pt;
            color: #334155;
            font-style: italic;
        }

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
        }

        .signature-sublabel {
            font-size: 7.5pt;
            color: #64748b;
        }

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

    <div class="toolbar-screen">
        <div style="display: flex; align-items: center; gap: 8px;">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
            <span><strong>Vista Previa A4</strong> · Reporte Comparativo de Rendimiento Académico</span>
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
                <div class="report-subtitle">Reporte Comparativo de Rendimiento Académico</div>
                <div class="report-meta-periodo">
                    Año Lectivo {{ $gradoSeccion->anoLectivo->nombre ?? date('Y') }} · 
                    Comparativa: {{ $bimestres->pluck('numero')->map(fn($n) => $n . '° Bimestre')->join(' vs. ') }}
                </div>
            </div>
            <div class="header-badge">
                <div><strong>Fecha de emisión:</strong></div>
                <div>{{ now()->format('d/m/Y') }}</div>
            </div>
        </div>

        <!-- Ficha de la Sección -->
        <div class="meta-card">
            <div class="meta-item">
                <span class="meta-label">Grado y Sección</span>
                <span class="meta-value">{{ $gradoSeccion->grado->nombre ?? '' }} {{ $gradoSeccion->seccion->nombre ?? '' }}</span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Nivel Educativo</span>
                <span class="meta-value">{{ ucfirst($gradoSeccion->grado->nivel ?? 'Secundaria') }}</span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Bimestres analizados</span>
                <span class="meta-value">{{ $bimestres->pluck('numero')->join('°, ') }}° Bimestre</span>
            </div>
        </div>

        <!-- Leyenda -->
        <div class="legend-bar">
            <span style="font-weight: 700; color: #475569; text-transform: uppercase;">Distribución de Logro:</span>
            <div class="legend-item">
                <span class="legend-box seg-ad"></span>
                <span>AD · Destacado</span>
            </div>
            <div class="legend-item">
                <span class="legend-box seg-a"></span>
                <span>A · Esperado</span>
            </div>
            <div class="legend-item">
                <span class="legend-box seg-b"></span>
                <span>B · En Proceso</span>
            </div>
            <div class="legend-item">
                <span class="legend-box seg-c"></span>
                <span>C · En Inicio</span>
            </div>
        </div>

        <!-- Contenido por Curso -->
        @forelse($datosComparativa as $cId => $area)
            <div class="curso-block">
                <div class="curso-header">
                    <h2 class="curso-title">{{ $area['nombre'] }}</h2>
                </div>

                @foreach($area['competencias'] as $compNombre => $compData)
                    <div class="comp-block">
                        <div class="comp-title">{{ $compNombre }}</div>

                        <!-- Tabla de porcentajes por bimestre -->
                        <table class="comp-table">
                            <thead>
                                <tr>
                                    <th>Periodo</th>
                                    <th>Total Est.</th>
                                    <th>% AD</th>
                                    <th>% A</th>
                                    <th>% B</th>
                                    <th>% C</th>
                                    <th>Nivel Esperado (A+AD)</th>
                                    <th>Riesgo (B+C)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($bimestres as $b)
                                    @php 
                                        $bData = $compData['bimestres_data'][$b->id] ?? null; 
                                        $pAD = $bData['porcentajes']['AD'] ?? 0;
                                        $pA  = $bData['porcentajes']['A'] ?? 0;
                                        $pB  = $bData['porcentajes']['B'] ?? 0;
                                        $pC  = $bData['porcentajes']['C'] ?? 0;
                                    @endphp
                                    <tr>
                                        <td>{{ $b->numero }}° Bimestre</td>
                                        <td>{{ $bData['total_estudiantes'] ?? '-' }}</td>
                                        <td style="color: #1e40af; font-weight: 700;">{{ $pAD }} %</td>
                                        <td style="color: #0f766e; font-weight: 700;">{{ $pA }} %</td>
                                        <td style="color: #d97706; font-weight: 700;">{{ $pB }} %</td>
                                        <td style="color: #b91c1c; font-weight: 700;">{{ $pC }} %</td>
                                        <td style="font-weight: 700;">{{ round($pA + $pAD, 1) }} %</td>
                                        <td style="font-weight: 700; color: {{ ($pB + $pC) > 40 ? '#b91c1c' : '#475569' }};">
                                            {{ round($pB + $pC, 1) }} %
                                        </td>
                                    </tr>
                                @endforeach
                                @php 
                                    $varAD = $compData['variaciones']['AD'] ?? 0;
                                    $varA  = $compData['variaciones']['A'] ?? 0;
                                    $varB  = $compData['variaciones']['B'] ?? 0;
                                    $varC  = $compData['variaciones']['C'] ?? 0;
                                @endphp
                                <tr style="background-color: #f8fafc; font-weight: 700;">
                                    <td>Variación neta (Δ)</td>
                                    <td>-</td>
                                    <td style="color: {{ $varAD >= 0 ? '#1e40af' : '#b91c1c' }};">
                                        {{ $varAD > 0 ? '+' : '' }}{{ $varAD }} %
                                    </td>
                                    <td style="color: {{ $varA >= 0 ? '#0f766e' : '#b91c1c' }};">
                                        {{ $varA > 0 ? '+' : '' }}{{ $varA }} %
                                    </td>
                                    <td>{{ $varB > 0 ? '+' : '' }}{{ $varB }} %</td>
                                    <td style="color: {{ $varC <= 0 ? '#0f766e' : '#b91c1c' }};">
                                        {{ $varC > 0 ? '+' : '' }}{{ $varC }} %
                                    </td>
                                    <td colspan="2">-</td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Gráfico de barras apiladas comparativas 100% -->
                        <div style="margin-top: 6px; margin-bottom: 6px;">
                            @foreach($bimestres as $b)
                                @php 
                                    $bData = $compData['bimestres_data'][$b->id] ?? null; 
                                    $pAD = $bData['porcentajes']['AD'] ?? 0;
                                    $pA  = $bData['porcentajes']['A'] ?? 0;
                                    $pB  = $bData['porcentajes']['B'] ?? 0;
                                    $pC  = $bData['porcentajes']['C'] ?? 0;
                                @endphp
                                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                                    <span style="font-size: 7.5pt; font-weight: 700; color: #475569; width: 65px;">{{ $b->numero }}° Bim:</span>
                                    <div class="stacked-bar-container" style="flex: 1;">
                                        @if($pC > 0)
                                            <div class="stacked-bar-seg seg-c" style="width: {{ $pC }}%;">{{ $pC }}%</div>
                                        @endif
                                        @if($pB > 0)
                                            <div class="stacked-bar-seg seg-b" style="width: {{ $pB }}%;">{{ $pB }}%</div>
                                        @endif
                                        @if($pA > 0)
                                            <div class="stacked-bar-seg seg-a" style="width: {{ $pA }}%;">{{ $pA }}%</div>
                                        @endif
                                        @if($pAD > 0)
                                            <div class="stacked-bar-seg seg-ad" style="width: {{ $pAD }}%;">{{ $pAD }}%</div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Interpretación Pedagógica -->
                        <div class="comp-interpretation">
                            <strong>Análisis pedagógico:</strong> {{ $compData['interpretacion_comparativa'] ?? '' }}
                        </div>

                    </div>
                @endforeach
            </div>
        @empty
            <div style="padding: 24px; text-align: center; color: #64748b; border: 1px dashed #cbd5e1; border-radius: 8px;">
                No se encontraron datos comparativos para los cursos y bimestres seleccionados.
            </div>
        @endforelse

        <!-- Firmas -->
        <div class="signatures-container">
            <table class="signatures-table">
                <tr>
                    <td>
                        <div class="signature-line"></div>
                        <div class="signature-label">Coordinación Académica</div>
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

    <script>
        window.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                window.print();
            }, 450);
        });
    </script>
</body>
</html>
