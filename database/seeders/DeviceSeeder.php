<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Device;

class DeviceSeeder extends Seeder
{
    public function run()
    {
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
                    $manufacturer = $config['manufacturers'][$i % count($config['manufacturers'])];
                    $status = $statuses[$i % count($statuses)];

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
                    $serialNumber = 'SN' . $officeId . $config['device_type_id'] . $i;
                    $modelNumber = 'MD' . $config['device_type_id'] . $i;

                    // Use firstOrCreate to prevent duplicates based on serial number
                    Device::firstOrCreate(
                        ['serial_number' => $serialNumber],
                        [
                            'name' => $manufacturer . ' Device',
                            'description' => 'Sample device for ' . $manufacturer,
                            'device_type_id' => $config['device_type_id'],
                            'office_id' => $officeId,
                            'model_number' => $modelNumber,
                            'manufacturer' => $manufacturer,
                            'status' => $status,
                            'image' => $image,
                            'device_category_id' => self::resolveDeviceCategoryId($config['device_type_id'])
                        ]
                    );
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
