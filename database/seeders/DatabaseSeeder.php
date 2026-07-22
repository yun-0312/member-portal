<?php

namespace Database\Seeders;

use App\Models\Schedule;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
            RolePermissionSeeder::class,

            MedicalInstitutionSeeder::class,
            UserSeeder::class,
            UserInstitutionAssignSeeder::class,

            GroupCategorySeeder::class,
            GroupSeeder::class,

            ContentCategorySeeder::class,
            ContentSubcategorySeeder::class,
            ContentSeeder::class,

            WorkshopSeeder::class,

            NoticeCategorySeeder::class,
            NoticeSeeder::class,

            RoomSeeder::class,
            ScheduleCategorySeeder::class,
            ScheduleSeeder::class,
            RecurrenceSeeder::class,
            OccurrenceSeeder::class,

            FaqCategorySeeder::class,
            FaqSeeder::class,

            VideoSeeder::class,
        ]);
    }
}
