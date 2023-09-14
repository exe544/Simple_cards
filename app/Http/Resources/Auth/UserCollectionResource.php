<?php

namespace App\Http\Resources\Auth;

use Illuminate\Http\Resources\Json\JsonResource;

class UserCollectionResource extends JsonResource
{
    public $users = UserResource::class;

}
