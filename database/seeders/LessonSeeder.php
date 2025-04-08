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
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
