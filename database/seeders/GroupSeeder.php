<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Group;
use App\Models\GroupCategory;

class GroupSeeder extends Seeder
{
    public function run(): void
    {
        // カテゴリID取得
        $committeeId = GroupCategory::where('name', '委員会')->value('id');
        $associationId = GroupCategory::where('name', '四医会')->value('id');
        $boardId = GroupCategory::where('name', '理事会')->value('id');

        // 委員会一覧
        $committees = [
            ['name' => '総務庶務委員会'],
            ['name' => '経理委員会'],
            ['name' => '保健事業委員会'],
            ['name' => '公衆衛生・学校医委員会'],
            ['name' => '地域医療連携委員会'],
            ['name' => '在宅医療委員会'],
            ['name' => '介護保険委員会'],
            ['name' => '病院・防災・救急委員会'],
            ['name' => '学術・臨床研修委員会'],
            ['name' => '保険委員会'],
            ['name' => '広報・医療情報委員会'],
        ];

        foreach ($committees as $committee) {
            Group::create([
                'group_category_id' => $committeeId,
                'name' => $committee['name'],
            ]);
        }

        // 四医会一覧
        $associations = [
            ['name' => '長崎医会'],
            ['name' => '池袋医会'],
            ['name' => '巣鴨医会'],
            ['name' => '高田医会'],
        ];

        foreach ($associations as $association) {
            Group::create([
                'group_category_id' => $associationId,
                'name' => $association['name'],
            ]);
        }

        //理事会一覧
        Group::firstOrCreate([
            'group_category_id' => $boardId,
            'name' => '理事会',
        ]);
    }
}
