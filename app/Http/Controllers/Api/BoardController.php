<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Board\BoardDestroyRequest;
use App\Http\Requests\Board\BoardShowRequest;
use App\Http\Requests\Board\BoardStoreRequest;
use App\Http\Requests\Board\BoardUpdateRequest;
use App\Http\Resources\Board\BoardCollectionResource;
use App\Http\Resources\Board\BoardResource;
use App\Models\Board;
use App\Models\User;
use App\Services\BoardService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class BoardController extends Controller
{
    private $boardService;

    public function __construct(BoardService $boardService)
    {
        $this->boardService = $boardService;
    }

    public function store(BoardStoreRequest $request)
    {
        $validated = $request->validated();
        /**
         * @var User $user
         */
        $user = Auth::user();
        $board = $this->boardService->storeBoard($validated, $user);
        $board->load('users');

        return new BoardResource($board);
    }

    public function show(BoardShowRequest $request, Board $board)
    {
        $board->load(['creator', 'users']);
        return new BoardResource($board);
    }

    public function update(BoardUpdateRequest $request, Board $board): BoardResource
    {
        $validatedData = $request->validated();
        $board = $this->boardService->updateBoard($validatedData, $board);
        $board->load(['creator', 'users']);
        return new BoardResource($board);
    }

    public function destroy(BoardDestroyRequest $request, Board $board)
    {
        if ($board->background_img_path !== null) {
            Storage::delete($board->background_img_path);
        }

        $board->destroy($board->id);
        return new JsonResponse(null, 204);
    }

    public function index()
    {
        $userId = Auth::id();
        $response = Board::where('creator_id', $userId)->with('users')->orWhereHas(
            'users',
            function (Builder $query) use ($userId) {
                $query->where('user_id', $userId);
            }
        )->get();
        return new BoardCollectionResource($response);
    }

}
