<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CourseDivisionSeeder extends Seeder
{
    public function run()
    {
        // Paso 1: Limpiar la tabla completamente
        DB::statement('SET FOREIGN_KEY_CHECKS=0;'); // Desactiva las claves foráneas
        DB::table('course_division')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;'); // Reactiva las claves foráneas

        // Paso 2: Definir exactamente 24 combinaciones únicas para insertar
        $courseDivisions = [
            ['course_id' => 1, 'division_id' => 3],
            ['course_id' => 1, 'division_id' => 4],
            ['course_id' => 1, 'division_id' => 5],
            ['course_id' => 1, 'division_id' => 6],
            ['course_id' => 1, 'division_id' => 7],
            ['course_id' => 2, 'division_id' => 3],
            ['course_id' => 2, 'division_id' => 4],
            ['course_id' => 2, 'division_id' => 5],
            ['course_id' => 2, 'division_id' => 6],
            ['course_id' => 2, 'division_id' => 7],
            ['course_id' => 3, 'division_id' => 3],
            ['course_id' => 3, 'division_id' => 4],
            ['course_id' => 3, 'division_id' => 5],
            ['course_id' => 3, 'division_id' => 6],
            ['course_id' => 3, 'division_id' => 7],
            ['course_id' => 4, 'division_id' => 3],
            ['course_id' => 4, 'division_id' => 4],
            ['course_id' => 4, 'division_id' => 5],
            ['course_id' => 4, 'division_id' => 6],
            ['course_id' => 4, 'division_id' => 7],
            ['course_id' => 5, 'division_id' => 1],
            ['course_id' => 5, 'division_id' => 2],
        ];

        // Paso 3: Inserta los registros sin duplicados y con timestamps
        $dataToInsert = array_map(function ($entry) {
            return array_merge($entry, [
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }, $courseDivisions);

        DB::table('course_division')->insert($dataToInsert);

        // Paso 4: Verificación de los datos
        $insertedCount = DB::table('course_division')->count();

        // Opcional: Puedes consultar los registros y verificarlos
        $insertedRecords = DB::table('course_division')->get();
       exit(); // Detiene el proceso y muestra los registros en la consola o navegador
    }
}

