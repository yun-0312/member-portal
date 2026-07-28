<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ContentCategory;
use App\Models\Role;

class ContentCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            // 書類系
            [
                'name' => '大規模災害対応マニュアル',
                'slug' => 'disaster-manual',
                'section' => 'download',
                'display_type' => 'list',
                'sort_order' => 1,
            ],
            [
                'name' => '健診・検診マニュアル',
                'slug' => 'health-check-manual',
                'section' => 'download',
                'display_type' => 'year_archive',
                'sort_order' => 2,
            ],
            [
                'name' => '予防接種総括票',
                'slug' => 'vaccination-summary',
                'section' => 'download',
                'display_type' => 'list',
                'sort_order' => 3,
            ],
            [
                'name' => '公衆衛生関連',
                'slug' => 'public-health',
                'section'      => 'download',
                'display_type' => 'list',
                'sort_order' => 4,
            ],
            [
                'name' => '登録変更届',
                'slug' => 'registration-change',
                'section'      => 'download',
                'display_type' => 'list',
                'sort_order' => 5,
            ],
            [
                'name' => '各種委託料一覧',
                'slug' => 'commission-fees',
                'section'      => 'download',
                'display_type' => 'list',
                'sort_order' => 6,
            ],
            [
                'name' => 'その他（書類）',
                'slug' => 'others-documents',
                'section'      => 'download',
                'display_type' => 'list',
                'sort_order' => 7,
            ],

            // 議事録系
            [
                'name' => '理事会ニュース',
                'slug' => 'board-news',
                'section' => 'main_menu',
                'display_type' => 'year_archive',
                'sort_order' => 8,
            ],
            [
                'name' => '委員会',
                'slug' => 'committee',
                'section' => 'main_menu',
                'display_type' => 'subcategory',
                'sort_order' => 9,
            ],
            [
                'name' => '四医会',
                'slug' => 'four-medical-association',
                'section' => 'main_menu',
                'display_type' => 'subcategory',
                'sort_order' => 10,
            ],
            [
                'name' => '会報・記念誌',
                'slug' => 'bulletin-magazine',
                'section' => 'main_menu',
                'display_type' => 'subcategory',
                'sort_order' => 11,
            ],
            [
                'name' => '広報',
                'slug' => 'public-relations',
                'section' => 'main_menu',
                'display_type' => 'list',
                'sort_order' => 12,
            ],
            [
                'name' => '諸規定',
                'slug' => 'regulations',
                'section' => 'main_menu',
                'display_type' => 'subcategory',
                'sort_order' => 13,
            ],
            [
                'name' => '会員名簿',
                'slug' => 'member-directory',
                'section' => 'main_menu',
                'display_type' => 'year_archive',
                'sort_order' => 14,
            ],
            [
                'name' => '総会議案',
                'slug' => 'general-meeting-agenda',
                'section' => 'main_menu',
                'display_type' => 'year_archive',
                'sort_order' => 15,
            ],
            [
                'name' => 'その他（議事録）',
                'slug' => 'others-minutes',
                'section' => 'main_menu',
                'display_type' => 'subcategory',
                'sort_order' => 16,
            ],
            [
                'name' => '理事会専用',
                'slug' => 'board-exclusive',
                'section' => 'special',
                'display_type' => 'subcategory',
                'sort_order' => 17,
            ],
        ];

        //ロール取得
        $roles = Role::pluck('id', 'name');

        foreach ($categories as $categoryData) {
            $category = ContentCategory::create($categoryData);

            //デフォルト：全ロール閲覧可能
            $allowedRoles = [
                $roles['admin'],
                $roles['staff'],
                $roles['director'],
                $roles['member'],
                $roles['medical_staff'],
            ];

            //理事会ニュース以下はmedical_staffを除外
            $restrictedSlugsForMedicalStaff = [
                'board-news',
                'committee',
                'four-medical-association',
                'bulletin-magazine',
                'public-relations',
                'regulations',
                'member-directory',
                'general-meeting-agenda',
                'others-minutes',
            ];

            if (in_array($categoryData['slug'], $restrictedSlugsForMedicalStaff)) {
                $allowedRoles = array_diff($allowedRoles, [
                    $roles['medical_staff'],
                ]);
            }

            //理事会専用はmedical_staffとmemberを除外
            if ($categoryData['slug'] === 'board-exclusive') {
                $allowedRoles = array_diff($allowedRoles, [
                    $roles['medical_staff'],
                    $roles['member'],
                ]);
            }

            //target_roles登録
            $category->roles()->attach(array_values($allowedRoles));
        }

    }
}
