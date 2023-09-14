<?php

declare(strict_types=1);

namespace App\Http\Requests\Board;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class BoardShowRequest extends FormRequest
{

    public function authorize(): bool
    {
        if ($this->user()->cannot('show', $this->board)) {
            abort(Response::HTTP_FORBIDDEN, "This board available only for board's members or creator");
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
