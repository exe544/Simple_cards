<?php

declare(strict_types=1);

namespace App\Http\Resources\Board;

use Illuminate\Http\Resources\Json\JsonResource;

class BoardCollectionResource extends JsonResource
{
    public $collects = BoardResource::class;

}
