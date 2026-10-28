<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\OrderStatus;

class OrderStatusSeeder extends Seeder
{
    public function run(): void
    {
        // name => [color, sort order]; firstOrCreate so running the seeder again never adds duplicates
        $statuses = [
            'Pending'   => ['#F59E0B', 1],
            'Preparing' => ['#3B82F6', 2],
            'Ready'     => ['#8B5CF6', 3],
            'Served'    => ['#2563EB', 4],
            'Packaged'  => ['#F59E0B', 5],
            'Completed' => ['#16A34A', 6],
            'Cancelled' => ['#DC2626', 7],
        ];

        foreach ($statuses as $status => [$color, $sort]) {
            OrderStatus::firstOrCreate(['status_name' => $status], ['color' => $color, 'sort_order' => $sort]);
        }
    }
}
