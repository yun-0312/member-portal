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
        $roles = Role::pluck('id', 'name');

        $allRoleIds = [
            $roles['admin'],
            $roles['staff'],
            $roles['member'],
            $roles['director'],
        ];

        $noMemberRoleIds = [
            $roles['admin'],
            $roles['staff'],
            $roles['director'],
        ];

        // staff のみ
        $staffUsers = User::whereHas('role', fn($q) => $q->where('name', 'staff'))->get();

       // 委員会
        $committeeCategory = ContentCategory::where('slug', 'committee')->first();
        if ($committeeCategory) {
            $subcategories = ContentSubcategory::where('category_id', $committeeCategory->id)->get();

            foreach ($subcategories as $sub) {
                for ($i = 1; $i <= 24; $i++) {

                $content = Content::factory()->create([
                    'category_id'    => $committeeCategory->id,
                    'subcategory_id' => $sub->id,
                    'title'          => $sub->name . '資料 ' . $i,
                    'meeting_date' => fake()->dateTimeBetween('-2 year', 'now'),
                    'published_at' => fake()->dateTimeBetween('-2 year', 'now'),
                    'created_by'     => $staffUsers->random()->id,
                    ]);

                    $content->roles()->attach($allRoleIds);
                }
            }
        }

       // 四医会
        $fourMedicalAssociationCategory = ContentCategory::where('slug', 'four-medical-association
committee')->first();
        if ($fourMedicalAssociationCategory) {
            $subcategories = ContentSubcategory::where('category_id', $fourMedicalAssociation->id)->get();

            foreach ($subcategories as $sub) {
                for ($i = 1; $i <= 24; $i++) {

                    $content = Content::factory()->create([
                        'category_id'    => $fourMedicalAssociationCategory->id,
                        'subcategory_id' => $sub->id,
                        'title'          => $sub->name . '資料 ' . $i,
                        'meeting_date' => fake()->dateTimeBetween('-2 year', 'now'),
                        'published_at' => fake()->dateTimeBetween('-2 year', 'now'),
                        'created_by'     => $staffUsers->random()->id,
                    ]);

                    $content->roles()->attach($allRoleIds);
                }
            }
        }

        // 理事会
        $boardCategory = ContentCategory::where('slug', 'board-news')->first();
        if ($boardCategory) {
            for ($i = 1; $i <= 24; $i++) {
                $content = Content::factory()->create([
                    'category_id'     => $boardCategory->id,
                    'subcategory_id' => null,
                    'title'        => '理事会ニュース ' . $i,
                    'meeting_date' => fake()->dateTimeBetween('-2 year', 'now'),
                    'published_at' => fake()->dateTimeBetween('-2 year', 'now'),
                    'created_by'   => $staffUsers->random()->id,
                ]);

                    $content->roles()->attach($allRoleIds);
            }
        }

        // 会報・記念誌（subcategory）
        $magazinesCategory = ContentCategory::where('slug', 'bulletin-magazine')->first();
        if ($magazinesCategory) {
            $subcategories = ContentSubcategory::where('category_id', $magazinesCategory->id)->get();

            foreach ($subcategories as $sub) {
                for ($i = 1; $i <= 3; $i++) {
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

        // 諸規定（subcategory）
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

        // その他（subcategory）
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

        // 理事会専用（subcategory）
        $boardCategory = ContentCategory::where('slug', 'board-exclusive')->first();
        if ($boardCategory) {
            $subcategories = ContentSubcategory::where('category_id', $boardCategory->id)->get();

            foreach ($subcategories as $sub) {
                $content = Content::factory()->create([
                    'category_id'    => $boardCategory->id,
                    'subcategory_id' => $sub->id,
                    'title'          => $sub->name,
                    'published_at'   => fake()->dateTimeBetween('-3 years', 'now'),
                    'created_by'     => $staffUsers->random()->id,
                ]);

                $content->roles()->attach($noMemberRoleIds);
            }
        }
    }
}
