<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TagCesc;

class TagCescSeeder extends Seeder
{
    /**
     * Executar la sembrada de la base de dades.
     *
     * @return void
     */
    public function run()
    {
        // Inserir els 5 tags predefinits en la taula tags_cesc
        $tags = [
            ['name' => 'popular'],
            ['name' => 'rebutjat'],
            ['name' => 'agressiu'],
            ['name' => 'prosocial'],
            ['name' => 'víctima'],
        ];

        // Usar el model Tag per inserir els tags en la base de dades
        TagCesc::insert($tags);

        // Si prefereixes usar create en lloc d'insert:
        // foreach ($tags as $tag) {
        //     Tag::create($tag);
        // }
    }
}