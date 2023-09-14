<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Board;
use App\Models\Column;

class ColumnService
{
    public function storeColumn(array $validatedData): Column
    {
        $column = new Column();
        $column->fill($validatedData);
        $column->board()->associate(Board::find($validatedData['board_id']));

        $column->save();

        return $column;
    }

    public function updateColumn(array $validatedData, Column $column): Column
    {
        if (array_key_exists('name', $validatedData)) {
            $column->name = $validatedData['name'];
        }
        if (array_key_exists('place', $validatedData)) {
            $column->place = $validatedData['place'];
        }
        $column->save();
        return $column;
    }
}
