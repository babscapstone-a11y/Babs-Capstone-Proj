<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DiscountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $discounts = [
    'None',
    '10% Off',
    '20% Off',
    'Buy One Get One Free',
    'Seasonal Discount'
];
    }
}
