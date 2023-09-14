<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Models\Board;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BoardControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testStoreOk(): void
    {
        $boardData = Board::factory()->make()->toArray();
        $creator = User::find($boardData['creator_id']);
        Sanctum::actingAs($creator);

        $teamEmails = User::factory()->count(3)->create()->pluck('email');
        $boardData['team_emails'] = $teamEmails;

        $response = $this->postJson(route('boards.store'), $boardData);

        $response->assertStatus(201);


        $this->assertDatabaseHas('boards', [
            'name' => $boardData['name'],
            'creator_id' => $boardData['creator_id'],
        ]);
    }

    public function testShowOk(): void
    {
        $board = Board::factory()->withUsers()->create();

        $creator = $board->creator;
        Sanctum::actingAs($creator);

        $response = $this->getJson(route('boards.show', $board->id));
        $response->assertStatus(200);

        $response->assertJson(fn(AssertableJson $json) => $json->has('data')
            ->missing('background_img')
        );

        $responseArray = $response->json()['data'];
        $this->assertEquals($board->name, $responseArray['name']);
        $this->assertEquals($board->creator_id, $responseArray['creator_id']);

        Sanctum::actingAs($board->users->first());
        $response = $this->getJson(route('boards.show', $board->id));
        $response->assertStatus(200);
    }

    public function testShowAccessForMembersOrCreator(): void
    {
        $board = Board::factory()->withUsers()->create();
        Sanctum::actingAs(User::factory()->create());
        $response = $this->getJson(route('boards.show', $board->id));
        $response->assertStatus(403);
        $response->assertJsonPath('message', "This board available only for board's members or creator");

//positive test for members
        Sanctum::actingAs($board->users()->first());
        $response = $this->getJson(route('boards.show', $board->id));
        $response->assertStatus(200);
    }

    public function testUpdateOk(): void
    {
        $boardDefault = Board::factory()->withUsers()->create();
        Sanctum::actingAs($boardDefault->creator);

        $boardNew = Board::factory()->make()->toArray();
        User::factory()->count(3)->create();
        $teamEmails = User::where('id', '!=', $boardDefault->creator_id)->pluck('email');
        $boardNew['team_emails'] = $teamEmails;

        $response = $this->putJson(route('boards.update', $boardDefault->id), $boardNew);

        $response->assertStatus(200);

        $this->assertDatabaseHas('boards', [
            'name' => $boardNew['name'],
        ]);
    }

    public function testUpdateBoardOnlyByCreator(): void
    {
        $board = Board::factory()->withUsers()->create();
        $boardUpdate = Board::factory()->make()->toArray();
        Sanctum::actingAs(User::factory()->create());

        $response = $this->putJson(route('boards.update', $board->id), $boardUpdate);
        $response->assertStatus(403);
        $response->assertJsonPath('message', 'Denied. You are not a creator of the board');

        Sanctum::actingAs($board->users->first());
        $response = $this->putJson(route('boards.update', $board->id), $boardUpdate);
        $response->assertStatus(403);
    }

    public function testDestroyOk(): void
    {
        $board = Board::factory()->create();
        Sanctum::actingAs($board->creator);

        $response = $this->deleteJson(route('boards.destroy', $board->id));

        $response->assertStatus(204);

        $this->assertDatabaseMissing('boards', [
            'id' => $board->id,
        ]);
    }

    public function testDestroyBoardOnlyByCreator(): void
    {
        $board = Board::factory()->withUsers()->create();
        Sanctum::actingAs(User::factory()->create());

        $response = $this->deleteJson(route('boards.destroy', $board->id));
        $response->assertStatus(403);
        $this->assertDatabaseHas('boards', [
            'id' => $board->id,
        ]);
        $response->assertJsonPath('message', 'Denied. You are not a creator of the board');

        Sanctum::actingAs($board->users->first());
        $response = $this->deleteJson(route('boards.destroy', $board->id));
        $response->assertStatus(403);
    }

    public function testIndexOk(): void
    {
        $user = User::factory()->create();
        Board::factory(3)->create(['creator_id' => $user->id]);
        Sanctum::actingAs($user);

        $response = $this->getJson(route('boards.index'));
        $response->assertStatus(200);

        $responseArray = $response->json()['data'];
        foreach ($responseArray as $value) {
            $this->assertEquals($value['creator_id'], $user->id);
        }
    }


    public function testStoreWithBackgroundImgOk(): void
    {
        Storage::fake('local');
        $board = Board::factory()->withBackgroundFile()->make()->toArray();
        $creator = User::find($board['creator_id']);
        Sanctum::actingAs($creator);

        $response = $this->postJson(route('boards.store'), $board);

        Storage::disk('local')->assertExists(
            'public/backgrounds/' . Auth::id() . '/' . $board['background_img']->getClientOriginalName()
        );

        $response->assertStatus(201);
    }


    public function testUpdateWithBackgroundImgOk(): void
    {
        Storage::fake('local');
        $board = Board::factory()->withBackgroundPath()->create();
        Sanctum::actingAs($board->creator);

        $board2 = Board::factory()->withBackgroundFile()->make()->toArray();

        $response = $this->putJson(route('boards.update', $board->id), $board2);


        Storage::disk('local')->assertExists(
            'public/backgrounds/' . Auth::id() . '/' . $board2['background_img']->getClientOriginalName()
        );
        Storage::disk('local')->assertMissing($board->background_img_path);
        $response->assertStatus(200);
    }

    public function testDeleteImageAftedBoardDestroyOk(): void
    {
        Storage::fake('local');
        $board = Board::factory()->withBackgroundPath()->create();
        $response = $this->actingAs($board->creator)->deleteJson(route('boards.destroy', $board->id));

        $response->assertStatus(204);
        Storage::disk('local')->assertMissing($board->background_img_path);
    }


    public function testShowWithImageOk(): void
    {
        Storage::fake('local');
        $board = Board::factory()->withBackgroundPath()->create();

        $response = $this->actingAs($board->creator)->getJson(route('boards.show', $board->id));

        $response->assertStatus(200);
        $response->assertJsonStructure();
        $response->assertJson(fn(AssertableJson $json) => $json->has('data.background_img'));
    }
}
