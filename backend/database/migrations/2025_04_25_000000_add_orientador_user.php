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
        // Asegurarnos de que los roles existen
        $roles = ['profesor', 'alumno', 'admin', 'tutor', 'orientador'];
        
        foreach ($roles as $roleName) {
            // Verificar si el rol ya existe
            $roleExists = DB::table('roles')->where('name', $roleName)->exists();
            
            if (!$roleExists) {
                DB::table('roles')->insert([
                    'name' => $roleName,
                    'description' => "Rol de $roleName",
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
        
        // Obtener el ID del rol orientador
        $orientadorRoleId = DB::table('roles')->where('name', 'orientador')->value('id');
        
        // Crear usuario para el rol de orientador si no existe
        $userExists = DB::table('users')->where('email', 'orientador@test.com')->exists();
        
        if (!$userExists && $orientadorRoleId) {
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
        
        // Obtener el ID del rol tutor
        $tutorRoleId = DB::table('roles')->where('name', 'tutor')->value('id');
        
        // Crear usuario para el rol de tutor si no existe
        $userExists = DB::table('users')->where('email', 'tutor@test.com')->exists();
        
        if (!$userExists && $tutorRoleId) {
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
