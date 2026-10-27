<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use App\Services\AnaliticaEvaluacionService;

class AnaliticaDatabaseAggregationTest extends TestCase
{
    private AnaliticaEvaluacionService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new AnaliticaEvaluacionService();

        // Crear esquema mínimo en memoria en SQLite para probar agregaciones reales sin RefreshDatabase
        Schema::create('grados', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('nivel');
            $table->integer('orden')->default(1);
            $table->timestamps();
        });

        Schema::create('secciones', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->timestamps();
        });

        Schema::create('grado_secciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grado_id');
            $table->foreignId('seccion_id');
            $table->foreignId('ano_lectivo_id');
            $table->timestamps();
        });

        Schema::create('cursos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->timestamps();
        });

        Schema::create('competencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curso_id');
            $table->string('nombre');
            $table->integer('orden')->default(1);
            $table->timestamps();
        });

        Schema::create('matriculas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id');
            $table->foreignId('grado_seccion_id');
            $table->foreignId('ano_lectivo_id');
            $table->string('estado')->default('activo');
            $table->timestamps();
        });

        Schema::create('notas_bimestrales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id');
            $table->foreignId('curso_id');
            $table->foreignId('competencia_id');
            $table->foreignId('bimestre_id');
            $table->string('nota', 5)->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('notas_bimestrales');
        Schema::dropIfExists('matriculas');
        Schema::dropIfExists('competencias');
        Schema::dropIfExists('cursos');
        Schema::dropIfExists('grado_secciones');
        Schema::dropIfExists('secciones');
        Schema::dropIfExists('grados');

        parent::tearDown();
    }

    public function test_get_distribucion_bimestral_agrega_notas_correctamente_y_excluye_retirados_y_nulos(): void
    {
        // 1. Sembrado de datos mínimos
        $cursoId = DB::table('cursos')->insertGetId(['nombre' => 'Matemática']);
        $compId = DB::table('competencias')->insertGetId([
            'curso_id' => $cursoId,
            'nombre' => 'Resuelve problemas de cantidad',
            'orden' => 1
        ]);

        $anoId = 1;
        $bimestreId = 1;
        $gradoSecId = 10;

        // Estudiante 1: Activo, Nota AD
        DB::table('matriculas')->insert([
            'estudiante_id' => 101,
            'grado_seccion_id' => $gradoSecId,
            'ano_lectivo_id' => $anoId,
            'estado' => 'activo'
        ]);
        DB::table('notas_bimestrales')->insert([
            'estudiante_id' => 101,
            'curso_id' => $cursoId,
            'competencia_id' => $compId,
            'bimestre_id' => $bimestreId,
            'nota' => 'AD'
        ]);

        // Estudiante 2: Activo, Nota A
        DB::table('matriculas')->insert([
            'estudiante_id' => 102,
            'grado_seccion_id' => $gradoSecId,
            'ano_lectivo_id' => $anoId,
            'estado' => 'activo'
        ]);
        DB::table('notas_bimestrales')->insert([
            'estudiante_id' => 102,
            'curso_id' => $cursoId,
            'competencia_id' => $compId,
            'bimestre_id' => $bimestreId,
            'nota' => 'A'
        ]);

        // Estudiante 3: Activo, Nota nula (debe ser excluido)
        DB::table('matriculas')->insert([
            'estudiante_id' => 103,
            'grado_seccion_id' => $gradoSecId,
            'ano_lectivo_id' => $anoId,
            'estado' => 'activo'
        ]);
        DB::table('notas_bimestrales')->insert([
            'estudiante_id' => 103,
            'curso_id' => $cursoId,
            'competencia_id' => $compId,
            'bimestre_id' => $bimestreId,
            'nota' => null
        ]);

        // Estudiante 4: Retirado, con nota B (debe ser excluido)
        DB::table('matriculas')->insert([
            'estudiante_id' => 104,
            'grado_seccion_id' => $gradoSecId,
            'ano_lectivo_id' => $anoId,
            'estado' => 'retirado'
        ]);
        DB::table('notas_bimestrales')->insert([
            'estudiante_id' => 104,
            'curso_id' => $cursoId,
            'competencia_id' => $compId,
            'bimestre_id' => $bimestreId,
            'nota' => 'B'
        ]);

        // Ejecutar servicio
        $resultado = $this->service->getDistribucionBimestral($anoId, [
            'bimestre_id' => $bimestreId,
            'grado_seccion_id' => $gradoSecId
        ]);

        // Validar que solo se cuentan Estudiante 1 (AD) y Estudiante 2 (A)
        $this->assertArrayHasKey($cursoId, $resultado);
        $comp = $resultado[$cursoId]['competencias']['Resuelve problemas de cantidad'];

        $this->assertEquals(2, $comp['total']);
        $this->assertEquals(1, $comp['AD']);
        $this->assertEquals(1, $comp['A']);
        $this->assertEquals(0, $comp['B']);
        $this->assertEquals(0, $comp['C']);
        $this->assertEquals(50.0, $comp['porcentajes']['AD']);
        $this->assertEquals(50.0, $comp['porcentajes']['A']);
    }
}
