<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Course;
use App\Models\Division;
use Faker\Factory as Faker;

class CourseUserSeeder extends Seeder
{
    /**
     * Ejecuta el seeder.
     */
    public function run(): void
    {
        // Obtener estudiantes y profesores
        $students = User::where('role_id', 2)->get();
        $teachers = User::where('role_id', 1)->get();

        // Obtener todos los cursos y divisiones disponibles
        $courses = Course::all();
        $divisions = Division::all();

        // Arrays para insertar relaciones en course_user y course_division
        $courseUsers = [];
        $courseDivisions = [];

        // Instanciar Faker para generar divisiones aleatorias
        $faker = Faker::create();

        // Asignar curso y división aleatoria a los estudiantes
        foreach ($students as $student) {
            $randomCourse = $courses->random(); // Curso aleatorio
            $randomDivision = $divisions->random(); // División aleatoria

            // Crear relación en course_user
            $courseUsers[] = [
                'course_id' => $randomCourse->id,
                'user_id' => $student->id,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Verificar y crear relación en course_division
            if (!DB::table('course_division')
                    ->where('course_id', $randomCourse->id)
                    ->where('division_id', $randomDivision->id)
                    ->exists()) {
                $courseDivisions[] = [
                    'course_id' => $randomCourse->id,
                    'division_id' => $randomDivision->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // Asignar cursos a los profesores (sin divisiones)
        foreach ($teachers as $teacher) {
            $randomCourse = $courses->random(); // Curso aleatorio

            // Crear relación en course_user
            $courseUsers[] = [
                'course_id' => $randomCourse->id,
                'user_id' => $teacher->id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Insertar relaciones en course_user
        DB::table('course_user')->insert($courseUsers);

        // Insertar relaciones en course_division (sin duplicados)
        DB::table('course_division')->insert($courseDivisions);
    }
}
