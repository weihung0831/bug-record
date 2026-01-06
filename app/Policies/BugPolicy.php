<?php

namespace App\Policies;

use App\Models\Bug;
use App\Models\User;

class BugPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Bug $bug): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Bug $bug): bool
    {
        return $user->id === $bug->reporter_id
            || $user->id === $bug->assignee_id;
    }

    public function delete(User $user, Bug $bug): bool
    {
        return $user->id === $bug->reporter_id;
    }
}
