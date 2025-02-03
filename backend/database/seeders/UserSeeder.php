<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;


class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear 2 usuarios admin
        User::factory()->count(100)->create();
    }
}
// Crear un usuario específico
User::create([
    'name' => 'profesor',
    'last_name' => 'profesor',
    'email' => 'profesor@gmail.com',
    'password' => Hash::make('password'),
    'role_id' => Role::where('name', 'profesor')->first()->id,
]);

User::create([
    'name' => 'Lucas',
    'last_name' => 'Benitez',
    'email' => 'lucas@gmail.com',
    'password' => Hash::make('password'),
    'role_id' => Role::where('name', 'alumno')->first()->id,
]);

// Crear otro usuario específico
User::create([
    'name' => 'Adri',
    'last_name' => 'Stevez',
    'email' => 'adri@gmail.com',
    'password' => Hash::make('password'),
    'role_id' => Role::where('name', 'alumno')->first()->id,
]);

User::create([
    'name' => 'Joselito',
    'last_name' => 'Joselito',
    'email' => 'joselito@gmail.com',
    'password' => Hash::make('password'),
    'role_id' => Role::where('name', 'alumno')->first()->id,
]);

User::create([
    'name' => 'Aleiram',
    'last_name' => 'Minaya',
    'email' => 'ale@gmail.com',
    'password' => Hash::make('password'),
    'role_id' => Role::where('name', 'alumno')->first()->id,
]);

User::create([
    'name' => 'Araceli',
    'last_name' => 'Pacheco',
    'email' => 'ara@gmail.com',
    'password' => Hash::make('password'),
    'role_id' => Role::where('name', 'alumno')->first()->id,
]);
