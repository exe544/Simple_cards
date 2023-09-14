<?php

declare(strict_types=1);

namespace App\Http\Requests\Column;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class ColumnShowRequest extends FormRequest
{
    public function authorize(): bool
    {
        if ($this->user()->cannot('show', $this->column)) {
            abort(Response::HTTP_FORBIDDEN, "This column available only for board's members or creator");
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
