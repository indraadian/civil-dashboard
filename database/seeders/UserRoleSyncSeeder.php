<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserRoleSyncSeeder extends Seeder
{
    /**
     * Sync existing user table records with Spatie roles.
     */
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            if ($user->role === 'super_admin') {
                $user->assignRole('Super Admin');
            } elseif ($user->role === 'admin') {
                $user->assignRole('Admin');
            } else {
                $user->assignRole('User');
            }
        }
    }
}
