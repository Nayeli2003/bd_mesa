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
        Schema::table('tipo_problema', function (Blueprint $table) {
            $table->unsignedBigInteger('id_prioridad')->nullable();

            $table->foreign('id_prioridad')
                ->references('id_prioridad')
                ->on('prioridad');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tipo_problema', function (Blueprint $table) {
            //
        });
    }
};
