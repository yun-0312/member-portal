<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\NoticeCategory;
use App\Models\Notice;
use App\Models\Role;
use App\Models\User;

class NoticeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
public function run(): void
    {
        // ターゲットロールのIDリストを取得
        $roleIds = Role::whereIn('name', ['admin', 'staff', 'member', 'director', 'medical_staff'])
            ->pluck('id')
            ->toArray();

        // 投稿者（staffロールのユーザーまたは最初のユーザー）を準備
        $staffUser = User::whereHas('role', fn($q) => $q->where('name', 'staff'))->first() ?? User::first();

        // カテゴリ取得
        $letterCat = NoticeCategory::where('slug', 'letter')->value('id');
        $circulateCat = NoticeCategory::where('slug', 'circulate')->value('id');

        // 委員会一覧
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

        // 事務局レターの固定タイトル
        $letterTitles = [
            '外部講演会のお知らせ',
            '医療事務コールセンター問い合わせ報告書を更新しました',
            '資料をアップしました',
        ];

        foreach ($letterTitles as $title) {
            $notice = Notice::factory()->create([
                'title'          => $title,
                'committee_name' => null,
                'category_id'    => $letterCat,
                'created_by'     => $staffUser?->id ?? 1,
            ]);

            // target_roles を付与
            if (!empty($roleIds)) {
                $notice->roles()->attach($roleIds);
            }
        }

        $serial = 1;

        for ($i = 0; $i < 20; $i++) {
            $committee = $committees[array_rand($committees)];
            $title = "【26-" . str_pad($serial, 4, '0', STR_PAD_LEFT) . "】     {$committee}研修会";

            $notice = Notice::factory()->create([
                'title'          => $title,
                'committee_name' => $committee,
                'category_id'    => $circulateCat,
                'created_by'     => $staffUser?->id ?? 1,
            ]);

            // target_roles を付与
            if (!empty($roleIds)) {
                $notice->roles()->attach($roleIds);
            }

            $serial++;
        }
    }
}
