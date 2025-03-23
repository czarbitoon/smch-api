<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Report;
use App\Models\User;
use App\Models\Device;
use App\Models\Office;
use Carbon\Carbon;

class ReportSeeder extends Seeder
{
    public function run()
    {
        // Get existing users, devices, and offices
        $users = User::all();
        $devices = Device::all();
        $offices = Office::all();

        if ($users->isEmpty() || $devices->isEmpty() || $offices->isEmpty()) {
            throw new \Exception('Please ensure Users, Devices, and Offices are seeded first.');
        }

        // Get staff and admin users for resolved_by field
        $staffUsers = $users->filter(function ($user) {
            return $user->type >= 1; // Staff and admin users
        });

        $statuses = ['pending', 'in_progress', 'resolved', 'closed'];
        $priorities = ['Low', 'Medium', 'High', 'Critical'];

        // Create 50 sample reports
        for ($i = 0; $i < 50; $i++) {
            $device = $devices->random();
            $user = $users->random();
            $status = $statuses[array_rand($statuses)];
            $createdAt = Carbon::now()->subDays(rand(1, 30));

            $reportData = [
                'title' => 'Report #' . ($i + 1) . ': ' . fake()->sentence(4),
                'description' => fake()->paragraph(3),
                'device_id' => $device->id,
                'user_id' => $user->id,
                'office_id' => $device->office_id,
                'status' => $status,
                'priority' => $priorities[array_rand($priorities)],
                'created_at' => $createdAt,
                'updated_at' => $createdAt
            ];

            // Add resolution details for resolved reports
            if (in_array($status, ['resolved', 'closed'])) {
                $resolvedBy = $staffUsers->random();
                $resolvedAt = Carbon::parse($createdAt)->addHours(rand(1, 72));

                $reportData = array_merge($reportData, [
                    'resolution_notes' => fake()->paragraph(),
                    'resolved_by' => $resolvedBy->id,
                    'resolved_at' => $resolvedAt,
                    'updated_at' => $resolvedAt
                ]);
            }

            Report::create($reportData);
        }
    }
}