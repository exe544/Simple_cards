<?php

declare(strict_types=1);

namespace App\Http\Requests\Card;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class CardShowRequest extends FormRequest
{
    public function authorize(): bool
    {
        if ($this->user()->cannot('updateOrShow', $this->card)) {
            abort(Response::HTTP_FORBIDDEN, 'You are not creator or a member of the board in question');
        }
        return true;
    }


    public function rules(): array
    {
        return [
            //
        ];
    }
}
