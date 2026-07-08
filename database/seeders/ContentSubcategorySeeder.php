<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ContentCategory;
use App\Models\ContentSubcategory;

class ContentSubcategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $magazinesCategory = ContentCategory::where('slug', 'bulletin-magazine')->first();
        if ($magazinesCategory) {
            ContentSubcategory::create([
                'category_id' => $magazinesCategory->id,
                'name' => '会報',
                'sort_order' => 1,
            ]);
            ContentSubcategory::create([
                'category_id' => $magazinesCategory->id,
                'name' => '記念誌',
                'sort_order' => 2,
            ]);
        }

        // 諸規定
        $regulationsCategory = ContentCategory::where('slug', 'regulations')->first();
        if ($regulationsCategory) {
            $items = [
                ['name' => '定款', 'sort_order' => 1],
                ['name' => '施行細則', 'sort_order' => 2],
                ['name' => '諸規定', 'sort_order' => 3],
                ['name' => '内規', 'sort_order' => 4],
            ];

            foreach ($items as $item) {
                ContentSubcategory::create([
                    'category_id' => $regulationsCategory->id,
                    'name'        => $item['name'],
                    'sort_order'  => $item['sort_order'],
                ]);
            }
        }

        // その他
        $othersCategory = ContentCategory::where('slug', 'others-minutes')->first();
        if ($othersCategory) {
            $items = [
                ['name' => '感染症情報', 'sort_order' => 1],
                ['name' => '外部団体講習会のお知らせ', 'sort_order' => 2],
                ['name' => '新型コロナウィルス感染症情報', 'sort_order' => 3],
                ['name' => 'サイバーセキュリティ関連資料', 'sort_order' => 4],
                ['name' => '部活動', 'sort_order' => 5],
            ];

            foreach ($items as $item) {
                ContentSubcategory::create([
                    'category_id' => $othersCategory->id,
                    'name'        => $item['name'],
                    'sort_order'  => $item['sort_order'],
                ]);
            }
        }

        // 理事会専用
        $boardCategory = ContentCategory::where('slug', 'board-exclusive')->first();
        if ($boardCategory) {
            $items = [
                ['name' => '理事会（抄録・資料）', 'sort_order' => 1],
                ['name' => '庶務報告・協議事項', 'sort_order' => 2],
                ['name' => 'その他', 'sort_order' => 3],
            ];

            foreach ($items as $item) {
                ContentSubcategory::create([
                    'category_id' => $boardCategory->id,
                    'name'        => $item['name'],
                    'sort_order'  => $item['sort_order'],
                ]);
            }
        }
    }
}
