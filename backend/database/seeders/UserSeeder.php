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

        // Verificar y obtener el rol de profesor
        $rolProfesor = Role::where('name', 'profesor')->first();
        
        if (!$rolProfesor) {
            $this->command->error('El rol de profesor no existe. Ejecuta primero RoleSeeder.');
            return;
        }
        
        // Crear usuario profesor
        $profesor = User::create([
            'name' => 'profesor',
            'last_name' => 'profesor',
            'email' => 'profesor@gmail.com',
            'password' => Hash::make('password'),
            'role_id' => $rolProfesor->id,
            'remember_token' => Str::random(60), // Añadir remember_token
        ]);

        // Generar token para profesor
        $tokens['profesor'] = $profesor->createToken('Groupify')->plainTextToken;

        // Verificar y obtener el rol de alumno
        $rolAlumno = Role::where('name', 'alumno')->first();
        
        if (!$rolAlumno) {
            $this->command->error('El rol de alumno no existe. Ejecuta primero RoleSeeder.');
            return;
        }
        
        // Crear usuario Lucas
        $lucas = User::create([
            'name' => 'Lucas',
            'last_name' => 'Benitez',
            'email' => 'lucas@gmail.com',
            'password' => Hash::make('password'),
            'role_id' => $rolAlumno->id,
            'remember_token' => Str::random(60), // Añadir remember_token
            'image' => 'https://media.licdn.com/dms/image/v2/D4D03AQGPp0Yrjkv_DQ/profile-displayphoto-shrink_400_400/profile-displayphoto-shrink_400_400/0/1714931862069?e=1744848000&v=beta&t=uBmxp5nw0Li0eBmwUiur6AsXsNf7NSgSKUcbrtclHJA'
        ]);

        // Generar token para Lucas
        $tokens['lucas'] = $lucas->createToken('Groupify')->plainTextToken;

        // Crear usuario Adri
        $adri = User::create([
            'name' => 'Adri',
            'last_name' => 'Stevez',
            'email' => 'adri@gmail.com',
            'password' => Hash::make('password'),
            'role_id' => $rolAlumno->id,
            'remember_token' => Str::random(60), // Añadir remember_token
            'image' => 'https://media.licdn.com/dms/image/v2/D4D03AQH1WwSOsPAnmw/profile-displayphoto-shrink_400_400/profile-displayphoto-shrink_400_400/0/1689523295036?e=1744848000&v=beta&t=_ygufCfSmMDLV6Bdeok5rVBrmxwbAi2QIP9c30KP8EE'
        ]);

        // Generar token para Adri
        $tokens['adri'] = $adri->createToken('Groupify')->plainTextToken;

        // Crear usuario Joselito
        $joselito = User::create([
            'name' => 'Joselito',
            'last_name' => 'Joselito',
            'email' => 'joselito@gmail.com',
            'password' => Hash::make('password'),
            'role_id' => $rolAlumno->id,
            'remember_token' => Str::random(60), // Añadir remember_token
            'image' => 'https://media.licdn.com/dms/image/v2/D4D03AQEdFtig7c-woQ/profile-displayphoto-shrink_400_400/profile-displayphoto-shrink_400_400/0/1682366266997?e=1744848000&v=beta&t=B_XULbr-qSGAJFNQGGgUUA-WXMuudQkrr2tpptD7jxM'
        ]);

        // Generar token para Joselito
        $tokens['joselito'] = $joselito->createToken('Groupify')->plainTextToken;

        // Crear usuario Aleiram
        $aleiram = User::create([
            'name' => 'Aleiram',
            'last_name' => 'Minaya',
            'email' => 'ale@gmail.com',
            'password' => Hash::make('password'),
            'role_id' => $rolAlumno->id,
            'remember_token' => Str::random(60), // Añadir remember_token
            'image' => 'https://media.licdn.com/dms/image/v2/D4D03AQESIlLlguI6sA/profile-displayphoto-shrink_400_400/B4DZOGXw62HcAk-/0/1733126197378?e=1744848000&v=beta&t=I6fLRblAncYVYI7sSkO9ol5SvjevBPr61mc_PEjQa7E'
        ]);

        // Generar token para Aleiram
        $tokens['aleiram'] = $aleiram->createToken('Groupify')->plainTextToken;

        // Crear usuario Araceli
        $araceli = User::create([
            'name' => 'Araceli',
            'last_name' => 'Pacheco',
            'email' => 'ara@gmail.com',
            'password' => Hash::make('password'),
            'role_id' => $rolAlumno->id,
            'remember_token' => Str::random(60), // Añadir remember_token
            'image' => 'https://media.licdn.com/dms/image/v2/D4D03AQHgqZ8mMp5enQ/profile-displayphoto-shrink_400_400/profile-displayphoto-shrink_400_400/0/1694774589516?e=1744848000&v=beta&t=anV8DGNUfA18T7Zkbcty53xTe_AZ_o1briGnsonrKEc'
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