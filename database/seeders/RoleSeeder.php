<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['role_name' => 'admin',        'display_name' => 'Administrator'],
            ['role_name' => 'cashier',       'display_name' => 'Cashier'],
            ['role_name' => 'kitchen_staff', 'display_name' => 'Kitchen Staff'],
            ['role_name' => 'table_server',  'display_name' => 'Table Server'],
            ['role_name' => 'customer',      'display_name' => 'Customer'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(
                ['role_name' => $role['role_name']],
                ['display_name' => $role['display_name']]
            );
        }
    }
}
