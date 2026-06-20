<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\NotasBimestralesImportService;
use App\Models\User;
use App\Models\GradoSeccion;
use App\Models\Grado;
use App\Models\Seccion;
use App\Models\AnoLectivo;
use App\Models\Bimestre;
use App\Models\Estudiante;
use App\Models\Curso;
use App\Models\Competencia;
use App\Models\Calificacion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class NotasBimestralesImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_with_valid_and_unknown_sheets()
    {
        // 1. Setup necessary models in database
        $admin = User::factory()->create();
        $ano = AnoLectivo::create(['anio' => 2026, 'activo' => true]);
        $grado = Grado::create(['nombre' => '1ro Secundaria', 'nivel' => 'secundaria', 'orden' => 7]);
        $seccion = Seccion::create(['nombre' => 'A']);
        $gradoSeccion = GradoSeccion::create([
            'grado_id' => $grado->id,
            'seccion_id' => $seccion->id,
            'ano_lectivo_id' => $ano->id,
            'activo' => true
        ]);
        $bimestre = Bimestre::create([
            'numero' => 1,
            'ano_lectivo_id' => $ano->id,
            'fecha_inicio' => '2026-03-01',
            'fecha_fin' => '2026-05-15',
            'estado' => 'abierto'
        ]);

        $estudiante = Estudiante::create([
            'codigo_estudiante' => '123456789012',
            'nombres' => 'Juan',
            'apellido_paterno' => 'Perez',
            'apellido_materno' => 'Gomez',
            'dni' => '76543210',
            'fecha_nacimiento' => '2015-05-15',
            'sexo' => 'M',
            'estado' => 'activo'
        ]);

        // Create the spreadsheet
        $spreadsheet = new Spreadsheet();
        
        // Active sheet (0) will be 0001-ART Y CULT (valid sheet)
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('0001-ART Y CULT');
        // Row 1: Header/Title (trash/junk)
        $sheet1->setCellValue('A1', 'REPORT DE CALIFICACIONES');
        // Row 2: Headers
        $sheet1->setCellValue('A2', 'Nº');
        $sheet1->setCellValue('B2', 'Código del Estudiante');
        $sheet1->setCellValue('C2', 'DNI');
        $sheet1->setCellValue('D2', 'Nombres y Apellidos');
        $sheet1->setCellValue('E2', 'Aprecia de manera crítica manifestaciones artístico-culturales.');
        $sheet1->setCellValue('F2', 'Conclusión Descriptiva / Observación');
        $sheet1->setCellValue('G2', 'Crea proyectos desde los lenguajes artísticos.');
        $sheet1->setCellValue('H2', 'Conclusión Descriptiva / Observación');
        
        // Row 3: Student data
        $sheet1->setCellValue('A3', '1');
        $sheet1->setCellValue('B3', '123456789012');
        $sheet1->setCellValue('C3', '76543210');
        $sheet1->setCellValue('D3', 'Perez Gomez, Juan');
        $sheet1->setCellValue('E3', 'A');
        $sheet1->setCellValue('F3', 'Excelente desempeño');
        $sheet1->setCellValue('G3', 'B');
        $sheet1->setCellValue('H3', '');

        // Add a second sheet: Generalidades (should be ignored automatically)
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Generalidades');
        $sheet2->setCellValue('A1', 'Esta hoja no debe procesarse');
        $sheet2->setCellValue('A2', 'Dato 1');
        $sheet2->setCellValue('B2', 'Dato 2');
        $sheet2->setCellValue('A3', '1');
        $sheet2->setCellValue('B3', '2');

        // Save to file
        $tempPath = tempnam(sys_get_temp_dir(), 'test_import') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        // Run the import service
        $service = new NotasBimestralesImportService();
        $result = $service->import(
            $tempPath,
            $gradoSeccion->id,
            $ano->id,
            $bimestre->id,
            $admin->id,
            'test_file.xlsx',
            ['0001-ART Y CULT', 'Generalidades']
        );

        // Assertions
        $this->assertTrue($result['success']);
        $this->assertEquals(2, $result['importados']); // 2 competencies imported for 1 student
        $this->assertEmpty($result['errores']);

        // Check if database records were created
        $curso = Curso::where('codigo', 'ART')->first();
        $this->assertNotNull($curso);

        $calificaciones = Calificacion::where('estudiante_id', $estudiante->id)
            ->where('curso_id', $curso->id)
            ->get();

        $this->assertCount(2, $calificaciones);

        $calif1 = $calificaciones->firstWhere('observacion', 'Excelente desempeño');
        $this->assertNotNull($calif1);
        $this->assertEquals('A', $calif1->calificacion_letra);

        // Cleanup
        @unlink($tempPath);
    }
}
