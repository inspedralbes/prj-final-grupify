<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::statement("
            CREATE VIEW anonymous_analysis AS
            SELECT
                ar.formulario,
                ar.pregunta,
                ar.respuesta,
                COUNT(*) AS frecuencia,
                CONCAT('Curso-', au.curso_id) AS curso
            FROM anonymous_responses ar
            JOIN anonymous_users au ON ar.usuario = au.anonymous_id
            GROUP BY ar.formulario, ar.pregunta, ar.respuesta, au.curso_id
        ");
    }

    public function down()
    {
        DB::statement("DROP VIEW IF EXISTS anonymous_analysis");
    }
};
