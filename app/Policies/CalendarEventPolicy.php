<?php

namespace App\Policies;

use App\Models\CalendarEvent;
use App\Models\User;

class CalendarEventPolicy
{
    public function viewAny(User $user): bool { return true; }

    public function view(User $user, CalendarEvent $event): bool
    {
        return $event->owner_id === $user->id || $user->hasAnyRole(['admin', 'manager']);
    }

    public function create(User $user): bool { return true; }

    public function update(User $user, CalendarEvent $event): bool
    {
        return $event->owner_id === $user->id || $user->hasAnyRole(['admin', 'manager']);
    }

    public function delete(User $user, CalendarEvent $event): bool
    {
        return $event->owner_id === $user->id || $user->hasRole('admin');
    }
}
