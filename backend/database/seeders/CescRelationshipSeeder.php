<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use App\Models\User;

class CescRelationshipSeeder extends Seeder
{

    public function run()
    {
        $faker = Faker::create();

        $questionTags = [
            3 => 1,  14 => 1,  // POPULAR
            4 => 2,            // RECHAZADO
            5 => 3,  7 => 3,  8 => 3,  10 => 3,  // AGRESIVO
            6 => 4,  9 => 4,  // PROSOCIAL
            11 => 5, 12 => 5, 13 => 5 // VÍCTIMA
        ];

        // Obtener combinaciones únicas de curso y división
        $classGroups = DB::table('course_division_user')
            ->select('course_id', 'division_id')
            ->distinct()
            ->get();

        // Iterar sobre cada grupo de clase
        foreach ($classGroups as $group) {
            // Obtener estudiantes de la misma clase
            $students = User::where('role_id', 2)
                ->whereIn('id', function ($query) use ($group) {
                    $query->select('user_id')
                        ->from('course_division_user')
                        ->where('course_id', $group->course_id)
                        ->where('division_id', $group->division_id);
                })
                ->get();

            // Generar relaciones para cada estudiante con sus compañeros de clase
            foreach ($students as $user) {
                $peers = $students->where('id', '!=', $user->id)->pluck('id')->toArray();

                if (count($peers) >= 6) { // Asegurar que hay suficientes compañeros
                    $usedPeers3 = []; // Para la pregunta 3
                    $usedPeers4 = []; // Para la pregunta 4

                    foreach ($questionTags as $questionId => $tagId) {
                        $availablePeers = $peers;

                        // Evitar duplicados entre preguntas 3 y 4
                        if ($questionId == 3) {
                            $selectedPeers = $faker->randomElements($availablePeers, 3);
                            $usedPeers3 = $selectedPeers;
                        } elseif ($questionId == 4) {
                            $availablePeers = array_diff($peers, $usedPeers3);
                            $selectedPeers = $faker->randomElements($availablePeers, 3);
                            $usedPeers4 = $selectedPeers;
                        } else {
                            // Para las demás preguntas, no hay restricciones
                            $selectedPeers = $faker->randomElements($availablePeers, 3);
                        }

                        // Insertar las relaciones en la base de datos
                        foreach ($selectedPeers as $peerId) {
                            DB::table('cesc_relationships')->insert([
                                'user_id' => $user->id,
                                'peer_id' => $peerId,
                                'question_id' => $questionId,
                                'tag_id' => $tagId,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }
                }
            }
        }
    }
}
