<?php

// Database/Seeders/DeviceTypesTableSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DeviceTypesTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('device_types')->insert([
            ['name' => 'Laptops', 'device_category_id' => 1],
            ['name' => 'Desktops', 'device_category_id' => 1],
            ['name' => 'Tablets', 'device_category_id' => 1],
            ['name' => 'Projectors', 'device_category_id' => 2],
            ['name' => 'Speakers', 'device_category_id' => 2],
            ['name' => 'Microphones', 'device_category_id' => 2],
            ['name' => 'Routers', 'device_category_id' => 3],
            ['name' => 'Switches', 'device_category_id' => 3],
            ['name' => 'Access Points', 'device_category_id' => 3],
            ['name' => 'Printers', 'device_category_id' => 4],
            ['name' => 'Scanners', 'device_category_id' => 4],
            ['name' => 'Copiers', 'device_category_id' => 4],
        ]);
    }
}