<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CommentUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Obtener todos los estudiantes de la base de datos (suponiendo role_id = 2)
        $students = DB::table('users')->where('role_id', 2)->get(); // Puedes ajustar el valor según cómo gestionas los role_ids

        // Obtener todos los comentarios de la base de datos
        $comments = DB::table('comments')->pluck('id')->toArray(); // Obtener solo los IDs de los comentarios existentes

        // Asignar entre 1 y 3 comentarios aleatorios a cada estudiante
        foreach ($students as $student) {
            $randomCommentIds = array_rand($comments, rand(1, 3)); // Seleccionar entre 1 y 3 comentarios aleatorios

            // Si `array_rand` devuelve un solo valor, lo convertimos en array
            if (!is_array($randomCommentIds)) {
                $randomCommentIds = [$randomCommentIds];
            }

            // Insertar las relaciones entre estudiante y comentario en la tabla `comment_user`
            foreach ($randomCommentIds as $commentId) {
                DB::table('comment_user')->insert([
                    'comment_id' => $comments[$commentId], // Relacionamos el comentario con el estudiante
                    'student_id' => $student->id, // Relacionamos el estudiante con este comentario
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
