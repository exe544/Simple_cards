<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Board extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',

    ];

    public function cards(): HasManyThrough
    {
        return $this->through('columns')->has('cards');
    }

    public function columns(): HasMany
    {
        return $this->hasMany(Column::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id', 'id');
    }

    public function users(): BelongsToMany
    {
        return $this->BelongsToMany(User::class, 'users_boards', 'board_id', 'user_id');
    }

}
