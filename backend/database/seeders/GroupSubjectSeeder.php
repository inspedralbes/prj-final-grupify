<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GroupSubjectSeeder extends Seeder
{
    /**
     * Ejecuta el seeder.
     */
    public function run(): void
    {
        // Datos de ejemplo para group_subject
        $groupSubjects = [
            ['group_id' => 1, 'subject_id' => 1],
            ['group_id' => 1, 'subject_id' => 2],
            ['group_id' => 1, 'subject_id' => 3],
            ['group_id' => 2, 'subject_id' => 1],
            ['group_id' => 2, 'subject_id' => 4],
            ['group_id' => 3, 'subject_id' => 2],
            ['group_id' => 3, 'subject_id' => 3],
            ['group_id' => 4, 'subject_id' => 5],
            ['group_id' => 5, 'subject_id' => 4],
            ['group_id' => 5, 'subject_id' => 5],
        ];

        // Agregar timestamps automáticamente
        $groupSubjectsWithTimestamps = array_map(function ($data) {
            return array_merge($data, [
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }, $groupSubjects);

        // Insertar los datos en la tabla group_subject
        DB::table('group_subject')->insert($groupSubjectsWithTimestamps);
    }
}
