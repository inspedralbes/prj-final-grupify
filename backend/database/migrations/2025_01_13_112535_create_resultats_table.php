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
        Schema::create('resultats', function (Blueprint $table) {
            $table->integer('id_enquesta');
            $table->integer('id_alumne');
            $table->integer('totalAgressivitat')->nullable();
            $table->integer('agressivitatFisica')->nullable();
            $table->integer('agressivitatVerbal')->nullable();
            $table->integer('agressivitatRelacional')->nullable();
            $table->string('totalAgressivitat_SN', 1)->nullable();
            $table->string('agressivitatFisica_SN', 1)->nullable();
            $table->string('agressivitatVerbal_SN', 1)->nullable();
            $table->string('agressivitatRelacional_SN', 1)->nullable();
            $table->integer('prosocialitat')->nullable();
            $table->string('prosocialitat_SN', 1)->nullable();
            $table->integer('totalVictimitzacio')->nullable();
            $table->integer('victimitzacioFisica')->nullable();
            $table->integer('victimitzacioVerbal')->nullable();
            $table->integer('victimitzacioRelacional')->nullable();
            $table->string('totalVictimitzacio_SN', 1)->nullable();
            $table->string('victimitzacioFisica_SN', 1)->nullable();
            $table->string('victimitzacioVerbal_SN', 1)->nullable();
            $table->string('victimitzacioRelacional_SN', 1)->nullable();
            $table->string('popular_SN', 1)->nullable();
            $table->string('rebutjat_SN', 1)->nullable();
            $table->string('ignorat_SN', 1)->nullable();
            $table->string('controvertit_SN', 1)->nullable();
            $table->string('norma_SN', 1)->nullable();
            $table->string('triesPositives_SN', 1)->nullable();
            $table->string('triesNegatives_SN', 1)->nullable();

            $table->primary(['id_enquesta', 'id_alumne']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resultats');
    }
};
