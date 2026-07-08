<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\NoticeCategory;

class NoticeCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => '事務局レター',
                'slug' => 'letter',
                'sort_order' => 1,
            ],
            [
                'name' => '回覧',
                'slug' => 'circulate',
                'sort_order' => 2,
            ],
            [
                'name' => '理事向け',
                'slug' => 'director',
                'sort_order' => 3,
            ],
            [
                'name' => '全体向け',
                'slug' => 'general',
                'sort_order' => 4,
            ],
        ];

        foreach ($categories as $category) {
            NoticeCategory::create($category);
        }
    }
}
