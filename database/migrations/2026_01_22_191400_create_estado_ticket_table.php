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
    Schema::create('estado_ticket', function (Blueprint $table) {
        $table->bigIncrements('id_estado');
        $table->string('nombre', 80)->unique(); // Ej: Abierto, En proceso, Cerrado
    });
    }

    public function down(): void
    {
    Schema::dropIfExists('estado_ticket');
    }

};
