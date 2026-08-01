<?php

namespace App\Policies;

use App\Models\Candidate;
use App\Models\User;

class CandidatePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('candidate.view');
    }

    public function view(User $user, Candidate $candidate): bool
    {
        return $user->can('candidate.view');
    }

    public function create(User $user): bool
    {
        return $user->can('candidate.create');
    }

    public function update(User $user, Candidate $candidate): bool
    {
        return $user->can('candidate.update');
    }

    public function delete(User $user, Candidate $candidate): bool
    {
        return $user->can('candidate.delete');
    }
}
