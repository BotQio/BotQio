<?php

namespace Tests\Feature\Host\Commands;

use App\Actions\AssignJobToBot;
use App\Enums\BotStatusEnum;
use App\Enums\JobStatusEnum;
use App\Errors\HostErrors;
use Illuminate\Http\Response;
use Tests\Helpers\PassportHelper;
use Tests\TestCase;

class BotErrorCommandTest extends TestCase
{
    use PassportHelper;

    protected function botErrorCommand($data = null)
    {
        return $this->postJson('/host', array_filter([
            'command' => 'BotError',
            'data' => $data
        ]));
    }

    /** @test */
    public function unauthenticatedHostCannotPerformThisAction()
    {
        $this
            ->botErrorCommand()
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertExactJson(HostErrors::oauthAuthorizationInvalid()->toArray());
    }

    /** @test */
    public function hostCanSubmitErrorOnIdleBot()
    {
        $bot = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->host($this->mainHost)
            ->create();

        $error = 'ERROR TEXT';

        $this
            ->withTokenFromHost($this->mainHost)
            ->botErrorCommand([
                'id' => $bot->id,
                'error' => $error,
            ])
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'status' => 'success',
                'data' => [],
            ]);

        $bot->refresh();

        $this->assertEquals(BotStatusEnum::ERROR, $bot->status);
        $this->assertEquals($error, $bot->error_text);
    }

    /** @test */
    public function botErrorWithJobFailsJob()
    {
        $bot = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->host($this->mainHost)
            ->create();

        $job = $this->job()->worker($bot)->create();

        app(AssignJobToBot::class)->execute($bot, $job);
        $bot->refresh();
        $job->refresh();

        $error = 'ERROR TEXT';

        $this
            ->withTokenFromHost($this->mainHost)
            ->botErrorCommand([
                'id' => $bot->id,
                'error' => $error,
            ])
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'status' => 'success',
                'data' => [],
            ]);

        $bot->refresh();
        $job->refresh();

        $this->assertEquals(BotStatusEnum::ERROR, $bot->status);
        $this->assertEquals($error, $bot->error_text);
        $this->assertEquals(JobStatusEnum::FAILED, $job->status);
    }

    /** @test */
    public function hostCannotSubmitErrorOnOfflineBot()
    {
        $bot = $this->bot()
            ->state(BotStatusEnum::OFFLINE)
            ->host($this->mainHost)
            ->create();

        $this
            ->withTokenFromHost($this->mainHost)
            ->botErrorCommand([
                'id' => $bot->id,
                'error' => 'ERROR TEXT',
            ])
            ->assertStatus(Response::HTTP_CONFLICT)
            ->assertExactJson(HostErrors::botStatusConflict()->toArray());

        $bot->refresh();

        $this->assertEquals(BotStatusEnum::OFFLINE, $bot->status);
        $this->assertNull($bot->error_text);
    }

    /** @test */
    public function botMustBelongToAHost()
    {
        $bot = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->create();

        $this
            ->withTokenFromHost($this->mainHost)
            ->botErrorCommand([
                'id' => $bot->id,
                'error' => 'ERROR TEXT',
            ])
            ->assertStatus(Response::HTTP_CONFLICT)
            ->assertExactJson(HostErrors::botHasNoHost()->toArray());
    }

    /** @test */
    public function botMustBelongToHostMakingRequest()
    {
        $bot = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->host($this->host()->create())
            ->create();

        $error = 'ERROR TEXT';

        $this
            ->withTokenFromHost($this->mainHost)
            ->botErrorCommand([
                'id' => $bot->id,
                'error' => $error,
            ])
            ->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertExactJson(HostErrors::botIsNotAssignedToThisHost()->toArray());

        $bot->refresh();

        $this->assertEquals(BotStatusEnum::IDLE, $bot->status);
        $this->assertNull($bot->error_text);
    }

    /** @test */
    public function missingIdFieldThrowsException()
    {
        $error = 'ERROR TEXT';

        $this
            ->withTokenFromHost($this->mainHost)
            ->botErrorCommand([
                'error' => $error,
            ])
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertExactJson(HostErrors::missingParameter('id')->toArray());
    }

    /** @test */
    public function missingErrorFieldThrowsException()
    {
        $bot = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->create();

        $this
            ->withTokenFromHost($this->mainHost)
            ->botErrorCommand([
                'id' => $bot->id,
            ])
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertExactJson(HostErrors::missingParameter('error')->toArray());
    }
}
