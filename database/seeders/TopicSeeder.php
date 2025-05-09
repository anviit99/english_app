<?php

namespace Database\Seeders;

use App\Models\Topic;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TopicSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 20; $i++) {

            for ($level = 1; $level <= 4; $level++) {
                DB::table('topics')->insert([
                    'category_id' => $i,
                    'name' => "Topic $level",
                    'level' => $level,
                    'is_exam' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('topics')->insert([
                'category_id' => $i,
                'name' => "Bài kiểm tra",
                'level' => 5,
                'is_exam' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
