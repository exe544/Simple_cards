<?php

declare(strict_types=1);

namespace App\Http\Resources\Card;


use Illuminate\Http\Resources\Json\ResourceCollection;

class CardCollectionResource extends ResourceCollection
{
    public $collects = CardResource::class;
}
