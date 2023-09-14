<?php

declare(strict_types=1);

namespace App\Http\Requests\Card;

use App\Models\Column;
use App\Models\Tag;
use App\Rules\UniquePropertyForEntityRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        $userId = Auth::id();
        $board = Column::where('id', $this->column_id)->firstOrFail()->board()->firstOrFail();
        if ($userId === $board->creator_id || $board->users()->where('user_id', $userId)->exists()) {
            return true;
        }
        abort(Response::HTTP_FORBIDDEN, 'You are not creator or a member of the board in question');
    }

    public function rules(): array
    {
        $board = Column::find($this->column_id)->board;
        return [
            'title' => [
                'required',
                'string',
                'max:100',
                new UniquePropertyForEntityRule(null, $board, 'cards')
            ],
            'priority' => ['required', 'integer', 'min:1', 'max:5'],
            'description' => [
                'required',
                'string',
                'max:255',
                new UniquePropertyForEntityRule(null, $board, 'cards'),
            ],
            'due_dat' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'is_active' => ['required', 'boolean'],
            'column_id' => ['required', 'integer', Rule::exists(Column::class, 'id')],
            'tag_ids' => ['array'],
            'tag_ids.*' => ['integer', Rule::exists(Tag::class, 'id')],
        ];
    }
}
