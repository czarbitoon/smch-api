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

        $deviceConfigs = [
            // Laptops
            [
                'subcategory_id' => 1, // Student Laptops
                'manufacturers' => ['Dell', 'HP', 'Lenovo'],
                'count_per_office' => 2
            ],
            [
                'subcategory_id' => 2, // Teacher Laptops
                'manufacturers' => ['Apple', 'Dell', 'Lenovo'],
                'count_per_office' => 1
            ],
            [
                'subcategory_id' => 3, // Administrative Laptops
                'manufacturers' => ['Dell', 'HP', 'Lenovo'],
                'count_per_office' => 1
            ],
            
            // Desktops
            [
                'subcategory_id' => 4, // Library Computers
                'manufacturers' => ['Dell', 'HP'],
                'count_per_office' => 3
            ],
            [
                'subcategory_id' => 5, // Lab Computers
                'manufacturers' => ['Dell', 'HP', 'Lenovo'],
                'count_per_office' => 5
            ],
            
            // Projectors
            [
                'subcategory_id' => 7, // Classroom Projectors
                'manufacturers' => ['Epson', 'BenQ', 'ViewSonic'],
                'count_per_office' => 1
            ],
            [
                'subcategory_id' => 8, // Auditorium Projectors
                'manufacturers' => ['Epson', 'Sony'],
                'count_per_office' => 1
            ],
            
            // Printers
            [
                'subcategory_id' => 10, // Network Printers
                'manufacturers' => ['HP', 'Canon', 'Epson'],
                'count_per_office' => 1
            ]
        ];

        $statuses = ['active', 'inactive', 'maintenance'];
        $officeIds = range(1, 10); // Based on the offices in OfficeSeeder

        foreach ($deviceConfigs as $config) {
            foreach ($officeIds as $officeId) {
                for ($i = 0; $i < $config['count_per_office']; $i++) {
                    $manufacturer = $faker->randomElement($config['manufacturers']);
                    $status = $faker->randomElement($statuses);
                    
                    Device::create([
                        'name' => $manufacturer . ' Device',
                        'description' => $faker->sentence(),
                        'device_subcategory_id' => $config['subcategory_id'],
                        'office_id' => $officeId,
                        'serial_number' => $faker->unique()->regexify('[A-Z0-9]{10}'),
                        'model_number' => $faker->regexify('[A-Z]{2}[0-9]{4}'),
                        'manufacturer' => $manufacturer,
                        'status' => $status
                    ]);
                }
            }
        }

    }
}
