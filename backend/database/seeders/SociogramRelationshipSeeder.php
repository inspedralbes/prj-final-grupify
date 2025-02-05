<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use App\Models\User;

class SociogramRelationshipSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

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

                if (count($peers) >= 6) { // Asegurarse de tener suficientes compañeros
                    $usedPeers15 = []; // Para la pregunta 15
                    $usedPeers16 = []; // Para la pregunta 16

                    for ($questionId = 15; $questionId <= 21; $questionId++) {
                        // Definir el tipo de relación: solo la pregunta 16 es negativa
                        $relationshipType = ($questionId == 16) ? 'negative' : 'positive';

                        // Filtrar compañeros ya elegidos para evitar duplicados entre preguntas 15 y 16
                        $availablePeers = $peers;

                        // Para la pregunta 15 y 16 asegurarse que no se repitan entre ellas
                        if ($questionId == 15) {
                            $selectedPeers = $faker->randomElements($availablePeers, 3);
                            $usedPeers15 = array_merge($usedPeers15, $selectedPeers);
                        } elseif ($questionId == 16) {
                            // Filtrar los compañeros ya seleccionados en la pregunta 15
                            $availablePeers = array_diff($peers, $usedPeers15);
                            $selectedPeers = $faker->randomElements($availablePeers, 3);
                            $usedPeers16 = array_merge($usedPeers16, $selectedPeers);
                        } else {
                            // Para las preguntas 17-21 no se aplican restricciones
                            $selectedPeers = $faker->randomElements($availablePeers, 3);
                        }

                        // Insertar las relaciones
                        foreach ($selectedPeers as $peerId) {
                            DB::table('sociogram_relationships')->insert([
                                'user_id' => $user->id,
                                'peer_id' => $peerId,
                                'question_id' => $questionId,
                                'relationship_type' => $relationshipType,
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
