<?php

declare(strict_types=1);

namespace App\Http\Requests\Board;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BoardStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:30'],
            'background_img' => ['image', 'mimes:jpg,jpeg,bmp,png,svg', 'max:10240'],
            'team_emails' => ['array'],
            'team_emails.*' => ['email', Rule::exists(User::class, 'email')],
        ];
    }
}
