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
        Schema::create('respostes', function (Blueprint $table) {
            $table->integer('id_enquesta');
            $table->integer('id_alumne');
            $table->integer('numero_alumno')->nullable();
            $table->string('nom')->nullable();
            $table->enum('genero', ['Noi', 'Noia'])->nullable();
            $table->string('clase')->nullable();
            $table->string('tutor')->nullable();
            $table->string('centro')->nullable();
            $table->string('poblacion')->nullable();
            $table->integer('soc_POS_1')->nullable();
            $table->integer('soc_POS_2')->nullable();
            $table->integer('soc_POS_3')->nullable();
            $table->integer('soc_NEG_1')->nullable();
            $table->integer('soc_NEG_2')->nullable();
            $table->integer('soc_NEG_3')->nullable();
            $table->integer('ar_i_1')->nullable();
            $table->integer('ar_i_2')->nullable();
            $table->integer('ar_i_3')->nullable();
            $table->integer('pros_1')->nullable();
            $table->integer('pros_2')->nullable();
            $table->integer('pros_3')->nullable();
            $table->integer('af_1')->nullable();
            $table->integer('af_2')->nullable();
            $table->integer('af_3')->nullable();
            $table->integer('ar_d_1')->nullable();
            $table->integer('ar_d_2')->nullable();
            $table->integer('ar_d_3')->nullable();
            $table->integer('pros_2_1')->nullable();
            $table->integer('pros_2_2')->nullable();
            $table->integer('pros_2_3')->nullable();
            $table->integer('av_1')->nullable();
            $table->integer('av_2')->nullable();
            $table->integer('av_3')->nullable();
            $table->integer('vf_1')->nullable();
            $table->integer('vf_2')->nullable();
            $table->integer('vf_3')->nullable();
            $table->integer('vv_1')->nullable();
            $table->integer('vv_2')->nullable();
            $table->integer('vv_3')->nullable();
            $table->integer('vr_1')->nullable();
            $table->integer('vr_2')->nullable();
            $table->integer('vr_3')->nullable();
            $table->integer('amics_1')->nullable();
            $table->integer('amics_2')->nullable();
            $table->integer('amics_3')->nullable();

            $table->primary(['id_enquesta', 'id_alumne']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('respostes');
    }
};
