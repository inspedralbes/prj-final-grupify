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

        // Crear usuario orientador
        $orientador = User::create([
            'name' => 'Orientador',
            'last_name' => 'Escolar',
            'email' => 'orientador@gmail.com',
            'password' => Hash::make('password'),
            'role_id' => Role::where('name', 'orientador')->first()->id,
            'remember_token' => Str::random(60),
            'image' => 'https://i.pravatar.cc/150?img='.rand(1, 70)
        ]);

        // Generar token para orientador
        $tokens['orientador'] = $orientador->createToken('Groupify')->plainTextToken;

        // A diferencia de los tutores y profesores, los orientadores no necesitan ser asignados
        // específicamente a cursos o divisiones, ya que pueden acceder a la información de
        // todo el centro escolar dentro de sus permisos

        // Sin embargo, podemos crear entradas en la tabla form para el orientador
        // Asegurémonos que tengamos al menos un formulario asociado al orientador
        DB::table('forms')->insert([
            'title' => 'Formulario de Evaluación Psicopedagógica',
            'description' => 'Evaluación creada por el departamento de orientación',
            'status' => 1, // activo
            'teacher_id' => $orientador->id,
            'is_global' => 0, // no es global
            'date_limit' => now()->addDays(30),
            'time_limit' => '23:59',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Guardar los tokens en un archivo
        file_put_contents(storage_path('tokens.json'), json_encode($tokens, JSON_PRETTY_PRINT));

        $this->command->info('Se ha creado el usuario orientador correctamente.');
        $this->command->info('Email: orientador@gmail.com | Password: password');
    }
}
