<?php

namespace Database\Seeders;

use App\Models\AddOn;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AddonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $addons = [
            [
                'add_on_name' => 'Addon 1',
                'add_on_details' => 'Addon 1 details',
                'add_on_price' => 1000,
            ],
            [
                'add_on_name' => 'Addon 2',
                'add_on_details' => 'Addon 2 details',
                'add_on_price'=> 500,
            ],
            [
                'add_on_name' => 'Addon 3',
                'add_on_details' => 'Addon 3 details',
                'add_on_price'=> 800,
            ]
        ];

        foreach ($addons as $addon) {
            AddOn::create($addon);
        }
    }
}
