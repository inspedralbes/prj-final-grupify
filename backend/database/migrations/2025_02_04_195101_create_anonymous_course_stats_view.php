<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::statement("
            CREATE VIEW anonymous_course_stats AS
            SELECT
                CONCAT('Curso-', c.id) AS curso_id,
                COUNT(DISTINCT au.anonymous_id) AS total_alumnos,
                COUNT(DISTINCT ar.real_id) AS respuestas_totales,
                ROUND((COUNT(DISTINCT ar.real_id) / COUNT(DISTINCT au.anonymous_id)) * 100, 2) AS participacion_porcentaje
            FROM courses c
            LEFT JOIN anonymous_users au ON c.id = au.curso_id
            LEFT JOIN anonymous_responses ar ON au.anonymous_id = ar.usuario
            GROUP BY c.id
        ");
    }

    public function down()
    {
        DB::statement("DROP VIEW IF EXISTS anonymous_course_stats");
    }
};
