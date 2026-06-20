<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
 * Genera automáticamente la lista de mensualidades (estado DEBE)
 * para todos los estudiantes activos el ÚLTIMO DÍA de cada mes a las 00:05 AM.
 * El comando toma el mes actual al ejecutarse, creando los registros del
 * mes que finaliza, listo para que el administrador gestione los pagos.
 *
 * Para que funcione se debe tener configurado el Task Scheduler de Windows
 * o un cron job apuntando a: php artisan schedule:run
 */
Schedule::command('mensualidades:generar', [
    '--mes'  => now()->month,
    '--anio' => now()->year,
])->lastDayOfMonth('00:05')->withoutOverlapping();
