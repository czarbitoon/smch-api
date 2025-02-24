<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DeviceType;

class DeviceTypeSeeder extends Seeder
{
    public function run()
    {
        $types = [
            // Computing Devices (Category ID: 1)
            ['name' => 'Laptops', 'device_category_id' => 1],
            ['name' => 'Desktops', 'device_category_id' => 1],
            ['name' => 'Tablets', 'device_category_id' => 1],
            
            // Audio-Visual Devices (Category ID: 2)
            ['name' => 'Projectors', 'device_category_id' => 2],
            ['name' => 'Speakers', 'device_category_id' => 2],
            ['name' => 'Microphones', 'device_category_id' => 2],
            
            // Networking Devices (Category ID: 3)
            ['name' => 'Routers', 'device_category_id' => 3],
            ['name' => 'Switches', 'device_category_id' => 3],
            ['name' => 'Access Points', 'device_category_id' => 3],
            
            // Printing Devices (Category ID: 4)
            ['name' => 'Printers', 'device_category_id' => 4],
            ['name' => 'Scanners', 'device_category_id' => 4],
            ['name' => 'Copiers', 'device_category_id' => 4]
        ];

        foreach ($types as $type) {
            DeviceType::create([
                'name' => $type['name'],
                'device_category_id' => $type['device_category_id'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
