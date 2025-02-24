<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DeviceSubcategory;

class DeviceSubcategorySeeder extends Seeder
{
    public function run()
    {
        $subcategories = [
            // Computing Devices - Laptops (Type ID: 1)
            ['name' => 'Student Laptops', 'device_type_id' => 1],
            ['name' => 'Teacher Laptops', 'device_type_id' => 1],
            ['name' => 'Administrative Laptops', 'device_type_id' => 1],
            
            // Computing Devices - Desktops (Type ID: 2)
            ['name' => 'Library Computers', 'device_type_id' => 2],
            ['name' => 'Lab Computers', 'device_type_id' => 2],
            ['name' => 'Admin Computers', 'device_type_id' => 2],
            
            // Audio-Visual - Projectors (Type ID: 4)
            ['name' => 'Classroom Projectors', 'device_type_id' => 4],
            ['name' => 'Auditorium Projectors', 'device_type_id' => 4],
            ['name' => 'Portable Projectors', 'device_type_id' => 4],
            
            // Printing Devices - Printers (Type ID: 10)
            ['name' => 'Network Printers', 'device_type_id' => 10],
            ['name' => 'Department Printers', 'device_type_id' => 10],
            
            // Audio-Visual - Other
            ['name' => 'Smart Boards', 'device_type_id' => 5],
            ['name' => 'Document Cameras', 'device_type_id' => 6]
        ];

        foreach ($subcategories as $subcategory) {
            DeviceSubcategory::create([
                'name' => $subcategory['name'],
                'device_type_id' => $subcategory['device_type_id'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
