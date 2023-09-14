<?php

namespace App\Rules;

use App\Models\Board;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UniquePropertyForEntityRule implements ValidationRule
{
    protected $board;
    protected $recordId;
    protected $relation;

    public function __construct(mixed $recordId, Board $board, string $relation)
    {
        $this->recordId = $recordId;
        $this->board = $board;
        $this->relation = $relation;
    }

    /**
     * Run the validation rule.
     *
     * @param \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $relationship = $this->relation;
        if ($this->board->$relationship()->where($this->relation . '.id', '!=', $this->recordId)->where(
            $attribute,
            $value
        )->exists()) {
            $fail('Item with the same data already exists in board');
        }
    }
}
