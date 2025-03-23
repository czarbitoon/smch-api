<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            UsersTableSeeder::class,
            OfficeSeeder::class,
            DeviceCategorySeeder::class,
            DeviceTypeSeeder::class,
            DeviceSubcategorySeeder::class,
            DeviceSeeder::class,
            ReportSeeder::class,
        ]);
    }
}
