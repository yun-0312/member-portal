<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Group;
use App\Models\Role;

class GroupAssignSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ロールID取得
        $adminId = Role::where('name', 'admin')->value('id');
        $staffId = Role::where('name', 'staff')->value('id');
        $directorId = Role::where('name', 'director')->value('id');
        $memberId = Role::where('name', 'member')->value('id');
        $medicalStaffId = Role::where('name', 'medical_staff')->value('id');

        //理事会グループ一覧
        $boardGroup = Group::where('name', '理事会')->firstOrFail();

        // 委員会グループ一覧
        $committeeGroups = Group::whereHas('category', function ($q) {
            $q->where('name', '委員会');
        })->get();

        // 四医会グループ一覧
        $associationGroups = Group::whereHas('category', function ($q) {
            $q->where('name', '四医会');
        })->get();

        // staff → 複数委員会に所属（ランダムで2〜4つ）
        User::where('role_id', $staffId)
            ->get()
            ->each(function ($user) use ($committeeGroups) {
                $count = rand(2, 4);
                $user->groups()->sync(
                    $committeeGroups->random($count)->pluck('id')->toArray()
                );
            });

        // director → 委員会に所属（1〜2つ）+理事会（全員）
        User::where('role_id', $directorId)
            ->get()
            ->each(function ($user) use ($committeeGroups, $boardGroup) {
                $count = rand(1, 2);
                $committeeIds = $committeeGroups->random($count)->pluck('id')->toArray();
                $user->groups()->sync($committeeIds);

                $user->groups()->syncWithoutDetaching([$boardGroup->id]);
            });

        // member → 四医会に所属（必ず1つ）
        $members = User::where('role_id', $memberId)->get();

        $members->each(function ($user) use ($associationGroups) {
            $user->groups()->sync([$associationGroups->random()->id]);
        });

        // member の中から 4 名を委員会にも所属させる
        $committeeMembers = $members->random(min(4, $members->count()));

        $committeeMembers->each(function ($user) use ($committeeGroups) {
            // 既存の四医会所属を保持しつつ委員会を追加
            $current = $user->groups->pluck('id')->toArray();
            $addCommittee = $committeeGroups->random()->id;

            $user->groups()->sync(array_unique([
                ...$current,
                $addCommittee,
            ]));
        });

        // medical_staff → 所属なし
        User::where('role_id', $medicalStaffId)
            ->get()
            ->each(function ($user) {
                $user->groups()->sync([]);
            });

        // admin → 所属なし
        User::where('role_id', $adminId)
            ->get()
            ->each(function ($user) {
                $user->groups()->sync([]);
            });
    }
}
