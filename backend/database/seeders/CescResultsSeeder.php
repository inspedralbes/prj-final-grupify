<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CescResultsSeeder extends Seeder
{
    public function run()
    {
        // Vaciar la tabla antes de insertar nuevos datos (opcional)
        DB::table('cesc_results')->truncate();

        // Obtener los votos recibidos agrupados por peer_id y tag_id
        $results = DB::table('cesc_relationships')
            ->select('peer_id', 'tag_id', DB::raw('COUNT(*) as vote_count'))
            ->groupBy('peer_id', 'tag_id')
            ->get();

        // Insertar los resultados en la tabla cesc_results
        foreach ($results as $result) {
            DB::table('cesc_results')->insert([
                'peer_id' => $result->peer_id,
                'tag_id' => $result->tag_id,
                'vote_count' => $result->vote_count,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
