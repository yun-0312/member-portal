<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Video;
use App\Models\Role;
use App\Models\TargetRole;

class VideoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //ロール一覧を取得
        $roles = Role::pluck('id', 'name');

        $roleIds = [
            $roles['admin'],
            $roles['staff'],
            $roles['member'],
            $roles['director'],
            $roles['medical_staff'],
        ];

        $videos = Video::factory()->count(5)->create();

        foreach ($videos as $video) {
            $video->roles()->attach($roleIds);
        }
    }
}
