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
            Office::create([
                'name' => $office,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
