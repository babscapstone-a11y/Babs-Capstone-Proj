<?php

namespace Database\Seeders;

use App\Models\ModeOfPayment;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ModeOfPaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $methods = ['Cash', 'Cashless'];

        foreach ($methods as $method) {
            ModeOfPayment::firstOrCreate(['method_name' => $method]);
        }
    }
}
