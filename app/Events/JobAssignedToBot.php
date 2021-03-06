<?php

namespace App\Events;

use App\Models\Bot;
use App\Models\Job;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class JobAssignedToBot extends Event implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    /**
     * @var Job
     */
    public $job;
    /**
     * @var Bot
     */
    public $bot;

    /**
     * Create a new event instance.
     *
     * @param Job $job
     * @param Bot $bot
     */
    public function __construct(Job $job, Bot $bot)
    {
        $this->job = $job;
        $this->bot = $bot;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return $this
            ->userChannel($this->job->creator_id)
            ->userChannel($this->bot->creator_id)
            ->hostChannel($this->bot->host_id)
            ->jobChannel($this->job)
            ->botChannel($this->bot)
            ->channels();
    }
}
