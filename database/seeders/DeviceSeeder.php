<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Device;
use Faker\Factory as Faker;

class DeviceSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        $deviceTypes = [
            'Laptop', 'Desktop', 'Tablet', 'Projector',
            'Printer', 'Smart Board', 'Document Camera',
            'Chromebook', 'Server', 'Network Switch'
        ];

        $manufacturers = [
            'Dell', 'HP', 'Lenovo', 'Apple', 'Microsoft',
            'Samsung', 'Acer', 'Asus', 'Epson', 'Promethean'
        ];

        $statuses = ['active', 'inactive', 'maintenance', 'retired'];

        for ($i = 0; $i < 100; $i++) {
            Device::create([
                'name' => $faker->word . ' ' . $faker->randomElement(['Device', 'Equipment', 'System']),
                'description' => $faker->sentence,
                'device_subcategory_id' => $faker->numberBetween(1, 10),
                'office_id' => $faker->numberBetween(1, 10),
                'serial_number' => 'SN-' . $faker->unique()->bothify('########'),
                'model_number' => 'MOD-' . $faker->bothify('#######'),
                'manufacturer' => $faker->randomElement($manufacturers),
                'status' => $faker->randomElement($statuses),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
