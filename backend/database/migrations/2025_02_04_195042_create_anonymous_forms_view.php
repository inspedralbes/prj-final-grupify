<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        DB::statement("
            CREATE VIEW anonymous_forms AS
            SELECT
                f.id AS real_id,
                CONCAT('Form-', f.id) AS anonymous_id,
                CONCAT('Profesor-', ROW_NUMBER() OVER (PARTITION BY f.teacher_id ORDER BY f.id)) AS teacher_anonymous_id,
                f.title,
                f.responses_count,
                f.status
            FROM forms f
        ");
    }

    public function down()
    {
        DB::statement("DROP VIEW IF EXISTS anonymous_forms");
    }
};
