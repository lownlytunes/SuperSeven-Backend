<?php

namespace Database\Seeders;

use App\Models\Package;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $packages = [
            [
                'package_name' => 'Package 1',
                'package_details' => 'Package 1 details',
                'package_price' => 1000
            ],
            [
                'package_name' => 'Package 2',
                'package_details' => 'Package 2 details',
                'package_price' => 2000
            ],
            [
                'package_name' => 'Package 3',
                'package_details' => 'Package 3 details',
                'package_price' => 3000
            ]
        ];

        foreach ($packages as $package) {
            Package::create($package);
        }
    }
}
