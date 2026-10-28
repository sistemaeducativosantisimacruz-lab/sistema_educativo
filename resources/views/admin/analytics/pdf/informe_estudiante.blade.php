<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informe de Rendimiento Académico - {{ $estudiante->apellidos_nombres ?? ($estudiante->apellido_paterno . ' ' . $estudiante->apellido_materno . ', ' . $estudiante->nombres) }} - I.E.P. Santísima Cruz</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 22mm 22mm 22mm 22mm;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            font-family: 'Segoe UI', Arial, Helvetica, sans-serif;
            color: #0f172a;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
            line-height: 1.5;
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
            max-width: 175mm;
            margin: 0 auto;
            padding: 30px 20px 40px 20px;
            min-height: 250mm;
            display: flex;
            flex-direction: column;
            background-color: #ffffff;
        }

        /* Encabezado Institucional (Fiel a Captura 2) */
        .report-header {
            margin-bottom: 6px;
        }

        .institution-title {
            font-size: 15pt;
            font-weight: 800;
            color: #0f2942;
            margin: 0 0 3px 0;
            letter-spacing: -0.2px;
        }

        .report-subtitle {
            font-size: 10pt;
            color: #334155;
            font-weight: 500;
            margin: 0 0 8px 0;
        }

        .header-divider {
            height: 2px;
            background-color: #1e3a8a;
            width: 100%;
            margin-bottom: 18px;
        }

        /* Metadatos del Estudiante (Fiel a Captura 2) */
        .meta-container {
            font-size: 9.5pt;
            color: #1e293b;
            margin-bottom: 24px;
            line-height: 1.6;
        }

        .meta-container strong {
            color: #0f172a;
            font-weight: 700;
        }

        /* Tablas de Competencias (Fiel a Captura 2) */
        .curso-section {
            margin-bottom: 28px;
            page-break-inside: avoid;
        }

        .curso-title-multi {
            font-size: 11pt;
            font-weight: 800;
            color: #0f2942;
            margin: 0 0 10px 0;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 4px;
        }

        .competencias-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5pt;
            margin-bottom: 22px;
        }

        .competencias-table th {
            text-align: left;
            padding: 9px 6px;
            font-weight: 700;
            color: #0f172a;
            border-bottom: 1.5px solid #cbd5e1;
            font-size: 9pt;
        }

        .competencias-table th.col-periodo {
            text-align: center;
            width: 75px;
        }

        .competencias-table td {
            padding: 10px 6px;
            border-bottom: 1px solid #e2e8f0;
            color: #1e293b;
            vertical-align: middle;
        }

        .competencias-table td.col-periodo {
            text-align: center;
            font-weight: 500;
            font-size: 9.5pt;
            color: #0f172a;
        }

        /* Bloque de Diagnóstico Cualitativo (Fiel a Captura 2) */
        .diagnostico-container {
            margin-bottom: 24px;
        }

        .diagnostico-heading {
            font-size: 10pt;
            font-weight: 700;
            color: #0f172a;
            margin: 0 0 6px 0;
        }

        .diagnostico-paragraph {
            font-size: 9pt;
            color: #334155;
            line-height: 1.55;
            margin: 0;
            text-align: justify;
        }

        /* Síntesis Global (para reportes de varios cursos) */
        .global-summary-box {
            background-color: #f8fafc;
            border-left: 3px solid #1e3a8a;
            padding: 12px 14px;
            margin-top: 14px;
            margin-bottom: 24px;
            page-break-inside: avoid;
        }

        .global-summary-title {
            font-size: 9.5pt;
            font-weight: 700;
            color: #0f172a;
            margin: 0 0 6px 0;
        }

        /* Firmas Institucionales (Fiel a Captura 2) */
        .signatures-section {
            margin-top: auto;
            padding-top: 70px;
            page-break-inside: avoid;
            width: 100%;
        }

        .signatures-table {
            width: 100%;
            border-collapse: collapse;
            border: none;
        }

        .signatures-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding: 0 30px;
            border: none;
        }

        .signature-line {
            border-top: 1px solid #334155;
            width: 180px;
            margin: 0 auto 6px auto;
        }

        .signature-title {
            font-size: 9.5pt;
            color: #1e293b;
            font-weight: 500;
        }

        @media print {
            .toolbar-screen {
                display: none !important;
            }
            .document-container {
                padding: 0 !important;
                max-width: 100% !important;
                min-height: auto !important;
            }
            body {
                background: white !important;
            }
        }
    </style>
