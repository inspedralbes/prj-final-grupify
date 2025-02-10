<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAnswersTable extends Migration
{
    public function up()
    {
        Schema::create('answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('form_id')->constrained()->onDelete('cascade');
            $table->foreignId('question_id')->constrained()->onDelete('cascade');
            $table->text('answer'); // El campo que almacenará las respuestas, puede ser un string o un JSON
            $table->tinyInteger('rating')->unsigned()->nullable(); // Campo para almacenar el rating (1-5)
            $table->string('answer_type')->nullable(); // Tipo de respuesta (rating, text, checkbox, etc.)
            $table->timestamps();
        });

    }

    public function down()
    {
        Schema::dropIfExists('answers');
    }
}
