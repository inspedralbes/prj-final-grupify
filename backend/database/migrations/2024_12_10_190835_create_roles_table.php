<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();  // 'alumno', 'profesor', 'admin', 'tutor', 'orientador'
            $table->text('description')->nullable(); // Descripción de las funciones del rol
            $table->timestamps();
        });

        // Insertar los roles básicos directamente en la migración
        // Esto garantiza que existan siempre, antes de ejecutar cualquier seeder
        $roles = [
            [
                'name' => 'profesor',
                'description' => 'Pot assignar formularis, veure totes les respostes, accedir a totes les anàlisis i gestionar classes.',
            ],
            [
                'name' => 'alumne',
                'description' => 'Pot respondre formularis assignats i veure la seva pròpia informació.',
            ],
            [
                'name' => 'administrador',
                'description' => 'Té accés complet a totes les funcionalitats del sistema.',
            ],
            [
                'name' => 'tutor',
                'description' => 'Similar a un professor però només pot assignar formularis i veure qui ha respost. No pot veure les respostes ni les anàlisis detallades.',
            ],
            [
                'name' => 'orientador',
                'description' => 'Té accés a tots els formularis i les seves anàlisis però no pot enviar formularis als alumnes.',
            ],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->insert([
                'name' => $role['name'],
                'description' => $role['description'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
