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
            'name' => 'Group 1',
            'description' => 'Description of Group 1',
            'number_of_students' => 3,
        ]);

        Group::create([
            'name' => 'Group 2',
            'description' => 'Description of Group 2',
            'number_of_students' => 4,
        ]);

        Group::create([
            'name' => 'Group 3',
            'description' => 'Description of Group 3',
            'number_of_students' => 4,
        ]);

        Group::create([
            'name' => 'Group 4',
            'description' => 'Description of Group 4',
            'number_of_students' => 3,
        ]);

        Group::create([
            'name' => 'Group 5',
            'description' => 'Description of Group 5',
            'number_of_students' => 4,
        ]);

        Group::create([
            'name' => 'Group 6',
            'description' => 'Description of Group 6',
            'number_of_students' => 3,
        ]);

        Group::create([
            'name' => 'Group 7',
            'description' => 'Description of Group 7',
            'number_of_students' => 4,
        ]);

        Group::create([
            'name' => 'Group 8',
            'description' => 'Description of Group 8',
            'number_of_students' => 3,
        ]);

        Group::create([
            'name' => 'Group 9',
            'description' => 'Description of Group 9',
            'number_of_students' => 3,
        ]);

        Group::create([
            'name' => 'Group 10',
            'description' => 'Description of Group 10',
            'number_of_students' => 4,
        ]);
    }
}
