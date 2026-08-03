<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Content;
use App\Models\User;
use App\Models\ContentCategory;
use App\Models\ContentSubcategory;
use App\Models\Role;

class ContentSeeder extends Seeder
{
    public function run(): void
    {
        // 🔍 ロール名をすべて小文字にして安全にIDを取得する
        $roles = Role::all()->pluck('id', 'name')->mapWithKeys(function ($id, $name) {
            return [strtolower($name) => $id];
        });

        $adminId    = $roles->get('admin');
        $staffId    = $roles->get('staff');
        $directorId = $roles->get('director');
        $memberId   = $roles->get('member');

        // null を排除した全ロールIDと、memberを除外したロールID
        $allRoleIds = array_filter([$adminId, $staffId, $directorId, $memberId]);
        $noMemberRoleIds = array_filter([$adminId, $staffId, $directorId]);

        // staff のみ（存在しない場合のフォールバックを設定）
        $staffUsers = User::whereHas('role', fn($q) => $q->where('name', 'staff'))->get();
        if ($staffUsers->isEmpty()) {
            $staffUsers = User::limit(1)->get(); // 保険: 最初に見つかったユーザーを使用
        }

        // --- 1. 委員会 ---
        $committeeCategory = ContentCategory::where('slug', 'committee')->first();
        if ($committeeCategory) {
            $subcategories = ContentSubcategory::where('category_id', $committeeCategory->id)->get();

            foreach ($subcategories as $sub) {
                for ($i = 1; $i <= 20; $i++) {
                    $content = Content::factory()->create([
                        'category_id'    => $committeeCategory->id,
                        'subcategory_id' => $sub->id,
                        'title'          => $sub->name . '資料 ' . $i,
                        'meeting_date'   => fake()->dateTimeBetween('-3 year', 'now'),
                        'published_at'   => fake()->dateTimeBetween('-3 year', 'now'),
                        'created_by'     => $staffUsers->random()->id,
                    ]);

                    $content->roles()->attach($allRoleIds);
                }
            }
        }

        // --- 2. 四医会 (slug のタイポを修正) ---
        $fourMedicalAssociationCategory = ContentCategory::where('slug', 'four-medical-association')->first();
        if ($fourMedicalAssociationCategory) {
            $subcategories = ContentSubcategory::where('category_id', $fourMedicalAssociationCategory->id)->get();

            foreach ($subcategories as $sub) {
                for ($i = 1; $i <= 20; $i++) {
                    $content = Content::factory()->create([
                        'category_id'    => $fourMedicalAssociationCategory->id,
                        'subcategory_id' => $sub->id,
                        'title'          => $sub->name . '資料 ' . $i,
                        'meeting_date'   => fake()->dateTimeBetween('-3 year', 'now'),
                        'published_at'   => fake()->dateTimeBetween('-3 year', 'now'),
                        'created_by'     => $staffUsers->random()->id,
                    ]);

                    $content->roles()->attach($allRoleIds);
                }
            }
        }

        // --- 3. 理事会 ---
        $boardCategory = ContentCategory::where('slug', 'board-news')->first();
        if ($boardCategory) {
            for ($i = 1; $i <= 20; $i++) {
                $content = Content::factory()->create([
                    'category_id'     => $boardCategory->id,
                    'subcategory_id'  => null,
                    'title'           => '理事会ニュース ' . $i,
                    'meeting_date'    => fake()->dateTimeBetween('-3 year', 'now'),
                    'published_at'    => fake()->dateTimeBetween('-3 year', 'now'),
                    'created_by'      => $staffUsers->random()->id,
                ]);

                $content->roles()->attach($allRoleIds);
            }
        }

        // --- 4. 会報・記念誌 ---
        $magazinesCategory = ContentCategory::where('slug', 'bulletin-magazine')->first();
        if ($magazinesCategory) {
            $subcategories = ContentSubcategory::where('category_id', $magazinesCategory->id)->get();

            foreach ($subcategories as $sub) {
                for ($i = 1; $i <= 20; $i++) {
                    $content = Content::factory()->create([
                        'category_id'    => $magazinesCategory->id,
                        'subcategory_id' => $sub->id,
                        'title'          => $sub->name . ' ' . fake()->year . '年度版',
                        'published_at'   => fake()->dateTimeBetween('-5 years', 'now'),
                        'created_by'     => $staffUsers->random()->id,
                    ]);

                    $content->roles()->attach($allRoleIds);
                }
            }
        }

        // --- 5. 諸規定 ---
        $rulesCategory = ContentCategory::where('slug', 'regulations')->first();
        if ($rulesCategory) {
            $subcategories = ContentSubcategory::where('category_id', $rulesCategory->id)->get();

            foreach ($subcategories as $sub) {
                $content = Content::factory()->create([
                    'category_id'    => $rulesCategory->id,
                    'subcategory_id' => $sub->id,
                    'title'          => $sub->name . '（' . fake()->year . '年度改訂）',
                    'published_at'   => fake()->dateTimeBetween('-5 years', 'now'),
                    'created_by'     => $staffUsers->random()->id,
                ]);

                $content->roles()->attach($allRoleIds);
            }
        }

        // --- 6. その他（議事録） ---
        $othersCategory = ContentCategory::where('slug', 'others-minutes')->first();
        if ($othersCategory) {
            $subcategories = ContentSubcategory::where('category_id', $othersCategory->id)->get();

            foreach ($subcategories as $sub) {
                $content = Content::factory()->create([
                    'category_id'    => $othersCategory->id,
                    'subcategory_id' => $sub->id,
                    'title'          => $sub->name,
                    'published_at'   => fake()->dateTimeBetween('-3 years', 'now'),
                    'created_by'     => $staffUsers->random()->id,
                ]);

                $content->roles()->attach($allRoleIds);
            }
        }

        // --- 7. 理事会専用 ---
        $boardExclusiveCategory = ContentCategory::where('slug', 'board-exclusive')->first();
        if ($boardExclusiveCategory) {
            $subcategories = ContentSubcategory::where('category_id', $boardExclusiveCategory->id)->get();

            foreach ($subcategories as $sub) {
                $content = Content::factory()->create([
                    'category_id'    => $boardExclusiveCategory->id,
                    'subcategory_id' => $sub->id,
                    'title'          => $sub->name,
                    'published_at'   => fake()->dateTimeBetween('-3 years', 'now'),
                    'created_by'     => $staffUsers->random()->id,
                ]);

                $content->roles()->attach($noMemberRoleIds);
            }
        }

        // --- 8. 書類系（download）---
        $downloadSlugs = [
            'disaster-manual',
            'health-check-manual',
            'vaccination-summary',
            'public-health',
            'registration-change',
            'commission-fees',
            'others-documents',
        ];

        foreach ($downloadSlugs as $slug) {
            $category = ContentCategory::where('slug', $slug)->first();

            if ($category) {
                $subcategories = ContentSubcategory::where('category_id', $category->id)->get();

                if ($subcategories->count() > 0) {
                    foreach ($subcategories as $sub) {
                        for ($i = 1; $i <= 10; $i++) {
                            $content = Content::factory()->create([
                                'category_id'    => $category->id,
                                'subcategory_id' => $sub->id,
                                'title'          => $sub->name . '資料 ' . $i,
                                'published_at'   => fake()->dateTimeBetween('-2 years', 'now'),
                                'created_by'     => $staffUsers->random()->id,
                            ]);

                            // 🔍 書類系にもロール権限をアタッチ
                            $content->roles()->attach($allRoleIds);
                        }
                    }
                } else {
                    for ($i = 1; $i <= 10; $i++) {
                        $content = Content::factory()->create([
                            'category_id'    => $category->id,
                            'subcategory_id' => null,
                            'title'          => $category->name . '資料 ' . $i,
                            'published_at'   => fake()->dateTimeBetween('-2 years', 'now'),
                            'created_by'     => $staffUsers->random()->id,
                        ]);

                        //  書類系にもロール権限をアタッチ
                        $content->roles()->attach($allRoleIds);
                    }
                }
            }
        }
    }
}