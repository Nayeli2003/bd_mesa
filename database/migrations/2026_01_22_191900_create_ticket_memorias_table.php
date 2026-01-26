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
     Schema::create('ticket_memorias', function (Blueprint $table) {
        $table->bigIncrements('id_ticket_memorias');

        $table->unsignedBigInteger('id_ticket');
        $table->text('pdf_url'); // URL o ruta del PDF
        $table->timestamp('fecha_creacion')->useCurrent();

        $table->foreign('id_ticket')->references('id_ticket')->on('ticket')
            ->onUpdate('cascade')->onDelete('cascade');
    });
    }

public function down(): void
    {
        Schema::dropIfExists('ticket_memorias');
    }

};
