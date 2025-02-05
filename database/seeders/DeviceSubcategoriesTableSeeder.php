<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DeviceSubcategoriesTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('device_subcategories')->insert([
            ['name' => 'Student Laptops', 'device_type_id' => 1],
            ['name' => 'Teacher Laptops', 'device_type_id' => 1],
            ['name' => 'Administrative Laptops', 'device_type_id' => 1],
            ['name' => 'Classroom Projectors', 'device_type_id' => 4],
            ['name' => 'Auditorium Projectors', 'device_type_id' => 4],
            ['name' => 'Portable Projectors', 'device_type_id' => 4],
        ]);
    }
}