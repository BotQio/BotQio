<?php

namespace Tests\Unit;

use App;
use App\Models\Bot;
use App\Models\Cluster;
use App\Enums\BotStatusEnum;
use App\Rules\MatchExists;
use Illuminate\Support\Facades\Validator;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

class MatchExistsTest extends TestCase
{
    /** @test */
    public function matchingOnModelIdAttribute()
    {
        $bot = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->create();

        $fieldValue = 'foo_'.$bot->id;
        $fields = [
            'field' => $fieldValue,
        ];

        $matchExists = new MatchExists([
            'foo_{id}' => App\Models\Bot::class,
        ]);

        $validator = Validator::make($fields, [
            'field' => $matchExists,
        ]);

        $this->assertTrue($validator->passes());

        $model = $matchExists->getModel($fieldValue);
        $this->assertEquals($bot->id, $model->id);
        $this->assertInstanceOf(Bot::class, $model);
    }

    /** @test */
    public function whenNothingMatches()
    {
        $fieldValue = 'foo_'.(Uuid::uuid4());
        $fields = [
            'field' => $fieldValue,
        ];

        $matchExists = new MatchExists([
            'foo_{id}' => App\Models\Bot::class,
        ]);

        $validator = Validator::make($fields, [
            'field' => $matchExists,
        ]);

        $this->assertFalse($validator->passes());
        $this->assertNull($matchExists->getModel($fieldValue));
    }

    /** @test */
    public function multipleFieldMatchesWithSameModel()
    {
        $bot = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->create();

        $fieldValue = 'bar_'.$bot->id;
        $fields = [
            'field' => $fieldValue,
        ];

        $matchExists = new MatchExists([
            'foo_{id}' => App\Models\Bot::class,
            'bar_{id}' => App\Models\Bot::class,
        ]);

        $validator = Validator::make($fields, [
            'field' => $matchExists,
        ]);

        $this->assertTrue($validator->passes());

        $model = $matchExists->getModel($fieldValue);
        $this->assertEquals($bot->id, $model->id);
        $this->assertInstanceOf(Bot::class, $model);
    }

    /** @test */
    public function multipleFieldMatchesWithDifferentModel()
    {
        $cluster = $this->cluster()->create();

        $fieldValue = 'bar_'.$cluster->id;
        $fields = [
            'field' => $fieldValue,
        ];

        $matchExists = new MatchExists([
            'foo_{id}' => App\Models\Bot::class,
            'bar_{id}' => App\Models\Cluster::class,
        ]);

        $validator = Validator::make($fields, [
            'field' => $matchExists,
        ]);

        $this->assertTrue($validator->passes());

        $model = $matchExists->getModel($fieldValue);
        $this->assertEquals($cluster->id, $model->id);
        $this->assertInstanceOf(Cluster::class, $model);
    }

    /** @test */
    public function fieldMatchesWithScope()
    {
        $bot = $this->bot()
            ->state(BotStatusEnum::IDLE)
            ->create();

        $this->actingAs($this->mainUser);

        $fieldValue = 'foo_'.$bot->id;

        $fields = [
            'field' => $fieldValue,
        ];

        $matchExists = new MatchExists([
            'foo_{id}' => App\Models\Bot::mine(),
        ]);

        $validator = Validator::make($fields, [
            'field' => $matchExists,
        ]);

        $this->assertTrue($validator->passes());

        $model = $matchExists->getModel($fieldValue);
        $this->assertEquals($bot->id, $model->id);
        $this->assertInstanceOf(Bot::class, $model);
    }
}
