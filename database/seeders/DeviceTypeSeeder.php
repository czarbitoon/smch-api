<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DeviceType;

class DeviceTypeSeeder extends Seeder
{
    public function run()
    {
        $types = [
            'Computers',
            'Projectors',
            'Printers',
            'Networking',
            'Audio/Visual',
            'Security',
            'Storage',
            'Peripherals',
            'Servers',
            'Other'
        ];

        foreach ($types as $type) {
            DeviceType::create([
                'name' => $type,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
