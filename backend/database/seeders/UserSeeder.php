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
        User::factory()->count(250)->create();
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
    'image' => 'https://media.licdn.com/dms/image/v2/D4D03AQGPp0Yrjkv_DQ/profile-displayphoto-shrink_400_400/profile-displayphoto-shrink_400_400/0/1714931862069?e=1744848000&v=beta&t=uBmxp5nw0Li0eBmwUiur6AsXsNf7NSgSKUcbrtclHJA'
]);

// Crear otro usuario específico
User::create([
    'name' => 'Adri',
    'last_name' => 'Stevez',
    'email' => 'adri@gmail.com',
    'password' => Hash::make('password'),
    'role_id' => Role::where('name', 'alumno')->first()->id,
    'image' => 'https://media.licdn.com/dms/image/v2/D4D03AQH1WwSOsPAnmw/profile-displayphoto-shrink_400_400/profile-displayphoto-shrink_400_400/0/1689523295036?e=1744848000&v=beta&t=_ygufCfSmMDLV6Bdeok5rVBrmxwbAi2QIP9c30KP8EE'
]);

User::create([
    'name' => 'Joselito',
    'last_name' => 'Joselito',
    'email' => 'joselito@gmail.com',
    'password' => Hash::make('password'),
    'role_id' => Role::where('name', 'alumno')->first()->id,
    'image' => 'https://media.licdn.com/dms/image/v2/D4D03AQEdFtig7c-woQ/profile-displayphoto-shrink_400_400/profile-displayphoto-shrink_400_400/0/1682366266997?e=1744848000&v=beta&t=B_XULbr-qSGAJFNQGGgUUA-WXMuudQkrr2tpptD7jxM'
]);

User::create([
    'name' => 'Aleiram',
    'last_name' => 'Minaya',
    'email' => 'ale@gmail.com',
    'password' => Hash::make('password'),
    'role_id' => Role::where('name', 'alumno')->first()->id,
    'image' => 'https://media.licdn.com/dms/image/v2/D4D03AQESIlLlguI6sA/profile-displayphoto-shrink_400_400/B4DZOGXw62HcAk-/0/1733126197378?e=1744848000&v=beta&t=I6fLRblAncYVYI7sSkO9ol5SvjevBPr61mc_PEjQa7E'
]);

User::create([
    'name' => 'Araceli',
    'last_name' => 'Pacheco',
    'email' => 'ara@gmail.com',
    'password' => Hash::make('password'),
    'role_id' => Role::where('name', 'alumno')->first()->id,
    'image' => 'https://media.licdn.com/dms/image/v2/D4D03AQHgqZ8mMp5enQ/profile-displayphoto-shrink_400_400/profile-displayphoto-shrink_400_400/0/1694774589516?e=1744848000&v=beta&t=anV8DGNUfA18T7Zkbcty53xTe_AZ_o1briGnsonrKEc'
]);
