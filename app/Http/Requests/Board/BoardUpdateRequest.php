<?php

declare(strict_types=1);

namespace App\Http\Requests\Board;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class BoardUpdateRequest extends FormRequest
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
            'name' => ['required', 'string', 'min:3', 'max:30'],
            'background_img' => ['image', 'mimes:jpg,jpeg,bmp,png,svg', 'max:10240'],
            'team_emails' => ['array'],
            'team_emails.*' => ['email', Rule::exists(User::class, 'email')],
        ];
    }
}
