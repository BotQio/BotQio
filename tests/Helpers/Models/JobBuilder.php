<?php

namespace Tests\Helpers\Models;

use App\Models\Bot;
use App\Models\Cluster;
use App\Models\File;
use App\Models\Job;
use Carbon\Carbon;

class JobBuilder
{
    private $attributes;

    public function __construct($attributes = [])
    {
        $this->attributes = $attributes;
    }

    /**
     * @return Job
     */
    public function create()
    {
        return Job::unguarded(function () {
            /** @var Job $job */
            $job = Job::create($this->attributes);

            if(! is_null($job->bot_id)) {
                /** @var Bot $bot */
                $bot = Bot::find($job->bot_id);
                $bot->current_job_id = $job->id;
                $bot->save();
            }

            return $job;
        });
    }

    private function newWith($newAttributes)
    {
        return new self(
            array_merge(
                $this->attributes,
                $newAttributes
            )
        );
    }

    public function creator(\App\Models\User $user)
    {
        return $this->newWith(['creator_id' => $user->id]);
    }

    public function name(string $name)
    {
        return $this->newWith(['name' => $name]);
    }

    public function state(string $state)
    {
        return $this->newWith(['status' => $state]);
    }

    /**
     * @param Bot|Cluster $worker
     * @return JobBuilder
     */
    public function worker($worker)
    {
        return $this->newWith([
            'worker_id' => $worker->id,
            'worker_type' => $worker->getMorphClass(),
        ]);
    }

    public function createdAt(Carbon $createdAt)
    {
        return $this->newWith(['created_at' => $createdAt]);
    }

    public function bot(Bot $bot)
    {
        $builder = $this->newWith(['bot_id' => $bot->id]);

        if (! array_key_exists('worker_id', $this->attributes)) {
            $builder = $builder->worker($bot);
        }

        return $builder;
    }

    public function file(File $file)
    {
        return $this->newWith(['file_id' => $file->id]);
    }
}
