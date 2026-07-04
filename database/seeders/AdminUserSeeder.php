<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Staff;
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

        $admin = User::firstOrCreate(
            ['email' => 'admin@babsresto.com'],
            [
                'username' => 'admin',
                'password' => Hash::make('Admin@1234'),
                'role_id'  => $adminRole->id,
                'status'   => 'active',
            ]
        );

        Staff::firstOrCreate(
            ['user_id' => $admin->id],
            [
                'first_name' => 'Administrator',
                'last_name'  => '',
                'email'      => $admin->email,
                'contact_no' => '',
            ]
        );

        $owner = User::firstOrCreate(
            ['email' => 'owner@babsresto.com'],
            [
                'username' => 'owner',
                'password' => Hash::make('Owner@1234'),
                'role_id'  => $adminRole->id,
                'status'   => 'active',
            ]
        );

        Staff::firstOrCreate(
            ['user_id' => $owner->id],
            [
                'first_name' => 'Owner',
                'last_name'  => '',
                'email'      => $owner->email,
                'contact_no' => '',
            ]
        );
    }
}
