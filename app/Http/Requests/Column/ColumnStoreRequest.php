<?php

declare(strict_types=1);

namespace App\Http\Requests\Column;

use App\Models\Board;
use App\Rules\UniquePropertyForEntityRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ColumnStoreRequest extends FormRequest
{

    public function authorize(): bool
    {
        //Column in board 'n' can be created only by 'n' board's creator
        $user = $this->user();
        return $user->createdBoards()->where('id', $this->board_id)->exists();
    }


    public function rules(): array
    {
        $board = Board::find($this->board_id);

        return [
            'name' => [
                'required',
                'string',
                'min:3',
                'max:30',
                new UniquePropertyForEntityRule(null, $board, 'columns')
            ],
            'place' => [
                'required',
                'integer',
                'min:1',
                'max:10',
                new UniquePropertyForEntityRule(null, $board, 'columns'),
            ],
            'board_id' => ['required', 'integer', Rule::exists(Board::class, 'id'),],
        ];
    }
}
