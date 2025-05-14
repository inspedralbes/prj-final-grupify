<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpiar tokens existentes para evitar duplicados
        DB::table('personal_access_tokens')->truncate();

        // Crear usuarios masivos (opcional)
        User::factory()->count(250)->create();

        // Array para almacenar tokens - útil para pruebas
        $tokens = [];

        // Crear usuario profesor
        $profesor = User::create([
            'name' => 'profesor',
            'last_name' => 'profesor',
            'email' => 'profesor@gmail.com',
            'password' => Hash::make('password'),
            'role_id' => Role::where('name', 'profesor')->first()->id,
            'remember_token' => Str::random(60), // Añadir remember_token
            'image' => 'https://i.pravatar.cc/400?u=profesor@gmail.com'
        ]);

        // Generar token para profesor
        $tokens['profesor'] = $profesor->createToken('Groupify')->plainTextToken;

        // Crear usuario Lucas
        $lucas = User::create([
            'name' => 'Lucas',
            'last_name' => 'Benitez',
            'email' => 'lucas@gmail.com',
            'password' => Hash::make('password'),
            'role_id' => Role::where('name', 'alumno')->first()->id,
            'remember_token' => Str::random(60), // Añadir remember_token
            'image' => 'https://i.pravatar.cc/400?u=lucas@gmail.com'
        ]);

        // Generar token para Lucas
        $tokens['lucas'] = $lucas->createToken('Groupify')->plainTextToken;

        // Crear usuario Adri
        $adri = User::create([
            'name' => 'Adri',
            'last_name' => 'Stevez',
            'email' => 'adri@gmail.com',
            'password' => Hash::make('password'),
            'role_id' => Role::where('name', 'alumno')->first()->id,
            'remember_token' => Str::random(60), // Añadir remember_token
            'image' => 'https://i.pravatar.cc/400?u=adri@gmail.com'
        ]);

        // Generar token para Adri
        $tokens['adri'] = $adri->createToken('Groupify')->plainTextToken;

        // Crear usuario Joselito
        $joselito = User::create([
            'name' => 'Joselito',
            'last_name' => 'Joselito',
            'email' => 'joselito@gmail.com',
            'password' => Hash::make('password'),
            'role_id' => Role::where('name', 'alumno')->first()->id,
            'remember_token' => Str::random(60), // Añadir remember_token
            'image' => 'https://i.pravatar.cc/400?u=joselito@gmail.com'
        ]);

        // Generar token para Joselito
        $tokens['joselito'] = $joselito->createToken('Groupify')->plainTextToken;

        // Crear usuario Aleiram
        $aleiram = User::create([
            'name' => 'Aleiram',
            'last_name' => 'Minaya',
            'email' => 'ale@gmail.com',
            'password' => Hash::make('password'),
            'role_id' => Role::where('name', 'alumno')->first()->id,
            'remember_token' => Str::random(60), // Añadir remember_token
            'image' => 'https://i.pravatar.cc/400?u=ale@gmail.com'
        ]);

        // Generar token para Aleiram
        $tokens['aleiram'] = $aleiram->createToken('Groupify')->plainTextToken;

        // Crear usuario Araceli
        $araceli = User::create([
            'name' => 'Araceli',
            'last_name' => 'Pacheco',
            'email' => 'ara@gmail.com',
            'password' => Hash::make('password'),
            'role_id' => Role::where('name', 'alumno')->first()->id,
            'remember_token' => Str::random(60), // Añadir remember_token
            'image' => 'https://i.pravatar.cc/400?u=ara@gmail.com'
        ]);

        // Generar token para Araceli
        $tokens['araceli'] = $araceli->createToken('Groupify')->plainTextToken;

        // Guardar los tokens en un archivo o mostrarlos en la consola
        $this->command->info('Tokens generados:');
        foreach ($tokens as $name => $token) {
            $this->command->info("$name: $token");
        }

        // Verificar que se han creado los tokens de Sanctum
        $this->command->info('Número de tokens Sanctum creados: ' . DB::table('personal_access_tokens')->count());

        // Verificar que los usuarios tienen remember_token
        $this->command->info('Usuarios con remember_token: ' . User::whereNotNull('remember_token')->count());

        // Guardar los tokens de API en un archivo para recuperar en pruebas
        file_put_contents(storage_path('tokens.json'), json_encode($tokens, JSON_PRETTY_PRINT));
    }
}