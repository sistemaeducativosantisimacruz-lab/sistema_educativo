<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Informe de Rendimiento Académico - I.E.P. Santísima Cruz</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 2.5cm 2cm;
        }
        body {
            font-family: 'Figtree', 'Segoe UI', Arial, sans-serif;
            color: #1f2937;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
            line-height: 1.5;
            font-size: 11pt;
        }
        .header {
            margin-bottom: 24px;
        }
        .institution-title {
            font-size: 16pt;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
        }
        .report-subtitle {
            font-size: 11pt;
            color: #475569;
            margin-top: 4px;
            margin-bottom: 12px;
        }
        .divider {
            border-bottom: 2px solid #1e3a8a;
            margin-bottom: 24px;
        }
        .meta-section {
            margin-bottom: 24px;
            font-size: 10.5pt;
        }
        .meta-row {
            margin-bottom: 6px;
        }
        .meta-label {
            font-weight: 700;
            color: #0f172a;
        }
        .meta-value {
            color: #334155;
        }
        .competencias-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 28px;
            font-size: 10.5pt;
        }
        .competencias-table th {
            text-align: left;
            padding: 10px 12px;
            font-weight: 600;
            color: #334155;
            border-bottom: 1px solid #cbd5e1;
        }
        .competencias-table th.col-periodo {
            text-align: center;
            width: 80px;
        }
        .competencias-table td {
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
            color: #1e293b;
        }
        .competencias-table td.col-periodo {
            text-align: center;
            font-weight: 500;
        }
        .diagnostico-section {
            margin-bottom: 60px;
        }
        .diagnostico-title {
            font-weight: 700;
            color: #0f172a;
            font-size: 11pt;
            margin-bottom: 8px;
        }
        .diagnostico-text {
            color: #334155;
            font-size: 10.5pt;
            line-height: 1.6;
            margin: 0;
            text-align: justify;
        }
        .signatures-section {
            margin-top: 80px;
            display: flex;
            justify-content: space-between;
            width: 100%;
        }
        .signature-box {
            width: 42%;
            text-align: center;
            display: inline-block;
        }
        .signature-line {
            border-top: 1px solid #475569;
            margin-bottom: 8px;
            width: 100%;
        }
        .signature-label {
            font-size: 10pt;
            color: #334155;
            font-weight: 500;
        }
        .signatures-container {
            width: 100%;
            margin-top: 90px;
        }
        .signatures-table {
            width: 100%;
            border: none;
        }
        .signatures-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding: 0 40px;
            border: none;
        }
    </style>
</head>
<body>

    <!-- Cabecera Institucional -->
    <div class="header">
        <h1 class="institution-title">I.E.P. Santísima Cruz de Chulucanas</h1>
        <div class="report-subtitle">Informe de rendimiento académico · {{ $bimestreNombre ?? 'II Bimestre' }}</div>
        <div class="divider"></div>
    </div>

    <!-- Metadatos del Estudiante -->
    <div class="meta-section">
        <div class="meta-row">
            <span class="meta-label">Estudiante:</span> 
            <span class="meta-value">{{ $estudianteNombre ?? '[Nombre del estudiante]' }}</span>
        </div>
        <div class="meta-row">
            <span class="meta-label">Grado y sección:</span> 
            <span class="meta-value">{{ $gradoSeccionNombre ?? '3.° A' }}</span>
            <span style="margin: 0 8px; color: #94a3b8;">·</span>
            <span class="meta-label">Curso:</span> 
            <span class="meta-value">{{ $cursoNombre ?? 'Matemática' }}</span>
        </div>
    </div>

    <!-- Tabla de Competencias y Niveles (Bimestre Anterior vs. Actual) -->
    <table class="competencias-table">
        <thead>
            <tr>
                <th>Competencia</th>
                <th class="col-periodo">I Bim.</th>
                <th class="col-periodo">II Bim.</th>
            </tr>
        </thead>
        <tbody>
            @forelse($competenciasData ?? [] as $comp)
                <tr>
                    <td>{{ $comp['nombre'] }}</td>
                    <td class="col-periodo">{{ $comp['bimestre_ant'] ?? '-' }}</td>
                    <td class="col-periodo">{{ $comp['bimestre_act'] ?? '-' }}</td>
                </tr>
            @empty
                <!-- Filas de ejemplo exactas al prototipo aprobado -->
                <tr>
                    <td>Resuelve problemas de cantidad</td>
                    <td class="col-periodo">B</td>
                    <td class="col-periodo">A</td>
                </tr>
                <tr>
                    <td>Resuelve problemas de regularidad, equivalencia y cambio</td>
                    <td class="col-periodo">B</td>
                    <td class="col-periodo">B</td>
                </tr>
                <tr>
                    <td>Resuelve problemas de forma, movimiento y localización</td>
                    <td class="col-periodo">C</td>
                    <td class="col-periodo">B</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Bloque de Diagnóstico Cualitativo Automatizado -->
    <div class="diagnostico-section">
        <div class="diagnostico-title">Diagnóstico</div>
        <p class="diagnostico-text">
            {{ $diagnosticoTexto ?? 'El estudiante alcanza el nivel A en una de tres competencias y se encuentra en proceso (B) en las otras dos. Respecto al bimestre anterior muestra mejora en dos competencias. Se recomienda reforzar la competencia de forma, movimiento y localización.' }}
        </p>
    </div>

    <!-- Líneas de Firma -->
    <div class="signatures-container">
        <table class="signatures-table">
            <tr>
                <td>
                    <div class="signature-line"></div>
                    <div class="signature-label">Docente tutor</div>
                </td>
                <td>
                    <div class="signature-line"></div>
                    <div class="signature-label">Dirección</div>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
