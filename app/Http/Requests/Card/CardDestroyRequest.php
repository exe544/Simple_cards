<?php

declare(strict_types=1);

namespace App\Http\Requests\Card;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class CardDestroyRequest extends FormRequest
{

    public function authorize(): bool
    {
        if ($this->user()->cannot('destroy', $this->card)) {
            abort(Response::HTTP_FORBIDDEN, 'You are not card creator or creator of the board in question');
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
