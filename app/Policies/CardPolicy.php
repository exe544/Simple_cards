<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Card;
use App\Models\User;

class CardPolicy
{
    public function updateOrShow(User $user, Card $card): bool
    {
        return $card->canUserUpdateShowCard($user);
    }

    public function destroy(User $user, Card $card): bool
    {
        return $card->canUserDestroyCard($user);
    }
}
