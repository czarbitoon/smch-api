<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        // Use updateOrInsert to prevent duplicate entry errors
        $users = [
            [
                'email' => 'superadmin@example.com',
                'data' => [
                    'name' => 'Super Admin',
                    'email' => 'superadmin@example.com',
                    'password' => Hash::make('password'),
                    'user_role' => 'superadmin',
                    'office_id' => null,
                ]
            ],
            [
                'email' => 'admin@example.com',
                'data' => [
                    'name' => 'Admin',
                    'email' => 'admin@example.com',
                    'password' => Hash::make('password'),
                    'user_role' => 'admin',
                    'office_id' => null,
                ]
            ],
            [
                'email' => 'staff@example.com',
                'data' => [
                    'name' => 'Staff',
                    'email' => 'staff@example.com',
                    'password' => Hash::make('password'),
                    'user_role' => 'staff',
                    'office_id' => 2, // Computer Lab 1
                ]
            ],
            [
                'email' => 'user@example.com',
                'data' => [
                    'name' => 'User',
                    'email' => 'user@example.com',
                    'password' => Hash::make('password'),
                    'user_role' => 'user',
                    'office_id' => 1, // Main Office
                ]
            ],
        ];

        foreach ($users as $user) {
            DB::table('users')->updateOrInsert(
                ['email' => $user['email']],
                $user['data']
            );
        }
    }
}
