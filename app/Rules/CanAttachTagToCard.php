<?php

namespace App\Rules;

use App\Models\Card;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CanAttachTagToCard implements ValidationRule
{
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        //create array of card's ids where user is member or creator of the board
        $cardsUserHasAccess = Card::getBuilderForUser($this->user)->pluck('id')->toArray();
        $result = array_diff($value, $cardsUserHasAccess);

        if (!empty($result)) {
            $fail (
                'Denied. You do not have rights to attach tag on the mentioned card. You are not a member of the board, where card exist.'
            );
        }
    }
}
