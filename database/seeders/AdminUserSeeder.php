<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('role_name', 'admin')->first();

        if (! $adminRole) {
            return;
        }

        User::firstOrCreate(
            ['email' => 'admin@babsresto.com'],
            [
                'name'     => 'Administrator',
                'password' => Hash::make('Admin@1234'),
                'role_id'  => $adminRole->id,
                'status'   => 'active',
            ]
        );

        User::firstOrCreate(
            ['email' => 'owner@babsresto.com'],
            [
                'name'     => 'Owner',
                'password' => Hash::make('Owner@1234'),
                'role_id'  => $adminRole->id,
                'status'   => 'active',
            ]
        );
    }
}
