<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class CommentSeeder extends Seeder
{
    public function run()
    {
        // Usar Faker para generar datos falsos
        $faker = Faker::create();

        // Datos predefinidos de comentarios
        $comments = [
            'Rendiment acadèmic excel·lent durant el semestre.',
            'És un estudiant responsable i amb bones qualificacions.',
            'Demostra interès pels estudis i es comporta de manera respectuosa.',
            'Necessita millorar la seva actitud a classe, però les seves habilitats acadèmiques són bones.',
            'Malgrat les seves dificultats, està mostrant millores en el rendiment acadèmic.',
            'Ha de prestar més atenció durant les classes, sovint es distreu.',
            'Bon comportament, tot i que de vegades li falta participació en les activitats.',
            'Està mostrant un comportament cada cop més madur i responsable.',
            'Ha fet un esforç notable aquest trimestre, segueix així!',
            'El seu treball en grup podria millorar, però és una persona dedicada.',
            'Necessita més motivació en les matèries no d’interès.',
            'Pot millorar amb més atenció als detalls.',
            'Els seus comentaris en classe són sempre útils.',
            'En les darreres setmanes ha demostrat molta millora.',
            'Comporta’s millor amb els altres alumnes i respon adequadament.',
        ];

        // Generar 10 comentarios falsos
        for ($i = 0; $i < 20; $i++) {
            DB::table('comments')->insert([
                'teacher_id' => DB::table('users')->where('role_id', 1)->inRandomOrder()->value('id'), // Selecciona un ID de profesor aleatorio
                'content' => $faker->randomElement($comments), // Selección aleatoria de comentarios
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
