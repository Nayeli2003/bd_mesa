<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ticket', function (Blueprint $table) {

            // 👇 AQUÍ VA LO QUE PREGUNTASTE
            $table->unsignedBigInteger('id_tecnico')->nullable();

            $table->foreign('id_tecnico')
                ->references('id_usuario')
                ->on('usuario')
                ->onUpdate('cascade')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('ticket', function (Blueprint $table) {
            $table->dropForeign(['id_tecnico']);
            $table->dropColumn('id_tecnico');
        });
    }
};
