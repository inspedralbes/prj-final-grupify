<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GroupCourseSeeder extends Seeder
{
    /**
     * Ejecuta el seeder.
     */
    public function run(): void
    {
        // Datos de ejemplo para group_course
        $groupCourses = [
            ['id_group' => 1, 'course_id' => 1],
            ['id_group' => 1, 'course_id' => 2],
            ['id_group' => 2, 'course_id' => 3],
            ['id_group' => 2, 'course_id' => 1],
            ['id_group' => 3, 'course_id' => 2],
            ['id_group' => 3, 'course_id' => 4],
            ['id_group' => 4, 'course_id' => 3],
            ['id_group' => 4, 'course_id' => 5],
            ['id_group' => 5, 'course_id' => 4],
            ['id_group' => 5, 'course_id' => 5],
        ];

        // Agregar timestamps automáticamente
        $groupCoursesWithTimestamps = array_map(function ($data) {
            return array_merge($data, [
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }, $groupCourses);

        // Insertar los datos en la tabla group_course
        DB::table('group_course')->insert($groupCoursesWithTimestamps);
    }
}
