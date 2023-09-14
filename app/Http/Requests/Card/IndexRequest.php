<?php

declare(strict_types=1);

namespace App\Http\Requests\Card;

use Illuminate\Foundation\Http\FormRequest;

class IndexRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'is_active' => ['boolean'],
        ];
    }
}
