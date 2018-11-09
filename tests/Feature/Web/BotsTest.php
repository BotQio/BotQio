<?php

namespace Tests\Feature\Web;

use App\Bot;
use App\Cluster;
use App\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Tests\HasUser;
use Tests\TestCase;

class BotsTest extends TestCase
{
    use HasUser;
    use WithFaker;

    /** @test */
    public function unauthenticatedUserCannotSeeBotsPage()
    {
        $this
            ->withExceptionHandling()
            ->get('/bots')
            ->assertRedirect('/login');
    }

    /** @test */
    public function userWithNoBotsSeesHelpfulMessage()
    {
        $this
            ->actingAs($this->user)
            ->get('/bots')
            ->assertSee('Click the "Create a Bot" button');
    }

    /** @test */
    public function userWithABotSeesThatBot()
    {
        $bot = factory(Bot::class)->create([
            'creator_id' => $this->user->id,
        ]);

        $this
            ->actingAs($this->user)
            ->get('/bots')
            ->assertSee($bot->name);
    }

    /** @test */
    public function unauthenticatedUserCannotSeeBotCreationPage()
    {
        $this
            ->withExceptionHandling()
            ->get('/bots/create')
            ->assertRedirect('/login');
    }

    /** @test */
    public function userCanSeeBotCreationPage()
    {
        $this
            ->actingAs($this->user)
            ->get('/bots/create')
            ->assertViewIs('bot.create')
            ->assertSee('<input name="name"')
            ->assertSee('<select name="type"')
            ->assertSee('<option value="3d_printer">3D Printer</option>')
            ->assertSee('<select name="cluster"');
    }

    /** @test */
    public function unauthenticatedUserCannotCreateBot()
    {
        $this
            ->withExceptionHandling()
            ->post('/bots')
            ->assertRedirect('/login');
    }

    protected function postBot($overrides = [])
    {
        /** @var Cluster $default */
        $cluster = factory(Cluster::class)
            ->create([
                'creator_id' => $this->user->id,
            ]);

        $default = [
            'name' => $this->faker->userName,
            'type' => '3d_printer',
            'cluster' => $cluster->id,
        ];

        return $this->post('/bots', array_merge($default, $overrides));
    }

    /** @test */
    public function userCanCreateBot()
    {
        $botName = $this->faker->name;
        $response = $this
            ->actingAs($this->user)
            ->postBot(['name' => $botName]);

        $bot = Bot::whereCreatorId($this->user->id)->where('name', $botName)->first();
        $this->assertNotNull($bot);
        $this->assertNotNull($bot->cluster);
        $response->assertRedirect("/bots/{$bot->id}");
    }

    /** @test */
    public function userCanSeeTheirBot()
    {
        $bot = factory(Bot::class)->create([
            'creator_id' => $this->user->id,
        ]);

        $this
            ->actingAs($this->user)
            ->get("/bots/{$bot->id}")
            ->assertSee($bot->name)
            ->assertSee($bot->status)
            ->assertSee("Creator: {$this->user->name}");
    }

    /** @test */
    public function userCanSeeBotsCluster()
    {
        $cluster = factory(Cluster::class)->create([
            'creator_id' => $this->user->id,
        ]);

        $bot = factory(Bot::class)->create([
            'creator_id' => $this->user->id,
            'cluster_id' => $cluster->id,
        ]);

        $this
            ->actingAs($this->user)
            ->get("/bots/{$bot->id}")
            ->assertSee($bot->name)
            ->assertSee($bot->status)
            ->assertSee("Creator: {$this->user->name}")
            ->assertSee($cluster->name);
    }

    /** @test */
    public function anotherUserCannotSeeMyBot()
    {
        $bot = factory(Bot::class)->create([
            'creator_id' => $this->user->id,
        ]);

        $otherUser = factory(User::class)->create();

        $this
            ->withExceptionHandling()
            ->actingAs($otherUser)
            ->get("/bots/{$bot->id}")
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /** @test */
    public function aUserCannotMakeABotWithTheSameNameAsAnExistingBot()
    {
        $bot = factory(Bot::class)->create([
            'creator_id' => $this->user->id,
        ]);

        $this
            ->withExceptionHandling()
            ->actingAs($this->user)
            ->postBot(['name' => $bot->name])
            ->assertSessionHasErrors('name');
    }

    /** @test */
    public function aDifferentUserCanMakeABotWithTheSameNameAsMyExistingBot()
    {
        $bot = factory(Bot::class)->create([
            'creator_id' => $this->user->id,
        ]);

        $otherUser = factory(User::class)->create();

        $otherCluster = factory(Cluster::class)->create([
            'creator_id' => $otherUser,
        ]);

        $response = $this
            ->actingAs($otherUser)
            ->postBot([
                'name' => $bot->name,
                'cluster' => $otherCluster->id,
            ]);

        $bot = Bot::whereCreatorId($otherUser->id)->where('name', $bot->name)->first();
        $this->assertNotNull($bot);
        $response->assertRedirect("/bots/{$bot->id}");
    }

    /** @test */
    public function anotherUserCannotAssignABotToMyCluster()
    {
        $otherUser = factory(User::class)->create();

        $this
            ->withExceptionHandling()
            ->actingAs($otherUser)
            ->postBot()
            ->assertSessionHasErrors('cluster');
    }

    /** @test */
    public function aUserCannotAssignABotToANonExistingCluster()
    {
        $this
            ->withExceptionHandling()
            ->actingAs($this->user)
            ->postBot(['cluster' => 9999])
            ->assertSessionHasErrors('cluster');
    }

    /** @test */
    public function botNameIsRequired()
    {
        $this
            ->withExceptionHandling()
            ->actingAs($this->user)
            ->postBot(['name' => null])
            ->assertSessionHasErrors('name');
    }

    /** @test */
    public function botTypeIsRequired()
    {
        $this
            ->withExceptionHandling()
            ->actingAs($this->user)
            ->postBot(['type' => null])
            ->assertSessionHasErrors('type');
    }

    /** @test */
    public function botClusterIsRequired()
    {
        $this
            ->withExceptionHandling()
            ->actingAs($this->user)
            ->postBot(['cluster' => null])
            ->assertSessionHasErrors('cluster');
    }
}
