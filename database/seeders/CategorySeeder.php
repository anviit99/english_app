<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['id' => 1,  'category_name' => 'self_introduction'],
            ['id' => 2,  'category_name' => 'daily_routines'],
            ['id' => 3,  'category_name' => 'hobbies_and_interests'],
            ['id' => 4,  'category_name' => 'family_members'],
            ['id' => 5,  'category_name' => 'food_and_drinks'],
            ['id' => 6,  'category_name' => 'weather_and_seasons'],
            ['id' => 7,  'category_name' => 'clothing_and_fashion'],
            ['id' => 8,  'category_name' => 'school_and_education'],
            ['id' => 9,  'category_name' => 'work_and_jobs'],
            ['id' => 10, 'category_name' => 'shopping_and_money'],
            ['id' => 11, 'category_name' => 'travel_and_sightseeing'],
            ['id' => 12, 'category_name' => 'sports_and_exercise'],
            ['id' => 13, 'category_name' => 'technology_and_devices'],
            ['id' => 14, 'category_name' => 'movies_and_entertainment'],
            ['id' => 15, 'category_name' => 'health_and_fitness'],
            ['id' => 16, 'category_name' => 'animals_and_pets'],
            ['id' => 17, 'category_name' => 'holidays_and_festivals'],
            ['id' => 18, 'category_name' => 'environment_and_nature'],
            ['id' => 20, 'category_name' => 'dreams_and_future_plans'],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(['id' => $category['id']], $category);
        }
    }
}
