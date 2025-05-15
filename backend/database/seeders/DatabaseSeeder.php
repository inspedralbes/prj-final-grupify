<?php

namespace Database\Seeders;

use App\Models\Answer;
use App\Models\Comment;
use App\Models\Course;
use App\Models\Division;
use App\Models\Form;
use App\Models\Group;
use App\Models\Question;
use Illuminate\Database\Seeder;
use Laravel\Prompts\FormStep;

use function Laravel\Prompts\form;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()

    {
        // Asegurarse de que RoleSeeder se ejecute primero
        $this->call([
            RoleSeeder::class,       // IMPORTANTE: Roles deben crearse primero
            CourseSeeder::class,
            SubjectSeeder::class,
            UserSeeder::class,
            TutorSeeder::class,       // Seeder para tutores
            OrientadorSeeder::class,  // Seeder para orientadores
            DivisionSeeder::class,
            GroupSeeder::class,
            GroupDivisionSeeder::class,
            GroupUserSeeder::class,
            GroupSubjectSeeder::class,
            GroupCourseSeeder::class,
            FormSeeder::class,   // Crear formularios primero
            QuestionSeeder::class, // Crear preguntas después
            OptionSeeder::class,
            CourseUserSeeder::class,
            SociogramRelationshipSeeder::class,
            CommentSeeder::class,
            CommentUserSeeder::class,
            CommentsGroupsSeeder::class,
            FormUserSeeder::class,
            TagCescSeeder::class,
            CescRelationshipSeeder::class,
            CescResultsSeeder::class,
            BitacoraSeeder::class,
            BitacoraNotesSeeder::class,
            CourseDivisionSeeder::class,
            CompetenceAnswerSeeder::class, // Seeder para generar datos de autoavaluación de competencias
        ]);
    }
}
