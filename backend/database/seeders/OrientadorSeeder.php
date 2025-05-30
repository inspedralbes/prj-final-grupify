<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrientadorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Recuperar tokens existentes si existen
        $tokens = [];
        if (file_exists(storage_path('tokens.json'))) {
            $tokens = json_decode(file_get_contents(storage_path('tokens.json')), true) ?? [];
        }

        // Verificar y obtener el rol de orientador
        $rolOrientador = Role::where('name', 'orientador')->first();
        
        if (!$rolOrientador) {
            $this->command->error('El rol de orientador no existe. Ejecuta primero RoleSeeder.');
            return;
        }
        
        // Crear usuario orientador
        $orientador = User::create([
            'name' => 'Orientador',
            'last_name' => 'Escolar',
            'email' => 'orientador@gmail.com',
            'password' => Hash::make('password'),
            'role_id' => $rolOrientador->id,
            'remember_token' => Str::random(60),
            'image' => 'https://i.pravatar.cc/150?img='.rand(1, 70)
        ]);

        // Generar token para orientador
        $tokens['orientador'] = $orientador->createToken('Groupify')->plainTextToken;

        // A diferencia de los tutores y profesores, los orientadores no necesitan ser asignados
        // específicamente a cursos o divisiones, ya que pueden acceder a la información de
        // todo el centro escolar dentro de sus permisos

        // Guardar los tokens en un archivo
        file_put_contents(storage_path('tokens.json'), json_encode($tokens, JSON_PRETTY_PRINT));

        $this->command->info('Se ha creado el usuario orientador correctamente.');
        $this->command->info('Email: orientador@gmail.com | Password: password');
    }
}
