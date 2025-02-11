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
            ['group_id' => 1, 'user_id' => 1],
            ['group_id' => 1, 'user_id' => 2],
            ['group_id' => 1, 'user_id' => 3],
            ['group_id' => 2, 'user_id' => 4],
            ['group_id' => 2, 'user_id' => 5],
            ['group_id' => 2, 'user_id' => 6],
            ['group_id' => 2, 'user_id' => 7],
            ['group_id' => 3, 'user_id' => 8],
            ['group_id' => 3, 'user_id' => 9],
            ['group_id' => 3, 'user_id' => 10],
            ['group_id' => 3, 'user_id' => 11],
            ['group_id' => 4, 'user_id' => 12],
            ['group_id' => 4, 'user_id' => 13],
            ['group_id' => 4, 'user_id' => 14],
            ['group_id' => 5, 'user_id' => 15],
            ['group_id' => 5, 'user_id' => 16],
            ['group_id' => 5, 'user_id' => 17],
            ['group_id' => 5, 'user_id' => 18],
            ['group_id' => 6, 'user_id' => 19],
            ['group_id' => 6, 'user_id' => 20],
            ['group_id' => 6, 'user_id' => 21],
            ['group_id' => 7, 'user_id' => 22],
            ['group_id' => 7, 'user_id' => 23],
            ['group_id' => 7, 'user_id' => 24],
            ['group_id' => 7, 'user_id' => 25],
            ['group_id' => 8, 'user_id' => 26],
            ['group_id' => 8, 'user_id' => 27],
            ['group_id' => 8, 'user_id' => 28],
            ['group_id' => 9, 'user_id' => 29],
            ['group_id' => 9, 'user_id' => 30],
            ['group_id' => 9, 'user_id' => 31],
            ['group_id' => 10, 'user_id' => 32],
            ['group_id' => 10, 'user_id' => 33],
            ['group_id' => 10, 'user_id' => 34],
            ['group_id' => 10, 'user_id' => 35],
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
