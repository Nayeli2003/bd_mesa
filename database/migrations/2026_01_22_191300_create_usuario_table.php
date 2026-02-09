<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuario', function (Blueprint $table) {
            $table->bigIncrements('id_usuario');

            $table->string('nombre', 120); //  para mostrar en UI
            $table->string('username', 80)->unique();
            $table->string('password');

            $table->unsignedBigInteger('id_rol');
            $table->foreign('id_rol')->references('id_rol')->on('rol');

            // ✅ nullable porque admin/tecnico no tienen sucursal
            $table->unsignedBigInteger('id_sucursal')->nullable();
            $table->foreign('id_sucursal')->references('id_sucursal')->on('sucursal');

            $table->boolean('activo')->default(true); // activar/desactivar
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuario');
    }
};
