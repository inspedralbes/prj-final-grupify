<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Course;
use App\Models\Division;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TutorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Recuperar tokens existentes si existen
        $tokens = [];
        if (file_exists(storage_path('tokens.json'))) {
            $tokens = json_decode(file_get_contents(storage_path('tokens.json')), true) ?? [];
        }

        // Crear usuario tutor
        $tutor = User::create([
            'name' => 'Tutor',
            'last_name' => 'Principal',
            'email' => 'tutor@gmail.com',
            'password' => Hash::make('password'),
            'role_id' => Role::where('name', 'tutor')->first()->id,
            'remember_token' => Str::random(60),
            'image' => 'https://i.pravatar.cc/150?img='.rand(1, 70)
        ]);

        // Crear un segundo tutor
        $tutor2 = User::create([
            'name' => 'Tutor',
            'last_name' => 'Secundario',
            'email' => 'tutor2@gmail.com',
            'password' => Hash::make('password'),
            'role_id' => Role::where('name', 'tutor')->first()->id,
            'remember_token' => Str::random(60),
            'image' => 'https://i.pravatar.cc/150?img='.rand(1, 70)
        ]);

        // Generar tokens para tutores
        $tokens['tutor'] = $tutor->createToken('Groupify')->plainTextToken;
        $tokens['tutor2'] = $tutor2->createToken('Groupify')->plainTextToken;

        // Asignar cursos y divisiones a los tutores (siguiendo el modelo del profesor)
        // Obtener cursos y divisiones
        $courses = Course::all();
        $divisions = Division::all();
        
        if ($courses->isEmpty()) {
            $this->command->error('No hay cursos disponibles. Ejecute primero CourseSeeder.');
            return;
        }
        
        if ($divisions->isEmpty()) {
            $this->command->error('No hay divisiones disponibles. Ejecute primero DivisionSeeder.');
            return;
        }

        // Para el primer tutor: Asignar un curso y división aleatorios
        $randomCourse1 = $courses->random();
        $randomDivision1 = $divisions->random();

        // Crear relación en course_user para el primer tutor
        DB::table('course_user')->insert([
            'course_id' => $randomCourse1->id,
            'user_id' => $tutor->id,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Crear relación en course_division_user para el primer tutor
        DB::table('course_division_user')->insert([
            'course_id' => $randomCourse1->id,
            'division_id' => $randomDivision1->id,
            'user_id' => $tutor->id,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        // Para el segundo tutor: Asignar un curso y división diferentes
        $randomCourse2 = $courses->except([$randomCourse1->id])->random();
        $randomDivision2 = $divisions->random();

        // Crear relación en course_user para el segundo tutor
        DB::table('course_user')->insert([
            'course_id' => $randomCourse2->id,
            'user_id' => $tutor2->id,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Crear relación en course_division_user para el segundo tutor
        DB::table('course_division_user')->insert([
            'course_id' => $randomCourse2->id,
            'division_id' => $randomDivision2->id,
            'user_id' => $tutor2->id,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Guardar los tokens en un archivo
        file_put_contents(storage_path('tokens.json'), json_encode($tokens, JSON_PRETTY_PRINT));

        $this->command->info('Se han creado tutores con sus respectivos cursos y divisiones asignados.');
        $this->command->info('Tutor 1: Curso ' . $randomCourse1->name . ' División ' . $randomDivision1->name);
        $this->command->info('Tutor 2: Curso ' . $randomCourse2->name . ' División ' . $randomDivision2->name);
    }
}
