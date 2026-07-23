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
        // 委員会
        $committeeCategory = ContentCategory::where('slug', 'committee')->first();
        if ($committeeCategory) {
            $items = [
                ['name' => '総務庶務委員会', 'sort_order' => 1],
                ['name' => '経理委員会', 'sort_order' => 2],
                ['name' => '保健事業委員会', 'sort_order' => 3],
                ['name' => '公衆衛生・学校医委員会', 'sort_order' => 4],
                ['name' => '地域医療連携委員会', 'sort_order' => 5],
                ['name' => '在宅医療委員会', 'sort_order' => 6],
                ['name' => '介護保険委員会', 'sort_order' => 7],
                ['name' => '病院・防災・救急委員会', 'sort_order' => 8],
                ['name' => '学術・臨床研修委員会', 'sort_order' => 9],
                ['name' => '保険委員会', 'sort_order' => 10],
                ['name' => '広報・医療情報委員会', 'sort_order' => 11],
            ];

            foreach ($items as $item) {
                ContentSubcategory::create([
                    'category_id' => $committeeCategory->id,
                    'name'        => $item['name'],
                    'sort_order'  => $item['sort_order'],
                ]);
            }
        }

        //四医会
        $fourMedicalAssociationCategory = ContentCategory::where('slug', 'four-medical-association')->first();
        if ($fourMedicalAssociationCategory) {
            $items = [
                ['name' => '長崎医会', 'sort_order' => 12],
                ['name' => '池袋医会', 'sort_order' => 13],
                ['name' => '巣鴨医会', 'sort_order' => 14],
                ['name' => '高田医会', 'sort_order' => 15],
            ];

            foreach ($items as $item) {
                ContentSubcategory::create([
                    'category_id' => $fourMedicalAssociationCategory->id,
                    'name'        => $item['name'],
                    'sort_order'  => $item['sort_order'],
                ]);
            }
        }

        //記念誌
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
                ['name' => '定款', 'sort_order' => 3],
                ['name' => '施行細則', 'sort_order' => 4],
                ['name' => '諸規定', 'sort_order' => 5],
                ['name' => '内規', 'sort_order' => 6],
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
                ['name' => '感染症情報', 'sort_order' => 7],
                ['name' => '外部団体講習会のお知らせ', 'sort_order' => 8],
                ['name' => '新型コロナウィルス感染症情報', 'sort_order' => 9],
                ['name' => 'サイバーセキュリティ関連資料', 'sort_order' => 10],
                ['name' => '部活動', 'sort_order' => 11],
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
                ['name' => '理事会（抄録・資料）', 'sort_order' => 12],
                ['name' => '庶務報告・協議事項', 'sort_order' => 13],
                ['name' => 'その他', 'sort_order' => 14],
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
