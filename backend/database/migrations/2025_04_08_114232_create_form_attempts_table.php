<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('form_id')->constrained()->onDelete('cascade');
            $table->timestamp('attempted_at')->useCurrent(); // Fecha del intento
            $table->float('average_rating')->nullable(); // Media del intento
            $table->timestamps(); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_attempts');
    }
};