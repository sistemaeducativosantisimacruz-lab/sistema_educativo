<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('movimiento_matriculas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('matricula_id')->constrained()->onDelete('cascade');
            $table->enum('tipo_movimiento', ['retiro', 'reincorporacion', 'cambio_seccion']);
            $table->date('fecha_movimiento');
            $table->string('motivo')->nullable();
            $table->text('observaciones')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null'); // Who did it
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimiento_matriculas');
    }
};
