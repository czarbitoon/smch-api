<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            OfficeSeeder::class,
            UsersTableSeeder::class,
            DeviceCategorySeeder::class,
            DeviceTypeSeeder::class,
            DeviceSeeder::class,
            ReportSeeder::class,
        ]);
    }
}
