<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model


{
    public static $basicAppTags = [
        ['title' => 'Backend', 'color' => 'black',],
        ['title' => 'Frontend', 'color' => 'blue',],
        ['title' => 'QA', 'color' => 'orange',],
        ['title' => 'ASAP', 'color' => 'red',],
        ['title' => 'For all', 'color' => 'purple',],
        ['title' => 'Updated', 'color' => 'pink',],
        ['title' => 'New feature', 'color' => 'golden',],
        ['title' => 'Bug', 'color' => 'light red',],
    ];
    protected $fillable = [
        'title',
        'color',
    ];
    use HasFactory;

    public function cards(): BelongsToMany
    {
        return $this->BelongsToMany(Card::class, 'cards_tags', 'tag_id', 'card_id');
    }

    public static function unusedTagsQuery(): Builder
    {
        $basicTagTitle = [];
        foreach (self::$basicAppTags as $value) {
            $basicTagTitle[] = $value['title'];
        }

        $unusedTags = Tag::doesntHave('cards')->whereNotIn('title', $basicTagTitle);

        return $unusedTags;
    }
}
