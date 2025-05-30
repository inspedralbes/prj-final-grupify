<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\BitacoraNote;
use App\Models\Bitacora;
use App\Models\Group;

class BitacoraNotesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener todas las bitácoras existentes
        $bitacoras = Bitacora::all();
        
        foreach ($bitacoras as $bitacora) {
            // Obtener el grupo asociado a la bitácora
            $group = Group::find($bitacora->group_id);
            
            if ($group) {
                // Obtener los usuarios del grupo
                $users = $group->users;
                
                // Para cada usuario del grupo
                foreach ($users as $user) {
                    // Crear entre 1 y 2 notas por usuario
                    $numberOfNotes = rand(1, 2);
                    
                    for ($i = 1; $i <= $numberOfNotes; $i++) {
                        BitacoraNote::create([
                            'bitacora_id' => $bitacora->id,
                            'user_id' => $user->id,
                            'title' => "Nota $i de l'usuari {$user->name}",
                            'content' => "Aquesta és la nota número $i creada per {$user->name} per al grup {$group->name}. " .
                                        "Contingut d'exemple generat per a propòsits de prova.",
                        ]);
                    }
                }
            }
        }
    }
}
