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
        CREATE VIEW anonymous_users AS
        SELECT
            u.id AS real_id,
            CONCAT('Usuario-', ROW_NUMBER() OVER (PARTITION BY c.id ORDER BY u.id)) AS anonymous_id,
            CONCAT('Curso-', c.id) AS curso_id
        FROM users u
        JOIN course_user cu ON u.id = cu.user_id
        JOIN courses c ON cu.course_id = c.id;
    ");
    }

    public function down()
    {
        DB::statement("DROP VIEW IF EXISTS anonymous_users");
    }
};
