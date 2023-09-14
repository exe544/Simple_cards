<?php

declare(strict_types=1);

namespace App\Http\Resources\Board;

use App\Http\Resources\Auth\UserCollectionResource;
use App\Http\Resources\Auth\UserResource;
use App\Http\Resources\Card\CardCollectionResource;
use App\Http\Resources\Column\ColumnCollectionResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;


class BoardResource extends JsonResource
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
            'background_img' => $this->when(
                $this->background_img_path !== null,
                asset(Storage::url($this->background_img_path))
            ),
            'columns' => new ColumnCollectionResource($this->whenLoaded('columns')),
            'cards' => new CardCollectionResource($this->whenLoaded('cards')),
            'creator_id' => $this->creator_id,
            'creator' => new UserResource($this->whenLoaded('creator')),
            'team' => new UserCollectionResource($this->whenLoaded('users')),
        ];
    }
}
