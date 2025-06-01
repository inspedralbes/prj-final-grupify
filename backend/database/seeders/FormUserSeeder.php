<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FormUserSeeder extends Seeder
{
    public function run()
    {
        $form_id = 3; //SOCIOGRAMA
        // Obtener los user_id únicos de la tabla sociogram_relationships
        $userIds = DB::table('sociogram_relationships')
            ->pluck('user_id')
            ->unique()
            ->toArray();

        // Insertar respuestas en form_user para cada usuario
        foreach ($userIds as $userId) {
            $courseDivision = DB::table('course_division_user')
                ->where('user_id', $userId)
                ->first(); // Obtener la primera coincidencia

            // Verificar que exista relación antes de insertar
            if ($courseDivision) {
                DB::table('form_user')->insert([
                    'form_id'     => $form_id,
                    'user_id'     => $userId,
                    'course_id'   => $courseDivision->course_id,
                    'division_id' => $courseDivision->division_id,
                    'answered'    => 1,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }
        }
    }
}
