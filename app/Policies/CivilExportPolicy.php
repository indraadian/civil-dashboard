<?php

namespace App\Policies;

use App\Models\CivilExport;
use App\Models\User;

class CivilExportPolicy
{
    /**
     * Hanya admin yang boleh memulai export.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * User hanya boleh melihat export miliknya sendiri (atau admin bisa lihat semua).
     */
    public function view(User $user, CivilExport $export): bool
    {
        return $user->isAdmin() || $export->created_by === $user->id;
    }

    /**
     * User hanya bisa download jika export miliknya dan belum kadaluarsa.
     */
    public function download(User $user, CivilExport $export): bool
    {
        return $this->view($user, $export) && $export->isDownloadable();
    }
}
