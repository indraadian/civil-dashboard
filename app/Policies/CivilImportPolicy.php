<?php

namespace App\Policies;

use App\Models\CivilImport;
use App\Models\User;

class CivilImportPolicy
{
    /**
     * Hanya admin yang boleh membuat/memulai import.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * User hanya boleh melihat import miliknya sendiri (atau admin bisa lihat semua).
     */
    public function view(User $user, CivilImport $import): bool
    {
        return $user->isAdmin() || $import->created_by === $user->id;
    }
}
