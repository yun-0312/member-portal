<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Notice
            'notice.view',
            'notice.create',
            'notice.update',
            'notice.delete',

            // Document
            'document.view',
            'document.create',
            'document.update',
            'document.delete',

            // Workshop
            'workshop.view',
            'workshop.create',
            'workshop.update',
            'workshop.delete',

            // Video
            'video.view',
            'video.create',
            'video.update',
            'video.delete',

            // FAQ
            'faq.view',
            'faq.create',
            'faq.update',
            'faq.delete',

            // User / Role / Permission 管理
            'user.manage',
            'role.manage',
            'permission.manage',

            // 編集履歴
            'history.view',
        ];

        foreach ($permissions as $name) {
            Permission::create(['name' => $name]);
        }
    }
}
