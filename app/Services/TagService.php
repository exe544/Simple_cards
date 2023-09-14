<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Collection;

class TagService
{
    public function storeTag(array $validated): Tag
    {
        $tag = new Tag();
        $tag->fill($validated);
        $tag->save();

        if (array_key_exists('card_ids', $validated)) {
            $tag->cards()->attach($validated['card_ids']);
        }
        return $tag;
    }

    public function indexTag(array $validated): Collection
    {
        $tagQuery = Tag::query()->with('cards');

        if (array_key_exists('title', $validated)) {
            $tagQuery = $tagQuery->where('title', 'like', '%' . $validated['title'] . '%');
        }
        $response = $tagQuery->get();
        return $response;
    }
}
