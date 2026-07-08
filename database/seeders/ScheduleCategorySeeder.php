<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ScheduleCategory;

class ScheduleCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => '講演会', 'slug' => 'lecture'],
            ['name' => '委員会', 'slug' => 'committee'],
            ['name' => '重要', 'slug' => 'important'],
            ['name' => 'その他', 'slug' => 'other'],
            ['name' => '2階案件', 'slug' => 'second-floor'],
        ];

        foreach ($categories as $category) {
            ScheduleCategory::create($category);
        }
    }
}
