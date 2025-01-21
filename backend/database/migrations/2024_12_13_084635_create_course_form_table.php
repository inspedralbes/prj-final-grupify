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

        if (!Schema::hasTable('course_form')) {
            Schema::create('course_form', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('course_id');
                $table->unsignedBigInteger('form_id');
                $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
                $table->foreign('form_id')->references('id')->on('forms')->onDelete('cascade');
                $table->timestamps();
                $table->unique('course_id','forms_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_form');
    }
};
