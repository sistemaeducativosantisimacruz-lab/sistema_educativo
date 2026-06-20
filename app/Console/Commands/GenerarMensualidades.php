<?php

namespace App\Console\Commands;

use App\Models\AnoLectivo;
use App\Models\Matricula;
use App\Models\Mensualidad;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerarMensualidades extends Command
{
    /**
     * The name and signature of the console command.
     * Permite pasar mes y año manualmente:
     *   php artisan mensualidades:generar
     *   php artisan mensualidades:generar --mes=3 --anio=2026
     */
    protected $signature = 'mensualidades:generar
                            {--mes=    : Número de mes (1-12). Por defecto: mes anterior al actual.}
                            {--anio=   : Año de 4 dígitos. Por defecto: año del mes anterior.}';

    protected $description = 'Genera registros de mensualidad (DEBE) para todos los estudiantes matriculados del mes indicado.';

    public function handle(): int
    {
        // Determinar el mes/año objetivo (por defecto el mes que acaba de terminar)
        $hoy = Carbon::now();
        $objetivo = $hoy->copy()->subMonth();

        $mes  = (int) ($this->option('mes')  ?: $objetivo->month);
        $anio = (int) ($this->option('anio') ?: $objetivo->year);

        if ($mes < 1 || $mes > 12) {
            $this->error("Mes inválido: {$mes}. Debe ser un número entre 1 y 12.");
            return Command::FAILURE;
        }

        $this->info("Generando mensualidades para {$mes}/{$anio} ...");

        // Obtener el año lectivo activo
        $anoLectivo = AnoLectivo::where('activo', true)->first();

        if (! $anoLectivo) {
            $this->warn('No hay un año lectivo activo. Se aborta.');
            return Command::FAILURE;
        }

        // Obtener todas las matrículas activas del año lectivo
        $matriculas = Matricula::where('ano_lectivo_id', $anoLectivo->id)
            ->where('estado', 'matriculado')
            ->get();

        if ($matriculas->isEmpty()) {
            $this->warn('No hay matrículas activas.');
            return Command::SUCCESS;
        }

        $nuevos    = 0;
        $existentes = 0;

        DB::transaction(function () use ($matriculas, $mes, $anio, &$nuevos, &$existentes) {
            foreach ($matriculas as $matricula) {
                $existe = Mensualidad::where('matricula_id', $matricula->id)
                    ->where('mes', $mes)
                    ->where('anio', $anio)
                    ->exists();

                if ($existe) {
                    $existentes++;
                    continue;
                }

                Mensualidad::create([
                    'matricula_id' => $matricula->id,
                    'mes'          => $mes,
                    'anio'         => $anio,
                    'estado'       => 'DEBE',
                ]);
                $nuevos++;
            }
        });

        $this->info("✓ {$nuevos} registros creados. {$existentes} ya existían.");
        return Command::SUCCESS;
    }
}
