<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Board;
use App\Models\User;

class BoardPolicy
{
    public function show(User $user, Board $board): bool
    {
        return $user->id === $board->creator_id || $board->users()->where('user_id', $user->id)->exists();
    }

    public function updateOrDestroy(User $user, Board $board): bool
    {
        return $user->id === $board->creator_id;
    }
}
