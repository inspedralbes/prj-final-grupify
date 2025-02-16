<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run()
    {
        Role::create(['name' => 'profesor']);
        Role::create(['name' => 'alumno']);
        Role::create(['name' => 'admin']);
    }
}
