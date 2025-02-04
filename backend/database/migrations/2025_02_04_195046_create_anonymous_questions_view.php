<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::statement("
            CREATE VIEW anonymous_questions AS
            SELECT
                q.id AS real_id,
                CONCAT('Pregunta-', q.id) AS anonymous_id,
                CONCAT('Form-', q.form_id) AS form_anonymous_id,
                q.title,
                q.type
            FROM questions q
        ");
    }

    public function down()
    {
        DB::statement("DROP VIEW IF EXISTS anonymous_questions");
    }
};
