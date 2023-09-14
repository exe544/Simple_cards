<?php

declare(strict_types=1);

namespace Tests\Feature\App\Http\Controllers\Api;

use App\Mail\CardMoving;
use App\Models\Board;
use App\Models\Card;
use App\Models\Column;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CardControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testStoreOk(): void
    {
        //ASSUMPTIONS
        $card = Card::factory()->make()->toArray();

        Sanctum::actingAs(User::first());

        //ACTIONS
        $response = $this->postJson(route('cards.store'), $card);

        //ASSERTIONS
        $response->assertStatus(201);


        $responseData = $response->json()['data'];

        $this->assertDatabaseHas('cards', [
            'id' => $responseData['id'],
            'title' => $card['title'],
            'priority' => $card['priority'],
            'description' => $card['description'],
            'due_dat' => $card['due_dat'],
            'is_active' => $card['is_active'],
            'column_id' => $card['column_id'],
        ]);
    }

    public function testStoreWithTagsOk(): void
    {
        $card = Card::factory()->make()->toArray();

        Sanctum::actingAs(User::first());

        //creating tag_id array
        Tag::factory()->count(2)->create();
        $card['tags_id'] = Tag::query()->pluck('id');

        $response = $this->postJson(route('cards.store'), $card);

        //check relations loaded
        $response->assertStatus(201);

        $response->assertJson(
            fn(AssertableJson $json) => $json->has(
                'data',
                fn(AssertableJson $json) => $json
                    ->whereType('column', 'array')
                    ->whereType('tags', 'array')
                    ->whereType('creator', 'array')
                    ->etc()
            )
        );
    }


    public function testShowOk(): void
    {
        $card = Card::factory()->withTags()->create();

        $cardData = $card->toArray();

        Sanctum::actingAs(User::first());
        $cardResponse = $this->getJson(route('cards.show', $card->id));

        $cardResponse->assertStatus(200);
        $responseData = $cardResponse->json()['data'];
        $this->assertEquals($cardData['title'], $responseData['title']);
        $this->assertEquals($cardData['priority'], $responseData['priority']);
        $this->assertEquals($cardData['description'], $responseData['description']);
        $this->assertEquals($cardData['due_dat'], $responseData['due_dat']);
        $this->assertEquals($cardData['is_active'], $responseData['is_active']);
        $this->assertEquals($cardData['column_id'], $responseData['column_id']);


        $cardResponse
            ->assertJson(
                fn(AssertableJson $json) => $json->has(
                    'data',
                    fn(AssertableJson $json) => $json
                        ->whereType('column', 'array')
                        ->whereType('tags', 'array')
                        ->whereType('creator', 'array')
                        ->etc()
                )
            );
    }

    public function testUpdateOk(): void
    {
        //create card in db
        $card = Card::factory()->create();
        Sanctum::actingAs($card->user);

        //create data for updating
        $updateData = Card::factory()->make()->toArray();

        $response = $this->putJson(route('cards.update', $card->id), $updateData);

        $response->assertStatus(200);

        //check that updated data was saved
        $responseData = $response->json()['data'];
        $this->assertEquals($responseData['title'], $updateData['title']);
        $this->assertEquals($responseData['description'], $updateData['description']);
        $this->assertEquals($responseData['priority'], $updateData['priority']);
        $this->assertEquals($responseData['due_dat'], $updateData['due_dat']);
        $this->assertEquals($responseData['is_active'], $updateData['is_active']);
    }

    public function testMailCardMovingByNotCreator(): void
    {
        $board = Board::factory()->withUsers()->create();
        $member = $board->users()->first();
        $oldColumn = Column::factory()->create(['board_id' => $board->id]);
        $card = Card::factory()->create(['column_id' => $oldColumn->id]);
        $creator = $card->user;

        $oldColumnPlace = $oldColumn->place;
        $newColumnPlace = $oldColumnPlace + 1;
        $newColumn = Column::factory()->create(['place' => $newColumnPlace, 'board_id' => $board->id]);

        Sanctum::actingAs($member);

        //create data for updating + move card to a new column
        $updateData = Card::factory()->make(['column_id' => $newColumn->id])->toArray();

        Mail::fake();
        $response = $this->putJson(route('cards.update', $card->id), $updateData);
        Mail::assertQueued(CardMoving::class, function (CardMoving $mail) use ($creator) {
            return $mail->hasTo($creator->email) &&
                $mail->hasSubject('Your card was moved');
        });
    }


    public function testUpdateWithTagsOk(): void
    {
        $card = Card::factory()->withTags()->create();
        Sanctum::actingAs($card->user);

        //create data for updating
        $updateData = Card::factory()->make()->toArray();

        $newTagCount = 2;
        $updateData['tag_id'] = Tag::factory()->count($newTagCount)->create()->pluck('id');

        $response = $this->putJson(route('cards.update', $card->id), $updateData);

        $response->assertStatus(200);

        $response->assertJson(
            fn(AssertableJson $json) => $json->has(
                'data',
                fn(AssertableJson $json) => $json
                    ->has(
                        'tags',
                        $newTagCount
                    )
                    ->etc()
            )
        );
    }


    public function testDestroyOk(): void
    {
        $card = Card::factory()->withTags()->create();
        $tag_ids = Tag::query()->pluck('id');

        Sanctum::actingAs($card->user);
        $response = $this->deleteJson(route('cards.destroy', $card->id));

        //check that the searched data were deleted
        $this->assertDatabaseMissing('cards', [
            'id' => $card->id,
        ]);
        foreach ($tag_ids as $id) {
            $this->assertDatabaseMissing('cards_tags', [
                'tag_id' => $id,
            ]);
        }

        $response->assertStatus(204);
    }


    public function testDestroyByMember(): void
    {
        $board = Board::factory()->withUsers()->create();
        $column = Column::factory()->create(['board_id' => $board->id]);
        $card = Card::factory()->create(['column_id' => $column->id]);
        Sanctum::actingAs($board->users()->first());
        $response = $this->deleteJson(route('cards.destroy', $card->id));
        $response->assertStatus(403);
        $response->assertJsonPath('message', 'You are not card creator or creator of the board in question');

        $card = Card::factory()->create(['column_id' => $column->id]);
        Sanctum::actingAs(User::factory()->create());
        $response = $this->deleteJson(route('cards.destroy', $card->id));
        $response->assertStatus(403);
        $response->assertJsonPath('message', 'You are not card creator or creator of the board in question');
    }


    public function testIndexOk(): void
    {
        $board = Board::factory()->create();
        foreach (range(1, 9) as $number) {
            Card::factory()->withTags()->for(
                Column::factory()->create(['place' => $number, 'board_id' => $board->id])
            )->create();
        }

        Sanctum::actingAs($board->creator);
        $response = $this->getJson(route('cards.index'));

        //check response 'data' array size
        $response->assertJson(fn(AssertableJson $json) => $json->has('data', 9));

        $response->assertJson(
            fn(AssertableJson $json) => $json->has(
                'data.0',
                fn(AssertableJson $json) => $json
                    ->whereType('tags', 'array')
                    ->whereType('column', 'array')
                    ->whereType('creator', 'array')
                    ->etc()
            )
        );
    }


    public function testIndexResultForUserWithCards(): void
    {
        $board = Board::factory()->withUsers()->create();
        foreach (range(1, 9) as $number) {
            Card::factory()->for(
                Column::factory()->create(['place' => $number, 'board_id' => $board->id])
            )->create();
        }

        Sanctum::actingAs($board->users()->first());
        $response = $this->getJson(route('cards.index'));
        $response->assertJson(fn(AssertableJson $json) => $json->has('data', 9));
    }

    public function testIndexResultForUserWithoutCards(): void
    {
        $board = Board::factory()->withUsers()->create();
        foreach (range(1, 9) as $number) {
            Card::factory()->for(
                Column::factory()->create(['place' => $number, 'board_id' => $board->id])
            )->create();
        }

        Sanctum::actingAs(User::factory()->create());
        $response = $this->getJson(route('cards.index'));

        $response->assertJson(fn(AssertableJson $json) => $json->has('data', 0));
    }


    public function testIndexOkActiveParams(): void
    {
        foreach (range(1, 9) as $number) {
            Card::factory()->withTags()->for(
                Column::factory()->create(['place' => $number])
            )->create();
        }
        Sanctum::actingAs(User::first());

        $response = $this->getJson(route('cards.index', ['is_active' => 1]));
        $response = $response->json()['data'];
        foreach ($response as $element) {
            $this->assertEquals($element['is_active'], 1);
        }


        $response = $this->getJson(route('cards.index', ['is_active' => 0]));
        $response = $response->json()['data'];
        foreach ($response as $element) {
            $this->assertEquals($element['is_active'], 0);
        }
    }


    public function testStoreCardUniqueTitleAndDescriptionForBoard(): void
    {
        $secondCardInBoard = Card::factory()->create();
        $boardModel = $secondCardInBoard->column->board;
        $columnModel = $secondCardInBoard->column;
        Sanctum::actingAs($boardModel->creator);

        $secondColumnPlace = $columnModel->place;
        $secondColumnPlace++;

        $secondColumnInBoard = Column::factory()->create(['place' => $secondColumnPlace, 'board_id' => $boardModel->id]
        );

        $newCard = Card::factory()->make(
            [
                'title' => $secondCardInBoard->title,
                'description' => $secondCardInBoard->description,
                'column_id' => $secondColumnInBoard->id
            ]
        )->toArray();

        $response = $this->postJson(route('cards.store'), $newCard);

        $response->assertStatus(422);
        $response->assertJsonPath('message', 'Item with the same data already exists in board (and 1 more error)');
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'title',
                'description',
            ]
        ]);
    }


    public function testUpdateCardUniqueTitleForBoard(): void
    {
        //first card creating
        $firstCardInBoard = Card::factory()->create();
        $boardModel = $firstCardInBoard->column->board;
        $columnModel = $firstCardInBoard->column;
        Sanctum::actingAs($boardModel->creator);

        //second card creating
        $secondColumnPlace = $columnModel->place;
        $secondColumnPlace++; //ensure that secondColumn will be with the place another then column where firstCardInBoard exists

        $secondColumnInBoard = Column::factory()->create(
            [
                'place' => $secondColumnPlace,
                'board_id' => $boardModel->id
            ] //ensure that secondColumn will be in same board
        );
        $secondCardInBoard = Card::factory()->create(['column_id' => $secondColumnInBoard->id]);

        //creating data for update
        $secondColumnPlace++;
        $thirdColumnInBoard = Column::factory()->create(['place' => $secondColumnPlace, 'board_id' => $boardModel->id]
        );
        $updateCard = Card::factory()->make(
            [
                'title' => $secondCardInBoard->title,
                //check that title have to be unique
                'description' => $firstCardInBoard->description,
                //check that model's own description can be ignored
                'column_id' => $thirdColumnInBoard->id
                //check card is in the same board _and_ can be moved in another column
            ]
        )->toArray();

        $response = $this->putJson(route('cards.update', $firstCardInBoard->id), $updateCard);

        $response->assertStatus(422);
        $response->assertJsonPath('message', 'Item with the same data already exists in board');
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'title',
            ]
        ]);
    }


    public function testOnlyMemberOrBoardCreatorCanStoreCard(): void
    {
        $board = Board::factory()->withUsers()->create();
        $column = Column::factory()->create(['board_id' => $board->id]);

        //positive test
        $card = Card::factory()->make(['column_id' => $column->id])->toArray();
        Sanctum::actingAs($board->users()->first());

        $response = $this->postJson(route('cards.store'), $card);
        $response->assertStatus(201);

        //negative test
        $card = Card::factory()->make(['column_id' => $column->id])->toArray();
        Sanctum::actingAs(User::factory()->create());

        $response = $this->postJson(route('cards.store'), $card);
        $response->assertStatus(403);
        $response->assertJsonPath('message', 'You are not creator or a member of the board in question');
    }

    public function testShowOnlyForBoardMembers(): void
    {
        $board = Board::factory()->withUsers()->create();
        $column = Column::factory()->create(['board_id' => $board->id]);
        $card = Card::factory()->create(['column_id' => $column->id]);
        Sanctum::actingAs($board->users()->first()); //acting as board member

        $response = $this->getJson(route('cards.show', $card->id));
        $response->assertStatus(200);

        Sanctum::actingAs(User::factory()->create());
        $response = $this->getJson(route('cards.show', $card->id));
        $response->assertStatus(403);
        $response->assertJsonPath('message', 'You are not creator or a member of the board in question');
    }

    public function testUpdateFromNotBoardMember(): void
    {
        $card = Card::factory()->create();
        $user = User::factory()->create();
        $updateData = Card::factory()->make()->toArray();
        Sanctum::actingAs($user);

        $response = $this->putJson(route('cards.update', $card->id), $updateData);
        $response->assertStatus(Response::HTTP_FORBIDDEN);
        $response->assertJsonPath('message', 'You are not creator or a member of the board in question');
    }

    public function testUpdateByTeamMemberOk(): void
    {
        Mail::fake();
        $board = Board::factory()->withUsers()->create();
        $column = Column::factory()->create(['place' => 1, 'board_id' => $board->id]);
        Sanctum::actingAs($board->users()->first());
        $card = Card::factory()->create(['column_id' => $column->id]);


        $columnNew = Column::factory()->create(['place' => 2, 'board_id' => $board->id]);
        $cardNew = Card::factory()->make(['column_id' => $columnNew->id])->toArray();

        $response = $this->putJson(route('cards.update', $card->id), $cardNew);

        $response->assertStatus(200);
    }

}
