<table>
    <tr>
        <td></td>
        <th style="background-color: #fce4d6; border: 1px solid #000000; text-align: center; vertical-align: middle; font-weight: bold;">
            Área
        </th>
        <th style="border: 1px solid #000000; text-align: center; vertical-align: middle; font-weight: bold;">
            Competencia
        </th>
        <th style="background-color: #fce4d6; border: 1px solid #000000; text-align: center; vertical-align: middle; font-weight: bold;">
            Mat.
        </th>
        <th style="border: 1px solid #000000; text-align: center; vertical-align: middle; font-weight: bold;">
            Sin Ev.
        </th>
        <th style="background-color: #fce4d6; border: 1px solid #000000; text-align: center; vertical-align: middle; font-weight: bold;">
            Eval.
        </th>
        <th style="background-color: #ff0000; color: #ffffff; border: 1px solid #000000; text-align: center; font-weight: bold;">Inicio (#)</th>
        <th style="background-color: #ff0000; color: #ffffff; border: 1px solid #000000; text-align: center; font-weight: bold;">Inicio (%)</th>
        <th style="background-color: #ffff00; color: #000000; border: 1px solid #000000; text-align: center; font-weight: bold;">Proceso (#)</th>
        <th style="background-color: #ffff00; color: #000000; border: 1px solid #000000; text-align: center; font-weight: bold;">Proceso (%)</th>
        <th style="background-color: #548235; color: #ffffff; border: 1px solid #000000; text-align: center; font-weight: bold;">Logrado (#)</th>
        <th style="background-color: #548235; color: #ffffff; border: 1px solid #000000; text-align: center; font-weight: bold;">Logrado (%)</th>
        <th style="background-color: #0070c0; color: #ffffff; border: 1px solid #000000; text-align: center; font-weight: bold;">Destacado (#)</th>
        <th style="background-color: #0070c0; color: #ffffff; border: 1px solid #000000; text-align: center; font-weight: bold;">Destacado (%)</th>
    </tr>

    <!-- Table Body -->
    @foreach($datosPorCurso as $cursoData)
        @php $cantCompetencias = count($cursoData['competencias']); @endphp
        @foreach($cursoData['competencias'] as $index => $comp)
            <tr>
                <td></td>
                @if($index === 0)
                    <td rowspan="{{ $cantCompetencias }}" style="background-color: #fce4d6; border: 1px solid #000000; text-align: center; vertical-align: middle; font-weight: bold;">
                        {{ $cursoData['curso_nombre'] }}
                    </td>
                @endif
                <td style="border: 1px solid #000000; vertical-align: middle;">
                    {{ $comp['nombre'] }}
                </td>
                <td style="border: 1px solid #000000; text-align: right; vertical-align: middle;">
                    {{ $comp['matriculados'] }}
                </td>
                <td style="border: 1px solid #000000; text-align: right; vertical-align: middle;">
                    {{ $comp['sin_evaluar'] }}
                </td>
                <td style="background-color: #fce4d6; border: 1px solid #000000; text-align: right; vertical-align: middle;">
                    {{ $comp['evaluados'] }}
                </td>
                
                <td style="border: 1px solid #000000; text-align: right; vertical-align: middle;">{{ $comp['C'] }}</td>
                <td style="background-color: #fce4d6; border: 1px solid #000000; text-align: right; vertical-align: middle;" data-format="0.00%">{{ $comp['porc_C'] }}</td>
                
                <td style="border: 1px solid #000000; text-align: right; vertical-align: middle;">{{ $comp['B'] }}</td>
                <td style="background-color: #fce4d6; border: 1px solid #000000; text-align: right; vertical-align: middle;" data-format="0.00%">{{ $comp['porc_B'] }}</td>
                
                <td style="border: 1px solid #000000; text-align: right; vertical-align: middle;">{{ $comp['A'] }}</td>
                <td style="background-color: #fce4d6; border: 1px solid #000000; text-align: right; vertical-align: middle;" data-format="0.00%">{{ $comp['porc_A'] }}</td>
                
                <td style="border: 1px solid #000000; text-align: right; vertical-align: middle;">{{ $comp['AD'] }}</td>
                <td style="background-color: #fce4d6; border: 1px solid #000000; text-align: right; vertical-align: middle;" data-format="0.00%">{{ $comp['porc_AD'] }}</td>
            </tr>
        @endforeach
    @endforeach
</table>
