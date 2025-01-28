<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class CommentsGroupsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtén todos los IDs de comentarios y grupos existentes
        $comments = DB::table('comments')->pluck('id')->toArray();
        $groups = DB::table('groups')->pluck('id')->toArray();

        // Valida que haya datos en ambas tablas
        if (empty($comments) || empty($groups)) {
            $this->command->warn('No hay suficientes datos en las tablas comments o groups para poblar la tabla intermedia.');
            return;
        }

        // Genera relaciones aleatorias entre comentarios y grupos
        $data = [];
        foreach ($comments as $comment) {
            // Relaciona cada comentario con 1 o más grupos de forma aleatoria
            $randomGroups = array_rand(array_flip($groups), rand(1, min(3, count($groups))));
            foreach ((array) $randomGroups as $group) {
                $data[] = [
                    'comment_id' => $comment,
                    'id_group' => $group,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // Inserta los datos en la tabla intermedia
        DB::table('comments_groups')->insert($data);
    }
}
