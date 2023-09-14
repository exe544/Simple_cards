<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\CustomException;
use App\Mail\CardMoving;
use App\Models\Card;
use App\Models\Column;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;


class CardService
{

    public function storeCard(array $validatedData, User $actingUser): Card
    {
        $card = new Card();
        $card->fill($validatedData);

        $card->column()->associate(Column::find($validatedData['column_id']));
        $card->user()->associate($actingUser);

        $card->save();
        if (array_key_exists('tag_ids', $validatedData)) {
            $card->tags()->attach($validatedData['tags_ids']);
        }
        return $card;
    }

    public function updateCard(Card $card, int $userId, array $validatedData): Card
    {
        DB::beginTransaction();

        try {
            $cardTitleBeforeUpdate = null;
            if ($card->title !== $validatedData['title']) {
                $cardTitleBeforeUpdate = $card->title;
            }
            $this->mailUser($card, $userId, $validatedData, $cardTitleBeforeUpdate);

            $card->fill($validatedData);
            $card->column()->associate(Column::find($validatedData['column_id']));
            $card->save();

            if (array_key_exists('tag_ids', $validatedData)) {
                $card->tags()->sync($validatedData['tag_ids']);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage(), [$e]);
            throw new CustomException('Failed to update. Sorry, changes were not saved!');
        }
        return $card;
    }

    public function indexCard(array $validatedData): Collection
    {
        $user = Auth::user();

        $cardCollectionQuery = Card::getBuilderForUser($user);

        //add relations and order to query
        $cardCollectionQuery = $cardCollectionQuery->orderBy('due_dat')->with(['column', 'tags', 'user']);


        // will show all active or inactive cards ordered by due_date
        if (array_key_exists('is_active', $validatedData)) {
            $cardCollectionQuery = $cardCollectionQuery->where('is_active', $validatedData['is_active']);
        }
        $cards = $cardCollectionQuery->get();

        return $cards;
    }

    private function mailUser($card, int $userId, array $validatedData, $cardTitleBeforeUpdate): void
    {
        if ($card->column_id !== $validatedData['column_id'] && $userId !== $card['creator_id']) {
            $actingUserEmail = Auth::user()->email;
            $newColumnName = Column::where('id', $validatedData['column_id'])->value('name');
            Mail::to($card->user()->get())->queue(
                new CardMoving($card, $newColumnName, $actingUserEmail, $cardTitleBeforeUpdate)
            );
        }
    }
}
