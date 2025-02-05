<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvitationsTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invitations', function (Blueprint $table) {
            $table->id();
            $table->string('token')->unique(); // Token único para el enlace
            $table->unsignedBigInteger('course_id');
            $table->unsignedBigInteger('division_id');
            $table->unsignedBigInteger('professor_id'); // Asociada al id del profesor
            $table->timestamp('expires_at')->nullable(); // Fecha en la que la invitación caduca
            $table->timestamps();

            // Claves foráneas
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            $table->foreign('division_id')->references('id')->on('divisions')->onDelete('cascade');
            $table->foreign('professor_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Revierte las migraciones.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invitations');
    }
}
