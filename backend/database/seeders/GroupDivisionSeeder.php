<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GroupDivisionSeeder extends Seeder
{
    /**
     * Ejecuta el seeder.
     */
    public function run(): void
    {
        // Definir las asociaciones entre grupos y divisiones
        $groupDivisionData = [
            ['group_id' => 1, 'division_id' => 1],
            ['group_id' => 1, 'division_id' => 3],
            ['group_id' => 2, 'division_id' => 2],
            ['group_id' => 2, 'division_id' => 4],
            ['group_id' => 3, 'division_id' => 5],
            ['group_id' => 4, 'division_id' => 6],
            ['group_id' => 5, 'division_id' => 7],
            ['group_id' => 5, 'division_id' => 3],
            ['group_id' => 6, 'division_id' => 1],
            ['group_id' => 6, 'division_id' => 2],
        ];

        // Insertar las asociaciones en la tabla intermedia
        DB::table('group_division')->insert(array_map(function ($data) {
            return array_merge($data, [
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }, $groupDivisionData));
    }
}
