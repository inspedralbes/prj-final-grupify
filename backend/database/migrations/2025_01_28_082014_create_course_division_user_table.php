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
        Schema::create('course_division_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade'); // Relación con courses
            $table->foreignId('division_id')->constrained('divisions')->onDelete('cascade'); // Relación con divisions
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Relación con users
            $table->timestamps();
            $table->unique(['course_id', 'division_id', 'user_id'], 'unique_course_division_user'); // Unicidad
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_division_user');
    }
};
