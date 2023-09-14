<?php

declare(strict_types=1);

namespace Tests\Feature\App\Http\Controllers\Api;

use App\Models\Board;
use App\Models\Card;
use App\Models\Column;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TagControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testStoreOkWithCards(): void
    {
        $board = Board::factory()->withUsers()->create();
        $column = Column::factory()->create(['board_id' => $board->id]);
        Card::factory()->count(2)->create(['column_id' => $column->id]);

        $tagData = Tag::factory()->make()->toArray();
        $tagData['card_ids'] = Card::query()->pluck('id')->toArray();
        Sanctum::actingAs($board->creator);

        $response = $this->postJson(route('tags.store'), $tagData);
        $response->assertCreated();

        $response->assertJson(
            fn(AssertableJson $json) => $json->has(
                'data',
                fn(AssertableJson $json) => $json
                    ->whereType('cards', 'array')
                    ->has('cards', 2)
                    ->etc()
            )
        );

        $responseData = $response->json()['data'];
        $this->assertEquals($tagData['title'], $responseData['title']);
        $this->assertEquals($tagData['color'], $responseData['color']);

        //acting as board member
        $tagData = Tag::factory()->make()->toArray();
        $tagData['card_ids'] = Card::query()->pluck('id')->toArray();
        Sanctum::actingAs($board->users()->first());

        $response = $this->postJson(route('tags.store'), $tagData);
        $response->assertCreated();
    }

    /**
     * @group new
     */
    public function testStoreByUserWithoutCards(): void
    {
        $board = Board::factory()->create();
        $column = Column::factory()->create(['board_id' => $board->id]);
        Card::factory()->count(2)->create(['column_id' => $column->id]);

        $tagData = Tag::factory()->make()->toArray();
        $tagData['card_ids'] = Card::query()->pluck('id')->toArray();

        $board2 = Board::factory()->create();
        Sanctum::actingAs($board2->creator);

        $response = $this->postJson(route('tags.store'), $tagData);
        $response->assertStatus(422);
        $response->assertJsonPath(
            'message',
            'Denied. You do not have rights to attach tag on the mentioned card. You are not a member of the board, where card exist.'
        );
    }

    public function testShowOk(): void
    {
        $tag = Tag::factory()->withCards()->create();
        $tagData = $tag->toArray();
        Sanctum::actingAs(Board::first()->creator);

        $response = $this->getJson(route('tags.show', $tag->id));

        $response->assertOk();

        $responseData = $response->json()['data'];
        $this->assertEquals($tagData['id'], $responseData['id']);
        $this->assertEquals($tagData['title'], $responseData['title']);
        $this->assertEquals($tagData['color'], $responseData['color']);

        $response->assertJson(
            fn(AssertableJson $json) => $json->has(
                'data',
                fn(AssertableJson $json) => $json
                    ->whereType('cards', 'array')
                    ->etc()
            )
        );
    }

    public function testIndexOk(): void
    {
        Tag::factory(5)->withCards()->create();

        Sanctum::actingAs(User::factory()->create());

        $response = $this->getJson(route('tags.index'));

        $response->assertStatus(200);
        $response->assertJson(
            fn(AssertableJson $json) => $json->has(
                'data.0',
                fn(AssertableJson $json) => $json
                    ->whereType('cards', 'array')
                    ->etc()
            )
        );
    }


    public function testIndexWithLikeOk(): void
    {
        Tag::factory(10)->state(
            new Sequence(
                ['title' => 'lang'],
                ['title' => 'Lola'],
                ['title' => 'Orland'],
                ['title' => 'Logs'],
                ['title' => 'Letter'],
                ['title' => 'Laptop x'],
                ['title' => 'laravel'],
                ['title' => 'lays'],
                ['title' => 'last'],
                ['title' => 'board'],
            )
        )->create();
        Sanctum::actingAs(User::factory()->create());

        $response = $this->getJson(route('tags.index', ['title' => 'la']));

        $response->assertJson(
            fn(AssertableJson $json) => $json->has('data', 7)
        );
    }


    public function testBoardMemberCreateTagOk(): void
    {
        $board = Board::factory()->withUsers()->create();
        $column = Column::factory()->create(['board_id' => $board->id]);
        Card::factory()->count(2)->create(['column_id' => $column->id]);

        $tag = Tag::factory()->make()->toArray();
        $tag['card_ids'] = Card::query()->pluck('id')->toArray();

        Sanctum::actingAs($board->users()->first());
        $response = $this->postJson(route('tags.store'), $tag);
        $response->assertStatus(201);
    }


    public function testUserWithoutBoardCantCreate(): void
    {
        $tag = Tag::factory()->make()->toArray();
        Sanctum::actingAs(User::factory()->create());

        $response = $this->postJson(route('tags.store'), $tag);
        $response->assertStatus(403);
        $response->assertJsonPath('message', 'Denied. You are not a member or creator of a board');
    }
}
