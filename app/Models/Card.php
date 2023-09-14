<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Card extends Model
{
    protected $fillable = [
        'title',
        'description',
        'due_dat',
        'priority',
        'is_active'
    ];

    use HasFactory;

    public function column(): BelongsTo
    {
        return $this->belongsTo(Column::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->BelongsToMany(Tag::class, 'cards_tags', 'card_id', 'tag_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function board()
    {
        return $this->column()->firstOrFail()->board();
    }

    public function canUserUpdateShowCard(User $user): bool
    {
        $userId = $user->id;
        $isUserBelongsToBoard = $this->board()
            ->where('creator_id', $userId)
            ->orWhereHas('users', function (Builder $query) use ($userId) {
                $query->where('user_id', $userId);
            })->exists();
        return $isUserBelongsToBoard;
    }

    public function canUserDestroyCard(User $user): bool
    {
        $userId = $user->id;
        return $userId === $this->user->id || $userId === $this->board->creator_id;
    }

    public static function getBuilderForUser(Authenticatable $user): Builder
    {
        $whereUserIsCreator = $user->createdBoards()->pluck('id');
        $whereUserIsMember = $user->boards()->pluck('board_id');
        return self::whereHas(
            'column',
            function (Builder $query) use ($whereUserIsCreator, $whereUserIsMember) {
                $query->whereIn('board_id', $whereUserIsCreator)->orWhereIn('board_id', $whereUserIsMember);
            }
        );
    }
}
