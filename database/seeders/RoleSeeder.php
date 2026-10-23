<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['admin', 'cashier', 'kitchen_staff', 'table_server', Role::CUSTOMER] as $roleName) {
            Role::firstOrCreate(['role_name' => $roleName]);
        }
    }
}
