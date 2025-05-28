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

        // Obtenir combinacions úniques de curs i divisió
        $classGroups = DB::table('course_division_user')
            ->select('course_id', 'division_id')
            ->distinct()
            ->get();

        // Iterar sobre cada grup de classe
        foreach ($classGroups as $group) {
            // Obtenir estudiants de la mateixa classe
            $students = User::where('role_id', 2)
                ->whereIn('id', function ($query) use ($group) {
                    $query->select('user_id')
                        ->from('course_division_user')
                        ->where('course_id', $group->course_id)
                        ->where('division_id', $group->division_id);
                })
                ->get();

            // Generar relacions per a cada estudiant amb els seus companys de classe
            foreach ($students as $user) {
                $peers = $students->where('id', '!=', $user->id)->pluck('id')->toArray();

                if (count($peers) >= 6) { // Assegurar-se de tenir suficients companys
                    $usedPeers15 = []; // Per a la pregunta 15
                    $usedPeers16 = []; // Per a la pregunta 16

                    for ($questionId = 15; $questionId <= 21; $questionId++) {
                        // Definir el tipus de relació: només la pregunta 16 és negativa
                        $relationshipType = ($questionId == 16) ? 'negative' : 'positive';

                        // Filtrar companys ja escollits per evitar duplicats entre preguntes 15 i 16
                        $availablePeers = $peers;

                        // Per a la pregunta 15 i 16 assegurar-se que no es repeteixin entre elles
                        if ($questionId == 15) {
                            $selectedPeers = $faker->randomElements($availablePeers, 3);
                            $usedPeers15 = array_merge($usedPeers15, $selectedPeers);
                        } elseif ($questionId == 16) {
                            // Filtrar els companys ja seleccionats en la pregunta 15
                            $availablePeers = array_diff($peers, $usedPeers15);
                            $selectedPeers = $faker->randomElements($availablePeers, 3);
                            $usedPeers16 = array_merge($usedPeers16, $selectedPeers);
                        } else {
                            // Per a les preguntes 17-21 no s'apliquen restriccions
                            $selectedPeers = $faker->randomElements($availablePeers, 3);
                        }

                        // Inserir les relacions
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
