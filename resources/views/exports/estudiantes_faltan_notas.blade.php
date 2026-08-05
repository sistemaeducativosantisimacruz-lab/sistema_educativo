<table>
    <thead>
        <tr>
            <th colspan="5" style="text-align: center; font-weight: bold; font-size: 14px;">
                Reporte de Estudiantes que faltan notas - {{ $nivel }} - Bimestre {{ $bimestre->numero }}
            </th>
        </tr>
        <tr>
            <th style="font-weight: bold; background-color: #f3f4f6; border: 1px solid #d1d5db; text-align: center;">Grado</th>
            <th style="font-weight: bold; background-color: #f3f4f6; border: 1px solid #d1d5db; text-align: center;">Sección</th>
            <th style="font-weight: bold; background-color: #f3f4f6; border: 1px solid #d1d5db; text-align: center;">DNI</th>
            <th style="font-weight: bold; background-color: #f3f4f6; border: 1px solid #d1d5db; text-align: center;">Código Modular</th>
            <th style="font-weight: bold; background-color: #f3f4f6; border: 1px solid #d1d5db; text-align: center;">Nombres y Apellidos</th>
        </tr>
    </thead>
    <tbody>
        @foreach($estudiantes as $index => $estudiante)
            <tr style="background-color: {{ $index % 2 == 0 ? '#ffffff' : '#f3f4f6' }};">
                <td style="border: 1px solid #d1d5db; text-align: center;">{{ $estudiante->grado }}</td>
                <td style="border: 1px solid #d1d5db; text-align: center;">{{ $estudiante->seccion }}</td>
                <td style="border: 1px solid #d1d5db; text-align: center; mso-number-format:'\@';">{{ $estudiante->dni }}</td>
                <td style="border: 1px solid #d1d5db; text-align: center; mso-number-format:'\@';">{{ $estudiante->codigo_modular }}</td>
                <td style="border: 1px solid #d1d5db;">{{ $estudiante->apellido_paterno }} {{ $estudiante->apellido_materno }}, {{ $estudiante->nombres }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
