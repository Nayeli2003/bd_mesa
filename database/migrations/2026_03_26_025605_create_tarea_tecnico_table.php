<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tarea_tecnico', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('id_tarea');
            $table->unsignedBigInteger('id_tecnico');

            $table->foreign('id_tarea')
                ->references('id_tarea')
                ->on('tarea')
                ->onDelete('cascade');

            $table->foreign('id_tecnico')
                ->references('id_usuario')
                ->on('usuario');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tarea_tecnico');
    }
};
