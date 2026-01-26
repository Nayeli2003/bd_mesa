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
        Schema::create('ticket_resuelto', function (Blueprint $table) {
            $table->bigIncrements('id_ticket_resuelto');
            $table->unsignedBigInteger('id_ticket');
            $table->unsignedBigInteger('id_usuario');
            $table->timestamp('fecha_resolucion')->useCurrent();
            $table->text('solucion');
            $table->text('observaciones');
            $table->time('descripcion');

            $table->foreign('id_ticket')->references('id_ticket')->on('ticket')
            ->onUpdate('cascade')->onDelete('restrict');

            $table->foreign('id_usuario')->references('id_usuario')->on('usuario')
            ->onUpdate('cascade')->onDelete('restrict');

            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_resuelto');
    }
};
