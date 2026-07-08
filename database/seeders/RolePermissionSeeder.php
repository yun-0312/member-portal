<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $all = Permission::pluck('id');

        //admin
        Role::where('name', 'admin')->first()
            ->permissions()->sync($all);

        //staff
        Role::where('name', 'staff')->first()
            ->permissions()->sync(
                Permission::whereNotIn('name', [
                    'role.manage',
                    'permission.manage',
                ])->pluck('id')
            );

        //director、member、medical_staff
        Role::whereIn('name', ['director', 'member', 'medical_staff'])->get()
            ->each(function ($role) {
                $role->permissions()->sync(
                Permission::where('name', 'like', '%.view')->pluck('id')
            );
        });
    }
}
