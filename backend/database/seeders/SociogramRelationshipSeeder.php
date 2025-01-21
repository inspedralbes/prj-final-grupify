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
        // Crear una instancia de Faker para generar datos falsos
        $faker = Faker::create();

        // Obtener usuarios con role_id_id = 2
        $users = User::where('role_id', 2)->get();

        // Crear relaciones entre esos usuarios (por ejemplo, 10 registros por cada usuario)
        foreach ($users as $user) {
            for ($i = 0; $i < 7; $i++) {
                // Elegir un peer_id aleatorio que no sea el mismo que user_id
                $peer = $faker->randomElement($users->pluck('id')->toArray());
                
                // Asegurar que el peer_id no sea igual al user_id
                while ($peer == $user->id) {
                    $peer = $faker->randomElement($users->pluck('id')->toArray());
                }

                // Crear una relación en la tabla sociogram_relationships para cada usuario
                DB::table('sociogram_relationships')->insert([
                    'user_id' => $user->id,  // ID del usuario que responde
                    'peer_id' => $peer,  // ID de un compañero aleatorio con role_id_id = 2
                    'question_id' => 15 + $i, // ID de la pregunta incrementando en cada vuelta del bucle
                    'relationship_type' => $i < 3 ? 'negative' : 'positive', // Solo 3 relaciones negativas, las demás positivas
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}