<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tarea_memorias', function (Blueprint $table) {
            $table->bigIncrements('id_tarea_memoria');

            $table->unsignedBigInteger('id_tarea');

            $table->text('pdf_url'); // ruta del archivo
            $table->timestamp('fecha_creacion')->useCurrent();

            $table->foreign('id_tarea')
                ->references('id_tarea')
                ->on('tarea')
                ->onDelete('cascade'); // 🔥 importante
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tarea_memorias');
    }
};
