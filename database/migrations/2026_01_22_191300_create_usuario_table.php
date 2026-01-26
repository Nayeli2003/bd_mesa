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
    Schema::create('usuario', function (Blueprint $table) {
        $table->bigIncrements('id_usuario');

        $table->string('username', 80)->unique();
        $table->string('password'); // aquí se guarda el hash bcrypt :)

        $table->unsignedBigInteger('id_rol');
         $table->foreign('id_rol')->references('id_rol')->on('rol');
        $table->unsignedBigInteger('id_sucursal');
        $table->foreign('id_sucursal')->references('id_sucursal')->on('sucursal');


       
        
    });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuario');
    }
};
