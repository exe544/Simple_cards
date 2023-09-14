<?php

declare(strict_types=1);

namespace App\Http\Requests\Tag;

use App\Models\Card;
use App\Rules\CanAttachTagToCard;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class TagRequest extends FormRequest
{

    public function authorize(): bool
    {
        $user = $this->user();
        if (($user->boards()->where('user_id', $user->id)->exists()) || $user->createdBoards()->where(
                'creator_id',
                $user->id
            )->exists()) {
            return true;
        }
        abort(Response::HTTP_FORBIDDEN, 'Denied. You are not a member or creator of a board');
    }


    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'unique:tags,title', 'min:2', 'max:30'],
            'color' => ['required', 'string', 'min:3', 'max:20'],
            'card_ids' => ['required', 'array', new CanAttachTagToCard($this->user())],
            'card_ids.*' => ['integer', Rule::exists(Card::class, 'id'),],
        ];
    }
}
