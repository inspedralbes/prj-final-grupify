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
            4 => 2,            // REBUTJAT
            5 => 3,  7 => 3,  8 => 3,  10 => 3,  // AGRESSIU
            6 => 4,  9 => 4,  // PROSOCIAL
            11 => 5, 12 => 5, 13 => 5 // VÍCTIMA
        ];

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

                if (count($peers) >= 6) { // Assegurar que hi ha suficients companys
                    $usedPeers3 = []; // Per a la pregunta 3
                    $usedPeers4 = []; // Per a la pregunta 4

                    foreach ($questionTags as $questionId => $tagId) {
                        $availablePeers = $peers;

                        // Evitar duplicats entre preguntes 3 i 4
                        if ($questionId == 3) {
                            $selectedPeers = $faker->randomElements($availablePeers, 3);
                            $usedPeers3 = $selectedPeers;
                        } elseif ($questionId == 4) {
                            $availablePeers = array_diff($peers, $usedPeers3);
                            $selectedPeers = $faker->randomElements($availablePeers, 3);
                            $usedPeers4 = $selectedPeers;
                        } else {
                            // Per a les altres preguntes, no hi ha restriccions
                            $selectedPeers = $faker->randomElements($availablePeers, 3);
                        }

                        // Inserir les relacions en la base de dades
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
