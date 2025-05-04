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
                'device_type_id' => 1, // Laptops
                'manufacturers' => ['Dell', 'HP', 'Lenovo'],
                'count_per_office' => 2
            ],
            [
                'device_type_id' => 1, // Laptops
                'manufacturers' => ['Apple', 'Dell', 'Lenovo'],
                'count_per_office' => 1
            ],
            [
                'device_type_id' => 1, // Laptops
                'manufacturers' => ['Dell', 'HP', 'Lenovo'],
                'count_per_office' => 1
            ],

            // Desktops
            [
                'device_type_id' => 2, // Desktops
                'manufacturers' => ['Dell', 'HP'],
                'count_per_office' => 3
            ],
            [
                'device_type_id' => 2, // Desktops
                'manufacturers' => ['Dell', 'HP', 'Lenovo'],
                'count_per_office' => 5
            ],

            // Projectors
            [
                'device_type_id' => 4, // Projectors
                'manufacturers' => ['Epson', 'BenQ', 'ViewSonic'],
                'count_per_office' => 1
            ],
            [
                'device_type_id' => 4, // Projectors
                'manufacturers' => ['Epson', 'Sony'],
                'count_per_office' => 1
            ],

            // Printers
            [
                'device_type_id' => 10, // Printers
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

                    // Assign image path based on device type (1-12)
                    $typeImages = [
                        1 => 'laptop.jpg',
                        2 => 'desktop.jpg',
                        3 => 'tablet.jpg',
                        4 => 'projector.jpg',
                        5 => 'speaker.jpg',
                        6 => 'microphone.jpg',
                        7 => 'router.jpg',
                        8 => 'switch.jpg',
                        9 => 'access point.jpg',
                        10 => 'printer.png',
                        11 => 'scanner.jpg',
                        12 => 'copier.png',
                    ];
                    $deviceTypeId = $config['device_type_id'];
                    $image = $deviceTypeId && isset($typeImages[$deviceTypeId]) ? $typeImages[$deviceTypeId] : 'default_device.jpg';
                    Device::create([
                        'name' => $manufacturer . ' Device',
                        'description' => $faker->sentence(),
                        'device_type_id' => $config['device_type_id'],
                        'office_id' => $officeId,
                        'serial_number' => $faker->unique()->regexify('[A-Z0-9]{10}'),
                        'model_number' => $faker->regexify('[A-Z]{2}[0-9]{4}'),
                        'manufacturer' => $manufacturer,
                        'status' => $status,
                        'image' => $image,
                        'device_category_id' => self::resolveDeviceCategoryId($config['device_type_id'])
                    ]);
                }
            }
        }

    }

    /**
     * Resolve device_category_id from device_type_id.
     */
    private static function resolveDeviceCategoryId($deviceTypeId)
    {
        // Map device_type_id to device_category_id based on DeviceTypeSeeder
        if (in_array($deviceTypeId, [1,2,3])) return 1; // Laptops, Desktops, Tablets
        if (in_array($deviceTypeId, [4,5,6])) return 2; // Projectors, Speakers, Microphones
        if (in_array($deviceTypeId, [7,8,9])) return 3; // Routers, Switches, Access Points
        if (in_array($deviceTypeId, [10,11,12])) return 4; // Printers, Scanners, Copiers
        return 5; // Other Devices
    }
}
