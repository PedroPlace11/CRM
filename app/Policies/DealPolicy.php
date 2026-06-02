<?php

namespace App\Policies;

use App\Models\Deal;
use App\Models\User;

class DealPolicy
{
    public function viewAny(User $user): bool { return true; }

    public function view(User $user, Deal $deal): bool
    {
        return $deal->owner_id === $user->id || $user->hasAnyRole(['admin', 'manager']);
    }

    public function create(User $user): bool { return true; }

    public function update(User $user, Deal $deal): bool
    {
        return $deal->owner_id === $user->id || $user->hasAnyRole(['admin', 'manager']);
    }

    public function delete(User $user, Deal $deal): bool
    {
        return $deal->owner_id === $user->id || $user->hasRole('admin');
    }
}
