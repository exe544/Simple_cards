<?php

declare(strict_types=1);

namespace App\Http\Resources\Column;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ColumnCollectionResource extends ResourceCollection
{
    public $collects = ColumnResource::class;
}
