<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Card\CardDestroyRequest;
use App\Http\Requests\Card\CardShowRequest;
use App\Http\Requests\Card\IndexRequest;
use App\Http\Requests\Card\StoreRequest;
use App\Http\Requests\Card\UpdateRequest;
use App\Http\Resources\Card\CardCollectionResource;
use App\Http\Resources\Card\CardResource;
use App\Models\Card;
use App\Services\CardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class CardController extends Controller
{
    private CardService $cardService;

    public function __construct(CardService $cardService)
    {
        $this->cardService = $cardService;
    }

    public function store(StoreRequest $request): CardResource
    {
        $validated = $request->validated();
        $card = $this->cardService->storeCard(
            $validated,
            Auth::user()
        );

        $card->load('tags');
        return new CardResource($card);
    }

    public function show(CardShowRequest $cardShowRequest, Card $card): CardResource
    {
        $card->load(['column', 'tags', 'user']);

        return new CardResource($card);
    }

    public function update(UpdateRequest $request, Card $card): CardResource
    {
        $validated = $request->validated();

        $card = $this->cardService->updateCard($card, Auth::id(), $validated);
        $card->load('tags');

        return new CardResource($card);
    }

    public function destroy(CardDestroyRequest $cardDestroyRequest, Card $card): JsonResponse
    {
        $card->destroy($card->id);
        return new JsonResponse(null, 204);
    }


    public function index(IndexRequest $request): CardCollectionResource
    {
        $validatedData = $request->validated();

        $cards = $this->cardService->indexCard($validatedData);

        return new CardCollectionResource($cards);
    }
}
