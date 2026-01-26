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
    Schema::create('ticket_evidencia', function (Blueprint $table) {
        $table->bigIncrements('id_evidencia');

        $table->unsignedBigInteger('id_ticket');
        $table->unsignedBigInteger('id_usuario');

        $table->string('tipo', 50);     // ej: imagen, pdf, video, otro
        $table->string('nombre', 200);  // nombre del archivo o etiqueta
        $table->timestamp('fecha')->useCurrent();

        $table->foreign('id_ticket')->references('id_ticket')->on('ticket')
            ->onUpdate('cascade')->onDelete('cascade');

        $table->foreign('id_usuario')->references('id_usuario')->on('usuario')
            ->onUpdate('cascade')->onDelete('restrict');
    });
    }

    public function down(): void
    {
    Schema::dropIfExists('ticket_evidencia');
    }

};
