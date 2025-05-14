<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('competences', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        // Insertar las competencias por defecto
        DB::table('competences')->insert([
            ['id' => 22, 'name' => 'Responsabilitat'],
            ['id' => 23, 'name' => 'Treball en equip'],
            ['id' => 24, 'name' => 'Gestió del temps'],
            ['id' => 25, 'name' => 'Comunicació'],
            ['id' => 26, 'name' => 'Adaptabilitat'],
            ['id' => 27, 'name' => 'Lideratge'],
            ['id' => 28, 'name' => 'Creativitat'],
            ['id' => 29, 'name' => 'Proactivitat'],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('competences');
    }
};