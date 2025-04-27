<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompetencesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('competences')->insert([
            ['id' => 22, 'name' => 'Responsabilitat'],
            ['id' => 23, 'name' => 'Treball en equip'],
            ['id' => 24, 'name' => 'Gestió del temps'],
            ['id' => 25, 'name' => 'Comunicació'],
            ['id' => 26, 'name' => 'Adaptabilitat'],
            ['id' => 27, 'name' => 'Lideratge'],
            ['id' => 28, 'name' => 'Creativitat'],
            ['id' => 29, 'name' => 'Proactivitat'],
        ]);
    }
}
