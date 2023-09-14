<?php

declare(strict_types=1);

namespace App\Http\Resources\Column;

use App\Http\Resources\Board\BoardResource;
use App\Http\Resources\Card\CardCollectionResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ColumnResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'place' => $this->place,
            'board' => new BoardResource($this->whenLoaded('board')),
            'cards' => new CardCollectionResource($this->whenLoaded('cards')),
        ];
    }
}
