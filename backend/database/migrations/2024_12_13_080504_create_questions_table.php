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
        if (!Schema::hasTable('questions')) {
            Schema::create('questions', function (Blueprint $table) {
                $table->id();
                $table->text('title');
                $table->unsignedBigInteger('form_id');
                $table->enum('type', ['text', 'number', 'multiple', 'checkbox', 'rating']); // Tipo de pregunta, con enum hacemos que pueda ser uno de los valores de la lista.
                $table->foreign('form_id')->references('id')->on('forms')->onDelete('cascade');
                $table->string('placeholder')->nullable();
                $table->text('context')->nullable();
                $table->timestamps();
            });
        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
