<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CescResultsSeeder extends Seeder
{
    public function run()
    {
        // Buidar la taula abans d'inserir noves dades (opcional)
        DB::table('cesc_results')->truncate();

        // Obtenir els vots rebuts agrupats per peer_id i tag_id
        $results = DB::table('cesc_relationships')
            ->select('peer_id', 'tag_id', DB::raw('COUNT(*) as vote_count'))
            ->groupBy('peer_id', 'tag_id')
            ->get();

        // Inserir els resultats en la taula cesc_results
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
