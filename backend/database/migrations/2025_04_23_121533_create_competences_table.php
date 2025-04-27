<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    public function up(): void
    {
        // Crear tabla de competencias
        Schema::create('competences', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        // Agregar columna opcional 'competence_id' a la tabla 'questions'
        Schema::table('questions', function (Blueprint $table) {
            $table->foreignId('competence_id')->nullable()->constrained('competences')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropForeign(['competence_id']);
            $table->dropColumn('competence_id');
        });

        Schema::dropIfExists('competences');
    }
};