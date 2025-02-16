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
        Schema::create('cesc_results', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('peer_id'); // El usuario que recibe los votos
            $table->unsignedBigInteger('tag_id'); // Tipo de tag (etiqueta)
            $table->integer('vote_count')->default(0); // Cantidad de votos
            $table->timestamps();

            // Relaciones
            $table->foreign('peer_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('tag_id')->references('id')->on('tags_cesc')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cesc_results');
    }
};
