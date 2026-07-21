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
                'description' => '災害時の対応マニュアル',
                'sort_order' => 1,
            ],
            [
                'name' => '健診・検診マニュアル',
                'slug' => 'health-check-manual',
                'description' => '健診・検診に関するマニュアル',
                'sort_order' => 2,
            ],
            [
                'name' => '予防接種総括票',
                'slug' => 'vaccination-summary',
                'description' => '予防接種関連の総括資料',
                'sort_order' => 3,
            ],
            [
                'name' => '公衆衛生関連',
                'slug' => 'public-health',
                'description' => '公衆衛生に関する資料',
                'sort_order' => 4,
            ],
            [
                'name' => '登録変更届',
                'slug' => 'registration-change',
                'description' => '登録変更に関する届出書類',
                'sort_order' => 5,
            ],
            [
                'name' => '各種委託料一覧',
                'slug' => 'commission-fees',
                'description' => '委託料に関する一覧資料',
                'sort_order' => 6,
            ],
            [
                'name' => 'その他（書類）',
                'slug' => 'others-documents',
                'description' => 'その他の書類関連コンテンツ',
                'sort_order' => 7,
            ],

            // 議事録系
            [
                'name' => '理事会ニュース',
                'slug' => 'board-news',
                'description' => '理事会に関するニュース',
                'sort_order' => 8,
            ],
            [
                'name' => '委員会',
                'slug' => 'committee',
                'description' => '委員会関連の議事録や資料',
                'sort_order' => 9,
            ],
            [
                'name' => '四医会',
                'slug' => 'four-medical-association',
                'description' => '四医会関連資料',
                'sort_order' => 10,
            ],
            [
                'name' => '会報・記念誌',
                'slug' => 'bulletin-magazine',
                'description' => '会報や記念誌などの刊行物',
                'sort_order' => 11,
            ],
            [
                'name' => '広報',
                'slug' => 'public-relations',
                'description' => '広報関連の資料',
                'sort_order' => 12,
            ],
            [
                'name' => '諸規定',
                'slug' => 'regulations',
                'description' => '各種規定類',
                'sort_order' => 13,
            ],
            [
                'name' => '会員名簿',
                'slug' => 'member-directory',
                'description' => '会員名簿関連資料',
                'sort_order' => 14,
            ],
            [
                'name' => '総会議案',
                'slug' => 'general-meeting-agenda',
                'description' => '総会議案資料',
                'sort_order' => 15,
            ],
            [
                'name' => 'その他（議事録）',
                'slug' => 'others-minutes',
                'description' => 'その他の議事録関連コンテンツ',
                'sort_order' => 16,
            ],
            [
                'name' => '理事会専用',
                'slug' => 'board-exclusive',
                'description' => '理事のみ閲覧可能な専用コンテンツ',
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
            $allowedRoles = array_values($allowedRoles);
            $category->roles()->attach($allowedRoles);
        }

    }
}
