<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        if (!DB::selectOne("SHOW TABLES LIKE 'anonymous_options'")) {
        DB::statement("
            CREATE VIEW anonymous_options AS
            SELECT
                o.id AS real_id,
                CONCAT('Opcion-', o.id) AS anonymous_id,
                CONCAT('Pregunta-', o.question_id) AS question_anonymous_id,
                o.text,
                o.value
            FROM options o
        ");
        }
    }

    public function down()
    {
        DB::statement("DROP VIEW IF EXISTS anonymous_options");
    }
};
