<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Board;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class BoardService
{
    public function storeBoard(array $validated, User $actingUser): Board
    {
        $board = new Board();
        if (array_key_exists('background_img', $validated)) {
            $path = Storage::putFileAs(
                'public/backgrounds/' . Auth::id(),
                $validated['background_img'],
                $validated['background_img']->getClientOriginalName()
            );

            $board['background_img_path'] = $path;
        }
        $board->fill($validated);

        $board->creator()->associate($actingUser);
        $board->save();

        if (array_key_exists('team_emails', $validated)) {
            $membersId = User::whereIn('email', $validated['team_emails'])->pluck('id');
            $board->users()->attach($membersId);
        }
        return $board;
    }

    public function updateBoard(array $validatedData, Board $board): Board
    {
        if (array_key_exists('background_img', $validatedData)) {
            Storage::delete($board->background_img_path);
            $path = Storage::putFileAs(
                'public/backgrounds/' . Auth::id(),
                $validatedData['background_img'],
                $validatedData['background_img']->getClientOriginalName()
            );

            $board['background_img_path'] = $path;
        }
        $board->fill($validatedData);
        $board->save();

        if (array_key_exists('team_emails', $validatedData)) {
            $board->users()->sync(User::whereIn('email', $validatedData['team_emails'])->pluck('id'));
        }

        return $board;
    }
}
