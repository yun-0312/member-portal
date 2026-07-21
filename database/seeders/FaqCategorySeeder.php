<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FaqCategory;

class FaqCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => '診察料', 'sort_order' => 1],
            ['name' => '指導管理料', 'sort_order' => 2],
            ['name' => '在宅医療', 'sort_order' => 3],
            ['name' => '検査', 'sort_order' => 4],
            ['name' => '画像診断', 'sort_order' => 5],
            ['name' => '投薬', 'sort_order' => 6],
            ['name' => '注射', 'sort_order' => 7],
            ['name' => '精神科専門療法', 'sort_order' => 8],
            ['name' => '処置', 'sort_order' => 9],
            ['name' => '手術', 'sort_order' => 10],
            ['name' => '麻酔', 'sort_order' => 11],
            ['name' => 'リハビリテーション', 'sort_order' => 12],
            ['name' => 'レセプト記載', 'sort_order' => 13],
            ['name' => '返戻', 'sort_order' => 14],
            ['name' => '公費請求', 'sort_order' => 15],
            ['name' => '点数改正', 'sort_order' => 16],
            ['name' => '保険医療材料', 'sort_order' => 17],
            ['name' => 'その他', 'sort_order' => 18],
        ];

        foreach ($categories as $category) {
            FaqCategory::create($category);
        }
    }
}
