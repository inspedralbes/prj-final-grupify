<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TagCesc;

class TagCescSeeder extends Seeder
{
    /**
     * Ejecutar la siembra de la base de datos.
     *
     * @return void
     */
    public function run()
    {
        // Insertar los 5 tags predefinidos en la tabla tags_cesc
        $tags = [
            ['name' => 'popular'],
            ['name' => 'rechazado'],
            ['name' => 'agresivo'],
            ['name' => 'prosocial'],
            ['name' => 'victima'],
        ];

        // Usar el modelo Tag para insertar los tags en la base de datos
        TagCesc::insert($tags);

        // Si prefieres usar create en lugar de insert:
        // foreach ($tags as $tag) {
        //     Tag::create($tag);
        // }
    }
}