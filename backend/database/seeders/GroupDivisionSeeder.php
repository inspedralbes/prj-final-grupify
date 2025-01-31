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
            ['id_group' => 1, 'division_id' => 1],
            ['id_group' => 1, 'division_id' => 3],
            ['id_group' => 2, 'division_id' => 2],
            ['id_group' => 2, 'division_id' => 4],
            ['id_group' => 3, 'division_id' => 5],
            ['id_group' => 4, 'division_id' => 6],
            ['id_group' => 5, 'division_id' => 7],
            ['id_group' => 5, 'division_id' => 3],
            ['id_group' => 6, 'division_id' => 1],
            ['id_group' => 6, 'division_id' => 2],
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
