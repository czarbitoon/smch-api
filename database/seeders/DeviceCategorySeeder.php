<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DeviceCategory;

class DeviceCategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            ['name' => 'Computing Devices'],
            ['name' => 'Audio-Visual Devices'],
            ['name' => 'Networking Devices'],
            ['name' => 'Printing Devices'],
            ['name' => 'Other Devices']
        ];

        foreach ($categories as $category) {
            DeviceCategory::create([
                'name' => $category['name'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}