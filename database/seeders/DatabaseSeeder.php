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
            DeviceSubcategorySeeder::class,
            DeviceSeeder::class,
        ]);
    }
}
