<?php

declare(strict_types=1);

namespace App\Http\Resources\Tag;

use App\Http\Resources\Card\CardCollectionResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TagResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'color' => $this->color,
            'cards' => new CardCollectionResource($this->whenLoaded('cards')),
        ];
    }
}
