<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GroupUserSeeder extends Seeder
{
    /**
     * Ejecuta el seeder.
     */
    public function run(): void
    {
        $groupUsers = [
            ['id_group' => 1, 'user_id' => 1],
            ['id_group' => 1, 'user_id' => 2],
            ['id_group' => 1, 'user_id' => 3],
            ['id_group' => 2, 'user_id' => 4],
            ['id_group' => 2, 'user_id' => 5],
            ['id_group' => 3, 'user_id' => 6],
            ['id_group' => 3, 'user_id' => 7],
            ['id_group' => 4, 'user_id' => 8],
            ['id_group' => 5, 'user_id' => 9],
            ['id_group' => 5, 'user_id' => 10],
        ];

        $groupUsersWithTimestamps = array_map(function ($data) {
            return array_merge($data, [
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }, $groupUsers);

        DB::table('group_user')->insert($groupUsersWithTimestamps);
    }
}
