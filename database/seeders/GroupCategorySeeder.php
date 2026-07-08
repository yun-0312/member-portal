<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GroupCategory;

class GroupCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => '委員会',
                'sort_order' => 1,
            ],
            [
                'name' => '四医会',
                'sort_order' => 2,
            ],
            [
                'name' => '理事会',
                'sort_order' => 3,
            ],
        ];

        foreach ($categories as $category) {
            GroupCategory::create($category);
        }
    }
}
