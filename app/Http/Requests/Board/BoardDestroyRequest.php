<?php

declare(strict_types=1);

namespace App\Http\Requests\Board;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class BoardDestroyRequest extends FormRequest
{
    public function authorize(): bool
    {
        if ($this->user()->cannot('updateOrDestroy', $this->board)) {
            abort(Response::HTTP_FORBIDDEN, 'Denied. You are not a creator of the board');
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
