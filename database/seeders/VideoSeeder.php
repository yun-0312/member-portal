<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Video;
use App\Models\Role;

class VideoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //  1. ロール名をすべて小文字化して安全にIDを取得
        $roles = Role::all()->pluck('id', 'name')->mapWithKeys(function ($id, $name) {
            return [strtolower($name) => $id];
        });

        //  2. 該当するロールIDを取得（null を自動で排除）
        $allowedRoleIds = array_filter([
            $roles->get('admin'),
            $roles->get('staff'),
            $roles->get('member'),
            $roles->get('director'),
            $roles->get('medical_staff'),
        ]);

        // 動画データの作成（5件）
        $videos = Video::factory()->count(5)->create();

        //  3. attach ではなく sync を使って安全に権限を同期
        foreach ($videos as $video) {
            $video->roles()->sync($allowedRoleIds);
        }
    }
}