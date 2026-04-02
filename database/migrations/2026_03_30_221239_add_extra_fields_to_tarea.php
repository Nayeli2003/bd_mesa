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
        Schema::table('tarea', function (Blueprint $table) {
            $table->unsignedBigInteger('id_sucursal')->after('descripcion');
            $table->unsignedBigInteger('id_tipo_problema')->after('id_sucursal');

            $table->foreign('id_sucursal')
                ->references('id_sucursal')
                ->on('sucursal');

            $table->foreign('id_tipo_problema')
                ->references('id_tipo_problema')
                ->on('tipo_problema');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tarea', function (Blueprint $table) {
            //
        });
    }
};
