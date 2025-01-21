<?php

namespace Database\Seeders;

use App\Models\Division;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DivisionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Division::create([
            'division' => '1',
        ]);
        Division::create([
            'division' => '2',
        ]);
        Division::create([
            'division' => 'A',
        ]);
        Division::create([
            'division' => 'B',
        ]);
        Division::create([
            'division' => 'C',
        ]);
        Division::create([
            'division' => 'D',
        ]);
        Division::create([
            'division' => 'E',
        ]);
    }
}
