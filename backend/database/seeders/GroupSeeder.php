<?php

namespace Database\Seeders;


use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Group;


class GroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Group::factory()->count(10)->create(); // aixo es si volem crear 10 grups amb factories (dades aleatories)
        Group::create([
            'name' => 'Grup 1',
            'description' => 'Un grup d estudiants i docents compromesos amb l
             aprenentatge col·laboratiu i l intercanvi de coneixements',
            'number_of_students' => 3,
        ]);

        Group::create([
            'name' => 'Grup 2',
            'description' => 'Un equip d alumnes apassionats per explorar idees 
             noves i aprofundir en diferents àrees del coneixement.',
            'number_of_students' => 4,
        ]);

        Group::create([
            'name' => 'Grup 3',
            'description' => 'Un grup enfocat al desenvolupament d habilitats 
             acadèmiques i professionals a través de l aprenentatge actiu.',
            'number_of_students' => 4,
        ]);

        Group::create([
            'name' => 'Grup 4',
            'description' => 'Un col·lectiu d estudiants que busquen millorar el 
             seu exercici acadèmic mitjançant el suport mutu i el treball en equip.',
            'number_of_students' => 3,
        ]);

        Group::create([
            'name' => 'Grup 5',
            'description' => 'Un grup de recerca i aprenentatge basat en la curiositat
             i el descobriment de noves àrees del coneixement.',
            'number_of_students' => 4,
        ]);

        Group::create([
            'name' => 'Grup 6',
            'description' => 'Un equip dedicat a la discussió i anàlisi de temes educatius
             per desenvolupar habilitats de pensament crític i argumentació.',
            'number_of_students' => 3,
        ]);

        Group::create([
            'name' => 'Grup 7',
            'description' => 'Un grup que busca aplicar la tecnologia i les noves metodologies 
             per millorar l ensenyament i l aprenentatge.',
            'number_of_students' => 4,
        ]);

        Group::create([
            'name' => 'Grup 8',
            'description' => 'Un espai on alumnes i professors treballen plegats per enfortir 
             l educació a través del diàleg i la col·laboració.',
            'number_of_students' => 3,
        ]);

        Group::create([
            'name' => 'Grup 9',
            'description' => 'Un col·lectiu que fomenta l autonomia en l aprenentatge i la 
             creació d estratègies efectives per a l èxit acadèmic.',
            'number_of_students' => 3,
        ]);

        Group::create([
            'name' => 'Grup 10',
            'description' => 'Un grup de suport acadèmic on els participants s ajuden mútuament
             en la comprensió i l aplicació de continguts educatius.',
            'number_of_students' => 4,
        ]);
    }
}
