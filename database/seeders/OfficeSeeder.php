<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Office;

class OfficeSeeder extends Seeder
{
    public function run()
    {
        $offices = [
            'Main Office',
            'Computer Lab 1',
            'Computer Lab 2',
            'Library',
            'Administration Office',
            'Teachers Room',
            'Science Lab',
            'Art Room',
            'Music Room',
            'Sports Office'
        ];

        foreach ($offices as $office) {
            // Use firstOrCreate to prevent duplicates
            Office::firstOrCreate(['name' => $office]);
        }
    }
}
