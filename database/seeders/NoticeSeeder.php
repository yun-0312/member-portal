<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\NoticeCategory;
use App\Models\Notice;
use App\Models\Role;
use Illuminate\Support\Str;

class NoticeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = Role::pluck('id', 'name');
        $letterCat = NoticeCategory::where('slug', 'letter')->value('id');
        $circulateCat = NoticeCategory::where('slug', 'circulate')->value('id');

        //委員会
        $committees = [
            '総務庶務委員会',
            '経理委員会',
            '保健事業委員会',
            '公衆衛生・学校医委員会',
            '地域医療連携委員会',
            '在宅医療委員会',
            '病院・防災・救急委員会',
            '学術・臨床研修委員会',
            '保険委員会',
            '広報・医療情報委員会',
        ];

        $letterTitles = [
            '外部講演会のお知らせ',
            '医療事務コールセンター問い合わせ報告書を更新しました',
            '資料をアップしました',
        ];

        //事務局レター
        foreach ($letterTitles as $title) {
            $notice = Notice::create([
                'title' => $title,
                'committee_name' => null,
                'body' => fake()->realText(30),
                'category_id' => $letterCat,
                'published_at' => now()->subDays(rand(1,30)),
                'created_by' => 1,
            ]);

            //target_rolesを付与
            $notice->roles()->attach([
                $roles['admin'],
                $roles['staff'],
                $roles['member'],
                $roles['director'],
                $roles['medical_staff'],
            ]);
        }

        //回覧
        $serial = 1;

        for ($i = 0; $i < 20; $i++) {
            $committee = $committees[array_rand($committees)];
            $title = "【26-".str_pad($serial, 4,'0', STR_PAD_LEFT)."】     {$committee}研修会";

            $notice = Notice::create([
                'title' => $title,
                'committee_name' => $committee,
                'body' => fake()->realText(80),
                'category_id' => $circulateCat,
                'published_at' => now()->subDays(rand(1, 30)),
                'created_by' => 1,
            ]);

            // target_roles を付与
            $notice->roles()->attach([
                $roles['admin'],
                $roles['staff'],
                $roles['member'],
                $roles['director'],
                $roles['medical_staff'],
            ]);

            $serial++;
        }

    }
}
