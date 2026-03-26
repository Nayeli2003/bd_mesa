<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tarea', function (Blueprint $table) {
            $table->bigIncrements('id_tarea');
            $table->text('titulo');
            $table->text('descripcion');
            $table->text('materiales')->nullable();
            $table->timestamp('fecha_creacion')->useCurrent();
            $table->timestamp('fecha_limite');
            $table->integer('prioridad');
            $table->unsignedBigInteger('creado_por');
            $table->string('estado')->default('pendiente');

            $table->foreign('creado_por')
                ->references('id_usuario')
                ->on('usuario');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tarea');
    }
};
