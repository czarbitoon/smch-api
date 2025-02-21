<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DeviceSubcategory;

class DeviceSubcategorySeeder extends Seeder
{
    public function run()
    {
        $subcategories = [
            'Student Laptops',
            'Teacher Laptops',
            'Classroom Projectors',
            'Library Computers',
            'Lab Computers',
            'Admin Computers',
            'Printers',
            'Smart Boards',
            'Document Cameras',
            'Servers'
        ];

        foreach ($subcategories as $subcategory) {
            DeviceSubcategory::create([
                'name' => $subcategory,
                'device_type_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
