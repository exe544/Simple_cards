<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Column;
use App\Models\User;

class ColumnPolicy
{

    public function show(User $user, Column $column): bool
    {
        $board = $column->board;
        return $user->id === $board->creator->id || $board->users()->where('user_id', $user->id)->exists();
    }

    public function updateOrDestroy(User $user, Column $column): bool
    {
        return $user->id === $column->board->creator_id;
    }

}
