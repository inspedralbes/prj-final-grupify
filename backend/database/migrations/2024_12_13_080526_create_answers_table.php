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
            $table->string('answer_type'); // El tipo de respuesta (multiple, checkbox, number, etc.)
            $table->timestamps();
        });

    }

    public function down()
    {
        Schema::dropIfExists('answers');
    }
}
