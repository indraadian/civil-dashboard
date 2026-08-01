<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@indev.com'],
            [
                'name' => 'Super Administrator',
                'role' => 'super_admin',
                'password' => Hash::make('123Admin'),
            ]
        );
        $superAdmin->assignRole('Super Admin');

        $admin = User::firstOrCreate(
            ['email' => 'admin@indev.com'],
            [
                'name' => 'Administrator',
                'role' => 'admin',
                'password' => Hash::make('123Admin'),
            ]
        );
        $admin->assignRole('Admin');

        $user = User::firstOrCreate(
            ['email' => 'user@indev.com'],
            [
                'name' => 'User Staff',
                'role' => 'user',
                'password' => Hash::make('123Password'),
            ]
        );
        $user->assignRole('User');
    }
}