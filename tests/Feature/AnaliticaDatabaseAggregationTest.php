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

        Schema::create('bimestres', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ano_lectivo_id');
            $table->integer('numero');
            $table->string('nombre')->nullable();
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
        Schema::dropIfExists('bimestres');
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

    public function test_get_comparativa_entre_secciones_retorna_categorias_dinamicas_segun_grado(): void
    {
        $anoId = 2026;
        $grado1Id = DB::table('grados')->insertGetId([
            'nombre' => '1ro Secundaria',
            'nivel' => 'secundaria',
            'orden' => 1
        ]);
        $secAId = DB::table('secciones')->insertGetId(['nombre' => 'A']);
        $secBId = DB::table('secciones')->insertGetId(['nombre' => 'B']);

        $gsAId = DB::table('grado_secciones')->insertGetId([
            'grado_id' => $grado1Id,
            'seccion_id' => $secAId,
            'ano_lectivo_id' => $anoId
        ]);
        $gsBId = DB::table('grado_secciones')->insertGetId([
            'grado_id' => $grado1Id,
            'seccion_id' => $secBId,
            'ano_lectivo_id' => $anoId
        ]);

        $bimestreId = DB::table('bimestres')->insertGetId([
            'ano_lectivo_id' => $anoId,
            'numero' => 1,
            'nombre' => 'Bimestre 1'
        ]);

        $cursoId = DB::table('cursos')->insertGetId(['nombre' => 'Matemática']);
        $compId = DB::table('competencias')->insertGetId([
            'curso_id' => $cursoId,
            'nombre' => 'C1',
            'orden' => 1
        ]);

        // Nota en Sección A: AD
        DB::table('matriculas')->insert([
            'estudiante_id' => 201,
            'grado_seccion_id' => $gsAId,
            'ano_lectivo_id' => $anoId,
            'estado' => 'activo'
        ]);
        DB::table('notas_bimestrales')->insert([
            'estudiante_id' => 201,
            'curso_id' => $cursoId,
            'competencia_id' => $compId,
            'bimestre_id' => $bimestreId,
            'nota' => 'AD'
        ]);

        // Nota en Sección B: C
        DB::table('matriculas')->insert([
            'estudiante_id' => 202,
            'grado_seccion_id' => $gsBId,
            'ano_lectivo_id' => $anoId,
            'estado' => 'activo'
        ]);
        DB::table('notas_bimestrales')->insert([
            'estudiante_id' => 202,
            'curso_id' => $cursoId,
            'competencia_id' => $compId,
            'bimestre_id' => $bimestreId,
            'nota' => 'C'
        ]);

        $res = $this->service->getComparativaEntreSecciones($anoId, $grado1Id, $bimestreId, $cursoId);

        $this->assertEquals($grado1Id, $res['grado_id']);
        $this->assertContains('1.° A', $res['categories']);
        $this->assertContains('1.° B', $res['categories']);
        $this->assertCount(4, $res['series']);
    }

    public function test_get_comparativa_entre_bimestres_retorna_bimestres_del_ano(): void
    {
        $anoId = 2026;
        $gradoId = DB::table('grados')->insertGetId([
            'nombre' => '4to Secundaria',
            'nivel' => 'secundaria',
            'orden' => 4
        ]);
        $secAId = DB::table('secciones')->insertGetId(['nombre' => 'A']);
        $gsId = DB::table('grado_secciones')->insertGetId([
            'grado_id' => $gradoId,
            'seccion_id' => $secAId,
            'ano_lectivo_id' => $anoId
        ]);

        $b1Id = DB::table('bimestres')->insertGetId(['ano_lectivo_id' => $anoId, 'numero' => 1, 'nombre' => 'B1']);
        $b2Id = DB::table('bimestres')->insertGetId(['ano_lectivo_id' => $anoId, 'numero' => 2, 'nombre' => 'B2']);

        $res = $this->service->getComparativaEntreBimestres($anoId, $gsId, null);

        $this->assertEquals($gsId, $res['grado_seccion_id']);
        $this->assertEquals('4.° A', $res['seccion_nombre']);
        $this->assertContains('I Bimestre', $res['categories']);
        $this->assertContains('II Bimestre', $res['categories']);
    }
}
