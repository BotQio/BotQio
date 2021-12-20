<?php

namespace Tests\Feature\Broadcasting;


use App\Models\Bot;
use App\Models\Host;
use App\Models\Job;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\TestResponse;
use Tests\Helpers\PassportHelper;
use Tests\TestCase;

abstract class BroadcastTestCase extends TestCase
{
    use PassportHelper;
    use WithFaker;

    private $nameMap = [
        Bot::class => 'bots',
        Host::class => 'hosts',
        Job::class => 'jobs',
        User::class => 'users',
    ];

    /**
     * @param Model|string $model
     * @param string|null $modelId
     * @return TestResponse
     * @throws Exception
     */
    public function authPrivateChannel($model, string $modelId = null): TestResponse
    {
        if ($model instanceof Model) {
            if (!is_null($modelId)) {
                throw new Exception("Cannot specify both model instance and ID");
            }

            $modelClass = get_class($model);
            $modelName = $this->nameMap[$modelClass];
            $modelId = $model->getKey();
        } else {
            $modelName = $this->nameMap[$model];
        }

        $channelName = "private-{$modelName}.{$modelId}";
        $socketId = "{$this->faker->numberBetween()}.{$this->faker->numberBetween()}";

        return $this
            ->post('/broadcasting/auth', [
                'socket_id' => $socketId,
                'channel_name' => $channelName,
            ]);
    }
}