</head>
<body>
    @php
        if (!function_exists('toRomanBimestreHelper')) {
            function toRomanBimestreHelper($num) {
                return match((int)$num) {
                    1 => 'I',
                    2 => 'II',
                    3 => 'III',
                    4 => 'IV',
                    default => (string)$num
                };
            }
        }
        $periodoTexto = $bimestres->map(fn($b) => toRomanBimestreHelper($b->numero) . ' Bimestre')->join(' · ');
        $cursoUnico = count($cursosData) === 1 ? $cursosData[0]['nombre'] : 'Todos los cursos';
        $estudianteNombre = $estudiante->apellidos_nombres ?? ($estudiante->apellido_paterno . ' ' . $estudiante->apellido_materno . ', ' . $estudiante->nombres);
    @endphp

    <!-- Barra de acciones en pantalla (oculta en el PDF impreso) -->
    <div class="toolbar-screen">
        <div style="display: flex; align-items: center; gap: 8px;">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

        <!-- Encabezado Institucional (Fiel a Captura 2) -->
        <div class="report-header">
            <h1 class="institution-title">I.E.P. Santísima Cruz de Chulucanas</h1>
            <div class="report-subtitle">Informe de rendimiento académico · {{ $periodoTexto }}</div>
        </div>
        <div class="header-divider"></div>

        <!-- Ficha del Estudiante (Fiel a Captura 2) -->
        <div class="meta-container">
            <div><strong>Estudiante:</strong> {{ $estudianteNombre }}</div>
            <div>
                <strong>Grado y sección:</strong> {{ $gradoSeccion->grado->nombre ?? '' }} {{ $gradoSeccion->seccion->nombre ?? '' }}
                &nbsp;·&nbsp;
                <strong>Curso:</strong> {{ $cursoUnico }}
            </div>
        </div>

        <!-- Bloques de Cursos Evaluados -->
        @forelse($cursosData as $curso)
            <div class="curso-section">
                @if(count($cursosData) > 1)
                    <div class="curso-title-multi">{{ $curso['nombre'] }}</div>
                @endif

                <!-- Tabla de Competencias (Fiel a Captura 2) -->
                <table class="competencias-table">
                    <thead>
                        <tr>
                            <th>Competencia</th>
                            @foreach($bimestres as $b)
                                <th class="col-periodo">{{ toRomanBimestreHelper($b->numero) }} Bim.</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($curso['competencias'] as $comp)
                            <tr>
                                <td>{{ $comp['nombre'] }}</td>
                                @foreach($bimestres as $b)
                                    @php $nota = $comp['notas_por_bimestre'][$b->id] ?? null; @endphp
                                    <td class="col-periodo">{{ $nota ?? '-' }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Diagnóstico Cualitativo del Curso (Fiel a Captura 2) -->
                <div class="diagnostico-container">
                    <div class="diagnostico-heading">Diagnóstico</div>
                    <p class="diagnostico-paragraph">{{ $curso['diagnostico'] }}</p>
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
                <div class="global-summary-title">Síntesis Global</div>
                <p class="diagnostico-paragraph">{{ $diagnosticoGlobal }}</p>
            </div>
        @endif

        <!-- Firmas Institucionales (Fiel a Captura 2) -->
        <div class="signatures-section">
            <table class="signatures-table">
                <tr>
                    <td>
                        <div class="signature-line"></div>
                        <div class="signature-title">Docente tutor</div>
                    </td>
                    <td>
                        <div class="signature-line"></div>
                        <div class="signature-title">Dirección</div>
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
