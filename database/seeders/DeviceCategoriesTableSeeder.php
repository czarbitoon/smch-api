<?php

// Database/Seeders/DeviceCategoriesTableSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DeviceCategoriesTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('device_categories')->insert([
            ['name' => 'Computing Devices'],
            ['name' => 'Audio-Visual Devices'],
            ['name' => 'Networking Devices'],
            ['name' => 'Printing Devices'],
            ['name' => 'Other Devices'],
        ]);
    }
}