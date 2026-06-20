<table>
    <!-- Empty row for spacing at top -->
    <tr></tr>
    
    <!-- General Title -->
    <tr>
        <td></td> <!-- Column A (Empty/Margin) -->
        <th colspan="13" style="background-color: #fce4d6; color: #000000; font-weight: bold; text-align: center; vertical-align: middle; height: 25px;">
            UNIDAD DE GESTIÓN EDUCATIVA LOCAL DE CHULUCANAS I.E 20037 SANTISIMA CRUZ
        </th>
    </tr>
    <tr>
        <td></td>
        <th colspan="13" style="background-color: #548235; color: #ffffff; font-weight: bold; text-align: center; vertical-align: middle; height: 25px;">
            CONSOLIDADO - {{ $bimestre_nombre }} - EDUCACIÓN {{ $nivel }} {{ $anio }} - {{ $curso_nombre }}
        </th>
    </tr>
    
    <tr></tr> <!-- Empty row spacing -->

    @foreach($datosPorGrado as $gradoData)
        @if(count($gradoData['competencias']) > 0)
            <!-- Table Headers -->
            <tr>
                <td></td>
                <th rowspan="3" style="background-color: #fce4d6; border: 1px solid #000000; text-align: center; vertical-align: middle; font-weight: bold;">
                    Grado
                </th>
                <th rowspan="3" style="background-color: #fce4d6; border: 1px solid #000000; text-align: center; vertical-align: middle; font-weight: bold;">
                    Competencias
                </th>
                <th rowspan="3" style="background-color: #fce4d6; border: 1px solid #000000; text-align: center; vertical-align: middle; font-weight: bold;">
                    Total Estudiantes Matriculados
                </th>
                <th rowspan="3" style="background-color: #fce4d6; border: 1px solid #000000; text-align: center; vertical-align: middle; font-weight: bold;">
                    Estudiantes sin evaluar
                </th>
                <th rowspan="3" style="background-color: #fce4d6; border: 1px solid #000000; text-align: center; vertical-align: middle; font-weight: bold;">
                    Total Estudiantes Evaluados
                </th>
                <th colspan="8" style="background-color: #fce4d6; border: 1px solid #000000; text-align: center; vertical-align: middle; font-weight: bold;">
                    Nivel de Logro
                </th>
            </tr>
            <tr>
                <td></td>
                <th colspan="2" style="background-color: #ff0000; color: #ffffff; border: 1px solid #000000; text-align: center; font-weight: bold;">En Inicio</th>
                <th colspan="2" style="background-color: #ffff00; color: #000000; border: 1px solid #000000; text-align: center; font-weight: bold;">En Proceso</th>
                <th colspan="2" style="background-color: #548235; color: #ffffff; border: 1px solid #000000; text-align: center; font-weight: bold;">Logrado</th>
                <th colspan="2" style="background-color: #0070c0; color: #ffffff; border: 1px solid #000000; text-align: center; font-weight: bold;">Destacado</th>
            </tr>
            <tr>
                <td></td>
                <th style="background-color: #ff0000; color: #ffffff; border: 1px solid #000000; text-align: center; font-weight: bold;">#</th>
                <th style="background-color: #ff0000; color: #ffffff; border: 1px solid #000000; text-align: center; font-weight: bold;">%</th>
                <th style="background-color: #ffff00; color: #000000; border: 1px solid #000000; text-align: center; font-weight: bold;">#</th>
                <th style="background-color: #ffff00; color: #000000; border: 1px solid #000000; text-align: center; font-weight: bold;">%</th>
                <th style="background-color: #548235; color: #ffffff; border: 1px solid #000000; text-align: center; font-weight: bold;">#</th>
                <th style="background-color: #548235; color: #ffffff; border: 1px solid #000000; text-align: center; font-weight: bold;">%</th>
                <th style="background-color: #0070c0; color: #ffffff; border: 1px solid #000000; text-align: center; font-weight: bold;">#</th>
                <th style="background-color: #0070c0; color: #ffffff; border: 1px solid #000000; text-align: center; font-weight: bold;">%</th>
            </tr>

            <!-- Table Body -->
            @php $cantCompetencias = count($gradoData['competencias']); @endphp
            @foreach($gradoData['competencias'] as $index => $comp)
                <tr>
                    <td></td>
                    @if($index === 0)
                        <td rowspan="{{ $cantCompetencias }}" style="background-color: #fce4d6; border: 1px solid #000000; text-align: center; vertical-align: middle; font-size: 16px; font-weight: bold;">
                            {{ $gradoData['grado_nombre'] }}
                        </td>
                    @endif
                    <td style="border: 1px solid #000000; vertical-align: middle;">
                        {{ $comp['nombre'] }}
                    </td>
                    <td style="border: 1px solid #000000; text-align: center; vertical-align: middle;">
                        {{ $comp['matriculados'] }}
                    </td>
                    <td style="border: 1px solid #000000; text-align: center; vertical-align: middle; background-color: #ffff00;">
                        {{ $comp['sin_evaluar'] }}
                    </td>
                    <td style="border: 1px solid #000000; text-align: center; vertical-align: middle;">
                        {{ $comp['evaluados'] }}
                    </td>
                    
                    <td style="border: 1px solid #000000; text-align: center; vertical-align: middle;">{{ $comp['C'] }}</td>
                    <td style="border: 1px solid #000000; text-align: center; vertical-align: middle;" data-format="0.0">{{ $comp['porc_C'] }}</td>
                    
                    <td style="border: 1px solid #000000; text-align: center; vertical-align: middle;">{{ $comp['B'] }}</td>
                    <td style="border: 1px solid #000000; text-align: center; vertical-align: middle;" data-format="0.0">{{ $comp['porc_B'] }}</td>
                    
                    <td style="border: 1px solid #000000; text-align: center; vertical-align: middle;">{{ $comp['A'] }}</td>
                    <td style="border: 1px solid #000000; text-align: center; vertical-align: middle;" data-format="0.0">{{ $comp['porc_A'] }}</td>
                    
                    <td style="border: 1px solid #000000; text-align: center; vertical-align: middle;">{{ $comp['AD'] }}</td>
                    <td style="border: 1px solid #000000; text-align: center; vertical-align: middle;" data-format="0.0">{{ $comp['porc_AD'] }}</td>
                </tr>
            @endforeach

            <tr></tr> <!-- Empty row spacing between tables -->
            <tr></tr> 
        @endif
    @endforeach
</table>
