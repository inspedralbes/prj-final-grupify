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
        $students = User::where('role_id', 2)->get(); // Estudiantes
        $teachers = User::where('role_id', 1)->get(); // Profesores

        // Obtener todos los cursos y divisiones disponibles
        $courses = Course::all();
        $divisionsESO = Division::whereIn('id', [3, 4, 5,6,7])->get(); // Divisiones ESO A, B, C, D, E
        $divisionsBachillerato = Division::whereIn('id', [1, 2])->get(); // Divisiones Bachillerato 1, 2

        // Arrays para insertar relaciones en course_user y course_division_user
        $courseUsers = [];
        $courseDivisionUsers = [];

        // Asignar curso y división aleatoria a los estudiantes
        foreach ($students as $student) {
            $randomCourse = $courses->random(); // Curso aleatorio

            // Si el curso es de ESO (1, 2, 3, 4), asignar divisiones A, B, C, D, E
            if ($randomCourse->id <= 4) {
                $randomDivision = $divisionsESO->random(); // División aleatoria de ESO (A, B, C, D, E)
            }
            // Si el curso es de Bachillerato (5, 6, ...), asignar divisiones 1 y 2
            else {
                $randomDivision = $divisionsBachillerato->random(); // División aleatoria de Bachillerato (1, 2)
            }

            // Crear relación en course_user
            $courseUsers[] = [
                'course_id' => $randomCourse->id,
                'user_id' => $student->id,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Crear relación en course_division_user asegurando que sea el mismo curso asignado
            $courseDivisionUsers[] = [
                'course_id' => $randomCourse->id, // Usar el mismo curso que en course_user
                'division_id' => $randomDivision->id,
                'user_id' => $student->id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Asignar cursos a los profesores (sin divisiones)
        foreach ($teachers as $teacher) {
            $randomCourse = $courses->random(); // Curso aleatorio

            // Si el curso es de ESO (1, 2, 3, 4), asignar divisiones A, B, C, D, E
            if ($randomCourse->id <= 4) {
                $randomDivision = $divisionsESO->random(); // División aleatoria de ESO
            }
            // Si el curso es de Bachillerato (5, 6, ...), asignar divisiones 1 y 2
            else {
                $randomDivision = $divisionsBachillerato->random(); // División aleatoria de Bachillerato
            }

            // Crear relación en course_user para el profesor
            $courseUsers[] = [
                'course_id' => $randomCourse->id,
                'user_id' => $teacher->id,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Crear relación en course_division_user para el profesor asegurando que sea el mismo curso
            $courseDivisionUsers[] = [
                'course_id' => $randomCourse->id, // Usar el mismo curso que en course_user
                'division_id' => $randomDivision->id,
                'user_id' => $teacher->id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Insertar relaciones en course_user
        DB::table('course_user')->insertOrIgnore($courseUsers); // Usamos insertOrIgnore para evitar duplicados

        // Insertar relaciones en course_division_user
        DB::table('course_division_user')->insertOrIgnore($courseDivisionUsers); // Usamos insertOrIgnore para evitar duplicados
    }
}
