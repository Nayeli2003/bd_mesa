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
        Schema::create('ticket_mensaje', function (Blueprint $table) {

            $table->bigIncrements('id_mensaje');

            $table->unsignedBigInteger('id_ticket');
            $table->unsignedBigInteger('id_usuario');

            $table->text('mensaje');
            $table->timestamp('fecha_envio')->useCurrent();
            $table->boolean('leido')->default(false);

            $table->foreign('id_ticket')
                ->references('id_ticket')
                ->on('ticket')
                ->onDelete('cascade');

            $table->foreign('id_usuario')
                ->references('id_usuario')
                ->on('usuario')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_mensajes');
    }
};
