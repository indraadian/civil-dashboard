<?php

namespace App\Actions\Import;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ProcessUserRowAction
{
    public function execute(array $row): void
    {
        $name     = trim($row['nama_lengkap'] ?? $row['name'] ?? '');
        $email    = trim($row['email'] ?? '');
        $role     = strtolower(trim($row['role'] ?? 'user'));
        $password = trim($row['password'] ?? '12345678');

        if (empty($name) || empty($email)) {
            return;
        }

        if (!in_array($role, ['admin', 'rw', 'rt', 'user'])) {
            $role = 'user';
        }

        User::updateOrCreate(
            ['email' => $email],
            [
                'name'     => $name,
                'role'     => $role,
                'password' => Hash::make($password),
            ]
        );
    }
}
