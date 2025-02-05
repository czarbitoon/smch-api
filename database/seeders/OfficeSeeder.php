<?php
// Database/Seeders/OfficeSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OfficeSeeder extends Seeder
{
    public function run()
    {
        DB::table('offices')->insert([

            [
                'name' => 'Administrative Office',
            ],
            [
                'name' => 'Guidance Office',
            ],
            [
                'name' => 'Finance Office',
            ],
            [
                'name' => 'Admissions Office',
            ],
            [
                'name' => 'Faculty Office',
            ],
            [
                'name' => 'IT Office',
            ],
            [
                'name' => 'Library',
            ],
            [
                'name' => 'Nurse\'s Office',
            ],
        ]);
    }
}