<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Obtener el ID del rol orientador (asumiendo que ya existe gracias al RoleSeeder)
        $orientadorRoleId = DB::table('roles')->where('name', 'orientador')->value('id');
        
        // Verificar si el rol orientador existe
        if ($orientadorRoleId) {
            // Crear usuario para el rol de orientador si no existe
            $userExists = DB::table('users')->where('email', 'orientador@test.com')->exists();
            
            if (!$userExists) {
                DB::table('users')->insert([
                    'name' => 'Orientador',
                    'last_name' => 'Test',
                    'email' => 'orientador@test.com',
                    'password' => Hash::make('password'),
                    'role_id' => $orientadorRoleId,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        } else {
            // Log o mensaje de advertencia si el rol no existe
            // Esto indica que los seeders no se han ejecutado correctamente
            // En una aplicación real, podrías usar Log::warning() aquí
            echo "Advertencia: El rol 'orientador' no existe. Asegúrate de ejecutar los seeders primero.\n";
        }
        
        // Obtener el ID del rol tutor (asumiendo que ya existe gracias al RoleSeeder)
        $tutorRoleId = DB::table('roles')->where('name', 'tutor')->value('id');
        
        // Verificar si el rol tutor existe
        if ($tutorRoleId) {
            // Crear usuario para el rol de tutor si no existe
            $userExists = DB::table('users')->where('email', 'tutor@test.com')->exists();
            
            if (!$userExists) {
                DB::table('users')->insert([
                    'name' => 'Tutor',
                    'last_name' => 'Test',
                    'email' => 'tutor@test.com',
                    'password' => Hash::make('password'),
                    'role_id' => $tutorRoleId,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        } else {
            // Log o mensaje de advertencia si el rol no existe
            echo "Advertencia: El rol 'tutor' no existe. Asegúrate de ejecutar los seeders primero.\n";
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar los usuarios de prueba
        DB::table('users')->where('email', 'orientador@test.com')->delete();
        DB::table('users')->where('email', 'tutor@test.com')->delete();
    }
};
