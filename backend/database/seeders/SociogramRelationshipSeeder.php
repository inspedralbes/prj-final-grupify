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

                if (count($peers) >= 6) {
                    // Seleccionar 3 compañeros únicos para la pregunta 15
                    $positivePeers = $faker->randomElements($peers, 3);

                    // Seleccionar 3 compañeros diferentes para la pregunta 16
                    $remainingPeers = array_diff($peers, $positivePeers);
                    $negativePeers = $faker->randomElements($remainingPeers, 3);

                    // Insertar relaciones positivas para la pregunta 15
                    foreach ($positivePeers as $peerId) {
                        DB::table('sociogram_relationships')->insert([
                            'user_id' => $user->id,
                            'peer_id' => $peerId,
                            'question_id' => 15,
                            'relationship_type' => 'positive',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }

                    // Insertar relaciones negativas para la pregunta 16
                    foreach ($negativePeers as $peerId) {
                        DB::table('sociogram_relationships')->insert([
                            'user_id' => $user->id,
                            'peer_id' => $peerId,
                            'question_id' => 16,
                            'relationship_type' => 'negative',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }
    }
} 
