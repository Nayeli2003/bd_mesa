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
    Schema::create('prioridad', function (Blueprint $table) {
        $table->bigIncrements('id_prioridad');
        $table->string('nombre', 80)->unique(); // Ej: Baja, Media, Alta
        $table->string('color', 30);            // Ej: verde, naranja, rojo o #HEX
    });
    }

    public function down(): void
    {
    Schema::dropIfExists('prioridad');
    }

};
