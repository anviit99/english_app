<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LessonSeeder extends Seeder
{
    public function run(): void
    {
        $json = file_get_contents(database_path('data/lessons.json'));
        $lessons = json_decode($json, true);

        foreach ($lessons as $lesson) {
            DB::table('lessons')->insert([
                'category_id' => (int) $lesson['category_id'],
                'title' => $lesson['title'],
                'description' => $lesson['description'],
                "question_text" => $lesson['question_text'] ?? null,
                "correct_answer" => $lesson['correct_answer'] ?? null,
                "options"=>  isset($lesson['options']) ? json_encode($lesson['options']) : null,
                "explain"=> $lesson['explain'] ?? null,
                "level"=> $lesson['level'] ?? null,
                "type"=> $lesson['type'] ?? null,
                "ipa"=> $lesson['ipa'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
