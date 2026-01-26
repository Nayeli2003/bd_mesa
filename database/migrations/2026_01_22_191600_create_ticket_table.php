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
    Schema::create('ticket', function (Blueprint $table) {
        $table->bigIncrements('id_ticket');

        $table->unsignedBigInteger('id_sucursal');
        $table->unsignedBigInteger('id_estado');
        $table->unsignedBigInteger('id_usuario');
        $table->unsignedBigInteger('id_prioridad');
        $table->unsignedBigInteger('id_tipo_problema');


        $table->string('titulo', 200);
        $table->text('descripcion');
        $table->timestamp('fecha_creacion')->useCurrent();
        $table->text('comentarios')->nullable();

        $table->foreign('id_sucursal')->references('id_sucursal')->on('sucursal')
            ->onUpdate('cascade')->onDelete('restrict');

        $table->foreign('id_estado')->references('id_estado')->on('estado_ticket')
            ->onUpdate('cascade')->onDelete('restrict');

        $table->foreign('id_usuario')->references('id_usuario')->on('usuario')
            ->onUpdate('cascade')->onDelete('restrict');

        $table->foreign('id_prioridad')->references('id_prioridad')->on('prioridad')
            ->onUpdate('cascade')->onDelete('restrict');

        $table->foreign('id_tipo_problema')->references('id_tipo_problema')->on('tipo_problema')
            ->onUpdate('cascade')->onDelete('restrict');
    });
    }

    public function down(): void
    {
    Schema::dropIfExists('ticket');
    }

};
