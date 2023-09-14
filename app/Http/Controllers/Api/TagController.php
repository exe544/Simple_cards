<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tag\TagIndexRequest;
use App\Http\Requests\Tag\TagRequest;
use App\Http\Resources\Tag\TagCollectionResource;
use App\Http\Resources\Tag\TagResource;
use App\Models\Tag;
use App\Services\TagService;

class TagController extends Controller
{
    private $tagService;

    public function __construct(TagService $tagService)
    {
        $this->tagService = $tagService;
    }

    public function store(TagRequest $request): TagResource
    {
        $validated = $request->validated();

        $tag = $this->tagService->storeTag($validated);

        $tag->load('cards');
        return new TagResource($tag);
    }

    public function show(Tag $tag): TagResource
    {
        $tag = $tag->load('cards');

        return new TagResource($tag);
    }

    public function index(TagIndexRequest $request)
    {
        $validated = $request->validated();

        $responce = $this->tagService->indexTag($validated);

        return new TagCollectionResource($responce);
    }
}
