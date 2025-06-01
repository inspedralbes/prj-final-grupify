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
        if(!Schema::hasTable('course_division')){
            Schema::create('course_division', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('course_id');
                $table->unsignedBigInteger('division_id');
                $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
                $table->foreign('division_id')->references('id')->on('divisions')->onDelete('cascade');
                $table->timestamps();
                //$table->unique(['course_id', 'division_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_division');
    }
};
