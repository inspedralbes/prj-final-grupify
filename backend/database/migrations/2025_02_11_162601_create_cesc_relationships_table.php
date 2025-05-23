<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('cesc_relationships')) {
            Schema::create('cesc_relationships', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id'); // El usuario que responde (quien contesta el cuestionario)
                $table->unsignedBigInteger('peer_id'); // El usuario al que hace referencia
                $table->unsignedBigInteger('question_id'); // La pregunta relacionada
                $table->unsignedBigInteger('tag_id'); // Relación con la tabla tags_cesc_table
                $table->timestamps();

                // Relaciones con claves foráneas
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('peer_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('question_id')->references('id')->on('questions')->onDelete('cascade');
                $table->foreign('tag_id')->references('id')->on('tags_cesc')->onDelete('cascade');
            });
        }
    }
    
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cesc_relationships');
    }
};
