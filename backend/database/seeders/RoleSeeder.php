<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run()
    {
        Role::create(['name' => 'professor']);
        Role::create(['name' => 'alumne']);
        Role::create(['name' => 'admin']);
    }
}
