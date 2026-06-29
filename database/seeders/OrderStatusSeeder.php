<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\OrderStatus;

class OrderStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            'Pending',
            'Preparing',
            'Ready',
            'Served',
            'Completed',
            'Cancelled'
        ];

        foreach ($statuses as $status) {
            OrderStatus::create([
                'status_name' => $status
            ]);
        }
    }
}
