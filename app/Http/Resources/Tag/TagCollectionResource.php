<?php

declare(strict_types=1);

namespace App\Http\Resources\Tag;

use Illuminate\Http\Resources\Json\ResourceCollection;

class TagCollectionResource extends ResourceCollection
{
    public $collects = TagResource::class;

}
