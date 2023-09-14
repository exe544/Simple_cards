<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Column\ColumnDestroyRequest;
use App\Http\Requests\Column\ColumnIndexRequest;
use App\Http\Requests\Column\ColumnShowRequest;
use App\Http\Requests\Column\ColumnStoreRequest;
use App\Http\Requests\Column\ColumnUpdateRequest;
use App\Http\Resources\Column\ColumnCollectionResource;
use App\Http\Resources\Column\ColumnResource;
use App\Models\Column;
use App\Services\ColumnService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;


class ColumnController extends Controller
{
    private $columnService;

    public function __construct(ColumnService $columnService)
    {
        $this->columnService = $columnService;
    }

    public function store(ColumnStoreRequest $request): ColumnResource
    {
        $validatedData = $request->validated();

        $column = $this->columnService->storeColumn($validatedData);

        return new ColumnResource($column);
    }

    public function show(ColumnShowRequest $columnShowRequest, Column $column): ColumnResource
    {
        $column->load(['cards', 'board']);

        return new ColumnResource($column);
    }

    public function update(ColumnUpdateRequest $request, Column $column): ColumnResource
    {
        $validatedData = $request->validated();
        $column = $this->columnService->updateColumn($validatedData, $column);

        $column->load('board');
        return new ColumnResource($column);
    }

    public function destroy(ColumnDestroyRequest $columnDestroyRequest, Column $column): JsonResponse
    {
        $column->destroy($column->id);
        return new JsonResponse(null, 204);
    }

    public function index(ColumnIndexRequest $request): ColumnCollectionResource
    {
        $user = Auth::user();

        $boardIdsWhereUserIsCreator = $user->createdBoards()->pluck('id');
        $boardIdsWhereUserIsMember = $user->boards()->pluck('board_id');
        $allUserColumns = Column::with(['board'])->whereIn('board_id', $boardIdsWhereUserIsCreator)->orWhereIn(
            'board_id',
            $boardIdsWhereUserIsMember
        )->get();
        return new ColumnCollectionResource($allUserColumns);
    }


}
