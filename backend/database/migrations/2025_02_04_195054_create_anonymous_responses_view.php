<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::statement("
            CREATE VIEW anonymous_responses AS
            SELECT
                a.id AS real_id,
                au.anonymous_id AS usuario,
                af.anonymous_id AS formulario,
                aq.anonymous_id AS pregunta,
                CASE
                    WHEN a.answer_type IN ('multiple', 'checkbox')
                    THEN JSON_ARRAYAGG(CONCAT('Opcion-', o.id))
                    ELSE a.answer
                END AS respuesta,
                a.answer_type AS tipo_respuesta
            FROM answers a
            JOIN anonymous_users au ON a.user_id = au.real_id
            JOIN anonymous_forms af ON a.form_id = af.real_id
            JOIN anonymous_questions aq ON a.question_id = aq.real_id
            LEFT JOIN options o ON JSON_CONTAINS(a.answer, CAST(o.id AS JSON))
            GROUP BY a.id, au.anonymous_id, af.anonymous_id, aq.anonymous_id, a.answer, a.answer_type
        ");
    }

    public function down()
    {
        DB::statement("DROP VIEW IF EXISTS anonymous_responses");
    }
};
