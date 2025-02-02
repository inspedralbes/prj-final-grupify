<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserNotificationsTable extends Migration
{
    public function up()
    {
        Schema::create('user_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('teacher_id'); // El creador de la notificación
            $table->string('title');
            $table->text('body');
            $table->timestamp('scheduled_at')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();

            // Relación con el usuario (profesor)
            $table->foreign('teacher_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_notifications');
    }
}
