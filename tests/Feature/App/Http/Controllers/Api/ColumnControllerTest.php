<?php

declare(strict_types=1);

namespace Tests\Feature\App\Http\Controllers\Api;

use App\Models\Board;
use App\Models\Column;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ColumnControllerTest extends TestCase
{
    use RefreshDatabase;


    public function testStoreOk(): void
    {
        $columnData = Column::factory()->make()->toArray();


        Sanctum::actingAs(Board::where('id', $columnData['board_id'])->first()->creator);

        $response = $this->postJson(route('column.store'), $columnData);

        $response->assertStatus(201);

        //test that relation 'board()' was loaded and exist in response
        $response
            ->assertJson(
                fn(AssertableJson $json) => $json->first(fn(AssertableJson $json) => $json
                    ->whereType('board', 'array')
                    ->etc()
                )
            );

        $responseData = $response->json()['data'];
        $this->assertEquals($columnData['name'], $responseData['name']);
        $this->assertEquals($columnData['place'], $responseData['place']);
//        $this->assertEquals($columnData['board_id'], $responseData['board_id']);

        $this->assertDatabaseHas('columns', [
            'name' => $responseData['name'],
            'place' => $responseData['place'],
            'board_id' => $columnData['board_id']
        ]);
    }


    public function testUpdateOk(): void
    {
        $newColumnRecord = Column::factory()->create();

        Sanctum::actingAs($newColumnRecord->board->creator);

        $updateData = Column::factory()->make()->toArray();

        $response = $this->putJson(route('column.update', $newColumnRecord->id), $updateData);

        $response->assertStatus(200);

        //Check that DB has new data in columns
        $this->assertDatabaseHas('columns', [
            'name' => $updateData['name'],
            'place' => $updateData['place'],
        ]);
    }


    public function testShowOk(): void
    {
        $column = Column::factory()->create();

        Sanctum::actingAs($column->board->creator);

        $response = $this->getJson(route('column.show', $column->id));


        $columnData = $column->toArray();
        $response->assertStatus(200);

        $responseData = $response->json()['data'];
        $this->assertEquals($columnData['name'], $responseData['name']);
        $this->assertEquals($columnData['place'], $responseData['place']);
    }


    public function testDestroyOk(): void
    {
        $column = Column::factory()->create();

        Sanctum::actingAs($column->board->creator);

        $response = $this->deleteJson(route('column.destroy', $column->id));
        $response->assertStatus(204);

        $this->assertDatabaseMissing('columns', [
            'id' => $column->id,
        ]);
    }

    public function testIndexOk(): void
    {
        $userBoardCreator = User::factory()->create();
        $board = Board::factory()->create(['creator_id' => $userBoardCreator->id]);

        Column::factory()->count(2)->create(['board_id' => $board->id]);
        Sanctum::actingAs($userBoardCreator);
        $response = $this->getJson(route('column.index'));


        $response->assertStatus(200);

        $response
            ->assertJson(fn(AssertableJson $json) => $json->has('data', 2));
    }


    public function testStoreColumnUniqueNameForBoard(): void
    {
        $columnExisting = Column::factory()->create();

        $place = $columnExisting['place'] + 1;
        $columnNew = Column::factory()->make(
            ['name' => $columnExisting->name, 'place' => $place, 'board_id' => $columnExisting->board_id]
        )->toArray();

        Sanctum::actingAs(Board::find($columnExisting->board_id)->creator);

        $response = $this->postJson(route('column.store'), $columnNew);

        $response->assertStatus(422);
        $response->assertJsonPath('message', 'Item with the same data already exists in board');
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'name',
            ]
        ]);
    }

    public function testStoreColumnUniquePlaceForBoard(): void
    {
        $columnExisting = Column::factory()->create();

        $name = $columnExisting['name'] . '1';
        $columnNew = Column::factory()->make(
            ['name' => $name, 'place' => $columnExisting['place'], 'board_id' => $columnExisting->board_id]
        )->toArray();

        Sanctum::actingAs(Board::find($columnExisting->board_id)->creator);

        $response = $this->postJson(route('column.store'), $columnNew);

        $response->assertStatus(422);
        $response->assertJsonPath('message', 'Item with the same data already exists in board');
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'place',
            ]
        ]);
    }

    public function testUpdateColumnUniqueNameForBoard(): void
    {
        $columnExisting = Column::factory()->create(['place' => 1]); //create basic Column
        $secondColumnInBoard = Column::factory()->create(['place' => 2, 'board_id' => $columnExisting['board_id']]
        ); // create another column in DB, because we can ignore $columnExisting data

        $columnNew = Column::factory()->make(
            ['name' => $secondColumnInBoard->name, 'place' => 3]
        )->toArray();
        Sanctum::actingAs(Board::find($columnExisting->board_id)->creator);

        $response = $this->putJson(route('column.update', $columnExisting->id), $columnNew);

        $response->assertStatus(422);
        $response->assertJsonPath('message', 'Item with the same data already exists in board');
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'name',
            ]
        ]);
    }

    public function testUpdateColumnUniquePlaceForBoard(): void
    {
        $columnExisting = Column::factory()->create(['place' => 1]); //create basic Column
        $secondColumnInBoard = Column::factory()->create(['place' => 2, 'board_id' => $columnExisting['board_id']]
        ); // create another column in DB, because we can ignore $columnExisting data

        $columnNew = Column::factory()->make(
            ['place' => $secondColumnInBoard->place]
        )->toArray();
        Sanctum::actingAs(Board::find($columnExisting->board_id)->creator);

        $response = $this->putJson(route('column.update', $columnExisting->id), $columnNew);

        $response->assertStatus(422);
        $response->assertJsonPath('message', 'Item with the same data already exists in board');
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'place',
            ]
        ]);
    }

    public function testUpdateColumnIgnoreOwnFieldsValuesOk(): void
    {
        $columnExisting = Column::factory()->create();

        $columnNew = Column::factory()->make(
            ['name' => $columnExisting->name, 'place' => $columnExisting->place]
        )->toArray();
        Sanctum::actingAs(Board::find($columnExisting->board_id)->creator);

        $response = $this->putJson(route('column.update', $columnExisting->id), $columnNew);

        $response->assertStatus(200);
    }


    public function testNotBoardCreatorCantCreateColumn(): void
    {
        $board = Board::factory()->withUsers()->create();
        $column = Column::factory()->make(['board_id' => $board->id])->toArray();
        $boardMember = $board->users->first();
        Sanctum::actingAs($boardMember);

        $response = $this->postJson(route('column.store'), $column);
        $response->assertStatus(403);

        Sanctum::actingAs(User::factory()->create());
        $response = $this->postJson(route('column.store'), $column);
        $response->assertStatus(403);

        //positive test with board_creator
        Sanctum::actingAs($board->creator);
        $response = $this->postJson(route('column.store'), $column);
        $response->assertStatus(201);
    }


    public function testOnlyBoardCreatorCanUpdate(): void
    {
        $board = Board::factory()->withUsers()->create();
        $columnBasic = Column::factory()->create(['place' => 1, 'board_id' => $board->id]);
        $columnNew = Column::factory()->make(['place' => 2, 'board_id' => $board->id])->toArray();

        $notRelatedUser = User::factory()->create();
        Sanctum::actingAs($notRelatedUser);
        $response = $this->putJson(route('column.update', $columnBasic->id), $columnNew);
        $response->assertStatus(403);
        $response->assertJsonPath('message', 'Denied. You are not a creator of the board');

        $boardMemberUser = $board->users->first();
        Sanctum::actingAs($boardMemberUser);
        $response = $this->putJson(route('column.update', $columnBasic->id), $columnNew);
        $response->assertStatus(403);
        $response->assertJsonPath('message', 'Denied. You are not a creator of the board');
    }

    public function testShowOnlyForBoardMembersAndCreator(): void
    {
        $board = Board::factory()->withUsers()->create();
        $column = Column::factory()->create(['board_id' => $board->id]);
        Sanctum::actingAs(User::factory()->create());

        $response = $this->getJson(route('column.show', $column->id));
        $response->assertStatus(403);
        $response->assertJsonPath('message', "This column available only for board's members or creator");

        //check acting as board member
        Sanctum::actingAs($board->users->first());
        $response = $this->getJson(route('column.show', $column->id));
        $response->assertStatus(200);
    }


    public function testOnlyCreatorCanDestroy(): void
    {
        $board = Board::factory()->withUsers()->create();
        $column = Column::factory()->create(['board_id' => $board->id]);
        $notRelatedUser = User::factory()->create();
        Sanctum::actingAs($notRelatedUser);

        $response = $this->deleteJson(route('column.update', $column->id));
        $response->assertStatus(403);
        $response->assertJsonPath('message', 'Denied. You are not a creator of the board');

        $boardMemberUser = $board->users->first();
        Sanctum::actingAs($boardMemberUser);
        $response = $this->deleteJson(route('column.update', $column->id));
        $response->assertStatus(403);
        $response->assertJsonPath('message', 'Denied. You are not a creator of the board');
    }
}
