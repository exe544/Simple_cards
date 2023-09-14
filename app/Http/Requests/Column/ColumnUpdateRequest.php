<?php

declare(strict_types=1);

namespace App\Http\Requests\Column;

use App\Models\Board;
use App\Rules\UniquePropertyForEntityRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class ColumnUpdateRequest extends FormRequest
{

    public function authorize(): bool
    {
        if ($this->user()->cannot('updateOrDestroy', $this->column)) {
            abort(Response::HTTP_FORBIDDEN, 'Denied. You are not a creator of the board');
        }
        return true;
    }

    public function rules(): array
    {
        $board = Board::find($this->column->board_id);

        return [
            'name' => [
                'string',
                'min:3',
                'max:30',
                new UniquePropertyForEntityRule($this->column->id, $board, 'columns'),
            ],
            'place' => [
                'integer',
                'min:1',
                'max:10',
                new UniquePropertyForEntityRule($this->column->id, $board, 'columns'),
            ],
        ];
    }
}
