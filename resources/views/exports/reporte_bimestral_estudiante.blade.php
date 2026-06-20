<table>
    <thead>
        <tr>
            <th colspan="6" style="font-size: 16px; font-weight: bold; text-align: center;">REPORTE DE NOTAS BIMESTRALES</th>
        </tr>
        <tr>
            <th colspan="6"><b>ESTUDIANTE:</b> {{ $estudiante->apellido_paterno }} {{ $estudiante->apellido_materno }}, {{ $estudiante->nombres }}</th>
        </tr>
        <tr>
            <th colspan="6"><b>DNI:</b> {{ $estudiante->dni }}  |  <b>CÓDIGO DE ESTUDIANTE:</b> {{ $estudiante->codigo_estudiante ?? 'N/A' }}</th>
        </tr>
        <tr>
            <th colspan="6"><b>GRADO Y SECCIÓN:</b> {{ $matricula ? $matricula->gradoSeccion->grado->nombre . ' - ' . $matricula->gradoSeccion->seccion->nombre : 'N/A' }}</th>
        </tr>
        <tr>
            <th colspan="6"></th>
        </tr>
        <tr>
            <th style="font-weight: bold; background-color: #4F46E5; color: #ffffff;">CURSO</th>
            <th style="font-weight: bold; background-color: #4F46E5; color: #ffffff;">COMPETENCIA</th>
            @foreach($bimestres as $bimestre)
                <th style="font-weight: bold; background-color: #4F46E5; color: #ffffff; text-align: center;">B{{ $bimestre->numero }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach($cursos as $curso)
            @if($curso->competencias->count() > 0)
                @foreach($curso->competencias as $index => $competencia)
                    <tr>
                        @if($index === 0)
                            <td rowspan="{{ $curso->competencias->count() }}" style="vertical-align: middle; font-weight: bold;">{{ $curso->nombre }}</td>
                        @endif
                        <td>{{ $competencia->nombre }}</td>
                        @foreach($bimestres as $bimestre)
                            <td style="text-align: center;">
                                @if(isset($notasMap[$curso->id][$competencia->nombre][$bimestre->id]))
                                    {{ $notasMap[$curso->id][$competencia->nombre][$bimestre->id]->nota == '0' ? '-' : $notasMap[$curso->id][$competencia->nombre][$bimestre->id]->nota }}
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            @endif
        @endforeach
    </tbody>
</table>
