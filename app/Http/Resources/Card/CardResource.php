<?php

declare(strict_types=1);

namespace App\Http\Resources\Card;

use App\Http\Resources\Auth\UserResource;
use App\Http\Resources\Column\ColumnResource;
use App\Http\Resources\Tag\TagCollectionResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CardResource extends JsonResource
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
            'title' => $this->title,
            'column_id' => $this->column_id,
            'description' => $this->description,
            'priority' => $this->priority,
            'due_dat' => $this->due_dat,
            'is_active' => $this->is_active,
            'column' => new ColumnResource($this->whenLoaded('column')),
            'tags' => new TagCollectionResource($this->whenLoaded('tags')),
            'creator_id' => $this->creator_id,
            'creator' => new UserResource($this->whenLoaded('user')),
            'created_at' => $this->created_at,
        ];
    }
}
