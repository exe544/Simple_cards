<?php

declare(strict_types=1);

namespace App\Http\Requests\Card;

use App\Models\Column;
use App\Models\Tag;
use App\Rules\UniquePropertyForEntityRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
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
        $board = Column::find($this->card->column_id)->board;

        return [
            'title' => [
                'required',
                'string',
                'max:100',
                new UniquePropertyForEntityRule($this->card->id, $board, 'cards'),
            ],
            'priority' => ['required', 'integer', 'min:1', 'max:5'],
            'description' => [
                'required',
                'string',
                'max:255',
                new UniquePropertyForEntityRule($this->card->id, $board, 'cards'),
            ],
            'due_dat' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'is_active' => ['required', 'boolean'],
            'column_id' => ['required', 'integer', Rule::exists(Column::class, 'id')],
            'tag_ids' => ['array'],
            'tag_ids.*' => ['integer', Rule::exists(Tag::class, 'id')],
        ];
    }
}
